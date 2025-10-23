<?php
require '../config/db.php';
require '../config/functions.php';

if (!is_logged() || !is_admin()) {
    redirect('/login/');
}

$cliente_id = (int)($_POST['cliente_id'] ?? 0);
$status = sanitizar($_POST['status'] ?? '');

if (!$cliente_id || !$status) {
    redirect('/admin/cliente.php');
}

$stmt = $conn->prepare("UPDATE clientes SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $cliente_id);

if ($stmt->execute()) {
    redirect('/admin/cliente.php?id=' . $cliente_id);
} else {
    redirect('/admin/clientes.php');
}
?>