<?php
require_once '../config/db.php';
$token = $_GET['token'] ?? '';

$stmt = $conn->prepare("SELECT id, email_token_expires FROM usuarios WHERE email_token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    die('Token inválido');
}
if (new DateTime() > new DateTime($user['email_token_expires'])) {
    die('Token expirado');
}

// confirmar
$stmt = $conn->prepare("UPDATE usuarios SET email_confirmed = 1, email_token = NULL, email_token_expires = NULL WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stmt->close();

// Login automático opcional e redirecionar para primeira tela pós-cadastro
session_start();
$_SESSION['usuario_id'] = $user['id'];
header('Location: /dashboard/first-after-signup.php');
exit;