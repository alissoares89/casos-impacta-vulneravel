<?php
use PHPUnit\Framework\TestCase;

class FakeResult {
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function fetch_assoc(): ?array
    {
        return $this->rows[0] ?? null;
    }

    public function __get(string $name)
    {
        if ($name === 'num_rows') {
            return count($this->rows);
        }
        return null;
    }
}

class FakeMySQLi {
    private FakeResult $result;

    public function __construct(FakeResult $result)
    {
        $this->result = $result;
    }

    public function query(string $sql): FakeResult
    {
        return $this->result;
    }
}

function attemptLogin(FakeMySQLi $conn, string $username, string $password, string &$error_message): bool
{
    $sql = "SELECT id, username FROM users WHERE username = '" . $username . "' AND password = '" . $password . "'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return true;
    }

    $error_message = 'Usuário ou senha inválidos.';
    return false;
}

final class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
        } else {
            session_start();
        }
    }

    public function testLoginWithValidCredentials(): void
    {
        $result = new FakeResult([['id' => 1, 'username' => 'admin']]);
        $conn = new FakeMySQLi($result);
        $error_message = '';

        $success = attemptLogin($conn, 'admin', 'senha123', $error_message);

        $this->assertTrue($success);
        $this->assertSame(1, $_SESSION['user_id']);
        $this->assertSame('admin', $_SESSION['username']);
        $this->assertSame('', $error_message);
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $result = new FakeResult([]);
        $conn = new FakeMySQLi($result);
        $error_message = '';

        $success = attemptLogin($conn, 'admin', 'senhaErrada', $error_message);

        $this->assertFalse($success);
        $this->assertSame('Usuário ou senha inválidos.', $error_message);
    }
}
