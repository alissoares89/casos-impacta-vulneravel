<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["user_id"])) {
    // Se não estiver logado, não pode processar a ação
    header("HTTP/1.1 403 Forbidden");
    echo "Acesso negado. Faça login primeiro.";
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Método não permitido.";
    exit;
}

// --- PONTO VULNERÁVEL A CSRF ---
// Não há verificação de token CSRF aqui.
// Qualquer site pode forjar uma requisição POST para este endpoint,
// e se o usuário estiver logado no "Casos da Impacta", a ação será executada.
/*
// Exemplo de como seria a proteção (comentado para manter a vulnerabilidade):
if (!isset($_POST["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Erro de CSRF: Token inválido ou ausente.";
    // Logar a tentativa de CSRF
    exit;
}
*/

// Processar a ação simulada
if (isset($_POST["action"]) && $_POST["action"] === "delete_user_simulated") {
    // Aqui, em um sistema real, ocorreria a lógica de exclusão do usuário.
    // Para esta demonstração, apenas definimos uma mensagem de sucesso.
    $user_id_to_delete = $_POST["user_id_to_delete"] ?? 
        'desconhecido
    ';
    $_SESSION["action_message"] = "Ação simulada de exclusão para o usuário ID " . htmlspecialchars($user_id_to_delete) . " processada com sucesso (via CSRF se aplicável).";

    // Redirecionar de volta para o painel
    header("Location: painel.php");
    exit;

} else {
    // Ação desconhecida ou não especificada
    $_SESSION["action_message"] = "Erro: Ação desconhecida ou inválida.";
    header("Location: painel.php");
    exit;
}

?>
