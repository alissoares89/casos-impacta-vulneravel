<?php
// Configurações do Banco de Dados - ATENÇÃO: Vulnerável por design

// Em um ambiente real, use variáveis de ambiente ou um arquivo de configuração seguro.
// Para a demonstração, as credenciais estão hardcoded (MÁ PRÁTICA DE SEGURANÇA).

// Use as variáveis de ambiente se disponíveis (padrão do Azure App Service)
$db_host = getenv('DB_HOST') ?: 'localhost'; // Ou o host do seu MySQL
$db_name = getenv('DB_NAME') ?: 'casos_impacta_db';
$db_user = getenv('DB_USERNAME') ?: 'root'; // Usuário do BD
$db_pass = getenv('DB_PASSWORD') ?: ''; // Senha do BD - Deixe em branco se não houver senha local

// Tentativa de conexão
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Checar conexão
if ($conn->connect_error) {
    // Em produção, logar o erro em vez de exibir diretamente
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Definir charset para UTF-8 para suportar caracteres especiais
$conn->set_charset("utf8mb4");

// Função para fechar a conexão (opcional, PHP geralmente fecha automaticamente no fim do script)
function close_db_connection($conn) {
    $conn->close();
}

// Nota: Este arquivo será incluído em outras páginas que precisam de acesso ao BD.
?>
