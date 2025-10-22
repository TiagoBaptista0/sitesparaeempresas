<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $stmt = $conn->prepare("UPDATE assinaturas SET status = 'cancelada' WHERE usuario_id = ? AND status = 'ativa'");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensagem'] = 'Assinatura cancelada com sucesso';
    header('Location: ../dashboard/pagamentos.php');
} catch (Exception $e) {
    $_SESSION['erro'] = 'Erro ao cancelar assinatura';
    header('Location: ../dashboard/pagamentos.php');
}
exit;
?>
