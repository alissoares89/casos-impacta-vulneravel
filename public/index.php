<?php
require_once __DIR__ . '/../db/db_config.php'; // Inclui a configuração do banco de dados

session_start(); // Inicia a sessão para gerenciar o estado do login (útil para CSRF depois)

// Buscar artigos do banco de dados
$sql = "SELECT id, title, LEFT(content, 150) AS excerpt, created_at FROM articles ORDER BY created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casos da Impacta - Notícias Universitárias</title>
    <link rel="stylesheet" href="../assets/style.css"> <!-- Link para o CSS -->
</head>
<body>
    <header>
        <h1>Casos da Impacta</h1>
        <nav>
            <a href="index.php">Início</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="admin/painel.php">Painel Admin (Simulado)</a> <!-- Link para área simulada de CSRF -->
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2>Últimas Notícias</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <article class="news-item">
                    <h3><a href="artigo.php?id=<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['title']); ?></a></h3>
                    <p><?php echo htmlspecialchars($row['excerpt']); ?>...</p>
                    <small>Publicado em: <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></small>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma notícia encontrada.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Casos da Impacta - Portal Fictício para Fins Educacionais</p>
    </footer>

<?php
// Fechar a conexão com o banco de dados
close_db_connection($conn);
?>
</body>
</html>

