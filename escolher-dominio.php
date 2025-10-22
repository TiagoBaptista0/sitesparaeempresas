<?php
require_once 'config/db.php';
require_once 'config/functions.php';
require_once 'config/config.php';

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ============ BUSCAR DADOS DO CLIENTE ============
$stmt = $conn->prepare("
    SELECT c.*, u.email, p.nome as plano_nome, p.preco as plano_preco, p.id as plano_id
    FROM clientes c
    JOIN users u ON c.user_id = u.id
    JOIN planos p ON c.plano_id = p.id
    WHERE c.user_id = ? AND c.status IN ('aguardando_email', 'aguardando_dominio')
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
    header('Location: dashboard/');
    exit;
}

$page_title = 'Escolher Domínio - SitesParaEmpresas';
include 'includes/header.php';
?>

<div class="container">
    <div style="max-width: 600px; margin: 0 auto;">
        
        <!-- HEADER -->
        <div style="text-align: center; margin-bottom: 40px;">
            <h1>🌐 Escolha seu Domínio</h1>
            <p style="color: #666; font-size: 14px;">Etapa 2 de 3: Domínio</p>
        </div>
        
        <!-- RESUMO DO PLANO -->
        <div class="card" style="background: #ecf0f1; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #666; font-size: 12px; margin-bottom: 5px;">PLANO SELECIONADO</p>
                    <h3 style="margin: 0;"><?php echo $cliente['plano_nome']; ?></h3>
                </div>
                <div style="text-align: right;">
                    <p style="font-size: 24px; color: #27ae60; margin: 0; font-weight: bold;">
                        R$ <?php echo number_format($cliente['plano_preco'], 2, ',', '.'); ?><span style="font-size: 12px;">/mês</span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- FORMULÁRIO DE DOMÍNIO -->
        <div class="card">
            <h3>Encontre seu domínio</h3>
            
            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <input 
                    type="text" 
                    id="dominio-input" 
                    placeholder="Digite seu domínio" 
                    style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
                <select id="extensao" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value=".com.br">.com.br</option>
                    <option value=".com">.com</option>
                    <option value=".net">.net</option>
                    <option value=".info">.info</option>
                    <option value=".biz">.biz</option>
                </select>
                <button class="btn" onclick="verificarDominio()" style="padding: 10px 20px;">Verificar</button>
            </div>
            
            <div id="resultado-dominio" style="display: none; margin: 20px 0; padding: 15px; border-radius: 4px;">
                <p id="msg-dominio" style="margin: 0 0 10px 0;"></p>
                <p id="preco-dominio" style="margin: 0;"></p>
                
                <form id="form-dominio" method="POST" action="api/confirmar-dominio.php" style="display: none; margin-top: 15px;">
                    <input type="hidden" name="dominio" id="dominio-hidden">
                    <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                    <button type="submit" class="btn" style="width: 100%;">Continuar para Pagamento →</button>
                </form>
            </div>
        </div>
        
        <!-- HELP -->
        <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 4px; font-size: 12px; color: #333;">
            <p style="margin: 0;"><strong>💡 Dica:</strong> Escolha um domínio que represente bem seu negócio. Você pode mudar depois.</p>
        </div>
    </div>
</div>

<script>
async function verificarDominio() {
    const dominio = document.getElementById('dominio-input').value.trim();
    const extensao = document.getElementById('extensao').value;
    
    if (!dominio) {
        alert('Digite um domínio');
        return;
    }
    
    // Validar domínio
    if (!/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/.test(dominio)) {
        alert('Domínio inválido. Use apenas letras, números e hífen.');
        return;
    }
    
    const dominio_completo = dominio + extensao;
    
    const formData = new FormData();
    formData.append('dominio', dominio_completo);
    
    try {
        const response = await fetch('api/verificar-dominio.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        const resultado = document.getElementById('resultado-dominio');
        const msg = document.getElementById('msg-dominio');
        const preco = document.getElementById('preco-dominio');
        const form = document.getElementById('form-dominio');
        const dominioHidden = document.getElementById('dominio-hidden');
        
        resultado.style.display = 'block';
        
        if (result.success && result.disponivel) {
            resultado.style.background = '#d4edda';
            resultado.style.border = '1px solid #c3e6cb';
            msg.textContent = '✅ Domínio disponível!';
            msg.style.color = '#155724';
            msg.style.fontWeight = 'bold';
            preco.innerHTML = '<strong>R$ ' + result.preco.toFixed(2).replace('.', ',') + ' / ano</strong>';
            form.style.display = 'block';
            dominioHidden.value = dominio_completo;
        } else {
            resultado.style.background = '#f8d7da';
            resultado.style.border = '1px solid #f5c6cb';
            msg.textContent = '❌ Domínio não disponível';
            msg.style.color = '#721c24';
            msg.style.fontWeight = 'bold';
            preco.textContent = '';
            form.style.display = 'none';
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao verificar domínio');
    }
}

// Lidar com envio do formulário
document.getElementById('form-dominio').addEventListener('submit', async function(e) {
    e.preventDefault();
        
        const formData = new FormData(this);
    
try {
const response = await fetch('api/confirmar-dominio.php', {
method: 'POST',
body: formData
    });
    
    const result = await response.json();

    if (result.success) {
        // Redirecionar para confirmação de pedido
        window.location.href = 'dashboard/confirmar-pedido.php';
} else {
        alert('Erro: ' + (result.message || 'Tente novamente'));
        }
            } catch (error) {
                console.error('Erro:', error);
            alert('Erro ao confirmar domínio');
        }
    });

// Permitir Enter para verificar
document.getElementById('dominio-input').addEventListener('keypress', (e) => {
if (e.key === 'Enter') {
verificarDominio();
}
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>