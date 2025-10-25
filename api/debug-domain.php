<?php
header('Content-Type: application/json');

// Ajustar caminhos
$base_dir = dirname(__DIR__);
require_once $base_dir . '/config/db.php';
require_once $base_dir . '/api/namecheap_helper.php';

// Teste simples
$domain = 'exemplo.com';

echo json_encode([
    'test' => 'debug',
    'domain' => $domain,
    'namecheap_user' => NAMECHEAP_API_USER,
    'namecheap_key_length' => strlen(NAMECHEAP_API_KEY),
    'api_url' => NAMECHEAP_API_URL,
]);

// Tentar chamar API
$result = callNamecheapAPI([
    'Command' => 'namecheap.domains.check',
    'DomainList' => $domain
]);

echo "\n\nAPI Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT);
?>
