<?php
require 'namecheap_helper.php';

header('Content-Type: application/json');

$domain = $_GET['domain'] ?? '';
$nameservers = $_GET['nameservers'] ?? 'ns1.testeempresa.com,ns2.testeempresa.com';

if (empty($domain)) {
    echo json_encode(['success' => false, 'message' => 'Domain parameter required']);
    exit;
}

$parts = explode('.', $domain);
if (count($parts) < 2) {
    echo json_encode(['success' => false, 'message' => 'Invalid domain format']);
    exit;
}

$sld = $parts[0];
$tld = implode('.', array_slice($parts, 1));

$result = callNamecheapAPI([
    'Command' => 'namecheap.domains.dns.setCustom',
    'SLD' => $sld,
    'TLD' => $tld,
    'Nameservers' => $nameservers
]);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => 'DNS configured successfully',
        'domain' => $domain,
        'nameservers' => $nameservers
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
?>