<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$assunto = isset($_POST['assunto']) ? trim($_POST['assunto']) : '';
$categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
$prioridade = isset($_POST['prioridade']) ? trim($_POST['prioridade']) : 'media';
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';

if (empty($assunto) || empty($categoria) || empty($mensagem)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO tickets (usuario_id, assunto, categoria, prioridade, mensagem, status) 
        VALUES (?, ?, ?, ?, ?, 'aberto')
    ");
    $stmt->bind_param("issss", $usuario_id, $assunto, $categoria, $prioridade, $mensagem);
    $stmt->execute();
    $ticket_id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Ticket criado com sucesso', 'ticket_id' => $ticket_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao criar ticket: ' . $e->getMessage()]);
}
?>
