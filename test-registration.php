<?php
// Test registration API
require_once 'vendor/autoload.php';
require_once 'config/db.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test data - using correct field names
$testData = [
    'nome' => 'Test User',
    'email' => 'test' . time() . '@example.com',  // Unique email for each test
    'telefone' => '(11) 99999-9999',
    'senha' => 'TestPassword123!',
    'confirmar_senha' => 'TestPassword123!',
    'nome_empresa' => 'Test Company',
    'ramo' => 'Technology',
    'plano_id' => '1',
    'termos' => '1'
];

// Simulate POST request to api/cadastro.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/cadastro.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response Headers:\n" . $headers . "\n";
echo "Response Body:\n" . $body . "\n";

// Check if registration was successful
if ($httpCode == 200) {
    $responseData = json_decode($body, true);
    if ($responseData && isset($responseData['success']) && $responseData['success']) {
        echo "Registration successful! Check MailHog at http://localhost:8025 for the confirmation email.\n";
    } else {
        echo "Registration failed: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "HTTP Error: " . $httpCode . "\n";
}
?>