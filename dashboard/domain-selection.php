<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar planos disponíveis
$stmt = $conn->prepare("SELECT id, nome, preco, descricao FROM planos WHERE ativo = 1 ORDER BY preco ASC");
$stmt->execute();
$result = $stmt->get_result();
$planos = [];
while ($row = $result->fetch_assoc()) {
    $planos[] = $row;
}
$stmt->close();

$page_title = 'Escolha seu Plano e Domínio';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Escolha seu Plano e Domínio</h1>
        <p>Selecione o plano que melhor se adequa às suas necessidades</p>
    </div>

    <div class="content-wrapper">
        <div class="plans-section">
            <h2>Nossos Planos</h2>
            <div class="plans-grid">
                <?php foreach ($planos as $plano): ?>
                    <div class="plan-card" data-plan-id="<?php echo $plano['id']; ?>" data-plan-price="<?php echo $plano['preco']; ?>">
                        <div class="plan-header">
                            <h3><?php echo htmlspecialchars($plano['nome']); ?></h3>
                            <div class="plan-price">
                                R$ <span class="price"><?php echo number_format($plano['preco'], 2, ',', '.'); ?></span>
                                <span class="period">/mês</span>
                            </div>
                        </div>
                        <div class="plan-description">
                            <p><?php echo htmlspecialchars($plano['descricao']); ?></p>
                        </div>
                        <button class="btn btn-select-plan" data-plan-id="<?php echo $plano['id']; ?>">
                            Selecionar Plano
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="domain-section" id="domainSection" style="display: none;">
            <h2>Registre seu Domínio</h2>
            <div class="domain-search">
                <input type="text" id="domainInput" placeholder="Digite o nome do seu domínio (ex: meusite)" class="domain-input">
                <select id="domainExtension" class="domain-extension">
                    <option value=".com.br">com.br</option>
                    <option value=".com">com</option>
                    <option value=".net">net</option>
                    <option value=".org">org</option>
                    <option value=".info">info</option>
                </select>
                <button id="searchDomainBtn" class="btn btn-search">Buscar Disponibilidade</button>
            </div>

            <div id="domainResults" class="domain-results" style="display: none;">
                <div id="domainStatus" class="domain-status"></div>
                <div id="domainPrice" class="domain-price" style="display: none;"></div>
            </div>

            <div class="selected-info">
                <div class="selected-plan">
                    <h4>Plano Selecionado:</h4>
                    <p id="selectedPlanName">-</p>
                    <p id="selectedPlanPrice">-</p>
                </div>
                <div class="selected-domain">
                    <h4>Domínio Selecionado:</h4>
                    <p id="selectedDomainName">-</p>
                    <p id="selectedDomainPrice">-</p>
                </div>
            </div>

            <form id="confirmForm" method="POST" action="order-confirmation.php" style="display: none;">
                <input type="hidden" name="plano_id" id="formPlanId">
                <input type="hidden" name="plano_price" id="formPlanPrice">
                <input type="hidden" name="domain" id="formDomain">
                <input type="hidden" name="domain_price" id="formDomainPrice">
                <input type="hidden" name="total" id="formTotal">
                <button type="submit" class="btn btn-primary btn-large">Continuar para Confirmação</button>
            </form>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
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

.content-wrapper {
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
}

.plans-section h2,
.domain-section h2 {
    color: #2c3e50;
    margin: 0 0 20px 0;
    font-size: 24px;
}

.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.plan-card {
    background: white;
    border: 2px solid #eee;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s;
    cursor: pointer;
}

.plan-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.plan-card.selected {
    border-color: #667eea;
    background: #f0f8ff;
}

.plan-header {
    margin-bottom: 20px;
}

.plan-header h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.plan-price {
    font-size: 24px;
    color: #667eea;
    font-weight: bold;
}

.plan-price .period {
    font-size: 14px;
    color: #666;
}

.plan-description {
    margin-bottom: 20px;
    min-height: 60px;
}

.plan-description p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.btn-select-plan {
    width: 100%;
    padding: 10px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-select-plan:hover {
    background: #5568d3;
}

.domain-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #eee;
}

.domain-search {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.domain-input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.domain-extension {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.btn-search {
    padding: 12px 20px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-search:hover {
    background: #5568d3;
}

.domain-results {
    margin-bottom: 20px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
}

.domain-status {
    margin-bottom: 10px;
    font-weight: bold;
}

.domain-status.available {
    color: #4CAF50;
}

.domain-status.unavailable {
    color: #f44336;
}

.domain-price {
    color: #667eea;
    font-size: 16px;
    font-weight: bold;
}

.selected-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
    padding: 20px;
    background: #f0f8ff;
    border-radius: 4px;
}

.selected-plan,
.selected-domain {
    padding: 15px;
    background: white;
    border-radius: 4px;
}

.selected-plan h4,
.selected-domain h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.selected-plan p,
.selected-domain p {
    margin: 5px 0;
    color: #666;
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
    font-size: 16px;
    width: 100%;
}

@media (max-width: 768px) {
    .domain-search {
        flex-direction: column;
    }

    .selected-info {
        grid-template-columns: 1fr;
    }

    .plans-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let selectedPlan = null;
let selectedDomain = null;
let selectedDomainPrice = 0;

document.querySelectorAll('.btn-select-plan').forEach(btn => {
    btn.addEventListener('click', function() {
        const planId = this.dataset.planId;
        const planCard = document.querySelector(`[data-plan-id="${planId}"]`);
        const planName = planCard.querySelector('h3').textContent;
        const planPrice = planCard.dataset.planPrice;

        // Remove previous selection
        document.querySelectorAll('.plan-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Select new plan
        planCard.classList.add('selected');
        selectedPlan = { id: planId, name: planName, price: planPrice };

        // Update selected info
        document.getElementById('selectedPlanName').textContent = planName;
        document.getElementById('selectedPlanPrice').textContent = 'R$ ' + parseFloat(planPrice).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

        // Show domain section
        document.getElementById('domainSection').style.display = 'block';
    });
});

document.getElementById('searchDomainBtn').addEventListener('click', async function() {
    const domain = document.getElementById('domainInput').value.trim();
    const extension = document.getElementById('domainExtension').value;

    if (!domain) {
        alert('Por favor, digite um nome de domínio');
        return;
    }

    const fullDomain = domain + extension;
    const resultsDiv = document.getElementById('domainResults');
    const statusDiv = document.getElementById('domainStatus');

    statusDiv.textContent = 'Verificando disponibilidade...';
    resultsDiv.style.display = 'block';

    try {
        const response = await fetch('../api/check-domain.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ domain: fullDomain })
        });

        const data = await response.json();

        if (data.available) {
            statusDiv.textContent = '✓ Domínio disponível!';
            statusDiv.className = 'domain-status available';
            document.getElementById('domainPrice').textContent = 'R$ ' + parseFloat(data.price).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) + ' por ano';
            document.getElementById('domainPrice').style.display = 'block';

            selectedDomain = fullDomain;
            selectedDomainPrice = data.price;

            document.getElementById('selectedDomainName').textContent = fullDomain;
            document.getElementById('selectedDomainPrice').textContent = 'R$ ' + parseFloat(data.price).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) + ' por ano';

            // Show confirm form
            if (selectedPlan) {
                const total = parseFloat(selectedPlan.price) + parseFloat(selectedDomainPrice);
                document.getElementById('formPlanId').value = selectedPlan.id;
                document.getElementById('formPlanPrice').value = selectedPlan.price;
                document.getElementById('formDomain').value = fullDomain;
                document.getElementById('formDomainPrice').value = selectedDomainPrice;
                document.getElementById('formTotal').value = total;
                document.getElementById('confirmForm').style.display = 'block';
            }
        } else {
            statusDiv.textContent = '✗ Domínio não disponível';
            statusDiv.className = 'domain-status unavailable';
            document.getElementById('domainPrice').style.display = 'none';
        }
    } catch (error) {
        statusDiv.textContent = 'Erro ao verificar domínio. Tente novamente.';
        statusDiv.className = 'domain-status unavailable';
    }
});
</script>

<?php include '../includes/footer.php'; ?>
