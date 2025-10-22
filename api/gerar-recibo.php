<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$pagamento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pagamento_id <= 0) {
    die('Pagamento inválido');
}

// Buscar pagamento
$stmt = $conn->prepare("
    SELECT p.*, u.nome as usuario_nome, u.email as usuario_email 
    FROM pagamentos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    WHERE p.id = ? AND p.usuario_id = ?
");
$stmt->bind_param("ii", $pagamento_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pagamento = $result->fetch_assoc();
$stmt->close();

if (!$pagamento) {
    die('Pagamento não encontrado');
}

// Gerar recibo em HTML
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pagamento #<?php echo $pagamento_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .info-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .total {
            text-align: right;
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sites Para Empresas</h1>
        <p>Recibo de Pagamento</p>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <label>Recibo Nº:</label>
            <p><?php echo str_pad($pagamento_id, 8, '0', STR_PAD_LEFT); ?></p>
        </div>

        <div class="info-item">
            <label>Data:</label>
            <p><?php echo date('d/m/Y H:i', strtotime($pagamento['data_criacao'])); ?></p>
        </div>

        <div class="info-item">
            <label>Cliente:</label>
            <p><?php echo htmlspecialchars($pagamento['usuario_nome']); ?></p>
        </div>

        <div class="info-item">
            <label>E-mail:</label>
            <p><?php echo htmlspecialchars($pagamento['usuario_email']); ?></p>
        </div>

        <div class="info-item">
            <label>Descrição:</label>
            <p><?php echo htmlspecialchars($pagamento['descricao']); ?></p>
        </div>

        <div class="info-item">
            <label>Método de Pagamento:</label>
            <p><?php echo ucfirst(str_replace('_', ' ', $pagamento['metodo_pagamento'])); ?></p>
        </div>
    </div>

    <div class="total">
        Total: R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?>
    </div>

    <div class="footer">
        <p>Este é um documento gerado eletronicamente e não necessita de assinatura.</p>
        <p>Sites Para Empresas - CNPJ: 00.000.000/0000-00</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            Imprimir Recibo
        </button>
    </div>
</body>
</html>
