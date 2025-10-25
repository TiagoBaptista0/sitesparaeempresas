<?php
session_start();
require_once '../config/db.php';
require_once '../vendor/autoload.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: domain-selection.php');
    exit;
}

$plano_id = $_POST['plano_id'] ?? null;
$plano_price = floatval($_POST['plano_price'] ?? 0);
$domain = $_POST['domain'] ?? null;
$domain_price = floatval($_POST['domain_price'] ?? 0);
$total = floatval($_POST['total'] ?? 0);

if (!$plano_id || !$domain || $plano_price <= 0 || $domain_price < 0) {
    header('Location: domain-selection.php');
    exit;
}

$stmt = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$mp_access_token = getenv('MP_ACCESS_TOKEN') ?: (defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : null);
if (!$mp_access_token) {
    die('Erro: Token do Mercado Pago não configurado');
}

\MercadoPago\SDK::setAccessToken($mp_access_token);

$item1 = new \MercadoPago\Item();
$item1->title = "Plano - Primeiro mês";
$item1->quantity = 1;
$item1->unit_price = (float)$plano_price;

$item2 = new \MercadoPago\Item();
$item2->title = "Registro de domínio (1 ano) - " . $domain;
$item2->quantity = 1;
$item2->unit_price = (float)$domain_price;

$preference = new \MercadoPago\Preference();
$preference->items = array($item1, $item2);

$base_url = rtrim(getenv('BASE_URL') ?: 'http://localhost/sitesparaeempresas', '/');
$preference->back_urls = [
    'success' => $base_url . '/dashboard/payment-success.php',
    'failure' => $base_url . '/dashboard/payment-error.php',
    'pending' => $base_url . '/dashboard/payment-pending.php',
];
$preference->auto_return = 'approved';

$payer = new \MercadoPago\Payer();
$payer->email = $usuario['email'];
$payer->name = $usuario['nome'];
$preference->payer = $payer;

// Configurar webhook URL para receber notificações
$webhook_url = getenv('MERCADOPAGO_WEBHOOK_URL') ?: $base_url . '/api/mercadopago_webhook.php';
$preference->notification_url = $webhook_url;
$preference->external_reference = uniqid();

try {
    $preference->save();

    $stmt = $conn->prepare("
        INSERT INTO pagamentos (usuario_id, plano_id, dominio, valor, valor_plano, valor_dominio, status, metodo_pagamento, descricao, mercadopago_preference_id, mercadopago_payment_id)
        VALUES (?, ?, ?, ?, ?, ?, 'pendente', 'mercadopago', ?, ?, ?)
    ");

    $descricao = "Plano + Domínio: " . $domain;
    $stmt->bind_param("iisdddsss",
        $usuario_id,
        $plano_id,
        $domain,
        $total,
        $plano_price,
        $domain_price,
        $descricao,
        $preference->id,
        $preference->init_point
    );
    $stmt->execute();
    $stmt->close();

    header('Location: ' . $preference->init_point);
    exit;

} catch (Exception $e) {
    error_log("Erro ao criar preferência Mercado Pago: " . $e->getMessage());
    die('Erro ao processar pagamento. Tente novamente.');
}
?>