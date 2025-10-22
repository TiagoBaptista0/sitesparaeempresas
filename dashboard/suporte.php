<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar tickets do usuário
$stmt = $conn->prepare("
    SELECT * FROM tickets 
    WHERE usuario_id = ? 
    ORDER BY data_criacao DESC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Suporte';

?>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">
            <h2>Sites Para Empresas</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="meu-site.php"><i class="fas fa-globe"></i> Meu Site</a>
            <a href="pagamentos.php"><i class="fas fa-credit-card"></i> Pagamentos</a>
            <a href="suporte.php" class="active"><i class="fas fa-headset"></i> Suporte</a>
            <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
            <a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>

    <main class="dashboard-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-headset"></i> Suporte</h1>
            <p>Central de ajuda e tickets</p>
        </div>

        <div class="card">
            <h2>Abrir Novo Ticket</h2>
            <form id="ticketForm" class="form-horizontal">
                <div class="form-group">
                    <label for="assunto">Assunto</label>
                    <input type="text" id="assunto" name="assunto" placeholder="Descreva brevemente o problema" required>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecione...</option>
                        <option value="tecnico">Problema Técnico</option>
                        <option value="financeiro">Financeiro</option>
                        <option value="duvida">Dúvida</option>
                        <option value="sugestao">Sugestão</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="prioridade">Prioridade</label>
                    <select id="prioridade" name="prioridade" required>
                        <option value="baixa">Baixa</option>
                        <option value="media" selected>Média</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" rows="6" placeholder="Descreva detalhadamente seu problema ou dúvida" required></textarea>
                </div>
                
                <div id="message"></div>
                
                <button type="submit" class="btn btn-primary">Enviar Ticket</button>
            </form>
        </div>

        <div class="card">
            <h2>Meus Tickets</h2>
            <?php if (count($tickets) > 0): ?>
            <div class="tickets-list">
                <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-item">
                    <div class="ticket-header">
                        <h3><?php echo htmlspecialchars($ticket['assunto']); ?></h3>
                        <span class="badge badge-<?php echo $ticket['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                        </span>
                    </div>
                    <div class="ticket-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($ticket['data_criacao'])); ?></span>
                        <span><i class="fas fa-tag"></i> <?php echo ucfirst($ticket['categoria']); ?></span>
                        <span><i class="fas fa-flag"></i> <?php echo ucfirst($ticket['prioridade']); ?></span>
                    </div>
                    <div class="ticket-message">
                        <p><?php echo nl2br(htmlspecialchars($ticket['mensagem'])); ?></p>
                    </div>
                    <div class="ticket-actions">
                        <a href="ticket-detalhes.php?id=<?php echo $ticket['id']; ?>" class="btn btn-secondary btn-small">Ver Detalhes</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted">Você ainda não abriu nenhum ticket de suporte.</p>
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

.form-horizontal {
    max-width: 700px;
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

.form-group input[type="text"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
}

.form-group textarea {
    resize: vertical;
}

.tickets-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ticket-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.ticket-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 18px;
}

.ticket-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    color: #666;
    font-size: 14px;
}

.ticket-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.ticket-message {
    margin-bottom: 15px;
    padding: 15px;
    background: white;
    border-radius: 5px;
}

.ticket-message p {
    margin: 0;
    color: #2c3e50;
    line-height: 1.6;
}

.ticket-actions {
    display: flex;
    gap: 10px;
}

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-aberto { background: #2196F3; color: white; }
.badge-em_andamento { background: #FF9800; color: white; }
.badge-resolvido { background: #4CAF50; color: white; }
.badge-fechado { background: #9E9E9E; color: white; }

.btn-small {
    padding: 8px 15px;
    font-size: 13px;
}

.alert {
    padding: 12px;
    border-radius: 5px;
    margin: 15px 0;
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
    
    .ticket-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .ticket-meta {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<script>
document.getElementById('ticketForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Enviando...';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('../api/criar-ticket.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">Ticket criado com sucesso! Redirecionando...</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Ticket';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">Erro ao criar ticket.</div>';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Enviar Ticket';
    }
});
</script>

<?php include '../includes/footer.php'; ?>