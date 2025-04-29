<?php
require_once __DIR__ . '/../db/db_config.php'; // Inclui a configuração do banco de dados

session_start(); // Inicia a sessão

// Verificar se o ID do artigo foi passado via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirecionar para a página inicial se o ID for inválido
    header('Location: index.php');
    exit;
}

$article_id = intval($_GET['id']);

// Buscar o artigo específico
// ATENÇÃO: Usar prepared statements seria a prática segura aqui.
$sql_article = "SELECT a.id, a.title, a.content, a.created_at, u.username AS author_name
                 FROM articles a
                 LEFT JOIN users u ON a.author_id = u.id
                 WHERE a.id = ?";
$stmt_article = $conn->prepare($sql_article);
$stmt_article->bind_param("i", $article_id);
$stmt_article->execute();
$result_article = $stmt_article->get_result();

if ($result_article->num_rows === 0) {
    // Artigo não encontrado
    echo "Artigo não encontrado.";
    // Considerar redirecionar ou mostrar uma página 404 mais amigável
    exit;
}

$article = $result_article->fetch_assoc();
$stmt_article->close();

// Buscar comentários para este artigo
// ATENÇÃO: A exibição do comment_text será vulnerável a XSS.
$sql_comments = "SELECT author_name, comment_text, created_at
                 FROM comments
                 WHERE article_id = ?
                 ORDER BY created_at DESC";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $article_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Casos da Impacta</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">Casos da Impacta</a></h1>
        <nav>
            <a href="index.php">Início</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="admin/painel.php">Painel Admin (Simulado)</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <article class="full-article">
            <h2><?php echo htmlspecialchars($article['title']); ?></h2>
            <small>Por: <?php echo htmlspecialchars($article['author_name'] ?? 'Desconhecido'); ?> | Publicado em: <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></small>
            <div class="article-content">
                <?php echo nl2br(htmlspecialchars($article['content'])); // Usar nl2br para quebras de linha, htmlspecialchars para segurança básica ?>
            </div>
        </article>

        <section class="comments-section">
            <h3>Comentários</h3>

            <!-- Formulário de Comentário - Vulnerável a XSS na exibição -->
            <form action="comentario.php" method="POST">
                <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                <div>
                    <label for="author_name">Nome:</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                <div>
                    <label for="comment_text">Comentário:</label>
                    <textarea id="comment_text" name="comment_text" rows="4" required></textarea>
                </div>
                <button type="submit">Enviar Comentário</button>
            </form>

            <hr>

            <?php if ($result_comments && $result_comments->num_rows > 0): ?>
                <?php while($comment = $result_comments->fetch_assoc()): ?>
                    <div class="comment">
                        <p>
                            <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                            <small>(<?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>)</small>
                        </p>
                        <!-- PONTO VULNERÁVEL A XSS: Exibindo o comentário sem sanitização adequada -->
                        <p><?php echo $comment['comment_text']; ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Seja o primeiro a comentar!</p>
            <?php endif; ?>
            <?php $stmt_comments->close(); ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Casos da Impacta - Portal Fictício para Fins Educacionais</p>
    </footer>

<?php
close_db_connection($conn);
?>
</body>
</html>

