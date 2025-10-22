<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Validar dados do POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: domain-selection.php');
    exit;
}

$plano_id = $_POST['plano_id'] ?? null;
$plano_price = floatval($_POST['plano_price'] ?? 0);
$domain = $_POST['domain'] ?? null;
$domain_price = floatval($_POST['domain_price'] ?? 0);
$total = floatval($_POST['total'] ?? 0);

if (!$plano_id || !$domain || $plano_price <= 0 || $domain_price < 0) {
    header('Location: domain-selection.php');
    exit;
}

// Buscar informações do plano
$stmt = $conn->prepare("SELECT nome, descricao FROM planos WHERE id = ?");
$stmt->bind_param("i", $plano_id);
$stmt->execute();
$result = $stmt->get_result();
$plano = $result->fetch_assoc();
$stmt->close();

if (!$plano) {
    header('Location: domain-selection.php');
    exit;
}

// Buscar informações do usuário
$stmt = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$page_title = 'Confirmação do Pedido';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Confirmação do Pedido</h1>
        <p>Revise os detalhes antes de prosseguir para o pagamento</p>
    </div>

    <div class="confirmation-container">
        <div class="order-details">
            <h2>Detalhes do Pedido</h2>

            <div class="detail-section">
                <h3>Informações Pessoais</h3>
                <div class="detail-item">
                    <span class="label">Nome:</span>
                    <span class="value"><?php echo htmlspecialchars($usuario['nome']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">E-mail:</span>
                    <span class="value"><?php echo htmlspecialchars($usuario['email']); ?></span>
                </div>
            </div>

            <div class="detail-section">
                <h3>Plano Selecionado</h3>
                <div class="detail-item">
                    <span class="label">Plano:</span>
                    <span class="value"><?php echo htmlspecialchars($plano['nome']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Descrição:</span>
                    <span class="value"><?php echo htmlspecialchars($plano['descricao']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Preço Mensal:</span>
                    <span class="value">R$ <?php echo number_format($plano_price, 2, ',', '.'); ?></span>
                </div>
            </div>

            <div class="detail-section">
                <h3>Domínio</h3>
                <div class="detail-item">
                    <span class="label">Domínio:</span>
                    <span class="value"><?php echo htmlspecialchars($domain); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Preço (1 ano):</span>
                    <span class="value">R$ <?php echo number_format($domain_price, 2, ',', '.'); ?></span>
                </div>
            </div>

            <div class="detail-section total-section">
                <h3>Resumo Financeiro</h3>
                <div class="detail-item">
                    <span class="label">Plano (1º mês):</span>
                    <span class="value">R$ <?php echo number_format($plano_price, 2, ',', '.'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Domínio (1 ano):</span>
                    <span class="value">R$ <?php echo number_format($domain_price, 2, ',', '.'); ?></span>
                </div>
                <div class="detail-item total">
                    <span class="label">Total a Pagar:</span>
                    <span class="value">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                </div>
            </div>

            <div class="info-box">
                <h4>Informações Importantes:</h4>
                <ul>
                    <li>✓ Seu domínio será registrado em até 24 horas</li>
                    <li>✓ Você receberá um e-mail de confirmação</li>
                    <li>✓ Acesso imediato ao painel de controle</li>
                    <li>✓ Suporte técnico 24/7 disponível</li>
                </ul>
            </div>
        </div>

        <div class="payment-section">
            <h2>Próximo Passo</h2>
            <p>Clique no botão abaixo para prosseguir para o pagamento seguro via Mercado Pago.</p>

            <form method="POST" action="payment.php">
                <input type="hidden" name="plano_id" value="<?php echo htmlspecialchars($plano_id); ?>">
                <input type="hidden" name="plano_price" value="<?php echo htmlspecialchars($plano_price); ?>">
                <input type="hidden" name="domain" value="<?php echo htmlspecialchars($domain); ?>">
                <input type="hidden" name="domain_price" value="<?php echo htmlspecialchars($domain_price); ?>">
                <input type="hidden" name="total" value="<?php echo htmlspecialchars($total); ?>">

                <button type="submit" class="btn btn-primary btn-large">Prosseguir para Pagamento</button>
            </form>

            <a href="domain-selection.php" class="btn btn-secondary">Voltar e Editar</a>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 40px 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    color: #2c3e50;
    margin: 0 0 10px 0;
    font-size: 32px;
}

.page-header p {
    color: #666;
    margin: 0;
    font-size: 16px;
}

.confirmation-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.order-details,
.payment-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.order-details h2,
.payment-section h2 {
    color: #2c3e50;
    margin: 0 0 20px 0;
    font-size: 24px;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
}

.detail-section {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.detail-section:last-child {
    border-bottom: none;
}

.detail-section h3 {
    color: #2c3e50;
    margin: 0 0 15px 0;
    font-size: 16px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    color: #666;
}

.detail-item .label {
    font-weight: 600;
    color: #2c3e50;
}

.detail-item .value {
    text-align: right;
}

.detail-item.total {
    border-top: 2px solid #667eea;
    padding-top: 15px;
    font-size: 18px;
    font-weight: bold;
    color: #667eea;
}

.total-section {
    background: #f0f8ff;
    padding: 15px;
    border-radius: 4px;
    border: none;
}

.info-box {
    background: #e8f5e9;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #4CAF50;
    margin-top: 20px;
}

.info-box h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.info-box ul {
    margin: 0;
    padding-left: 20px;
}

.info-box li {
    margin: 8px 0;
    color: #333;
}

.payment-section p {
    color: #666;
    margin-bottom: 20px;
}

.btn {
    display: block;
    width: 100%;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s;
    margin-bottom: 10px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-large {
    padding: 15px 30px;
    font-size: 18px;
    font-weight: bold;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

@media (max-width: 768px) {
    .confirmation-container {
        grid-template-columns: 1fr;
    }

    .detail-item {
        flex-direction: column;
    }

    .detail-item .value {
        text-align: left;
        margin-top: 5px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
