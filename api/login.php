<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, nome, email, senha, tipo, status FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'E-mail ou senha incorretos']);
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    // Verificar status do usuário
    if ($usuario['status'] !== 'ativo') {
        echo json_encode(['success' => false, 'message' => 'Sua conta está inativa ou suspensa']);
        exit;
    }
    
    // Verificar senha
    if (!password_verify($senha, $usuario['senha'])) {
        echo json_encode(['success' => false, 'message' => 'E-mail ou senha incorretos']);
        exit;
    }
    
    // Atualizar último acesso
    $stmt = $conn->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?");
    $stmt->bind_param("i", $usuario['id']);
    $stmt->execute();
    $stmt->close();
    
    // Criar sessão
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo'];
    
    // Registrar log
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $stmt = $conn->prepare("INSERT INTO logs (usuario_id, acao, descricao, ip_address, user_agent) VALUES (?, 'login', 'Login realizado com sucesso', ?, ?)");
    $stmt->bind_param("iss", $usuario['id'], $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'redirect' => $usuario['tipo'] === 'admin' ? '../admin/index.php' : '../dashboard/index.php'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao processar login']);
}
?>