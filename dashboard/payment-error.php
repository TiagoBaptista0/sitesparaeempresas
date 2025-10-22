<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Erro no Pagamento';
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box" style="max-width: 600px;">
        <div class="error-header">
            <div class="error-icon">✕</div>
            <h1>Erro no Pagamento</h1>
            <p>Não conseguimos processar seu pagamento</p>
        </div>

        <div class="error-content">
            <div class="error-message">
                <p>Desculpe, ocorreu um erro ao processar seu pagamento.</p>
                <p>Por favor, tente novamente ou entre em contato com nosso suporte.</p>
            </div>

            <div class="error-reasons">
                <h3>Possíveis Motivos:</h3>
                <ul>
                    <li>Dados de cartão incorretos</li>
                    <li>Saldo insuficiente</li>
                    <li>Cartão expirado ou bloqueado</li>
                    <li>Erro de conexão com o banco</li>
                </ul>
            </div>

            <div class="support-box">
                <h4>Precisa de Ajuda?</h4>
                <p>Entre em contato com nosso suporte técnico:</p>
                <p><strong>E-mail:</strong> suporte@sitesparaempresas.com</p>
                <p><strong>Telefone:</strong> (11) 3000-0000</p>
            </div>
        </div>

        <div class="button-group">
            <a href="order-confirmation.php" class="btn btn-primary">Tentar Novamente</a>
            <a href="domain-selection.php" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
</div>

<style>
.error-header {
    text-align: center;
    margin-bottom: 30px;
}

.error-icon {
    display: inline-block;
    width: 80px;
    height: 80px;
    background: #f44336;
    color: white;
    border-radius: 50%;
    font-size: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.error-header h1 {
    color: #f44336;
    margin-bottom: 10px;
}

.error-content {
    margin: 30px 0;
}

.error-message {
    background: #ffebee;
    padding: 20px;
    border-radius: 4px;
    border-left: 4px solid #f44336;
    margin-bottom: 20px;
}

.error-message p {
    margin: 10px 0;
    color: #c62828;
}

.error-reasons {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.error-reasons h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.error-reasons ul {
    margin: 0;
    padding-left: 20px;
}

.error-reasons li {
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
    background: #f44336;
    color: white;
}

.btn-primary:hover {
    background: #da190b;
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
