<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar pagamentos do usuário
$stmt = $conn->prepare("
    SELECT * FROM pagamentos 
    WHERE usuario_id = ? 
    ORDER BY data_criacao DESC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pagamentos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Buscar assinatura ativa
$stmt = $conn->prepare("
    SELECT a.*, p.nome as plano_nome 
    FROM assinaturas a 
    JOIN planos p ON a.plano_id = p.id 
    WHERE a.usuario_id = ? AND a.status = 'ativa'
    ORDER BY a.data_criacao DESC 
    LIMIT 1
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$assinatura = $result->fetch_assoc();
$stmt->close();

$page_title = 'Pagamentos';

?>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">
            <h2>Sites Para Empresas</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="meu-site.php"><i class="fas fa-globe"></i> Meu Site</a>
            <a href="pagamentos.php" class="active"><i class="fas fa-credit-card"></i> Pagamentos</a>
            <a href="suporte.php"><i class="fas fa-headset"></i> Suporte</a>
            <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
            <a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>

    <main class="dashboard-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-credit-card"></i> Pagamentos</h1>
            <p>Histórico de pagamentos e assinatura</p>
        </div>

        <?php if ($assinatura): ?>
        <div class="card">
            <h2>Assinatura Atual</h2>
            <div class="subscription-info">
                <div class="sub-item">
                    <label>Plano:</label>
                    <p><?php echo htmlspecialchars($assinatura['plano_nome']); ?></p>
                </div>
                <div class="sub-item">
                    <label>Valor:</label>
                    <p class="price">R$ <?php echo number_format($assinatura['valor'], 2, ',', '.'); ?>/mês</p>
                </div>
                <div class="sub-item">
                    <label>Status:</label>
                    <p><span class="badge badge-<?php echo $assinatura['status']; ?>"><?php echo ucfirst($assinatura['status']); ?></span></p>
                </div>
                <div class="sub-item">
                    <label>Próximo Pagamento:</label>
                    <p><?php echo $assinatura['data_proximo_pagamento'] ? date('d/m/Y', strtotime($assinatura['data_proximo_pagamento'])) : 'N/A'; ?></p>
                </div>
            </div>
            <div class="subscription-actions">
                <a href="../planos.php" class="btn btn-secondary">Alterar Plano</a>
                <button class="btn btn-danger" onclick="cancelarAssinatura()">Cancelar Assinatura</button>
            </div>
        </div>
        <?php else: ?>
        <div class="card alert-warning">
            <h2><i class="fas fa-exclamation-triangle"></i> Nenhuma Assinatura Ativa</h2>
            <p>Você não possui uma assinatura ativa. Escolha um plano para continuar.</p>
            <a href="../planos.php" class="btn btn-primary">Ver Planos</a>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>Histórico de Pagamentos</h2>
            <?php if (count($pagamentos) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Método</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagamentos as $pagamento): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($pagamento['data_criacao'])); ?></td>
                            <td><?php echo htmlspecialchars($pagamento['descricao'] ?: 'Pagamento de assinatura'); ?></td>
                            <td>R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $pagamento['status']; ?>">
                                    <?php echo ucfirst($pagamento['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $pagamento['metodo_pagamento'] ?: 'N/A'; ?></td>
                            <td>
                                <?php if ($pagamento['status'] == 'aprovado'): ?>
                                    <button class="btn-small btn-secondary" onclick="gerarRecibo(<?php echo $pagamento['id']; ?>)">
                                        <i class="fas fa-file-pdf"></i> Recibo
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">Nenhum pagamento registrado ainda.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
.dashboard-container {
    display: flex;
    min-height: 100vh;
    background: #f5f5f5;
}

.sidebar {
    width: 250px;
    background: #2c3e50;
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}

.sidebar .logo {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar .logo h2 {
    font-size: 18px;
    margin: 0;
}

.sidebar-nav {
    padding: 20px 0;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
    background: rgba(255,255,255,0.1);
    color: white;
}

.sidebar-nav a i {
    margin-right: 10px;
    width: 20px;
}

.dashboard-content {
    margin-left: 250px;
    flex: 1;
    padding: 30px;
}

.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
}

.subscription-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.sub-item label {
    display: block;
    font-weight: bold;
    color: #666;
    margin-bottom: 5px;
}

.sub-item p {
    margin: 0;
    color: #2c3e50;
}

.sub-item .price {
    font-size: 24px;
    font-weight: bold;
    color: #4CAF50;
}

.subscription-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.alert-warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background: #f8f9fa;
    font-weight: bold;
}

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-ativa, .badge-aprovado { background: #4CAF50; color: white; }
.badge-pendente { background: #FF9800; color: white; }
.badge-cancelada, .badge-recusado { background: #f44336; color: white; }

.btn-small {
    padding: 5px 10px;
    font-size: 12px;
}

.text-muted {
    color: #666;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .dashboard-content {
        margin-left: 0;
    }
}
</style>

<script>
function cancelarAssinatura() {
    if (confirm('Tem certeza que deseja cancelar sua assinatura?')) {
        window.location.href = '../api/cancelar-assinatura.php';
    }
}

function gerarRecibo(pagamentoId) {
    window.open('../api/gerar-recibo.php?id=' + pagamentoId, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>