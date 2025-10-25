<?php
require_once '../config/db.php';
require_once '../vendor/autoload.php';

// Usar token do ambiente ou configuração
$mp_access_token = $_ENV['MP_ACCESS_TOKEN'] ?? 'TEST-2235218074018734-101521-e81d1e6f8f3e4c4e0f5e5e5e5e5e5e5e-191014229';
\MercadoPago\SDK::setAccessToken($mp_access_token);

// Configurar SSL para ngrok
$cacert_path = $_ENV['CACERT_PATH'] ?? 'C:/wamp64/bin/php/php8.3.14/cacert.pem';
if (file_exists($cacert_path)) {
    \MercadoPago\SDK::setSSLVerification($cacert_path);
}

$plano_price = floatval($_POST['plano_price']);
$domain_price = floatval($_POST['domain_price']);

// Verificar se os preços são válidos
if ($plano_price <= 0 || $domain_price < 0) {
    echo json_encode(['success' => false, 'message' => 'Valores inválidos']);
    exit;
}

$item1 = new \MercadoPago\Item();
$item1->title = "Plano - Primeiro mês";
$item1->quantity = 1;
$item1->unit_price = (float)$plano_price;

$item2 = new \MercadoPago\Item();
$item2->title = "Registro de domínio (1 ano) - " . $_POST['domain'];
$item2->quantity = 1;
$item2->unit_price = (float)$domain_price;

$preference = new \MercadoPago\Preference();
$preference->items = array($item1, $item2);

// URLs de retorno usando ngrok
$ngrok_url = $_ENV['NGROK_URL'] ?? 'https://julian-interprotoplasmic-lanette.ngrok-free.dev';
$base_url = rtrim($ngrok_url, '/');
$preference->back_urls = [
    'success' => $base_url . '/sitesparaeempresas/dashboard/payment-success.php',
    'failure' => $base_url . '/sitesparaeempresas/dashboard/payment-error.php',
    'pending' => $base_url . '/sitesparaeempresas/dashboard/payment-error.php',
];
$preference->auto_return = 'approved';

// Webhook URL
$preference->notification_url = $ngrok_url . '/sitesparaeempresas/api/mercadopago_webhook.php';

try {
    $preference->save();

    $init_point = $preference->init_point;

    // Salvar order no DB: usuario_id, plano_id, domain_id, total, preference_id = $preference->id
    // Esta parte precisa ser implementada com base na estrutura do banco de dados

    echo json_encode(['success' => true, 'init_point' => $init_point]);
} catch (Exception $e) {
    error_log("Erro ao criar preferência Mercado Pago: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao processar pagamento']);
}