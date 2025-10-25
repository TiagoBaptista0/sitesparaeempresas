<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder a requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once '../config/db.php';
    require_once 'namecheap_helper.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao carregar configurações: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$domain = $input['domain'] ?? null;

if (!$domain) {
    http_response_code(400);
    echo json_encode(['error' => 'Domain is required']);
    exit;
}

// Validar formato do domínio
if (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*\.[a-z]{2,}$/i', $domain)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid domain format']);
    exit;
}

// Separar domínio e extensão
$parts = explode('.', $domain);
if (count($parts) < 2) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid domain format']);
    exit;
}

// Verificar se é .com.br
if (count($parts) >= 3 && $parts[count($parts)-2] === 'com' && $parts[count($parts)-1] === 'br') {
    $sld = $parts[count($parts)-3];
    $tld = 'com.br';
} else {
    $sld = $parts[count($parts)-2];
    $tld = $parts[count($parts)-1];
}

// Validar SLD (Second Level Domain)
if (strlen($sld) < 2 || strlen($sld) > 63) {
    http_response_code(400);
    echo json_encode(['error' => 'Domain name must be between 2 and 63 characters']);
    exit;
}

// Preço do domínio
$domain_prices = [
    'com.br' => 35.00,
    'com' => 12.00,
    'net' => 12.00,
    'org' => 12.00,
    'info' => 12.00,
];

$price = $domain_prices[$tld] ?? 12.00;

// Verificar disponibilidade via Namecheap API
if (!empty(NAMECHEAP_API_USER) && !empty(NAMECHEAP_API_KEY)) {
    try {
        $result = callNamecheapAPI([
            'Command' => 'namecheap.domains.check',
            'DomainList' => $domain
        ]);

        if (!empty($result['success'])) {
            // Parsear resposta XML com tratamento de erros do libxml
            $use_internal_errors = libxml_use_internal_errors(true);
            $xml = simplexml_load_string($result['response']);
            if ($xml && isset($xml->CommandResponse->DomainCheckResult)) {
                $domain_check = $xml->CommandResponse->DomainCheckResult;
                $is_available = (string)$domain_check['Available'] === 'true';

                echo json_encode([
                    'available' => $is_available,
                    'domain' => $domain,
                    'price' => $price,
                    'source' => 'namecheap'
                ]);
                // Restaurar comportamento do libxml antes de sair
                libxml_clear_errors();
                libxml_use_internal_errors($use_internal_errors);
                exit;
            } else {
                // Coletar erros do libxml para depuração
                $errors = libxml_get_errors();
                $error_messages = [];
                foreach ($errors as $err) {
                    $error_messages[] = trim($err->message) . " (line {$err->line})";
                }
                libxml_clear_errors();
                libxml_use_internal_errors($use_internal_errors);

                if (!empty($error_messages)) {
                    error_log("Erro ao parsear resposta XML do Namecheap para domínio: $domain; erros: " . implode("; ", $error_messages));
                } else {
                    // Se não houver erros do libxml, logar a resposta para investigação
                    $snippet = substr($result['response'], 0, 1000);
                    error_log("Erro ao parsear resposta XML do Namecheap para domínio: $domain; resposta: " . $snippet);
                }
            }
        } else {
            // Se API falhar, usar fallback. Logar mensagem se disponível
            $msg = isset($result['message']) ? $result['message'] : 'Unknown error';
            $code = isset($result['code']) ? $result['code'] : '';
            error_log("Erro na API Namecheap para domínio $domain: $msg " . ($code !== '' ? "(code: $code)" : ""));
        }
    } catch (Throwable $e) {
        // Capturar exceções inesperadas e seguir para fallback
        error_log("Exceção ao consultar Namecheap para domínio $domain: " . $e->getMessage());
    }
}

// Fallback: lista de domínios indisponíveis conhecidos
$unavailable_domains = [
    'google.com',
    'facebook.com',
    'amazon.com',
    'microsoft.com',
    'apple.com',
    'netflix.com',
    'youtube.com',
    'twitter.com',
    'instagram.com',
    'linkedin.com',
    'github.com',
    'stackoverflow.com',
    'wikipedia.org',
    'wordpress.com',
    'blogger.com',
    'tumblr.com',
    'reddit.com',
    'pinterest.com',
    'snapchat.com',
    'tiktok.com'
];

$is_available = !in_array(strtolower($domain), $unavailable_domains);

echo json_encode([
    'available' => $is_available,
    'domain' => $domain,
    'price' => $price,
    'source' => 'fallback',
    'warning' => 'Usando verificação offline. Configure credenciais Namecheap para verificação em tempo real.'
]);
?>
