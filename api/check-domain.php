<?php
header('Content-Type: application/json');

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

// Simular verificação de disponibilidade
// Em produção, você integraria com um serviço de registro de domínios
$unavailable_domains = ['google.com', 'facebook.com', 'amazon.com', 'microsoft.com'];

$is_available = !in_array(strtolower($domain), $unavailable_domains);

// Preço do domínio (em produção, viria de um banco de dados)
$domain_prices = [
    '.com.br' => 35.00,
    '.com' => 12.00,
    '.net' => 12.00,
    '.org' => 12.00,
    '.info' => 12.00,
];

$extension = substr($domain, strrpos($domain, '.'));
$price = $domain_prices[$extension] ?? 12.00;

echo json_encode([
    'available' => $is_available,
    'domain' => $domain,
    'price' => $price
]);
?>
