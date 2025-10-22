<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$plano_id = isset($_POST['plano_id']) ? intval($_POST['plano_id']) : 0;
$valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
$metodo_pagamento = isset($_POST['metodo_pagamento']) ? $_POST['metodo_pagamento'] : '';

if ($plano_id <= 0 || $valor <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Buscar plano
$stmt = $conn->prepare("SELECT * FROM planos WHERE id = ?");
$stmt->bind_param("i", $plano_id);
$stmt->execute();
$result = $stmt->get_result();
$plano = $result->fetch_assoc();
$stmt->close();

if (!$plano) {
    echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
    exit;
}

try {
    // Iniciar transação
    $conn->begin_transaction();

    // Cancelar assinaturas ativas anteriores
    $stmt = $conn->prepare("UPDATE assinaturas SET status = 'cancelada' WHERE usuario_id = ? AND status = 'ativa'");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();

    // Criar nova assinatura
    $data_proximo_pagamento = date('Y-m-d', strtotime('+1 month'));
    $stmt = $conn->prepare("
        INSERT INTO assinaturas (usuario_id, plano_id, valor, status, data_proximo_pagamento) 
        VALUES (?, ?, ?, 'ativa', ?)
    ");
    $stmt->bind_param("iids", $usuario_id, $plano_id, $valor, $data_proximo_pagamento);
    $stmt->execute();
    $assinatura_id = $stmt->insert_id;
    $stmt->close();

    // Registrar pagamento
    $descricao = "Assinatura do plano " . $plano['nome'];
    $status_pagamento = 'aprovado'; // Simulação - em produção, integrar com gateway

    $stmt = $conn->prepare("
        INSERT INTO pagamentos (usuario_id, assinatura_id, valor, status, metodo_pagamento, descricao) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iidsss", $usuario_id, $assinatura_id, $valor, $status_pagamento, $metodo_pagamento, $descricao);
    $stmt->execute();
    $pagamento_id = $stmt->insert_id;
    $stmt->close();

    // Commit da transação
    $conn->commit();

    // Resposta baseada no método de pagamento
    $response = ['success' => true, 'message' => 'Pagamento processado com sucesso'];

    if ($metodo_pagamento === 'pix') {
        // Em produção, gerar QR Code real via Mercado Pago
        $response['payment_url'] = '../pagamento-pix.php?id=' . $pagamento_id;
    } elseif ($metodo_pagamento === 'boleto') {
        // Em produção, gerar boleto real via Mercado Pago
        $response['payment_url'] = '../pagamento-boleto.php?id=' . $pagamento_id;
    }

    echo json_encode($response);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Erro ao processar pagamento: ' . $e->getMessage()]);
}
?>
