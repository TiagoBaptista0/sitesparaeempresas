<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar site do usuário
$stmt = $conn->prepare("SELECT * FROM sites WHERE usuario_id = ? ORDER BY data_criacao DESC LIMIT 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$site = $result->fetch_assoc();
$stmt->close();

$page_title = 'Meu Site';

?>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">
            <h2>Sites Para Empresas</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="meu-site.php" class="active"><i class="fas fa-globe"></i> Meu Site</a>
            <a href="pagamentos.php"><i class="fas fa-credit-card"></i> Pagamentos</a>
            <a href="suporte.php"><i class="fas fa-headset"></i> Suporte</a>
            <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
            <a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>

    <main class="dashboard-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-globe"></i> Meu Site</h1>
            <p>Gerencie e edite seu site</p>
        </div>

        <?php if ($site): ?>
        <div class="card">
            <h2>Informações do Site</h2>
            <div class="site-info-grid">
                <div class="info-item">
                    <label>Nome do Site:</label>
                    <p><?php echo htmlspecialchars($site['nome']); ?></p>
                </div>
                
                <?php if ($site['dominio']): ?>
                <div class="info-item">
                    <label>Domínio:</label>
                    <p><a href="http://<?php echo htmlspecialchars($site['dominio']); ?>" target="_blank"><?php echo htmlspecialchars($site['dominio']); ?></a></p>
                </div>
                <?php endif; ?>
                
                <?php if ($site['subdominio']): ?>
                <div class="info-item">
                    <label>Subdomínio:</label>
                    <p><a href="http://<?php echo htmlspecialchars($site['subdominio']); ?>.sitesparaempresas.com" target="_blank"><?php echo htmlspecialchars($site['subdominio']); ?>.sitesparaempresas.com</a></p>
                </div>
                <?php endif; ?>
                
                <div class="info-item">
                    <label>Status:</label>
                    <p><span class="badge badge-<?php echo $site['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $site['status'])); ?></span></p>
                </div>
                
                <div class="info-item">
                    <label>Template:</label>
                    <p><?php echo ucfirst($site['template']); ?></p>
                </div>
                
                <div class="info-item">
                    <label>Última Atualização:</label>
                    <p><?php echo date('d/m/Y H:i', strtotime($site['data_atualizacao'])); ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Editar Site</h2>
            <form id="editSiteForm" class="form-horizontal">
                <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                
                <div class="form-group">
                    <label for="nome">Nome do Site</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($site['nome']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="subdominio">Subdomínio</label>
                    <div class="input-group">
                        <input type="text" id="subdominio" name="subdominio" value="<?php echo htmlspecialchars($site['subdominio']); ?>" required>
                        <span class="input-addon">.sitesparaempresas.com</span>
                    </div>
                    <small>Apenas letras, números e hífens</small>
                </div>
                
                <div class="form-group">
                    <label for="template">Template</label>
                    <select id="template" name="template">
                        <option value="padrao" <?php echo $site['template'] == 'padrao' ? 'selected' : ''; ?>>Padrão</option>
                        <option value="moderno" <?php echo $site['template'] == 'moderno' ? 'selected' : ''; ?>>Moderno</option>
                        <option value="elegante" <?php echo $site['template'] == 'elegante' ? 'selected' : ''; ?>>Elegante</option>
                        <option value="minimalista" <?php echo $site['template'] == 'minimalista' ? 'selected' : ''; ?>>Minimalista</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="em_construcao" <?php echo $site['status'] == 'em_construcao' ? 'selected' : ''; ?>>Em Construção</option>
                        <option value="ativo" <?php echo $site['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="inativo" <?php echo $site['status'] == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>
                
                <div id="message"></div>
                
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <?php if ($site['status'] == 'ativo'): ?>
                    <a href="http://<?php echo $site['dominio'] ?: $site['subdominio'] . '.sitesparaempresas.com'; ?>" target="_blank" class="btn btn-secondary">Visualizar Site</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php else: ?>
        <div class="card">
            <h2>Criar Novo Site</h2>
            <p>Você ainda não possui um site. Crie um agora!</p>
            
            <form id="createSiteForm" class="form-horizontal">
                <div class="form-group">
                    <label for="nome">Nome do Site</label>
                    <input type="text" id="nome" name="nome" placeholder="Minha Empresa" required>
                </div>
                
                <div class="form-group">
                    <label for="subdominio">Subdomínio</label>
                    <div class="input-group">
                        <input type="text" id="subdominio" name="subdominio" placeholder="minhaempresa" required>
                        <span class="input-addon">.sitesparaempresas.com</span>
                    </div>
                    <small>Apenas letras, números e hífens</small>
                </div>
                
                <div class="form-group">
                    <label for="template">Template</label>
                    <select id="template" name="template">
                        <option value="padrao">Padrão</option>
                        <option value="moderno">Moderno</option>
                        <option value="elegante">Elegante</option>
                        <option value="minimalista">Minimalista</option>
                    </select>
                </div>
                
                <div id="message"></div>
                
                <button type="submit" class="btn btn-primary">Criar Site</button>
            </form>
        </div>
        <?php endif; ?>
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

.site-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-item label {
    display: block;
    font-weight: bold;
    color: #666;
    margin-bottom: 5px;
}

.info-item p {
    margin: 0;
    color: #2c3e50;
}

.form-horizontal {
    max-width: 600px;
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
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.input-group {
    display: flex;
}

.input-group input {
    border-radius: 5px 0 0 5px;
}

.input-addon {
    padding: 10px 15px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-left: none;
    border-radius: 0 5px 5px 0;
    color: #666;
}

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-ativo { background: #4CAF50; color: white; }
.badge-em_construcao { background: #FF9800; color: white; }
.badge-inativo { background: #f44336; color: white; }

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
// Editar site existente
<?php if ($site): ?>
document.getElementById('editSiteForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Salvando...';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('../api/atualizar-site.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">Site atualizado com sucesso!</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Salvar Alterações';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">Erro ao atualizar site.</div>';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Salvar Alterações';
    }
});
<?php else: ?>
// Criar novo site
document.getElementById('createSiteForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Criando...';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('../api/criar-site.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">Site criado com sucesso! Redirecionando...</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Criar Site';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">Erro ao criar site.</div>';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Criar Site';
    }
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>