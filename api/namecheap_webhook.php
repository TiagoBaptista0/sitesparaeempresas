<?php
require '../config/db.php';
require 'namecheap_helper.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action = $input['action'] ?? '';
$orderId = $input['orderId'] ?? '';
$domain = $input['domain'] ?? '';
$userId = $input['userId'] ?? '';

if (empty($action) || empty($orderId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    if ($action === 'register_domain') {
        if (empty($domain) || empty($userId)) {
            throw new Exception('Domain and userId required for registration');
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('User not found');
        }

        $contact = [
            'FirstName' => $user['first_name'] ?? 'Cliente',
            'LastName' => $user['last_name'] ?? 'Sitesparaempresas',
            'Address1' => $user['address'] ?? 'Rua Exemplo, 123',
            'City' => $user['city'] ?? 'São José do Rio Pardo',
            'StateProvince' => $user['state'] ?? 'SP',
            'PostalCode' => $user['postal_code'] ?? '13720000',
            'Country' => $user['country'] ?? 'BR',
            'Phone' => $user['phone'] ?? '+55.19999999999',
            'EmailAddress' => $user['email']
        ];

        $params = [
            'Command' => 'namecheap.domains.create',
            'DomainName' => $domain,
            'Years' => 1,
        ];

        foreach (['Registrant', 'Tech', 'Admin', 'AuxBilling'] as $type) {
            foreach ($contact as $key => $value) {
                $params[$type . $key] = $value;
            }
        }

        $result = callNamecheapAPI($params);

        if (!$result['success']) {
            throw new Exception('Namecheap API error: ' . $result['message']);
        }

        $info = $result['xml']->CommandResponse->DomainCreateResult;
        $domainId = (string)$info['DomainID'];

        $stmt = $pdo->prepare('UPDATE orders SET domain_id = ?, status = ? WHERE id = ?');
        $stmt->execute([$domainId, 'domain_registered', $orderId]);

        echo json_encode([
            'success' => true,
            'message' => 'Domain registered successfully',
            'domainId' => $domainId,
            'domain' => $domain
        ]);

    } elseif ($action === 'set_dns') {
        if (empty($domain)) {
            throw new Exception('Domain required for DNS configuration');
        }

        $nameservers = $input['nameservers'] ?? 'ns1.testeempresa.com,ns2.testeempresa.com';

        $parts = explode('.', $domain);
        if (count($parts) < 2) {
            throw new Exception('Invalid domain format');
        }

        $sld = $parts[0];
        $tld = implode('.', array_slice($parts, 1));

        $result = callNamecheapAPI([
            'Command' => 'namecheap.domains.dns.setCustom',
            'SLD' => $sld,
            'TLD' => $tld,
            'Nameservers' => $nameservers
        ]);

        if (!$result['success']) {
            throw new Exception('Namecheap API error: ' . $result['message']);
        }

        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute(['dns_configured', $orderId]);

        echo json_encode([
            'success' => true,
            'message' => 'DNS configured successfully',
            'domain' => $domain,
            'nameservers' => $nameservers
        ]);

    } else {
        throw new Exception('Unknown action: ' . $action);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>