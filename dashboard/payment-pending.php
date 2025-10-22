<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Pagamento Pendente';
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box" style="max-width: 600px;">
        <div class="pending-header">
            <div class="pending-icon">⏳</div>
            <h1>Pagamento Pendente</h1>
            <p>Seu pagamento está sendo processado</p>
        </div>

        <div class="pending-content">
            <div class="pending-message">
                <p>Seu pagamento foi enviado para processamento.</p>
                <p>Você receberá uma confirmação por e-mail assim que o pagamento for aprovado.</p>
            </div>

            <div class="pending-info">
                <h3>O que Fazer Agora?</h3>
                <ol>
                    <li>Verifique seu e-mail para atualizações sobre o pagamento</li>
                    <li>Acesse seu dashboard para acompanhar o status</li>
                    <li>Se o pagamento não for confirmado em 24 horas, entre em contato com o suporte</li>
                </ol>
            </div>

            <div class="support-box">
                <h4>Precisa de Ajuda?</h4>
                <p>Entre em contato com nosso suporte técnico:</p>
                <p><strong>E-mail:</strong> suporte@sitesparaempresas.com</p>
                <p><strong>Telefone:</strong> (11) 3000-0000</p>
            </div>
        </div>

        <div class="button-group">
            <a href="index.php" class="btn btn-primary">Ir para Dashboard</a>
            <a href="../" class="btn btn-secondary">Voltar para Home</a>
        </div>
    </div>
</div>

<style>
.pending-header {
    text-align: center;
    margin-bottom: 30px;
}

.pending-icon {
    display: inline-block;
    width: 80px;
    height: 80px;
    background: #FF9800;
    color: white;
    border-radius: 50%;
    font-size: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.pending-header h1 {
    color: #FF9800;
    margin-bottom: 10px;
}

.pending-content {
    margin: 30px 0;
}

.pending-message {
    background: #fff3e0;
    padding: 20px;
    border-radius: 4px;
    border-left: 4px solid #FF9800;
    margin-bottom: 20px;
}

.pending-message p {
    margin: 10px 0;
    color: #e65100;
}

.pending-info {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.pending-info h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.pending-info ol {
    margin: 0;
    padding-left: 20px;
}

.pending-info li {
    margin: 10px 0;
    color: #666;
}

.support-box {
    background: #e3f2fd;
    padding: 20px;
    border-radius: 4px;
    border-left: 4px solid #2196F3;
}

.support-box h4 {
    margin: 0 0 15px 0;
    color: #1976D2;
}

.support-box p {
    margin: 8px 0;
    color: #555;
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
    background: #FF9800;
    color: white;
}

.btn-primary:hover {
    background: #F57C00;
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
