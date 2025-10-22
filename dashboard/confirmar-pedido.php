<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar informações do cliente com domínio selecionado
$stmt = $conn->prepare("
    SELECT c.*, u.email, p.nome as plano_nome, p.preco as plano_preco
    FROM clientes c
    JOIN users u ON c.user_id = u.id
    JOIN planos p ON c.plano_id = p.id
    WHERE c.user_id = ? AND c.status = 'aguardando_pagamento'
");

if (!$stmt) {
    die("Erro na preparação: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Verificar se encontrou cliente
if (!$cliente) {
    header('Location: index.php');
    exit;
}

// Verificar se tem domínio
if (empty($cliente['dominio'])) {
    header('Location: escolher-dominio.php');
    exit;
}

// ============ DEFINIR PREÇO DO DOMÍNIO ============
$precos = [
    '.com.br' => 39,
    '.com' => 89,
    '.net' => 79,
    '.info' => 49,
    '.biz' => 59
];

// Extrair extensão
$partes = explode('.', $cliente['dominio']);
$extensao = '.' . implode('.', array_slice($partes, 1));

$preco_dominio = $precos[$extensao] ?? 39;
$preco_plano = $cliente['plano_preco'];
$total = $preco_plano + $preco_dominio;

$page_title = 'Confirmar Pedido - SitesParaEmpresas';
include '../includes/header.php';
?>

<div class="container">
    <div style="max-width: 800px; margin: 30px auto;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1>📋 Confirmação do Pedido</h1>
            <p>Revise as informações antes de prosseguir com o pagamento</p>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <h2>Resumo do Pedido</h2>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <div>
                    <p style="margin: 0; font-weight: bold;">Plano: <?php echo htmlspecialchars($cliente['plano_nome']); ?></p>
                    <p style="margin: 5px 0 0 0; color: #666;">1 mês</p>
                </div>
                <div>
                    <p style="margin: 0; font-weight: bold;">R$ <?php echo number_format($preco_plano, 2, ',', '.'); ?></p>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <div>
                    <p style="margin: 0; font-weight: bold;">Domínio: <?php echo htmlspecialchars($cliente['dominio']); ?></p>
                    <p style="margin: 5px 0 0 0; color: #666;">1 ano</p>
                </div>
                <div>
                    <p style="margin: 0; font-weight: bold;">R$ <?php echo number_format($preco_dominio, 2, ',', '.'); ?></p>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <div>
                    <p style="margin: 0; font-size: 18px; font-weight: bold;">Total</p>
                </div>
                <div>
                    <p style="margin: 0; font-size: 24px; font-weight: bold; color: #27ae60;">R$ <?php echo number_format($total, 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <h2>Dados do Cliente</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($cliente['nome_empresa']); ?></p>
                    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
                </div>
                <div>
                    <p><strong>Domínio:</strong> <?php echo htmlspecialchars($cliente['dominio']); ?></p>
                    <p><strong>Plano:</strong> <?php echo htmlspecialchars($cliente['plano_nome']); ?></p>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <button id="proceedToPayment" class="btn" style="padding: 15px 40px; font-size: 18px;">
                <i class="fas fa-lock"></i> Prosseguir para Pagamento
            </button>
            <p style="margin-top: 15px; font-size: 14px; color: #666;">
                <i class="fas fa-shield-alt"></i> Pagamento seguro via Mercado Pago
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('proceedToPayment').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    
    try {
        const formData = new FormData();
        formData.append('plano_price', <?php echo $preco_plano; ?>);
        formData.append('domain_price', <?php echo $preco_dominio; ?>);
        formData.append('domain', '<?php echo $cliente['dominio']; ?>');
        
        const response = await fetch('../api/create-order.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success && result.init_point) {
            // Redirecionar para o Mercado Pago
            window.location.href = result.init_point;
        } else {
            alert('Erro ao criar pedido: ' + (result.message || 'Tente novamente'));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Prosseguir para Pagamento';
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao processar pagamento. Tente novamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Prosseguir para Pagamento';
    }
});
</script>

<?php
$conn->close();
include '../includes/footer.php';
?>