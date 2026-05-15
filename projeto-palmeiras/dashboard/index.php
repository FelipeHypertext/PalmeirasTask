<?php
session_start();
require_once("../config/db.php");
require_once("../includes/session_check.php");

verificarSessao(); 

$conn = conectar();

$sql = "SELECT t.id, t.titulo, t.description as descr, t.status, t.prazo,
               u.nome as responsavel_nome, u.posicao,
               (SELECT COUNT(*) FROM comentarios c WHERE c.id_tarefa = t.id) as total_comentarios
        FROM tarefas t
        JOIN usuarios u ON t.designado_para = u.id";
$result = $conn->query($sql);

$tarefas_banco = [];
while ($row = $result->fetch_assoc()) {
    $colMap = [
        'pendente' => 'pending',
        'em_andamento' => 'progress',
        'concluida' => 'done'
    ];
    
    $tagMap = [
        'Técnico' => 'tecnico', 'Auxiliar Técnico' => 'auxtec',
        'Preparador Físico' => 'prepfi', 'Treinador de Goleiros' => 'prepgoleiro',
        'Analista de Desempenho' => 'analista', 'Coordenador Técnico' => 'coordenador'
    ];

    $tarefas_banco[] = [
        'id' => $row['id'],
        'col' => $colMap[$row['status']] ?? 'pending',
        'title' => $row['titulo'],
        'desc' => $row['descr'],
        'tag' => $tagMap[$row['posicao']] ?? 'tecnico', // Fallback de cor
        'priority' => 'med',
        'date' => $row['prazo'] ? date('d/m', strtotime($row['prazo'])) : '—',
        'comments' => $row['total_comentarios'],
        'authors' => [['i' => mb_strtoupper(mb_substr($row['responsavel_nome'], 0, 2)), 'c' => 'av-a']]
    ];
}
$conn->close();

$titulo_pagina = 'Dashboard Kanban — Palmeiras FC'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
    <link rel="stylesheet" href="style.css"> 
    
    <script>
        const TAREFAS_REAIS = <?= json_encode($tarefas_banco) ?>;
    </script>
    <script src="main.js" defer></script> 
</head>
<body>

<?php require_once("../includes/header.php"); ?>

<main class="page">
  <div class="page-header" style="margin-bottom: 2rem;">
    <div>
      <h2 style="color: var(--verde-escuro); text-transform: uppercase; font-weight: 800;">Gerenciador de tarefas</h2>
      <div class="page-sub" id="sprint-sub" style="color: var(--cinza-texto); font-weight: 500;">Carregando tarefas…</div>
    </div>
    
    <a href="../tasks/create.php" class="btn btn--verde" style="padding: 10px 20px; text-decoration: none;">
      <i class="ti ti-plus"></i> Nova Tarefa
    </a>
  </div>
 
  <div class="board">
    <div class="col" id="col-pending" ondragover="onDragOver(event,'pending')" ondrop="onDrop(event,'pending')" ondragleave="onDragLeave(event)">
      <div class="col-header">
        <div class="col-label"><span class="col-dot dot-pending"></span>Pendente</div>
        <div style="display:flex;align-items:center;gap:6px">
          <span class="col-badge badge-pending" id="cnt-pending">0</span>
          <a href="../tasks/create.php" class="col-add-btn" style="text-decoration:none;"><i class="ti ti-plus"></i></a>
        </div>
      </div>
      <div class="drop-zone" id="zone-pending"></div>
      <div id="cards-pending"></div>
    </div>

    <div class="col" id="col-progress" ondragover="onDragOver(event,'progress')" ondrop="onDrop(event,'progress')" ondragleave="onDragLeave(event)">
      <div class="col-header">
        <div class="col-label"><span class="col-dot dot-progress"></span>Em andamento</div>
        <div style="display:flex;align-items:center;gap:6px">
          <span class="col-badge badge-progress" id="cnt-progress">0</span>
        </div>
      </div>
      <div class="drop-zone" id="zone-progress"></div>
      <div id="cards-progress"></div>
    </div>

    <div class="col" id="col-done" ondragover="onDragOver(event,'done')" ondrop="onDrop(event,'done')" ondragleave="onDragLeave(event)">
      <div class="col-header">
        <div class="col-label"><span class="col-dot dot-done"></span>Concluída</div>
        <div style="display:flex;align-items:center;gap:6px">
          <span class="col-badge badge-done" id="cnt-done">0</span>
        </div>
      </div>
      <div class="drop-zone" id="zone-done"></div>
      <div id="cards-done"></div>
    </div>
  </div>
</main>

<?php require_once("../includes/footer.php"); ?>
</body>
</html>