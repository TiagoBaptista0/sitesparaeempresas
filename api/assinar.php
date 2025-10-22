<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$plano_id = isset($_GET['plano_id']) ? intval($_GET['plano_id']) : 0;

if ($plano_id <= 0) {
    die('Plano inválido');
}

// Buscar informações do plano
$stmt = $conn->prepare("SELECT * FROM planos WHERE id = ?");
$stmt->bind_param("i", $plano_id);
$stmt->execute();
$result = $stmt->get_result();
$plano = $result->fetch_assoc();
$stmt->close();

if (!$plano) {
    die('Plano não encontrado');
}

// Buscar informações do usuário
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Verificar se já tem assinatura ativa
$stmt = $conn->prepare("SELECT * FROM assinaturas WHERE usuario_id = ? AND status = 'ativa'");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$assinatura_existente = $result->fetch_assoc();
$stmt->close();

$page_title = 'Assinar Plano';
include '../includes/header.php';
?>

<div class="container">
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Finalizar Assinatura</h1>
            <p>Complete o pagamento para ativar seu plano</p>
        </div>

        <?php if ($assinatura_existente): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Atenção:</strong> Você já possui uma assinatura ativa. Ao confirmar, sua assinatura atual será substituída.
        </div>
        <?php endif; ?>

        <div class="checkout-grid">
            <div class="checkout-summary">
                <h2>Resumo do Pedido</h2>

                <div class="plan-details">
                    <h3><?php echo htmlspecialchars($plano['nome']); ?></h3>
                    <p class="plan-description"><?php echo htmlspecialchars($plano['descricao']); ?></p>

                    <div class="features-list">
                        <h4>Recursos inclusos:</h4>
                        <ul>
                            <?php
                            $recursos = json_decode($plano['recursos'], true);
                            if ($recursos && is_array($recursos)) {
                                foreach ($recursos as $recurso) {
                                    echo '<li><i class="fas fa-check"></i> ' . htmlspecialchars($recurso) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>

                <div class="price-summary">
                    <div class="price-row">
                        <span>Plano <?php echo htmlspecialchars($plano['nome']); ?></span>
                        <span>R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="price-row total">
                        <span>Total</span>
                        <span>R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?>/mês</span>
                    </div>
                </div>
            </div>

            <div class="checkout-payment">
                <h2>Dados de Pagamento</h2>

                <form id="paymentForm">
                    <input type="hidden" name="plano_id" value="<?php echo $plano_id; ?>">
                    <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
                    <input type="hidden" name="valor" value="<?php echo $plano['preco']; ?>">

                    <div class="payment-methods">
                        <label class="payment-method active">
                            <input type="radio" name="metodo_pagamento" value="credit_card" checked>
                            <div class="method-content">
                                <i class="fas fa-credit-card"></i>
                                <span>Cartão de Crédito</span>
                            </div>
                        </label>

                        <label class="payment-method">
                            <input type="radio" name="metodo_pagamento" value="pix">
                            <div class="method-content">
                                <i class="fas fa-qrcode"></i>
                                <span>PIX</span>
                            </div>
                        </label>

                        <label class="payment-method">
                            <input type="radio" name="metodo_pagamento" value="boleto">
                            <div class="method-content">
                                <i class="fas fa-barcode"></i>
                                <span>Boleto</span>
                            </div>
                        </label>
                    </div>

                    <div id="creditCardFields" class="payment-fields">
                        <div class="form-group">
                            <label for="cardNumber">Número do Cartão</label>
                            <input type="text" id="cardNumber" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>

                        <div class="form-group">
                            <label for="cardName">Nome no Cartão</label>
                            <input type="text" id="cardName" name="card_name" placeholder="Nome como está no cartão">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cardExpiry">Validade</label>
                                <input type="text" id="cardExpiry" name="card_expiry" placeholder="MM/AA" maxlength="5">
                            </div>

                            <div class="form-group">
                                <label for="cardCvv">CVV</label>
                                <input type="text" id="cardCvv" name="card_cvv" placeholder="123" maxlength="4">
                            </div>
                        </div>
                    </div>

                    <div id="pixFields" class="payment-fields" style="display: none;">
                        <div class="pix-info">
                            <i class="fas fa-info-circle"></i>
                            <p>Após confirmar, você receberá um QR Code para pagamento via PIX. O pagamento é processado instantaneamente.</p>
                        </div>
                    </div>

                    <div id="boletoFields" class="payment-fields" style="display: none;">
                        <div class="boleto-info">
                            <i class="fas fa-info-circle"></i>
                            <p>Após confirmar, você receberá um boleto bancário. O pagamento pode levar até 3 dias úteis para ser processado.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="aceitar_termos" required>
                            <span>Aceito os <a href="../termos.php" target="_blank">termos de uso</a> e <a href="../privacidade.php" target="_blank">política de privacidade</a></span>
                        </label>
                    </div>

                    <div id="message"></div>

                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-lock"></i> Confirmar Pagamento
                    </button>

                    <p class="security-note">
                        <i class="fas fa-shield-alt"></i> Pagamento 100% seguro e criptografado
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.checkout-header {
    text-align: center;
    margin-bottom: 40px;
}

.checkout-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.checkout-summary,
.checkout-payment {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.checkout-summary h2,
.checkout-payment h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
}

.plan-details h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 24px;
}

.plan-description {
    color: #666;
    margin-bottom: 20px;
}

.features-list h4 {
    margin: 20px 0 10px 0;
    color: #2c3e50;
}

.features-list ul {
    list-style: none;
    padding: 0;
}

.features-list li {
    padding: 8px 0;
    color: #666;
}

.features-list li i {
    color: #4CAF50;
    margin-right: 10px;
}

.price-summary {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.price-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    color: #666;
}

.price-row.total {
    font-size: 20px;
    font-weight: bold;
    color: #2c3e50;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

.payment-method {
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.payment-method:hover {
    border-color: #007bff;
}

.payment-method.active {
    border-color: #007bff;
    background: #f0f8ff;
}

.payment-method input[type="radio"] {
    display: none;
}

.method-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.method-content i {
    font-size: 24px;
    color: #007bff;
}

.payment-fields {
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input[type="text"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.pix-info,
.boleto-info {
    background: #f0f8ff;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.pix-info i,
.boleto-info i {
    color: #007bff;
    margin-right: 10px;
}

.btn-large {
    width: 100%;
    padding: 15px;
    font-size: 16px;
    font-weight: bold;
}

.security-note {
    text-align: center;
    color: #666;
    font-size: 14px;
    margin-top: 15px;
}

.security-note i {
    color: #4CAF50;
    margin-right: 5px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border-left: 4px solid #ffc107;
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

@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }

    .payment-methods {
        grid-template-columns: 1fr;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Alternar métodos de pagamento
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('active'));
        this.classList.add('active');

        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;

        // Mostrar campos apropriados
        document.getElementById('creditCardFields').style.display = 'none';
        document.getElementById('pixFields').style.display = 'none';
        document.getElementById('boletoFields').style.display = 'none';

        if (radio.value === 'credit_card') {
            document.getElementById('creditCardFields').style.display = 'block';
        } else if (radio.value === 'pix') {
            document.getElementById('pixFields').style.display = 'block';
        } else if (radio.value === 'boleto') {
            document.getElementById('boletoFields').style.display = 'block';
        }
    });
});

// Máscara para número do cartão
document.getElementById('cardNumber')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})/g, '$1 ').trim();
    e.target.value = value;
});

// Máscara para validade
document.getElementById('cardExpiry')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

// Máscara para CVV
document.getElementById('cardCvv')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

// Processar pagamento
document.getElementById('paymentForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const messageDiv = document.getElementById('message');
    const submitBtn = e.target.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';

    const formData = new FormData(e.target);

    try {
        const response = await fetch('processar-pagamento.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">Pagamento processado com sucesso! Redirecionando...</div>';

            if (data.payment_url) {
                // Redirecionar para página de pagamento (PIX ou Boleto)
                setTimeout(() => window.location.href = data.payment_url, 2000);
            } else {
                // Redirecionar para dashboard
                setTimeout(() => window.location.href = '../dashboard/index.php', 2000);
            }
        } else {
            messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock"></i> Confirmar Pagamento';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">Erro ao processar pagamento. Tente novamente.</div>';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-lock"></i> Confirmar Pagamento';
    }
});
</script>

<?php include '../includes/footer.php'; ?>
