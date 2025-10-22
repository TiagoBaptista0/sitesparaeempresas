<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Test database connection
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
    exit;
}

// ============ VALIDAÇÕES DE MÉTODO ============
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// ============ RECEBER E SANITIZAR DADOS ============
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

// ============ VALIDAÇÕES ============
if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'E-mail não informado']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
    exit;
}

try {
    // ============ VERIFICAR SE USUÁRIO EXISTE E PRECISA DE CONFIRMAÇÃO ============
    $stmt = $conn->prepare("
        SELECT id, nome, email_confirmed, email_token 
        FROM usuarios 
        WHERE email = ? 
        LIMIT 1
    ");
    
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        error_log("Usuário não encontrado para email: " . $email);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado para o email: ' . $email]);
        exit;
    }

    // Se o e-mail já foi confirmado
    if ($usuario['email_confirmed']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'E-mail já confirmado']);
        exit;
    }

    // ============ GERAR NOVO TOKEN ============
    $novo_token = bin2hex(random_bytes(32));

    // ============ ATUALIZAR TOKEN NO BANCO ============
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET email_token = ?, email_token_expires = DATE_ADD(NOW(), INTERVAL 24 HOUR)
        WHERE id = ?
    ");

    if (!$stmt) {
        throw new Exception("Erro ao preparar update: " . $conn->error);
    }

    $stmt->bind_param("si", $novo_token, $usuario['id']);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar token: " . $stmt->error);
    }
    $stmt->close();

    // ============ ENVIAR EMAIL DE CONFIRMAÇÃO ============
    $base_url = rtrim($_ENV['BASE_URL'] ?? 'https://sitesparaempresas.com', '/');
    $link_confirmacao = $base_url . "/confirmar-email.php?token=" . $novo_token;

    $assunto = "Confirme seu e-mail - SitesParaEmpresas";

    $corpo = "
    <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; border-radius: 8px; }
                .button { display: inline-block; background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: bold; }
                .footer { font-size: 12px; color: #666; }
                hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Confirme seu E-mail - SitesParaEmpresas</h2>

                <p>Olá <strong>" . htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>

                <p>Você solicitou o reenvio do e-mail de confirmação. Para ativar sua conta, confirme seu e-mail clicando no botão abaixo:</p>

                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . htmlspecialchars($link_confirmacao, ENT_QUOTES, 'UTF-8') . "' class='button'>
                        Confirmar E-mail
                    </a>
                </p>

                <p>Ou copie este link:</p>
                <p style='background: #e0e0e0; padding: 10px; border-radius: 4px; word-break: break-all;'>
                    " . htmlspecialchars($link_confirmacao, ENT_QUOTES, 'UTF-8') . "
                </p>

                <p><strong>Este link expira em 24 horas.</strong></p>

                <hr>

                <p class='footer'>
                    Se você não solicitou este reenvio, ignore este e-mail ou entre em contato com o suporte.
                </p>
            </div>
        </body>
    </html>
    ";

    // Use PHPMailer wrapper function enviarEmail (should return bool)
    $email_enviado = enviarEmail($email, $assunto, $corpo);

    if (!$email_enviado) {
        error_log("Aviso: Email não reenviado para " . $email . " (usuário " . $usuario['id'] . ")");
    }

    // ============ RESPOSTA DE SUCESSO ============
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'E-mail de confirmação reenviado com sucesso!'
    ]);

} catch (Exception $e) {
    error_log("Erro ao reenviar confirmação: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao reenviar e-mail de confirmação. Tente novamente mais tarde.'
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>