-- Script de inicialização para o banco de dados casos_impacta_db

-- Criação do banco de dados (se não existir)
-- Em ambientes como o Azure App Service, o banco de dados geralmente é criado separadamente.
-- Este comando pode ser útil para configuração local.
-- CREATE DATABASE IF NOT EXISTS casos_impacta_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE casos_impacta_db;

-- Tabela de usuários
-- Vulnerabilidade: A senha não está hasheada, facilitando a visualização em caso de vazamento.
-- A lógica de login em PHP será vulnerável a SQL Injection.
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL -- Senha em texto plano para simplificar a demonstração
);

-- Tabela de artigos
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Tabela de comentários
-- Vulnerabilidade: O campo comment_text será usado para demonstrar XSS.
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL, -- Permitindo nomes não vinculados a usuários logados
    comment_text TEXT NOT NULL, -- Campo vulnerável a XSS
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id)
);

-- Inserção de dados de exemplo

-- Usuário administrador (vulnerável a SQL Injection no login)
INSERT INTO users (username, password) VALUES ('admin', 'senha123');
INSERT INTO users (username, password) VALUES ('editor', 'outrasenha');

-- Artigos de exemplo
INSERT INTO articles (title, content, author_id) VALUES
('Vulnerabilidades Web Comuns e Como o Azure WAF Pode Ajudar', 'Este artigo explora as vulnerabilidades mais comuns como SQL Injection, XSS e CSRF, e demonstra como o Web Application Firewall (WAF) do Azure oferece uma camada crucial de proteção...', 1),
('Novidades no Campus: Biblioteca Digital Expandida', 'A biblioteca da Impacta acaba de lançar uma expansão significativa em seu acervo digital, oferecendo acesso a milhares de novos e-books e periódicos...', 2),
('Guia de Sobrevivência para Calouros da Impacta', 'Chegou na Impacta? Não se preocupe! Preparamos um guia completo com dicas essenciais para você aproveitar ao máximo sua jornada acadêmica...', 2);

-- Comentários de exemplo (um deles com potencial XSS para teste inicial)
INSERT INTO comments (article_id, author_name, comment_text) VALUES
(1, 'Leitor Atento', 'Excelente artigo! Muito importante discutir segurança.'),
(1, 'Estudante Curioso', 'Como configuro o WAF na prática?'),
(2, 'Ana Silva', 'Ótima notícia sobre a biblioteca!'),
(1, 'Hacker Ético', '<script>alert("XSS Teste Inicial - Este comentário não deveria ser exibido assim!");</script>');

-- Fim do script

