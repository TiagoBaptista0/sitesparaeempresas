<?php
require 'namecheap_helper.php';

header('Content-Type: application/json');

$domain = $_GET['domain'] ?? '';
$firstName = $_GET['firstName'] ?? 'Tiago';
$lastName = $_GET['lastName'] ?? 'Baptista';
$email = $_GET['email'] ?? 'contato@sitesparaempresas.com';
$phone = $_GET['phone'] ?? '+55.19999999999';
$address = $_GET['address'] ?? 'Rua Exemplo, 123';
$city = $_GET['city'] ?? 'São José do Rio Pardo';
$state = $_GET['state'] ?? 'SP';
$postalCode = $_GET['postalCode'] ?? '13720000';
$country = $_GET['country'] ?? 'BR';

if (empty($domain)) {
    echo json_encode(['success' => false, 'message' => 'Domain parameter required']);
    exit;
}

$contact = [
    'FirstName' => $firstName,
    'LastName' => $lastName,
    'Address1' => $address,
    'City' => $city,
    'StateProvince' => $state,
    'PostalCode' => $postalCode,
    'Country' => $country,
    'Phone' => $phone,
    'EmailAddress' => $email
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

if ($result['success']) {
    $info = $result['xml']->CommandResponse->DomainCreateResult;
    echo json_encode([
        'success' => true,
        'message' => 'Domain registered successfully',
        'domainId' => (string)$info['DomainID'],
        'domain' => (string)$info['Domain'],
        'orderId' => (string)$info['OrderID']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
?>