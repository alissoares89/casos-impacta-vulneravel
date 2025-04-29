<?php
session_start();

// Se o usuário já estiver logado, redireciona para a página inicial
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$error_message = "; // Mensagem de erro para exibir, se houver

// Verifica se houve uma tentativa de login (via POST)
// A lógica de processamento do login (vulnerável a SQL Injection) será implementada aqui
// mas a vulnerabilidade em si será explorada na etapa 006.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once __DIR__ . 
        '/../db/db_config.php
    '; // Inclui a configuração do BD

    $username = $_POST["username"];
    $password = $_POST["password"];

    // --- PONTO VULNERÁVEL A SQL INJECTION ---
    // A consulta é construída concatenando diretamente a entrada do usuário.
    // Um invasor pode injetar código SQL malicioso nos campos username ou password.
    // Exemplo de ataque: username = ' OR '1'='1' -- 
    $sql = "SELECT id, username FROM users WHERE username = '" . $username . "' AND password = '" . $password . "'";
    // Em um código seguro, usaríamos Prepared Statements:
    // $sql = "SELECT id, username FROM users WHERE username = ? AND password = ?";
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("ss", $username, $hashed_password); // Comparar com hash da senha

    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        // Login bem-sucedido
        $user = $result->fetch_assoc();
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];

        // Gerar um token CSRF simples (será usado na etapa CSRF)
        // Em uma aplicação real, usar tokens mais robustos e por requisição.
        if (empty($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }

        header("Location: index.php"); // Redireciona para a página inicial após login
        exit;
    } else {
        // Login falhou
        $error_message = "Usuário ou senha inválidos.";
        // Log de tentativa de login falha seria útil em um sistema real.
    }

    close_db_connection($conn);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Casos da Impacta</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">Casos da Impacta</a></h1>
        <nav>
            <a href="index.php">Início</a>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <main>
        <h2>Login de Usuário</h2>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST" class="login-form">
            <div>
                <label for="username">Usuário:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Casos da Impacta - Portal Fictício para Fins Educacionais</p>
    </footer>
</body>
</html>

