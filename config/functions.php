<?php
// Não iniciar sessão aqui, já é feito no db.php

// Função para verificar se o usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// Função para verificar se é admin
function verificarAdmin() {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
        header('Location: ' . BASE_URL . '/dashboard/index.php');
        exit;
    }
}

// Função para formatar data brasileira
function formatarData($data) {
    if (!$data) return 'N/A';
    return date('d/m/Y', strtotime($data));
}

// Função para formatar data e hora brasileira
function formatarDataHora($data) {
    if (!$data) return 'N/A';
    return date('d/m/Y H:i', strtotime($data));
}

// Função para formatar moeda brasileira
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Função para sanitizar input
function sanitizar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para gerar token CSRF
function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para verificar token CSRF
function verificarTokenCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

 // Função para enviar email usando PHPMailer
function enviarEmail($para, $assunto, $mensagem, $altBody = '') {
    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configurações do servidor SMTP usando constantes definidas em db.php
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
        $mail->Password   = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
        $mail->SMTPSecure = (defined('SMTP_ENCRYPTION') && SMTP_ENCRYPTION) ? SMTP_ENCRYPTION : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $mail->CharSet    = 'UTF-8';

        // Se a constante APP_ENV estiver definida, permitir ajustes para ambiente de desenvolvimento
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $mail->SMTPAutoTLS = false;
            $mail->SMTPSecure = false;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        }

        // Remetente e destinatário
        $fromEmail = defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@sitesparaempresas.local';
        $fromName  = defined('EMAIL_NAME') ? EMAIL_NAME : 'SitesParaEmpresas';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($para);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;
        $mail->AltBody = $altBody ?: strip_tags($mensagem);

        // Enviar email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar email: {$mail->ErrorInfo}");
        return false;
    }
}

// Função para registrar log
function registrarLog($conn, $usuario_id, $acao, $descricao) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO logs (usuario_id, acao, descricao, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $usuario_id, $acao, $descricao, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}
?>