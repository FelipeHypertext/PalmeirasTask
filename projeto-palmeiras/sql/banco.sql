-- ============================================================
-- RESPONSÁVEL : João
-- ORDEM       : #1 — Fazer primeiro (todos dependem deste arquivo)
-- DESCRIÇÃO   : Criação do banco de dados e todas as tabelas
-- COMO USAR   : Abra o phpMyAdmin > aba SQL > cole este arquivo
-- ============================================================

CREATE DATABASE IF NOT EXISTS palmeirasdb
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

USE palmeirasdb;

-- ----------------------------------------------------------
-- TABELA: Usuarios
-- Armazena técnico (admin) e jogadores (membro)
-- ----------------------------------------------------------
CREATE TABLE usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    senha   VARCHAR(255)  NOT NULL,
    cargo       ENUM('admin', 'membro') DEFAULT 'membro',
    posicao   VARCHAR(100),           -- Ex: Atacante, Goleiro
    avatar     VARCHAR(255),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------
-- TABELA: Tarefas
-- Armazena as tarefas do Gerenciador
-- ----------------------------------------------------------
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
-- UPDATE CURRENT_TIMESTAMP atualiza automaticamente a coluna quando houver ação UPDATE

-- ----------------------------------------------------------
-- TABELA: Comentários
-- Comentários vinculados a uma tarefa
-- ----------------------------------------------------------
CREATE TABLE comentarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    id_tarefa    INT  NOT NULL,
    id_usuario    INT  NOT NULL,
    conteudo    TEXT NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tarefa) REFERENCES tarefas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
-- DELETE CASCADE é uma regra da chave estrangeira, na qual a tabela pai seja apagada essa tabela também sumirá.

-- ----------------------------------------------------------
-- TABELA: Histórico das tarefas
-- Histórico de cada alteração feita em uma tarefa
-- ----------------------------------------------------------
CREATE TABLE historico_tarefas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_tarefa       INT          NOT NULL,
    atualizado_por    INT          NOT NULL,
    campo_atualizado VARCHAR(100) NOT NULL,   -- Ex: 'status', 'title'
    valor_antigo     TEXT,
    valor_novo     TEXT,
    atualizado_em    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tarefa)   REFERENCES tarefas(id) ON DELETE CASCADE,
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
);

-- Área de Teste

-- ----------------------------------------------------------
-- DADOS INICIAIS
-- Admin padrão — senha: admin123
-- ⚠️ Gere o hash real rodando no terminal PHP:
--    php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
-- Depois substitua o hash abaixo pelo gerado
-- ----------------------------------------------------------
-- INSERT INTO usuarios (nome, email, senha, cargo, posicao) VALUES
-- ('Técnico Abel', 'admin@palmeiras.com',
-- '$2y$10$SUBSTITUA_ESTE_HASH_PELO_GERADO_NO_PHP', 'admin', 'Técnico');
