<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$senha_atual = isset($_POST['senha_atual']) ? $_POST['senha_atual'] : '';
$nova_senha = isset($_POST['nova_senha']) ? $_POST['nova_senha'] : '';

if (empty($senha_atual) || empty($nova_senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos']);
    exit;
}

if (strlen($nova_senha) < 6) {
    echo json_encode(['success' => false, 'message' => 'A nova senha deve ter no mínimo 6 caracteres']);
    exit;
}

// Buscar senha atual do usuário
$stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    exit;
}

// Verificar senha atual
if (!password_verify($senha_atual, $usuario['senha'])) {
    echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
    exit;
}

try {
    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $nova_senha_hash, $usuario_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha: ' . $e->getMessage()]);
}
?>
