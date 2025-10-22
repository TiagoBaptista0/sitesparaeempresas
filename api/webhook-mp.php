<?php
require '../config/db.php';
require '../config/functions.php';
require '../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Payment;

SDK::setAccessToken($_ENV['MP_ACCESS_TOKEN']);

$data = json_decode(file_get_contents('php://input'), true);

if ($data['type'] === 'payment') {
    $payment_id = $data['data']['id'];

    $payment = Payment::find_by_id($payment_id);

    if ($payment->status === 'approved') {
        // Atualizar pagamento no banco
        $mp_id = $payment->external_reference ?? $payment->id;

        $stmt = $conn->prepare("UPDATE pagamentos SET status = 'aprovado' WHERE mp_id = ?");
        $stmt->bind_param("s", $mp_id);
        $stmt->execute();

        // Ativar site do cliente
        $result = $conn->query("SELECT cliente_id FROM pagamentos WHERE mp_id = '$mp_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $cliente_id = $row['cliente_id'];

            $conn->query("UPDATE clientes SET status = 'ativo' WHERE id = $cliente_id");
        }
    }
}

http_response_code(200);
?>