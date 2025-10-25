<?php
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

// Usar variáveis de ambiente ou valores padrão
define('NAMECHEAP_API_USER', $_ENV['NAMECHEAP_API_USER'] ?? '');
define('NAMECHEAP_API_KEY', $_ENV['NAMECHEAP_API_KEY'] ?? '');
define('NAMECHEAP_USERNAME', $_ENV['NAMECHEAP_API_USER'] ?? '');
define('NAMECHEAP_CLIENT_IP', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

// Usar sandbox em desenvolvimento, produção em produção
$app_env = $_ENV['APP_ENV'] ?? 'development';
define('NAMECHEAP_API_URL', $app_env === 'production'
    ? 'https://api.namecheap.com/xml.response'
    : 'https://api.sandbox.namecheap.com/xml.response'
);

function callNamecheapAPI($params) {
    $baseParams = [
        'ApiUser' => NAMECHEAP_API_USER,
        'ApiKey' => NAMECHEAP_API_KEY,
        'UserName' => NAMECHEAP_USERNAME,
        'ClientIp' => NAMECHEAP_CLIENT_IP,
    ];
    $query = array_merge($baseParams, $params);
    $url = NAMECHEAP_API_URL . '?' . http_build_query($query);

    $response = file_get_contents($url);
    if ($response === false) {
        return ['success' => false, 'message' => 'Erro ao conectar com API Namecheap'];
    }

    $xml = simplexml_load_string($response);
    if (!$xml) {
        return ['success' => false, 'message' => 'Resposta inválida da API'];
    }

    if ($xml->Errors && $xml->Errors->Error) {
        return ['success' => false, 'message' => (string)$xml->Errors->Error];
    }
    return ['success' => true, 'response' => $response];
}
?>