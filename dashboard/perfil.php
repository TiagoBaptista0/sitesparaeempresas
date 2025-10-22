<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

$page_title = 'Perfil';

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
            <a href="suporte.php"><i class="fas fa-headset"></i> Suporte</a>
            <a href="perfil.php" class="active"><i class="fas fa-user"></i> Perfil</a>
            <a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>

    <main class="dashboard-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-user"></i> Meu Perfil</h1>
            <p>Gerencie suas informações pessoais</p>
        </div>

        <div class="card">
            <h2>Informações Pessoais</h2>
            <form id="perfilForm" class="form-horizontal">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                </div>
                
                <div id="message"></div>
                
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>
        </div>

        <div class="card">
            <h2>Alterar Senha</h2>
            <form id="senhaForm" class="form-horizontal">
                <div class="form-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" required>
                </div>
                
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" minlength="6" required>
                    <small>Mínimo de 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                </div>
                
                <div id="messageSenha"></div>
                
                <button type="submit" class="btn btn-primary">Alterar Senha</button>
            </form>
        </div>

        <div class="card">
            <h2>Informações da Conta</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Tipo de Conta:</label>
                    <p><?php echo ucfirst($usuario['tipo']); ?></p>
                </div>
                <div class="info-item">
                    <label>Status:</label>
                    <p><span class="badge badge-<?php echo $usuario['status']; ?>"><?php echo ucfirst($usuario['status']); ?></span></p>
                </div>
                <div class="info-item">
                    <label>Membro desde:</label>
                    <p><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></p>
                </div>
                <div class="info-item">
                    <label>Último acesso:</label>
                    <p><?php echo $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'N/A'; ?></p>
                </div>
            </div>
        </div>

        <div class="card danger-zone">
            <h2><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h2>
            <p>Ações irreversíveis que afetam sua conta.</p>
            <button class="btn btn-danger" onclick="excluirConta()">Excluir Minha Conta</button>
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

.form-group input {
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

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-ativo { background: #4CAF50; color: white; }
.badge-inativo { background: #f44336; color: white; }
.badge-suspenso { background: #FF9800; color: white; }

.danger-zone {
    border: 2px solid #f44336;
}

.danger-zone h2 {
    color: #f44336;
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
// Atualizar perfil
document.getElementById('perfilForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Salvando...';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('../api/atualizar-perfil.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">Perfil atualizado com sucesso!</div>';
        } else {
            messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">Erro ao atualizar perfil.</div>';
    }
    
    submitBtn.disabled = false;
    submitBtn.textContent = 'Salvar Alterações';
});

// Alterar senha
document.getElementById('senhaForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageSenhaDiv = document.getElementById('messageSenha');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    const novaSenha = document.getElementById('nova_senha').value;
    const confirmarSenha = document.getElementById('confirmar_senha').value;
    
    if (novaSenha !== confirmarSenha) {
        messageSenhaDiv.innerHTML = '<div class="alert alert-error">As senhas não coincidem!</div>';
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Alterando...';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('../api/alterar-senha.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageSenhaDiv.innerHTML = '<div class="alert alert-success">Senha alterada com sucesso!</div>';
            e.target.reset();
        } else {
            messageSenhaDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
        }
    } catch (error) {
        messageSenhaDiv.innerHTML = '<div class="alert alert-error">Erro ao alterar senha.</div>';
    }
    
    submitBtn.disabled = false;
    submitBtn.textContent = 'Alterar Senha';
});

// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 10) {
        value = value.replace(/(\\d{2})(\\d{5})(\\d{4})/, '($1) $2-$3');
    } else if (value.length > 6) {
        value = value.replace(/(\\d{2})(\\d{4})(\\d{0,4})/, '($1) $2-$3');
    } else if (value.length > 2) {
        value = value.replace(/(\\d{2})(\\d{0,5})/, '($1) $2');
    }
    
    e.target.value = value;
});

function excluirConta() {
    if (confirm('ATENÇÃO: Esta ação é irreversível! Tem certeza que deseja excluir sua conta?')) {
        if (confirm('Todos os seus dados, sites e pagamentos serão perdidos. Confirma a exclusão?')) {
            window.location.href = '../api/excluir-conta.php';
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
"""