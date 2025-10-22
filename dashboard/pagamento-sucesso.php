<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar informações do cliente
$stmt = $conn->prepare("
    SELECT c.*, u.email, p.nome as plano_nome
    FROM clientes c
    JOIN users u ON c.user_id = u.id
    JOIN planos p ON c.plano_id = p.id
    WHERE c.user_id = ? AND c.status = 'aguardando_pagamento'
");

if (!$stmt) {
    die("Erro na preparação: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Se não encontrou cliente aguardando pagamento, redirecionar
if (!$cliente) {
    header('Location: index.php');
    exit;
}

$page_title = 'Pagamento Confirmado - SitesParaEmpresas';
include '../includes/header.php';
?>

<div class="container">
    <div style="max-width: 600px; margin: 50px auto; text-align: center;">
        
        <div style="background: #d4edda; color: #155724; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
            <h1 style="font-size: 32px; margin: 0 0 20px 0;">✅ Pagamento Confirmado!</h1>
            <p style="font-size: 18px; margin: 0;">Seu pagamento foi processado com sucesso.</p>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <h2>📋 Pedido Confirmado</h2>
            
            <div style="text-align: left; margin: 20px 0;">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($cliente['nome_empresa']); ?></p>
                <p><strong>E-mail:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
                <p><strong>Plano:</strong> <?php echo htmlspecialchars($cliente['plano_nome']); ?></p>
                <p><strong>Domínio:</strong> <?php echo htmlspecialchars($cliente['dominio']); ?></p>
            </div>
            
            <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Próximos passos:</strong></p>
                <p style="margin: 10px 0 0 0;">Nossa equipe entrará em contato para criar seu site.</p>
            </div>
        </div>
        
        <a href="index.php" class="btn" style="display: inline-block; padding: 15px 40px; font-size: 18px;">
            Ir para o Dashboard
        </a>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>