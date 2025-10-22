<?php
require '../config/db.php';
require '../config/functions.php';

header('Content-Type: application/json');

if (!is_logged()) {
    send_json_error('Não autenticado');
}

$cliente_id = (int)($_POST['cliente_id'] ?? 0);
$titulo = sanitize($_POST['titulo'] ?? '');
$mensagem = sanitize($_POST['mensagem'] ?? '');

if (!$titulo || !$mensagem) {
    send_json_error('Preencha todos os campos');
}

$stmt = $conn->prepare("INSERT INTO suporte (cliente_id, titulo, mensagem, status) VALUES (?, ?, ?, 'aberto')");
$stmt->bind_param("iss", $cliente_id, $titulo, $mensagem);

if ($stmt->execute()) {
    send_json_success('Ticket criado com sucesso!');
} else {
    send_json_error('Erro ao criar ticket');
}
?>