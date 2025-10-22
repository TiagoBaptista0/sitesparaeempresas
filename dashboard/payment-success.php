<?php
session_start();
require_once '../config/db.php';
require_once '../vendor/autoload.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obter dados do Mercado Pago
$payment_id = $_GET['payment_id'] ?? null;
$preference_id = $_GET['preference_id'] ?? null;
$merchant_order_id = $_GET['merchant_order_id'] ?? null;

$mp_access_token = getenv('MP_ACCESS_TOKEN') ?: (defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : null);
if (!$mp_access_token) {
    die('Erro: Token do Mercado Pago não configurado');
}

\MercadoPago\SDK::setAccessToken($mp_access_token);

$payment_info = null;
$payment_status = 'unknown';

if ($payment_id) {
    try {
        $payment = \MercadoPago\Payment::find_by_id($payment_id);
        $payment_info = $payment;
        $payment_status = $payment->status;
    } catch (Exception $e) {
        error_log("Erro ao buscar pagamento: " . $e->getMessage());
    }
}

// Atualizar status do pedido no banco de dados
if ($payment_status === 'approved') {
    $stmt = $conn->prepare("
        UPDATE pedidos 
        SET status = 'aprovado', mercadopago_payment_id = ?, data_pagamento = NOW()
        WHERE usuario_id = ? AND status = 'pendente'
        ORDER BY id DESC LIMIT 1
    ");
    
    if ($stmt) {
        $stmt->bind_param("si", $payment_id, $usuario_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Buscar informações do pedido
    $stmt = $conn->prepare("
        SELECT * FROM pedidos 
        WHERE usuario_id = ? AND mercadopago_payment_id = ?
        LIMIT 1
    ");
    
    if ($stmt) {
        $stmt->bind_param("is", $usuario_id, $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        $stmt->close();
        
        if ($pedido) {
            // Criar assinatura
            $data_inicio = date('Y-m-d');
            $data_fim = date('Y-m-d', strtotime('+1 month'));
            
            $stmt = $conn->prepare("
                INSERT INTO assinaturas (usuario_id, plano_id, status, data_inicio, data_fim, data_proximo_pagamento, valor)
                VALUES (?, ?, 'ativa', ?, ?, ?, ?)
            ");
            
            if ($stmt) {
                $stmt->bind_param("iisssd", $usuario_id, $pedido['plano_id'], $data_inicio, $data_fim, $data_fim, $pedido['valor_plano']);
                $stmt->execute();
                $stmt->close();
            }
            
            // Criar registro de pagamento
            $stmt = $conn->prepare("
                INSERT INTO pagamentos (usuario_id, valor, status, metodo_pagamento, mercadopago_payment_id, data_pagamento, descricao)
                VALUES (?, ?, 'aprovado', 'mercadopago', ?, NOW(), ?)
            ");
            
            if ($stmt) {
                $descricao = "Plano + Domínio: " . $pedido['dominio'];
                $stmt->bind_param("idss", $usuario_id, $pedido['valor_total'], $payment_id, $descricao);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Buscar informações do usuário
$stmt = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

$page_title = 'Pagamento Processado';
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box" style="max-width: 600px;">
        <?php if ($payment_status === 'approved'): ?>
            <div class="success-header">
                <div class="success-icon">✓</div>
                <h1>Pagamento Aprovado!</h1>
                <p>Sua assinatura foi ativada com sucesso</p>
            </div>

            <div class="success-content">
                <div class="success-info">
                    <h3>Próximos Passos:</h3>
                    <ol>
                        <li>Você receberá um e-mail de confirmação em <strong><?php echo htmlspecialchars($usuario['email']); ?></strong></li>
                        <li>Seu domínio será registrado em breve</li>
                        <li>Você poderá acessar seu painel de controle para gerenciar seu site</li>
                    </ol>
                </div>

                <div class="payment-details">
                    <h4>Detalhes do Pagamento:</h4>
                    <p><strong>ID do Pagamento:</strong> <?php echo htmlspecialchars($payment_id); ?></p>
                    <p><strong>Status:</strong> <span class="status-approved">Aprovado</span></p>
                    <?php if ($payment_info): ?>
                        <p><strong>Valor:</strong> R$ <?php echo number_format($payment_info->transaction_amount, 2, ',', '.'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="button-group">
                <a href="index.php" class="btn btn-primary">Ir para Dashboard</a>
            </div>

        <?php elseif ($payment_status === 'pending'): ?>
            <div class="pending-header">
                <div class="pending-icon">⏳</div>
                <h1>Pagamento Pendente</h1>
                <p>Seu pagamento está sendo processado</p>
            </div>

            <div class="pending-content">
                <p>Você receberá uma confirmação por e-mail assim que o pagamento for processado.</p>
                <p>ID do Pagamento: <strong><?php echo htmlspecialchars($payment_id); ?></strong></p>
            </div>

            <div class="button-group">
                <a href="index.php" class="btn btn-primary">Voltar para Dashboard</a>
            </div>

        <?php else: ?>
            <div class="error-header">
                <div class="error-icon">✗</div>
                <h1>Erro no Pagamento</h1>
                <p>Não foi possível processar seu pagamento</p>
            </div>

            <div class="error-content">
                <p>Por favor, tente novamente ou entre em contato com nosso suporte.</p>
                <?php if ($payment_id): ?>
                    <p>ID do Pagamento: <strong><?php echo htmlspecialchars($payment_id); ?></strong></p>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <a href="domain-selection.php" class="btn btn-primary">Tentar Novamente</a>
                <a href="index.php" class="btn btn-secondary">Voltar para Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.success-header,
.pending-header,
.error-header {
    text-align: center;
    margin-bottom: 30px;
}

.success-icon,
.pending-icon,
.error-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.success-icon {
    color: #4CAF50;
}

.pending-icon {
    color: #FF9800;
}

.error-icon {
    color: #f44336;
}

.success-header h1,
.pending-header h1,
.error-header h1 {
    font-size: 28px;
    margin-bottom: 10px;
}

.success-content,
.pending-content,
.error-content {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.success-info ol {
    margin: 15px 0;
    padding-left: 20px;
}

.success-info li {
    margin-bottom: 10px;
    color: #333;
}

.payment-details {
    background: white;
    padding: 15px;
    border-radius: 4px;
    margin-top: 15px;
}

.payment-details h4 {
    margin: 0 0 15px 0;
    color: #333;
}

.payment-details p {
    margin: 8px 0;
    color: #666;
}

.status-approved {
    background: #4CAF50;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
}

.button-group {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background: #e0e0e0;
}
</style>

<?php include '../includes/footer.php'; ?>
