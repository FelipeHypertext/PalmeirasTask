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