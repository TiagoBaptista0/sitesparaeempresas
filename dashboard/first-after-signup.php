<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

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

$page_title = 'Bem-vindo!';
include '../includes/header.php';
?>

<div class="container">
    <div class="welcome-section">
        <div class="welcome-header">
            <h1>Bem-vindo, <?php echo htmlspecialchars(explode(' ', $usuario['nome'])[0]); ?>!</h1>
            <p>Sua conta foi criada com sucesso. Agora vamos configurar seu site.</p>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Escolha seu Plano</h3>
                    <p>Selecione o plano que melhor se adequa às suas necessidades</p>
                    <a href="domain-selection.php" class="btn btn-primary">Começar</a>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Registre seu Domínio</h3>
                    <p>Escolha um domínio único para seu site</p>
                    <p class="step-status">Próximo passo</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Confirme seu Pedido</h3>
                    <p>Revise os detalhes e confirme</p>
                    <p class="step-status">Próximo passo</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>Realize o Pagamento</h3>
                    <p>Pague de forma segura via Mercado Pago</p>
                    <p class="step-status">Próximo passo</p>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h2>O que você pode fazer agora:</h2>
            <ul class="features-list">
                <li>✓ Escolher entre nossos planos de hospedagem</li>
                <li>✓ Registrar um novo domínio</li>
                <li>✓ Configurar seu site</li>
                <li>✓ Acessar o painel de controle</li>
                <li>✓ Receber suporte técnico 24/7</li>
            </ul>
        </div>

        <div class="cta-section">
            <h2>Pronto para começar?</h2>
            <p>Clique no botão abaixo para escolher seu plano e domínio</p>
            <a href="domain-selection.php" class="btn btn-primary btn-large">Escolher Plano e Domínio</a>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 40px 20px;
}

.welcome-section {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.welcome-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 40px;
    text-align: center;
}

.welcome-header h1 {
    margin: 0 0 10px 0;
    font-size: 36px;
}

.welcome-header p {
    margin: 0;
    font-size: 18px;
    opacity: 0.9;
}

.steps-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 40px;
}

.step-card {
    background: #f9f9f9;
    border: 2px solid #eee;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s;
}

.step-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
}

.step-content h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 18px;
}

.step-content p {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 14px;
}

.step-status {
    color: #999;
    font-size: 12px;
    font-style: italic;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn:hover {
    background: #5568d3;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-large {
    padding: 15px 40px;
    font-size: 16px;
    display: block;
    width: 100%;
    text-align: center;
}

.info-section {
    padding: 40px;
    background: #f0f8ff;
    border-top: 1px solid #eee;
}

.info-section h2 {
    color: #2c3e50;
    margin: 0 0 20px 0;
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.features-list li {
    padding: 10px 0;
    color: #333;
    font-size: 16px;
}

.cta-section {
    padding: 40px;
    text-align: center;
    background: white;
    border-top: 1px solid #eee;
}

.cta-section h2 {
    color: #2c3e50;
    margin: 0 0 10px 0;
}

.cta-section p {
    color: #666;
    margin: 0 0 20px 0;
}

@media (max-width: 768px) {
    .welcome-header {
        padding: 40px 20px;
    }

    .welcome-header h1 {
        font-size: 24px;
    }

    .steps-container {
        grid-template-columns: 1fr;
        padding: 20px;
    }

    .btn-large {
        padding: 12px 20px;
        font-size: 14px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
