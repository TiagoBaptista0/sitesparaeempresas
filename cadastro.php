<?php
session_start();

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard/index.php');
    exit;
}

$page_title = 'Cadastro';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Crie sua conta</h1>
            <p>Comece a criar seu site profissional hoje</p>
        </div>

        <div id="message"></div>

        <form id="cadastroForm" class="auth-form">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required minlength="6">
                <small>Mínimo de 6 caracteres</small>
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="termos" required>
                    Aceito os <a href="#" target="_blank">termos de uso</a> e <a href="#" target="_blank">política de privacidade</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Criar Conta</button>
        </form>

        <div class="auth-footer">
            <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
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
    max-width: 500px;
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

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
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
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin-top: 3px;
}

.checkbox-label a {
    color: #667eea;
    text-decoration: none;
}

.checkbox-label a:hover {
    text-decoration: underline;
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
document.getElementById('cadastroForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // Validar senhas
    const senha = document.getElementById('senha').value;
    const confirmarSenha = document.getElementById('confirmar_senha').value;
    
    if (senha !== confirmarSenha) {
        messageDiv.innerHTML = '<div class="alert alert-error">As senhas não coincidem!</div>';
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Criando conta...';
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('api/cadastro.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();

        if (data.success) {
            // Get email before resetting form
            const userEmail = document.getElementById('email').value;
            messageDiv.innerHTML = '<div class="alert alert-success">Conta criada com sucesso! Verifique seu e-mail para confirmar o cadastro.</div>';
            // Clear form
            e.target.reset();
            // Redirect to confirmation page instead of login
            setTimeout(() => {
                window.location.href = 'confirmar-email.php?email=' + encodeURIComponent(userEmail);
            }, 3000);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Criar Conta';
        }
    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">Erro ao processar cadastro. Tente novamente.</div>';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Criar Conta';
    }
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
    } else if (value.length > 0) {
        value = value.replace(/(\\d*)/, '($1');
    }
    
    e.target.value = value;
});
</script>

<?php include 'includes/footer.php'; ?>