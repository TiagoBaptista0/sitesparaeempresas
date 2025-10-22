<?php
require '../config/db.php';
require '../config/functions.php';

if (!is_logged() || !is_admin()) {
    redirect('/login.php');
}

$result = $conn->query("SELECT COUNT(*) as total FROM clientes");
$row = $result->fetch_assoc();
$total_clientes = $row['total'];

$result2 = $conn->query("SELECT COUNT(*) as total FROM pagamentos WHERE status = 'aprovado'");
$row2 = $result2->fetch_assoc();
$total_pagamentos = $row2['total'];

$page_title = 'Admin - SitesParaEmpresas';
include '../includes/header.php';
?>

<div class="container">
    <h1>Admin Dashboard</h1>

    <div class="stats">
        <div class="card">
            <h3>Total de Clientes</h3>
            <p class="numero"><?php echo $total_clientes; ?></p>
        </div>
        <div class="card">
            <h3>Pagamentos Aprovados</h3>
            <p class="numero"><?php echo $total_pagamentos; ?></p>
        </div>
    </div>

    <div class="menu">
        <a href="clientes.php" class="btn">Ver Clientes</a>
        <a href="pagamentos.php" class="btn">Pagamentos</a>
        <a href="logout.php" class="btn btn-secondary">Sair</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>