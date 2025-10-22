<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $conn->begin_transaction();

    // Deletar sites
    $stmt = $conn->prepare("DELETE FROM sites WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();

    // Deletar tickets
    $stmt = $conn->prepare("DELETE FROM tickets WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();

    // Cancelar assinaturas
    $stmt = $conn->prepare("UPDATE assinaturas SET status = 'cancelada' WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();

    // Deletar usuário
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    // Destruir sessão
    session_destroy();

    header('Location: ../index.php?conta_excluida=1');
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['erro'] = 'Erro ao excluir conta';
    header('Location: ../dashboard/perfil.php');
}
exit;
?>
