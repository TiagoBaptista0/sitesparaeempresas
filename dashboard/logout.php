<?php
session_start();

// Registrar logout no log antes de destruir a sessão
if (isset($_SESSION['usuario_id'])) {
    require_once '../config/db.php';
    
    $usuario_id = $_SESSION['usuario_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO logs (usuario_id, acao, descricao, ip_address, user_agent) VALUES (?, 'logout', 'Logout realizado', ?, ?)");
    $stmt->bind_param("iss", $usuario_id, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// Destruir sessão
session_unset();
session_destroy();

// Limpar cookie de sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redirecionar para login
header('Location: ../login.php');
exit;
?>