<?php
session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';
require_once '../history/log.php';

verificarSessao();

$id_tarefa  = (int) ($_GET['id'] ?? 0);
$id_usuario = (int) $_SESSION['id_usuario'];
$cargo      = $_SESSION['cargo'] ?? 'membro';

if ($id_tarefa <= 0) {
    header('Location: ../dashboard/index.php');
    exit;
}

$conn = conectar();

$stmt_tarefa = $conn->prepare(
    "SELECT t.*,
            u_criador.nome    AS nome_criador,
            u_resp.nome       AS nome_responsavel,
            u_resp.posicao    AS posicao_responsavel,
            u_resp.avatar     AS avatar_responsavel
     FROM tarefas t
     JOIN usuarios u_criador ON u_criador.id = t.criado_por
     JOIN usuarios u_resp    ON u_resp.id    = t.designado_para
     WHERE t.id = ?"
);
$stmt_tarefa->bind_param('i', $id_tarefa);
$stmt_tarefa->execute();
$resultado = $stmt_tarefa->get_result();
$tarefa    = $resultado->fetch_assoc();
$stmt_tarefa->close();

if (!$tarefa) {
    $conn->close();
    header('Location: ../dashboard/index.php');
    exit;
}

$msg_sucesso = '';
$msg_erro    = '';

if (isset($_GET['sucesso'])) {
    if ($_GET['sucesso'] === 'comentario_adicionado') {
        $msg_sucesso = 'Comentário adicionado com sucesso!';
    }
}
if (isset($_GET['erro'])) {
    if ($_GET['erro'] === 'comentario_vazio') {
        $msg_erro = 'O comentário não pode estar vazio.';
    } elseif ($_GET['erro'] === 'falha_comentario') {
        $msg_erro = 'Erro ao salvar o comentário. Tente novamente.';
    }
}

$stmt_com = $conn->prepare(
    "SELECT c.conteudo, c.criado_em, u.nome, u.avatar
     FROM comentarios c
     JOIN usuarios u ON u.id = c.id_usuario
     WHERE c.id_tarefa = ?
     ORDER BY c.criado_em DESC"
);
$stmt_com->bind_param('i', $id_tarefa);
$stmt_com->execute();
$comentarios = $stmt_com->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_com->close();

$stmt_hist = $conn->prepare(
    "SELECT h.campo_atualizado, h.valor_antigo, h.valor_novo, h.atualizado_em, u.nome
     FROM historico_tarefas h
     JOIN usuarios u ON u.id = h.atualizado_por
     WHERE h.id_tarefa = ?
     ORDER BY h.atualizado_em DESC"
);
$stmt_hist->bind_param('i', $id_tarefa);
$stmt_hist->execute();
$historico = $stmt_hist->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_hist->close();

$conn->close();

function labelStatus(string $status): string {
    return match($status) {
        'pendente'     => 'Pendente',
        'em_andamento' => 'Em andamento',
        'concluida'    => 'Concluída',
        default        => ucfirst($status),
    };
}

function labelCampo(string $campo): string {
    return match($campo) {
        'titulo'        => 'Título',
        'description'   => 'Descrição',
        'prazo'         => 'Prazo',
        'status'        => 'Status',
        'designado_para'=> 'Responsável',
        'criacao'       => 'Criação',
        default         => ucfirst($campo),
    };
}

function fmtData(string $datetime): string {
    $dt = new DateTime($datetime);
    return $dt->format('d/m/Y H:i');
}

$prazo_vencido = false;
if (!empty($tarefa['prazo']) && $tarefa['status'] !== 'concluida') {
    $prazo_vencido = (new DateTime($tarefa['prazo'])) < (new DateTime('today'));
}

$pode_editar  = ($cargo === 'admin' || (int)$tarefa['criado_por'] === $id_usuario);
$pode_excluir = ($cargo === 'admin');

$titulo_pagina = 'Detalhes da Tarefa';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?> — Palmeiras FC</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/task.css">
    
    <style>
        .view-container { max-width: 800px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .view-cabecalho__topo { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--verde); padding-bottom: 10px; margin-bottom: 20px; }
        .view-titulo { color: var(--verde-escuro); margin: 0; }
        .view-status { padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
        .status-pendente { background: #FAEEDA; color: #633806; }
        .status-em_andamento { background: #E6F1FB; color: #0C447C; }
        .status-concluida { background: #EAF3DE; color: #27500A; }
        .view-acoes { margin-bottom: 20px; display: flex; gap: 10px; }
        .view-info { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; }
        .view-info__label { font-size: 0.85rem; color: #777; text-transform: uppercase; font-weight: bold; }
        .view-info__linha { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; background: var(--cinza); padding: 15px; border-radius: 8px; }
        .view-secao { margin-bottom: 30px; }
        .view-secao__titulo { font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 15px; color: var(--verde-escuro); }
        .comentario-item, .historico-item { padding: 15px; border-bottom: 1px solid #eee; }
        .comentario-item__header, .historico-item__descricao { margin-bottom: 8px; }
        .comentario-textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px; font-family: inherit; }
        .view-rodape { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<main class="view-container">

    <div class="view-cabecalho">
        <div class="view-cabecalho__topo">
            <h2 class="view-titulo"><?= htmlspecialchars($tarefa['titulo']) ?></h2>
            <span class="view-status status-<?= $tarefa['status'] ?>">
                <?= labelStatus($tarefa['status']) ?>
            </span>
        </div>

        <?php if ($pode_editar || $pode_excluir): ?>
        <div class="view-acoes">
            <?php if ($pode_editar): ?>
                <a href="../tasks/edit.php?id=<?= $id_tarefa ?>" class="btn btn--verde">Editar</a>
            <?php endif; ?>
            <?php if ($pode_excluir): ?>
                <a href="../tasks/delete.php?id=<?= $id_tarefa ?>" class="btn btn--vermelho">Excluir</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <section class="view-info">

        <?php if (!empty($tarefa['description'])): ?>
        <div class="view-info__grupo">
            <span class="view-info__label">Descrição</span>
            <p class="view-info__texto"><?= nl2br(htmlspecialchars($tarefa['description'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="view-info__linha">

            <div class="view-info__grupo">
                <span class="view-info__label">Responsável</span>
                <div class="view-responsavel">
                    <div>
                        <strong><?= htmlspecialchars($tarefa['nome_responsavel']) ?></strong>
                        <?php if (!empty($tarefa['posicao_responsavel'])): ?>
                            <br><small><?= htmlspecialchars($tarefa['posicao_responsavel']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="view-info__grupo">
                <span class="view-info__label">Criado por</span>
                <p class="view-info__texto"><?= htmlspecialchars($tarefa['nome_criador']) ?></p>
            </div>

            <div class="view-info__grupo">
                <span class="view-info__label">Prazo</span>
                <p class="view-info__texto <?= $prazo_vencido ? 'prazo-vencido' : '' ?>">
                    <?php if (!empty($tarefa['prazo'])): ?>
                        <?= (new DateTime($tarefa['prazo']))->format('d/m/Y') ?>
                        <?php if ($prazo_vencido): ?>
                            <span style="color: red; font-weight: bold;">(Vencido)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        Sem prazo definido
                    <?php endif; ?>
                </p>
            </div>

            <div class="view-info__grupo">
                <span class="view-info__label">Criado em</span>
                <p class="view-info__texto"><?= fmtData($tarefa['criado_em']) ?></p>
            </div>

        </div>
    </section>

    <section class="view-secao">
        <h3 class="view-secao__titulo">
            Comentários
            <span class="view-secao__qtd">(<?= count($comentarios) ?>)</span>
        </h3>

        <?php if (!empty($msg_sucesso)): ?>
            <p class="task-msg task-msg--sucesso"><?= htmlspecialchars($msg_sucesso) ?></p>
        <?php endif; ?>
        <?php if (!empty($msg_erro)): ?>
            <p class="task-msg task-msg--erro"><?= htmlspecialchars($msg_erro) ?></p>
        <?php endif; ?>

        <form class="comentario-form" action="../comments/add.php" method="POST">
            <input type="hidden" name="id_tarefa" value="<?= $id_tarefa ?>">
            <textarea
                name="conteudo"
                class="comentario-textarea"
                rows="3"
                placeholder="Escreva um comentário…"
                required
            ></textarea>
            <button type="submit" class="btn btn--verde">Comentar</button>
        </form>

        <?php if (empty($comentarios)): ?>
            <p style="color: #777; margin-top: 15px;">Nenhum comentário ainda. Seja o primeiro!</p>
        <?php else: ?>
            <div class="comentarios-lista">
                <?php foreach ($comentarios as $com): ?>
                <div class="comentario-item">
                    <div class="comentario-item__header">
                        <strong><?= htmlspecialchars($com['nome']) ?></strong>
                        <span style="color: #777; font-size: 0.85rem; margin-left: 10px;"><?= fmtData($com['criado_em']) ?></span>
                    </div>
                    <p class="comentario-item__texto"><?= nl2br(htmlspecialchars($com['conteudo'])) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="view-secao">
        <h3 class="view-secao__titulo">
            Histórico de Alterações
            <span class="view-secao__qtd">(<?= count($historico) ?>)</span>
        </h3>

        <?php if (empty($historico)): ?>
            <p style="color: #777;">Nenhuma alteração registrada ainda.</p>
        <?php else: ?>
            <div class="historico-lista">
                <?php foreach ($historico as $h): ?>
                <div class="historico-item">
                    <div class="historico-item__corpo">
                        <p class="historico-item__descricao">
                            <strong><?= htmlspecialchars($h['nome']) ?></strong>
                            alterou
                            <strong><?= labelCampo($h['campo_atualizado']) ?></strong>

                            <?php if (!empty($h['valor_antigo'])): ?>
                                de <span>"<?= htmlspecialchars($h['valor_antigo']) ?>"</span>
                            <?php endif; ?>

                            <?php if (!empty($h['valor_novo'])): ?>
                                para <span>"<?= htmlspecialchars($h['valor_novo']) ?>"</span>
                            <?php endif; ?>
                        </p>
                        <span style="color: #777; font-size: 0.85rem;"><?= fmtData($h['atualizado_em']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="view-rodape">
        <a href="../dashboard/index.php" class="btn btn--cinza">← Voltar ao Dashboard</a>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>