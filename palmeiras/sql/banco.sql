-- Banco de dados: Palmeiras FC — Gerenciador de Tarefas
-- Projeto Final PHP — MVC + PDO + Sessões + Cookies + CSRF

CREATE DATABASE IF NOT EXISTS palmeirasdb
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

USE palmeirasdb;

CREATE TABLE usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    senha           VARCHAR(255)  NOT NULL,
    cpf             CHAR(11)      NOT NULL,
    data_nascimento DATE          NOT NULL,
    cargo           ENUM('admin', 'membro') DEFAULT 'membro',
    posicao         VARCHAR(100)  DEFAULT NULL,
    token_lembrar   VARCHAR(64)   DEFAULT NULL
);

CREATE TABLE tarefas (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    titulo         VARCHAR(200)  NOT NULL,
    descricao      TEXT,
    status         ENUM('pendente', 'em_andamento', 'concluida') DEFAULT 'pendente',
    prazo          DATE          NOT NULL,
    criado_por     INT           NOT NULL,
    designado_para INT           NOT NULL,
    FOREIGN KEY (criado_por)     REFERENCES usuarios(id),
    FOREIGN KEY (designado_para) REFERENCES usuarios(id)
);

CREATE TABLE noticias (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    titulo     VARCHAR(200)  NOT NULL,
    conteudo   TEXT          NOT NULL,
    criado_por INT           NOT NULL,
    criado_em  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id)
);

-- -------------------------------------------------------
-- Admin padrão — senha: admin123  CPF: 00000000000  nascimento: 1990-01-01
-- Gere o hash rodando: php -r "echo password_hash('admin123', PASSWORD_ARGON2ID);"
-- Depois substitua HASH_AQUI e descomente as linhas abaixo.
-- -------------------------------------------------------
-- INSERT INTO usuarios (nome, email, senha, cpf, data_nascimento, cargo, posicao) VALUES
-- ('Técnico Abel', 'admin@palmeiras.com', 'HASH_AQUI', '00000000000', '1972-10-01', 'admin', 'Técnico');
