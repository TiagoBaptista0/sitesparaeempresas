<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$subdominio = isset($_POST['subdominio']) ? trim(strtolower($_POST['subdominio'])) : '';
$template = isset($_POST['template']) ? trim($_POST['template']) : 'padrao';

if (empty($nome) || empty($subdominio)) {
    echo json_encode(['success' => false, 'message' => 'Nome e subdomínio são obrigatórios']);
    exit;
}

// Validar subdomínio (apenas letras, números e hífens)
if (!preg_match('/^[a-z0-9-]+$/', $subdominio)) {
    echo json_encode(['success' => false, 'message' => 'Subdomínio inválido. Use apenas letras, números e hífens']);
    exit;
}

// Verificar se subdomínio já existe
$stmt = $conn->prepare("SELECT id FROM sites WHERE subdominio = ?");
$stmt->bind_param("s", $subdominio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Este subdomínio já está em uso']);
    exit;
}
$stmt->close();

try {
    $stmt = $conn->prepare("
        INSERT INTO sites (usuario_id, nome, subdominio, template, status) 
        VALUES (?, ?, ?, ?, 'em_construcao')
    ");
    $stmt->bind_param("isss", $usuario_id, $nome, $subdominio, $template);
    $stmt->execute();
    $site_id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Site criado com sucesso', 'site_id' => $site_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao criar site: ' . $e->getMessage()]);
}
?>
