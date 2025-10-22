<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar informações do usuário
$stmt = $conn->prepare("SELECT nome, email, tipo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Buscar assinatura ativa
$stmt = $conn->prepare("
    SELECT a.*, p.nome as plano_nome, p.recursos 
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

// Buscar site do usuário
$stmt = $conn->prepare("SELECT * FROM sites WHERE usuario_id = ? ORDER BY data_criacao DESC LIMIT 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$site = $result->fetch_assoc();
$stmt->close();

// Buscar últimos pagamentos
$stmt = $conn->prepare("
    SELECT * FROM pagamentos 
    WHERE usuario_id = ? 
    ORDER BY data_criacao DESC 
    LIMIT 5
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pagamentos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Buscar tickets abertos
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM tickets 
    WHERE usuario_id = ? AND status IN ('aberto', 'em_andamento')
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$tickets_abertos = $result->fetch_assoc()['total'];
$stmt->close();

$page_title = 'Dashboard';

?>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">
            <h2>Sites Para Empresas</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="meu-site.php"><i class="fas fa-globe"></i> Meu Site</a>
            <a href="pagamentos.php"><i class="fas fa-credit-card"></i> Pagamentos</a>
            <a href="suporte.php"><i class="fas fa-headset"></i> Suporte</a>
            <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>

    <main class="dashboard-content">
        <div class="dashboard-header">
            <h1>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h1>
            <p>Gerencie seu site e assinatura</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #4CAF50;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Status da Assinatura</h3>
                    <p class="stat-value"><?php echo $assinatura ? 'Ativa' : 'Inativa'; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #2196F3;">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="stat-info">
                    <h3>Status do Site</h3>
                    <p class="stat-value"><?php echo $site ? ucfirst(str_replace('_', ' ', $site['status'])) : 'Não criado'; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #FF9800;">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Tickets Abertos</h3>
                    <p class="stat-value"><?php echo $tickets_abertos; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #9C27B0;">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-info">
                    <h3>Próximo Pagamento</h3>
                    <p class="stat-value">
                        <?php 
                        if ($assinatura && $assinatura['data_proximo_pagamento']) {
                            echo date('d/m/Y', strtotime($assinatura['data_proximo_pagamento']));
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if ($assinatura): ?>
        <div class="card">
            <h2><i class="fas fa-star"></i> Seu Plano Atual</h2>
            <div class="plan-info">
                <h3><?php echo htmlspecialchars($assinatura['plano_nome']); ?></h3>
                <p class="plan-price">R$ <?php echo number_format($assinatura['valor'], 2, ',', '.'); ?>/mês</p>
                <div class="plan-features">
                    <?php 
                    $recursos = json_decode($assinatura['recursos'], true);
                    if ($recursos):
                        foreach ($recursos as $key => $value):
                    ?>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span><?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo is_bool($value) ? ($value ? 'Sim' : 'Não') : $value; ?></span>
                        </div>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
                <p class="plan-dates">
                    <strong>Válido até:</strong> <?php echo $assinatura['data_fim'] ? date('d/m/Y', strtotime($assinatura['data_fim'])) : 'Indeterminado'; ?>
                </p>
            </div>
        </div>
        <?php else: ?>
        <div class="card alert-warning">
            <h2><i class="fas fa-exclamation-triangle"></i> Nenhuma Assinatura Ativa</h2>
            <p>Você não possui uma assinatura ativa no momento. Escolha um plano para começar a criar seu site!</p>
            <a href="../planos.php" class="btn btn-primary">Escolher Plano</a>
        </div>
        <?php endif; ?>

        <?php if ($site): ?>
        <div class="card">
            <h2><i class="fas fa-globe"></i> Seu Site</h2>
            <div class="site-info">
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($site['nome']); ?></p>
                <?php if ($site['dominio']): ?>
                    <p><strong>Domínio:</strong> <a href="http://<?php echo htmlspecialchars($site['dominio']); ?>" target="_blank"><?php echo htmlspecialchars($site['dominio']); ?></a></p>
                <?php endif; ?>
                <?php if ($site['subdominio']): ?>
                    <p><strong>Subdomínio:</strong> <a href="http://<?php echo htmlspecialchars($site['subdominio']); ?>.sitesparaempresas.com" target="_blank"><?php echo htmlspecialchars($site['subdominio']); ?>.sitesparaempresas.com</a></p>
                <?php endif; ?>
                <p><strong>Status:</strong> <span class="badge badge-<?php echo $site['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $site['status'])); ?></span></p>
                <p><strong>Última atualização:</strong> <?php echo date('d/m/Y H:i', strtotime($site['data_atualizacao'])); ?></p>
            </div>
            <div class="site-actions">
                <a href="meu-site.php" class="btn btn-primary">Editar Site</a>
                <?php if ($site['status'] == 'ativo'): ?>
                    <a href="http://<?php echo $site['dominio'] ?: $site['subdominio'] . '.sitesparaempresas.com'; ?>" target="_blank" class="btn btn-secondary">Visualizar</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-history"></i> Últimos Pagamentos</h2>
            <?php if (count($pagamentos) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagamentos as $pagamento): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($pagamento['data_criacao'])); ?></td>
                            <td>R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $pagamento['status']; ?>">
                                    <?php echo ucfirst($pagamento['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $pagamento['metodo_pagamento'] ?: 'N/A'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="pagamentos.php" class="btn btn-secondary">Ver Todos</a>
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-info h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #666;
}

.stat-value {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
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
    display: flex;
    align-items: center;
    gap: 10px;
}

.plan-info {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.plan-price {
    font-size: 32px;
    font-weight: bold;
    color: #4CAF50;
    margin: 10px 0;
}

.plan-features {
    margin: 20px 0;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
}

.feature-item i {
    color: #4CAF50;
}

.site-info p {
    margin: 10px 0;
}

.site-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-ativa, .badge-ativo { background: #4CAF50; color: white; }
.badge-pendente, .badge-em_construcao { background: #FF9800; color: white; }
.badge-cancelada, .badge-inativo { background: #f44336; color: white; }
.badge-aprovado { background: #4CAF50; color: white; }
.badge-recusado { background: #f44336; color: white; }

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

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
