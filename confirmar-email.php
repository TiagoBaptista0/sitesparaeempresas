<?php
session_start();

// If user is already logged in and confirmed, redirect to dashboard
if (isset($_SESSION['usuario_id']) && isset($_SESSION['email_confirmed']) && $_SESSION['email_confirmed']) {
    header('Location: dashboard/index.php');
    exit;
}

$page_title = 'Confirmação de E-mail';
include 'includes/header.php';

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// If token is provided, try to confirm email
$message = '';
if (!empty($token)) {
    require_once 'config/db.php';

    try {
        // ============ VERIFICAR TOKEN ============
        $stmt = $conn->prepare("
            SELECT id, email_token_expires
            FROM usuarios
            WHERE email_token = ?
            AND email_confirmed = 0
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
        } else {
            $user = null;
            $message = '<div class="alert alert-error">Erro ao processar confirmação. Tente novamente.</div>';
        }

        if ($user) {
            // ============ VERIFICAR EXPIRAÇÃO ============
            if (new DateTime() > new DateTime($user['email_token_expires'])) {
                $message = '<div class="alert alert-error">Link de confirmação expirado. Solicite um novo e-mail.</div>';
            } else {
                // ============ CONFIRMAR E-MAIL ============
                $updateStmt = $conn->prepare("
                    UPDATE usuarios
                    SET email_confirmed = 1, email_token = NULL, email_token_expires = NULL, status = 'ativo'
                    WHERE id = ?
                ");

                if ($updateStmt) {
                    $updateStmt->bind_param("i", $user['id']);
                    if ($updateStmt->execute()) {
                        $_SESSION['usuario_id'] = $user['id'];
                        $_SESSION['email_confirmed'] = true;
                        $updateStmt->close();
                        $conn->close();
                        header('Location: dashboard/first-after-signup.php');
                        exit;
                    } else {
                        $message = '<div class="alert alert-error">Erro ao confirmar e-mail. Tente novamente.</div>';
                    }
                    $updateStmt->close();
                } else {
                    $message = '<div class="alert alert-error">Erro ao confirmar e-mail. Tente novamente.</div>';
                }
            }
        } else {
            if (empty($message)) {
                $message = '<div class="alert alert-error">Link de confirmação inválido ou já utilizado.</div>';
            }
        }

        // Ensure connection is closed if not already closed by a redirect/exit
        if ($conn) {
            $conn->close();
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">Erro ao processar confirmação. Tente novamente.</div>';
    }
}
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Confirmação de E-mail</h1>
            <?php if (!empty($email)): ?>
                <p>Enviamos um link de confirmação para <strong><?php echo htmlspecialchars($email); ?></strong></p>
            <?php else: ?>
                <p>Confirme seu endereço de e-mail</p>
            <?php endif; ?>
        </div>

        <div id="message">
            <?php if (!empty($message)): ?>
                <?php echo $message; ?>
            <?php elseif (!empty($email)): ?>
                <div class="alert alert-info">
                    <p>Por favor, verifique sua caixa de entrada e clique no link de confirmação enviado.</p>
                    <p>O link expira em 24 horas.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p>Nenhum e-mail especificado. Verifique sua caixa de entrada pelo e-mail de confirmação.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            <p>Não recebeu o e-mail? <a href="#" id="resendLink">Reenviar e-mail de confirmação</a></p>
            <p><a href="login.php">Já confirmou? Faça login</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.auth-box {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 500px;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.auth-header p {
    color: #666;
    margin: 0;
}

.auth-footer {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
    margin-top: 20px;
}

.auth-footer p {
    margin: 10px 0;
    color: #666;
}

.auth-footer a {
    color: #667eea;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}
</style>

<script>
document.getElementById('resendLink').addEventListener('click', async (e) => {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const resendLink = e.target;
    
    // Disable link during request
    resendLink.textContent = 'Enviando...';
    resendLink.style.pointerEvents = 'none';
    
    try {
        const response = await fetch('api/resend-confirmation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: '<?php echo htmlspecialchars($email); ?>'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-info">E-mail de confirmação reenviado com sucesso! Verifique sua caixa de entrada.</div>';
        } else {
            messageDiv.innerHTML = '<div class="alert alert-warning">' + data.message + '</div>';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-warning">Erro ao reenviar e-mail. Tente novamente.</div>';
    } finally {
        resendLink.textContent = 'Reenviar e-mail de confirmação';
        resendLink.style.pointerEvents = 'auto';
    }
});
</script>

<?php include 'includes/footer.php'; ?>