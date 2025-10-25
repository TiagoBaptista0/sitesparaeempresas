<?php
require 'namecheap_helper.php';

header('Content-Type: application/json');

$domain = $_GET['domain'] ?? '';

if (empty($domain)) {
    echo json_encode(['success' => false, 'message' => 'Domain parameter required']);
    exit;
}

$result = callNamecheapAPI([
    'Command' => 'namecheap.domains.check',
    'DomainList' => $domain
]);

if ($result['success']) {
    $info = $result['xml']->CommandResponse->DomainCheckResult;
    echo json_encode([
        'success' => true,
        'domain' => (string)$info['Domain'],
        'available' => (string)$info['Available'] === 'true',
        'isPremium' => (string)$info['IsPremium'] === 'true',
        'premiumPrice' => (string)$info['PremiumRegistrationPrice'] ?? null
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
?>