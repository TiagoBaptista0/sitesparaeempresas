<?php
require '../config/db.php';
require '../config/functions.php';
require '../vendor/autoload.php';

header('Content-Type: application/json');

if (!is_logged()) {
    send_json_error('Não autenticado');
}

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

SDK::setAccessToken($_ENV['MP_ACCESS_TOKEN']);

$cliente_id = (int)($_POST['cliente_id'] ?? 0);
$plano_id = (int)($_POST['plano_id'] ?? 1);

if (!$cliente_id || !$plano_id) {
    send_json_error('Dados inválidos');
}

// Buscar dados do cliente
$result = $conn->query("SELECT c.*, p.nome as plano_nome, p.preco FROM clientes c JOIN planos p ON c.plano_id = p.id WHERE c.id = $cliente_id");
$cliente = $result->fetch_assoc();

if (!$cliente) {
    send_json_error('Cliente não encontrado');
}

// Criar preferência de pagamento
$preference = new Preference();

$item = new Item();
$item->title = "Plano " . $cliente['plano_nome'] . " - " . $cliente['nome_empresa'];
$item->quantity = 1;
$item->unit_price = (float)$cliente['preco'];

$preference->items = array($item);
$preference->back_urls = array(
    "success" => $_ENV['BASE_URL'] . "/dashboard/pagamento-sucesso.php",
    "failure" => $_ENV['BASE_URL'] . "/dashboard/pagamento-erro.php",
    "pending" => $_ENV['BASE_URL'] . "/dashboard/pagamento-pendente.php"
);
$preference->auto_return = "approved";
$preference->notification_url = $_ENV['BASE_URL'] . "/api/webhook-mp.php";

$preference->save();

// Salvar pagamento pendente no banco
$valor = $cliente['preco'];
$data_hoje = data_hoje();
$proxima_renovacao = proxima_renovacao(30);

$stmt = $conn->prepare("INSERT INTO pagamentos (cliente_id, valor, data_pagamento, status, mp_id, proxima_renovacao) VALUES (?, ?, ?, 'pendente', ?, ?)");
$stmt->bind_param("idsss", $cliente_id, $valor, $data_hoje, $preference->id, $proxima_renovacao);
$stmt->execute();

send_json_success('Pagamento criado', [
    'preference_id' => $preference->id,
    'init_point' => $preference->init_point
]);
?>