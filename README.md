# 🟢 Gerenciador de Tarefas — Palmeiras FC
> Trabalho PHP — Prof. João Paulo Nunes da Silva
> Equipe: 6 integrantes | Sistema Kanban com autenticação e histórico de alterações

---

## 📁 Árvore de Pastas e Arquivos

```


palmeiras-tasks/
│
├── index.php                        # Redireciona para login ou dashboard
│
├── config/
│   └── db.php                       # Conexão com o banco via XAMPP (MySQLi)
│
├── auth/
│   ├── login.php                    # Página e lógica de login
│   ├── logout.php                   # Encerra sessão
│   └── register.php                 # Cadastro (apenas admin pode cadastrar jogadores)
│
├── dashboard/
│   └── index.php                    # Página principal (Kanban board)
│
├── tasks/
│   ├── create.php                   # Criar nova tarefa
│   ├── edit.php                     # Editar tarefa
│   ├── delete.php                   # Excluir tarefa (apenas admin)
│   ├── update_status.php            # Atualizar status (pendente/andamento/concluída)
│   ├── view.php                     # Detalhes da tarefa (comentários + histórico)
│   └── filter.php                   # Filtro de tarefas por usuário/status/data
│
├── comments/
│   └── add.php                      # Adicionar comentário em uma tarefa
│
├── history/
│   └── log.php                      # Registrar alterações no histórico
│
├── admin/
│   ├── users.php                    # Listar/gerenciar jogadores (somente admin)
│   └── edit_user.php                # Editar dados de um usuário
│
├── assets/
│   ├── css/
│   │   ├── style.css                # Estilos globais
│   │   ├── auth.css                 # Estilos das telas de login/cadastro
│   │   ├── dashboard.css            # Estilos do Kanban
│   │   └── task.css                 # Estilos dos cards de tarefa
│   └── img/
│       ├── logo_palmeiras.png       # Logo do Palmeiras
│       └── avatar_default.png       # Avatar padrão dos jogadores
│
├── includes/
│   ├── header.php                   # Header HTML reutilizável (nav + sessão)
│   ├── footer.php                   # Footer HTML reutilizável
│   └── session_check.php            # Verifica se usuário está logado
│
└── sql/
    └── banco.sql                    # Script SQL para criar o banco e as tabelas
```

---

## 🗄️ Banco de Dados — Conexão XAMPP

### `config/db.php`
```php
<?php

function conectar() {
    $conn = new mysqli("localhost", "root", "", "palmeirasdb");

    /*Caso dê algum erro na conexão*/
    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }
    /*Configura a codificação*/
    $conn->set_charset("utf8");
    return $conn;
}

?>
```

---

## 🗃️ Estrutura das Tabelas (`sql/banco.sql`)

```sql
CREATE DATABASE IF NOT EXISTS palmeirasdb
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

USE palmeirasdb;

-- TABELA: Usuarios
CREATE TABLE usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    senha   VARCHAR(255)  NOT NULL,
    cargo       ENUM('admin', 'membro') DEFAULT 'membro',
    posicao   VARCHAR(100),           -- Ex: Atacante, Goleiro
    avatar          VARCHAR(255)  DEFAULT NULL,
    lembrar_cookie  VARCHAR(64)   DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- TABELA: Tarefas
CREATE TABLE tarefas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo       VARCHAR(200) NOT NULL,
    description TEXT,
    status      ENUM('pendente', 'em_andamento', 'concluida') DEFAULT 'pendente',
    prazo    DATE,
    criado_por  INT NOT NULL,   -- FK → usuarios.id
    designado_para INT NOT NULL,   -- FK → usuarios.id
    criado_em  DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por)  REFERENCES usuarios(id),
    FOREIGN KEY (designado_para) REFERENCES usuarios(id)
);

-- TABELA: Comentários
CREATE TABLE comentarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    id_tarefa    INT  NOT NULL,
    id_usuario    INT  NOT NULL,
    conteudo    TEXT NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tarefa) REFERENCES tarefas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- TABELA: Histórico das tarefas
CREATE TABLE historico_tarefas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_tarefa       INT          NOT NULL,
    atualizado_por    INT          NOT NULL,
    campo_atualizado VARCHAR(100) NOT NULL,   -- Ex: 'status', 'titulo'
    valor_antigo     TEXT,
    valor_novo     TEXT,
    atualizado_em    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tarefa)   REFERENCES tarefas(id) ON DELETE CASCADE,
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
);

-- Usuário admin padrão (técnico — senha: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Técnico Abel', 'admin@palmeiras.com', '$2y$10$e0NRp4c9H1Q1QzY.example_hash', 'admin');
-- ⚠️ Gere o hash real com: echo password_hash('admin123', PASSWORD_DEFAULT);
```

---

## 👥 Divisão de Tarefas — Equipe de 6 Pessoas

> Cada membro é responsável pelos arquivos listados na sua célula.
> A integração final (testes, bugfix, CSS global) é responsabilidade coletiva.

---

### 🔴 Membro João — Banco de Dados + Config + Estrutura base
**Responsabilidade:** Fundação do projeto — sem isso ninguém trabalha.

| Arquivo | Descrição |
|---|---|
| `sql/banco.sql` | Criar todas as tabelas e o admin padrão |
| `config/db.php` | Conexão com XAMPP |
| `includes/session_check.php` | Função que protege páginas com login |
| `includes/header.php` | Header global com nav e dados de sessão |
| `includes/footer.php` | Footer global |
| `index.php` | Redireciona para login ou dashboard conforme sessão |

---

### 🟠 Membro Victor — Autenticação (Login, Logout, Cadastro)
**Responsabilidade:** Sistema de acesso seguro com sessões/cookies.

| Arquivo | Descrição |
|---|---|
| `auth/login.php` | Formulário + lógica de login com `session_start()` |
| `auth/logout.php` | Destroi sessão e redireciona |
| `auth/register.php` | Cadastro de jogadores (restrito ao admin) |
| `assets/css/auth.css` | Estilo das telas de autenticação (tema verde Palmeiras) |

**Detalhes importantes:**
- Usar `password_hash()` para salvar senhas e `password_verify()` no login
- Salvar `$_SESSION['user_id']`, `$_SESSION['user_name']`, `$_SESSION['role']`
- Cookie de "lembrar-me" com `setcookie()` é um diferencial

---

### 🟡 Membro Max — Criação e Edição de Tarefas
**Responsabilidade:** CRUD principal das tarefas.

| Arquivo | Descrição |
|---|---|
| `tasks/create.php` | Formulário para criar tarefa (título, descrição, prazo, responsável) |
| `tasks/edit.php` | Editar tarefa existente |
| `tasks/delete.php` | Excluir tarefa (só admin) |
| `assets/css/task.css` | Estilos dos cards e formulários de tarefa |

**Detalhes importantes:**
- Ao criar/editar, chamar a função de histórico em `history/log.php`
- Listar jogadores ativos no `<select>` de "atribuir para"
- Validar permissões: só o criador ou o admin pode editar

---

### 🟢 Membro Israel — Dashboard Kanban + Filtros
**Responsabilidade:** Coração visual do sistema.

| Arquivo | Descrição |
|---|---|
| `dashboard/index.php` | Kanban com 3 colunas: Pendente / Em andamento / Concluída |
| `tasks/filter.php` | Filtros por responsável, status e data limite |
| `tasks/update_status.php` | Mover card entre colunas (atualiza status via POST) |
| `assets/css/dashboard.css` | Layout Kanban, cores por status, responsividade |

**Detalhes importantes:**
- Cards arrastáveis são um diferencial visual (JS puro, sem biblioteca)
- Exibir foto/avatar e nome do responsável em cada card
- Mostrar badge de prazo vencido em vermelho

---

### 🔵 Membro Eduarda — Comentários + Histórico de Alterações
**Responsabilidade:** Comunicação da equipe e rastreabilidade.

| Arquivo | Descrição |
|---|---|
| `tasks/view.php` | Página de detalhes da tarefa com comentários e histórico |
| `comments/add.php` | Adicionar comentário (processa POST e redireciona) |
| `history/log.php` | Função `logHistory($conn, $task_id, $field, $old, $new)` |

**Detalhes importantes:**
- `log.php` é uma função utilitária chamada pelos outros membros (não uma página)
- Exibir histórico em ordem decrescente (mais recente primeiro)
- Formatar datas em português: `d/m/Y H:i`

---

### 🟣 Membro Douglas — Painel Admin + CSS Global + Integração Final
**Responsabilidade:** Gestão de usuários e acabamento visual do projeto.

| Arquivo | Descrição |
|---|---|
| `admin/users.php` | Listar todos os jogadores (nome, posição, e-mail, nº de tarefas) |
| `admin/edit_user.php` | Editar dados de um jogador |
| `assets/css/style.css` | CSS global: variáveis, tipografia, reset, cores Palmeiras |
| `assets/img/` | Reunir e padronizar imagens (logo, avatares) |

**Detalhes importantes:**
- Definir variáveis CSS: `--verde: #006B3F; --branco: #FFFFFF; --cinza: #f4f4f4;`
- Garantir que todos os arquivos incluam `header.php` e `footer.php` corretamente
- Revisar HTML semântico (`<main>`, `<section>`, `<article>`, `<nav>`, `<aside>`)
- Testar fluxo completo: cadastro → login → criar tarefa → comentar → logout

---

## 🔗 Fluxo Geral do Sistema

```
index.php
   └── (não logado) → auth/login.php
           └── (logado) → dashboard/index.php (Kanban)
                   ├── tasks/create.php      → cria tarefa + loga histórico
                   ├── tasks/view.php        → comentários + histórico
                   ├── tasks/update_status.php → move card + loga histórico
                   └── admin/ (só admin)
                           ├── users.php
                           └── edit_user.php
```

---

## ✅ Checklist de Entrega

- [ ] Banco criado e populado (`banco.sql` importado no phpMyAdmin)
- [ ] Login/Logout funcionando com sessão
- [ ] Cadastro de jogadores restrito ao admin
- [ ] Criação de tarefa com atribuição
- [ ] Kanban com 3 colunas e filtros
- [ ] Atualização de status com validação de permissão
- [ ] Comentários por tarefa
- [ ] Histórico de alterações registrado e exibido
- [ ] CSS com identidade visual do Palmeiras (verde #006B3F)
- [ ] HTML semântico em todos os arquivos
- [ ] Zero bibliotecas externas (sem Bootstrap, sem jQuery)

---

## 🚀 Como Rodar Localmente (XAMPP)

1. Instale o [XAMPP](https://www.apachefriends.org/) e inicie **Apache** e **MySQL**
2. Copie a pasta `palmeiras-tasks/` para `C:/xampp/htdocs/`
3. Abra o **phpMyAdmin** em `http://localhost/phpmyadmin`
4. Crie o banco e importe o arquivo `sql/banco.sql`
5. Acesse `http://localhost/palmeiras-tasks/` no navegador
6. Login padrão do técnico: `admin@palmeiras.com` / `admin123`

---

*Bom trabalho, equipe! Avanti Palestra! 🟢*
