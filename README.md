# Casos da Impacta - Portal de Notícias Vulnerável para Demonstração do Azure WAF

## Visão Geral

Este projeto consiste em um portal de notícias universitário fictício, chamado "Casos da Impacta", desenvolvido intencionalmente com vulnerabilidades de segurança web comuns: SQL Injection (SQLi), Cross-Site Scripting (XSS) e Cross-Site Request Forgery (CSRF).

O objetivo principal é servir como um ambiente de teste e demonstração para a eficácia do Azure Web Application Firewall (WAF) na detecção e mitigação desses tipos de ataques.

**ATENÇÃO:** Esta aplicação é insegura por design. **NÃO A UTILIZE EM AMBIENTES DE PRODUÇÃO** ou com dados sensíveis sem as devidas proteções (como o Azure WAF configurado em modo de prevenção).

## Tecnologias Utilizadas

*   **Linguagem**: PHP 8.1
*   **Banco de Dados**: MySQL (projetado para ser usado com o serviço "Banco de Dados do Azure para MySQL")
*   **Plataforma de Hospedagem Alvo**: Azure App Service (Linux)
*   **Proteção Alvo**: Azure WAF (implantado com Azure Application Gateway V2)

## Estrutura do Projeto

```
/casos-impacta
├── /admin
│   ├── painel.php           # Painel administrativo simulado (vulnerável a CSRF)
│   └── processar_acao.php   # Processa a ação do painel (sem token CSRF)
├── /assets
│   └── style.css            # Arquivo CSS básico (a ser criado se necessário)
├── /db
│   ├── db_config.php        # Configuração da conexão com o BD (lê variáveis de ambiente)
│   └── init.sql             # Script SQL para inicializar o banco de dados
├── /docs
│   ├── manual_instalacao.md # Manual detalhado para implantação no Azure e configuração/teste do WAF
│   └── referencias.docx     # Referências técnicas utilizadas (formato acadêmico)
├── /public
│   ├── index.php            # Página inicial, lista artigos
│   ├── artigo.php           # Exibe um artigo e seus comentários (exibição vulnerável a XSS)
│   ├── comentario.php       # Processa o envio de comentários (armazena XSS)
│   ├── login.php            # Formulário e processamento de login (vulnerável a SQL Injection)
│   └── logout.php           # Script simples para encerrar a sessão
└── README.md                # Este arquivo
```

## Funcionalidades e Vulnerabilidades

*   **Página Inicial (`/public/index.php`)**: Lista os títulos e trechos dos artigos mais recentes.
*   **Página do Artigo (`/public/artigo.php`)**: Exibe o conteúdo completo de um artigo e a seção de comentários.
    *   **Vulnerabilidade XSS (Stored)**: O conteúdo dos comentários (`comment_text`) é exibido diretamente na página sem sanitização adequada, permitindo a execução de scripts injetados.
*   **Envio de Comentário (`/public/comentario.php`)**: Processa o formulário de comentários.
    *   **Vulnerabilidade XSS (Stored)**: O texto do comentário é salvo no banco de dados sem sanitização, permitindo que scripts maliciosos sejam armazenados.
*   **Login (`/public/login.php`)**: Formulário de autenticação.
    *   **Vulnerabilidade SQL Injection**: A consulta SQL para verificar as credenciais do usuário é construída por concatenação direta da entrada do usuário, permitindo a injeção de código SQL malicioso para bypassar a autenticação.
*   **Painel Admin Simulado (`/admin/painel.php`)**: Página acessível após login que simula uma ação administrativa.
    *   **Vulnerabilidade CSRF**: O formulário para executar a ação simulada não inclui um token anti-CSRF, tornando a ação suscetível a ser disparada por uma requisição forjada de outro site.

## Como Usar

1.  **Leia o Manual de Instalação**: O arquivo `/docs/manual_instalacao.md` contém instruções passo a passo para:
    *   Implantar a aplicação no Azure App Service.
    *   Configurar o Banco de Dados do Azure para MySQL e importar o schema inicial.
    *   Configurar as variáveis de ambiente da aplicação.
    *   Criar e configurar um Azure Application Gateway com o WAF V2.
    *   Testar as vulnerabilidades (SQLi, XSS, CSRF) com e sem o WAF ativo (modos Detecção e Prevenção).
    *   Visualizar os logs do WAF.

2.  **Consulte as Referências**: O arquivo `/docs/referencias.docx` lista as fontes consultadas (OWASP, Microsoft, Fortinet) sobre as vulnerabilidades e tecnologias WAF.

## Objetivo Educacional

Este projeto visa fornecer um exemplo prático e funcional de como vulnerabilidades comuns podem ser exploradas e como uma solução de WAF como o Azure WAF pode ser configurada para proteger aplicações web contra esses ataques.

