-- ============================================================
-- RESPONSÁVEL : João
-- ORDEM       : #1 — Fazer primeiro (todos dependem deste arquivo)
-- DESCRIÇÃO   : Criação do banco de dados e todas as tabelas
-- COMO USAR   : Abra o phpMyAdmin > aba SQL > cole este arquivo
-- ============================================================

CREATE DATABASE IF NOT EXISTS palmeiras_tasks
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

USE palmeiras_tasks;

-- ----------------------------------------------------------
-- TABELA: users
-- Armazena técnico (admin) e jogadores (member)
-- ----------------------------------------------------------
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('admin', 'member') DEFAULT 'member',
    position   VARCHAR(100),           -- Ex: Atacante, Goleiro
    avatar     VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------
-- TABELA: tasks
-- Armazena as tarefas do Kanban
-- ----------------------------------------------------------
CREATE TABLE tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    status      ENUM('pendente', 'em_andamento', 'concluida') DEFAULT 'pendente',
    deadline    DATE,
    created_by  INT NOT NULL,   -- FK → users.id
    assigned_to INT NOT NULL,   -- FK → users.id
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by)  REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- ----------------------------------------------------------
-- TABELA: comments
-- Comentários vinculados a uma tarefa
-- ----------------------------------------------------------
CREATE TABLE comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    task_id    INT  NOT NULL,
    user_id    INT  NOT NULL,
    content    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ----------------------------------------------------------
-- TABELA: task_history
-- Histórico de cada alteração feita em uma tarefa
-- ----------------------------------------------------------
CREATE TABLE task_history (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    task_id       INT          NOT NULL,
    changed_by    INT          NOT NULL,
    field_changed VARCHAR(100) NOT NULL,   -- Ex: 'status', 'title'
    old_value     TEXT,
    new_value     TEXT,
    changed_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id)   REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- ----------------------------------------------------------
-- DADOS INICIAIS
-- Admin padrão — senha: admin123
-- ⚠️ Gere o hash real rodando no terminal PHP:
--    php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
-- Depois substitua o hash abaixo pelo gerado
-- ----------------------------------------------------------
INSERT INTO users (name, email, password, role, position) VALUES
('Técnico Abel', 'admin@palmeiras.com',
 '$2y$10$SUBSTITUA_ESTE_HASH_PELO_GERADO_NO_PHP', 'admin', 'Técnico');
