<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$dominio = strtolower(trim($_POST['dominio'] ?? ''));

// ============ VALIDAÇÕES ============

if (empty($dominio)) {
    echo json_encode(['success' => false, 'message' => 'Domínio inválido']);
    exit;
}

// Validar formato do domínio
if (!preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.(com\.br|com|net|info|biz))$/', $dominio)) {
    echo json_encode(['success' => false, 'message' => 'Formato de domínio inválido']);
    exit;
}

try {
    // ============ VERIFICAR NO BANCO DE DADOS ============
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE dominio = ?");
    
    if (!$stmt) {
        throw new Exception("Erro na preparação: " . $conn->error);
    }
    
    $stmt->bind_param("s", $dominio);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        echo json_encode([
            'success' => true,
            'disponivel' => false,
            'message' => 'Este domínio já está registrado'
        ]);
        exit;
    }
    $stmt->close();
    
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
    
    $preco = $precos[$extensao] ?? 39;
    
    // ============ SIMULAR VERIFICAÇÃO EM API EXTERNA ============
    // Aqui você poderia integrar com um serviço real como:
    // - Registro.br (para .com.br)
    // - NameCheap
    // - GoDaddy
    // Por enquanto, vamos simular
    
    // Palavras-chave reservadas (exemplo)
    $reservadas = [
        'admin', 'api', 'app', 'test', 'demo', 
        'www', 'mail', 'ftp', 'smtp', 'imap'
    ];
    
    $nome = $partes[0];
    if (in_array($nome, $reservadas)) {
        echo json_encode([
            'success' => true,
            'disponivel' => false,
            'message' => 'Este domínio é reservado'
        ]);
        exit;
    }
    
    // ============ SIMULAR INDISPONIBILIDADE ALEATÓRIA (REMOVER EM PRODUÇÃO) ============
    // Descomente para testes
    // $aleatorio = rand(1, 3);
    // if ($aleatorio === 2) {
    //     echo json_encode([
    //         'success' => true,
    //         'disponivel' => false,
    //         'message' => 'Domínio não disponível'
    //     ]);
    //     exit;
    // }
    
    // ============ DOMÍNIO DISPONÍVEL ============
    echo json_encode([
        'success' => true,
        'disponivel' => true,
        'preco' => $preco,
        'dominio' => $dominio
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao verificar domínio: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao verificar domínio'
    ]);
}

$conn->close();
?>