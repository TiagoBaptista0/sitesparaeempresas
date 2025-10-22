<?php
// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carregar variáveis de ambiente
if (file_exists(__DIR__ . '/../.env')) {
    $env_file = file_get_contents(__DIR__ . '/../.env');
    $lines = explode("\n", $env_file);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, ' "\'');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Configurações do banco de dados
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'sitesparaempresas');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Configurações do Mercado Pago (Brasil)
define('MP_PUBLIC_KEY', $_ENV['MP_PUBLIC_KEY'] ?? 'TEST-4eb6f22c-f997-4e2e-a751-c3381bb29a3a');
define('MP_ACCESS_TOKEN', $_ENV['MP_ACCESS_TOKEN'] ?? 'TEST-2235218074018734-101521-e81d1e6f8f3e4c4e0f5e5e5e5e5e5e5e-191014229');

// Configurações da aplicação
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Sites Para Empresas');
define('EMAIL_FROM', $_ENV['EMAIL_FROM'] ?? 'noreply@sitesparaempresas.com');
define('EMAIL_NAME', $_ENV['EMAIL_NAME'] ?? 'Sites Para Empresas');

// Configurações SMTP
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'localhost');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 1025);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? '');

// Definir BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:8000';
$path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$path = rtrim($path, '/');
define('BASE_URL', $protocol . '://' . $host . $path);

// Conectar ao banco de dados
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Erro de conexão com o banco de dados: " . $conn->connect_error);
    }

    // Definir charset para UTF-8
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>
