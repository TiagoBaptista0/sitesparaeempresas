<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Get user ID from POST or session
$usuario_id = intval($_POST['usuario_id'] ?? 0);

if (empty($usuario_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do usuário não informado']);
    exit;
}

try {
    // Generate confirmation token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+48 hours'));

    // Update user with token
    $stmt = $conn->prepare("UPDATE usuarios SET email_token = ?, email_token_expires = ? WHERE id = ?");
    $stmt->bind_param("ssi", $token, $expires, $usuario_id);
    $stmt->execute();
    $stmt->close();

    // Get user email and name
    $stmt = $conn->prepare("SELECT email, nome FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }

    // Build confirmation link
    $base_url = rtrim($_ENV['BASE_URL'] ?? 'http://localhost/sitesparaeempresas/', '/');
    $confirm_link = $base_url . "/confirmar-email.php?token=" . $token;

    // Prepare email content
    $assunto = "Confirme seu e-mail - SitesParaEmpresas";
    $mensagem = "
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
                <h2>Bem-vindo ao SitesParaEmpresas!</h2>

                <p>Olá <strong>" . htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>

                <p>Seu cadastro foi realizado com sucesso! Para ativar sua conta, confirme seu e-mail clicando no botão abaixo:</p>

                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . htmlspecialchars($confirm_link, ENT_QUOTES, 'UTF-8') . "' class='button'>
                        Confirmar E-mail
                    </a>
                </p>

                <p>Ou copie este link:</p>
                <p style='background: #e0e0e0; padding: 10px; border-radius: 4px; word-break: break-all;'>
                    " . htmlspecialchars($confirm_link, ENT_QUOTES, 'UTF-8') . "
                </p>

                <p><strong>Este link expira em 48 horas.</strong></p>

                <hr>

                <p class='footer'>
                    Se você não se cadastrou em nosso site, ignore este e-mail.
                </p>
            </div>
        </body>
    </html>
    ";

    // Send email
    $email_enviado = enviarEmail($usuario['email'], $assunto, $mensagem);

    if (!$email_enviado) {
        error_log("Aviso: Email não enviado para " . $usuario['email'] . " (usuário " . $usuario_id . ")");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Erro ao enviar email de confirmação: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar email de confirmação']);
}
?>