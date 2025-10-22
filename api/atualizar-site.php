<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$site_id = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$subdominio = isset($_POST['subdominio']) ? trim(strtolower($_POST['subdominio'])) : '';
$template = isset($_POST['template']) ? trim($_POST['template']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($site_id <= 0 || empty($nome) || empty($subdominio)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Validar subdomínio
if (!preg_match('/^[a-z0-9-]+$/', $subdominio)) {
    echo json_encode(['success' => false, 'message' => 'Subdomínio inválido']);
    exit;
}

// Verificar se o site pertence ao usuário
$stmt = $conn->prepare("SELECT id FROM sites WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $site_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Site não encontrado']);
    exit;
}
$stmt->close();

// Verificar se subdomínio já existe (exceto para o próprio site)
$stmt = $conn->prepare("SELECT id FROM sites WHERE subdominio = ? AND id != ?");
$stmt->bind_param("si", $subdominio, $site_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Este subdomínio já está em uso']);
    exit;
}
$stmt->close();

try {
    $stmt = $conn->prepare("
        UPDATE sites 
        SET nome = ?, subdominio = ?, template = ?, status = ?, data_atualizacao = NOW() 
        WHERE id = ? AND usuario_id = ?
    ");
    $stmt->bind_param("ssssii", $nome, $subdominio, $template, $status, $site_id, $usuario_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Site atualizado com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar site: ' . $e->getMessage()]);
}
?>
