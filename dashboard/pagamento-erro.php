<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$page_title = 'Pagamento Não Processado - SitesParaEmpresas';
include '../includes/header.php';
?>

<div class="container">
    <div style="max-width: 600px; margin: 50px auto; text-align: center;">
        
        <div style="background: #f8d7da; color: #721c24; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
            <h1 style="font-size: 32px; margin: 0 0 20px 0;">❌ Pagamento Não Processado</h1>
            <p style="font-size: 18px; margin: 0;">Seu pagamento não foi processado com sucesso.</p>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <h2>O que pode ter acontecido?</h2>
            
            <div style="text-align: left; margin: 20px 0;">
                <ul>
                    <li>Cartão com saldo insuficiente</li>
                    <li>Dados do cartão incorretos</li>
                    <li>Problemas com a operadora do cartão</li>
                    <li>Tempo limite excedido</li>
                </ul>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Deseja tentar novamente?</strong></p>
                <p style="margin: 10px 0 0 0;">Você pode voltar e tentar realizar o pagamento novamente.</p>
            </div>
        </div>
        
        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="index.php" class="btn" style="padding: 15px 30px;">
                Dashboard
            </a>
            <a href="confirmar-pedido.php" class="btn btn-primary" style="padding: 15px 30px;">
                Tentar Novamente
            </a>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>