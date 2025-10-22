<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se está logado
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$dominio = strtolower(trim($_POST['dominio'] ?? ''));
$cliente_id = (int)($_POST['cliente_id'] ?? 0);

// ============ VALIDAÇÕES ============

if (empty($dominio)) {
    echo json_encode(['success' => false, 'message' => 'Domínio inválido']);
    exit;
}

if ($cliente_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Cliente inválido']);
    exit;
}

// Validar formato do domínio
if (!preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.(com\.br|com|net|info|biz))$/', $dominio)) {
    echo json_encode(['success' => false, 'message' => 'Formato de domínio inválido']);
    exit;
}

try {
    // ============ VERIFICAR SE CLIENTE PERTENCE AO USUÁRIO ============
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE id = ? AND user_id = ?");
    
    if (!$stmt) {
        throw new Exception("Erro na preparação: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $cliente_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
        exit;
    }
    $stmt->close();
    
    // ============ ATUALIZAR DOMÍNIO NO CLIENTE ============
    $stmt = $conn->prepare("UPDATE clientes SET dominio = ?, status = 'aguardando_pagamento' WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Erro na preparação: " . $conn->error);
    }
    
    $stmt->bind_param("si", $dominio, $cliente_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar cliente: " . $stmt->error);
    }
    
    $stmt->close();
    
    // ============ BUSCAR INFORMAÇÕES DO CLIENTE E PLANO ============
    $stmt = $conn->prepare("
        SELECT c.*, p.nome as plano_nome, p.preco as plano_preco
        FROM clientes c
        JOIN planos p ON c.plano_id = p.id
        WHERE c.id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Erro na preparação: " . $conn->error);
    }
    
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();
    $stmt->close();
    
    if (!$cliente) {
        throw new Exception("Cliente não encontrado após atualização");
    }
    
    // ============ DEFINIR PREÇO DO DOMÍNIO ============
    $precos = [
        '.com.br' => 39,
        '.com' => 89,
        '.net' => 79,
        '.info' => 49,
        '.biz' => 59
    ];
    
    // Extrair extensão
    $partes = explode('.', $dominio);
    $extensao = '.' . implode('.', array_slice($partes, 1));
    
    $preco_dominio = $precos[$extensao] ?? 39;
    $preco_plano = $cliente['plano_preco'];
    $total = $preco_plano + $preco_dominio;
    
    // ============ RESPOSTA DE SUCESSO ============
    echo json_encode([
        'success' => true,
        'message' => 'Domínio confirmado com sucesso',
        'data' => [
            'cliente_id' => (int)$cliente_id,
            'dominio' => $dominio,
            'plano' => $cliente['plano_nome'],
            'preco_plano' => $preco_plano,
            'preco_dominio' => $preco_dominio,
            'total' => $total
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao confirmar domínio: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao confirmar domínio'
    ]);
}

$conn->close();
?>