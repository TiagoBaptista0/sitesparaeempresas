<?php
$page_title = 'Planos';
require_once 'config/db.php';

// Buscar planos ativos
$result = $conn->query("SELECT * FROM planos WHERE ativo = 1 ORDER BY preco ASC");
$planos = $result->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<section class="plans-hero">
    <div class="container">
        <h1>Escolha o Plano Ideal para Sua Empresa</h1>
        <p>Todos os planos incluem suporte técnico e atualizações gratuitas</p>
    </div>
</section>

<section class="plans">
    <div class="container">
        <div class="plans-grid">
            <?php foreach ($planos as $plano): 
                $recursos = json_decode($plano['recursos'], true);
            ?>
            <div class="plan-card">
                <div class="plan-header">
                    <h3><?php echo htmlspecialchars($plano['nome']); ?></h3>
                    <div class="plan-price">
                        <span class="currency">R$</span>
                        <span class="amount"><?php echo number_format($plano['preco'], 2, ',', '.'); ?></span>
                        <span class="period">/mês</span>
                    </div>
                    <p class="plan-description"><?php echo htmlspecialchars($plano['descricao']); ?></p>
                </div>
                
                <div class="plan-features">
                    <ul>
                        <?php if ($recursos): ?>
                            <?php foreach ($recursos as $key => $value): ?>
                                <li>
                                    <i class="fas fa-check"></i>
                                    <?php 
                                    $label = ucfirst(str_replace('_', ' ', $key));
                                    if (is_bool($value)) {
                                        echo $value ? $label : '';
                                    } else {
                                        echo "$label: $value";
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="plan-footer">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <button class="btn btn-primary btn-block" onclick="assinarPlano(<?php echo $plano['id']; ?>)">
                            Assinar Agora
                        </button>
                    <?php else: ?>
                        <a href="cadastro.php" class="btn btn-primary btn-block">Começar Agora</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.plans-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
}

.plans-hero h1 {
    font-size: 42px;
    margin-bottom: 15px;
}

.plans-hero p {
    font-size: 18px;
    opacity: 0.9;
}

.plans {
    padding: 80px 0;
    background: #f8f9fa;
}

.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.plan-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.plan-card:hover {
    transform: translateY(-10px);
}

.plan-header {
    padding: 40px 30px;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.plan-header h3 {
    font-size: 28px;
    margin-bottom: 20px;
}

.plan-price {
    margin: 20px 0;
}

.plan-price .currency {
    font-size: 24px;
    vertical-align: top;
}

.plan-price .amount {
    font-size: 48px;
    font-weight: bold;
}

.plan-price .period {
    font-size: 18px;
    opacity: 0.8;
}

.plan-description {
    opacity: 0.9;
    margin-top: 15px;
}

.plan-features {
    padding: 40px 30px;
}

.plan-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.plan-features li {
    padding: 12px 0;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 10px;
}

.plan-features li:last-child {
    border-bottom: none;
}

.plan-features i {
    color: #4CAF50;
    font-size: 18px;
}

.plan-footer {
    padding: 0 30px 40px;
}

@media (max-width: 768px) {
    .plans-hero h1 {
        font-size: 28px;
    }
    
    .plans-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function assinarPlano(planoId) {
    if (confirm('Deseja assinar este plano?')) {
        window.location.href = 'api/assinar.php?plano_id=' + planoId;
    }
}
</script>

<?php include 'includes/footer.php'; ?>