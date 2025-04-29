<?php
require_once __DIR__ . 
    '/../db/db_config.php
'; // Inclui a configuração do banco de dados

session_start(); // Inicia a sessão (embora não seja estritamente necessário para adicionar comentário anônimo)

// Verifica se o método da requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Verifica se os dados necessários foram enviados
    if (isset($_POST["article_id"], $_POST["author_name"], $_POST["comment_text"])) {

        $article_id = $_POST["article_id"];
        $author_name = $_POST["author_name"];
        $comment_text = $_POST["comment_text"]; // PONTO VULNERÁVEL A XSS

        // Validação básica (poderia ser mais robusta)
        if (!is_numeric($article_id) || empty(trim($author_name)) || empty(trim($comment_text))) {
            echo "Erro: Dados inválidos.";
            // Considerar redirecionar de volta com mensagem de erro
            exit;
        }

        // --- PONTO VULNERÁVEL A XSS ---
        // O comentário é inserido no banco de dados sem sanitização ou validação adequada.
        // Qualquer HTML ou script injetado aqui será armazenado.
        // A vulnerabilidade se manifesta quando este comentário é exibido em artigo.php.
        $sql = "INSERT INTO comments (article_id, author_name, comment_text) VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Embora estejamos usando prepared statement aqui para a inserção,
            // a vulnerabilidade XSS ocorre na *exibição* dos dados não sanitizados em artigo.php.
            // A inserção em si está protegida contra SQL Injection neste ponto específico.
            $stmt->bind_param("iss", $article_id, $author_name, $comment_text);

            if ($stmt->execute()) {
                // Comentário adicionado com sucesso
                // Redireciona de volta para a página do artigo
                header("Location: artigo.php?id=" . $article_id);
                exit;
            } else {
                echo "Erro ao adicionar comentário: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erro ao preparar a query: " . $conn->error;
        }

    } else {
        echo "Erro: Dados do formulário incompletos.";
    }

    close_db_connection($conn);

} else {
    // Se não for POST, redireciona para a página inicial (ou outra página apropriada)
    header("Location: index.php");
    exit;
}
?>
