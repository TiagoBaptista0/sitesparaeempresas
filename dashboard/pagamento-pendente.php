<?php
session_start();
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$page_title = 'Pagamento em Processamento - SitesParaEmpresas';
include '../includes/header.php';
?>

<div class="container">
    <div style="max-width: 600px; margin: 50px auto; text-align: center;">
        
        <div style="background: #fff3cd; color: #856404; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
            <h1 style="font-size: 32px; margin: 0 0 20px 0;">⏳ Pagamento em Processamento</h1>
            <p style="font-size: 18px; margin: 0;">Seu pagamento está sendo processado.</p>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <h2>O que acontece agora?</h2>
            
            <div style="text-align: left; margin: 20px 0;">
                <p>Seu pagamento está sendo processado pelo sistema financeiro. Isso pode levar alguns minutos.</p>
                
                <ul style="margin: 20px 0;">
                    <li>Para pagamentos via PIX, a confirmação é imediata</li>
                    <li>Para boletos, pode levar até 3 dias úteis</li>
                    <li>Para cartões de crédito, a confirmação é geralmente imediata</li>
                </ul>
                
                <p><strong>Você receberá um e-mail de confirmação assim que o pagamento for aprovado.</strong></p>
            </div>
            
            <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Não se preocupe!</strong></p>
                <p style="margin: 10px 0 0 0;">Nossa equipe entrará em contato assim que o pagamento for confirmado.</p>
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