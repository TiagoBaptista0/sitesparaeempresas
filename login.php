<?php
session_start();

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard/index.php');
    exit;
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Bem-vindo de volta!</h1>
            <p>Faça login para acessar sua conta</p>
        </div>

        <div id="message"></div>

        <form id="loginForm" class="auth-form">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="lembrar"> Lembrar-me
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>

        <div class="auth-footer">
            <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            <p><a href="#">Esqueceu sua senha?</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.auth-box {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 450px;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.auth-header p {
    color: #666;
    margin: 0;
}

.auth-form {
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="tel"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
}

.btn-block {
    width: 100%;
}

.auth-footer {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.auth-footer p {
    margin: 10px 0;
    color: #666;
}

.auth-footer a {
    color: #667eea;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

#message {
    margin-bottom: 20px;
}

.alert {
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 15px;
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
</style>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Entrando...';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">Login realizado com sucesso! Redirecionando...</div>';
            setTimeout(() => {
                window.location.href = 'dashboard/index.php';
            }, 1000);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Entrar';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">Erro ao processar login. Tente novamente.</div>';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Entrar';
    }
});
</script>

<?php include 'includes/footer.php'; ?>