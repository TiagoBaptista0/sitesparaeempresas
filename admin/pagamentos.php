<?php
require '../config/db.php';
require '../config/functions.php';
require '../config/config.php';

if (!is_logged() || !is_admin()) {
    redirect('/login.php');
}

$result = $conn->query("
    SELECT p.*, c.nome_empresa
    FROM pagamentos p
    JOIN clientes c ON p.cliente_id = c.id
    ORDER BY p.created_at DESC
");

$page_title = 'Pagamentos - Admin';
include '../includes/header.php';
?>

<div class="container">
    <h1>Pagamentos</h1>

    <table class="table">
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Status</th>
                <th>Próxima Renovação</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($pag = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $pag['nome_empresa']; ?></td>
                <td>R$ <?php echo number_format($pag['valor'], 2, ',', '.'); ?></td>
                <td><?php echo date('d/m/Y', strtotime($pag['data_pagamento'])); ?></td>
                <td><span class="status <?php echo $pag['status']; ?>"><?php echo STATUS_PAGAMENTO[$pag['status']]; ?></span></td>
                <td><?php echo date('d/m/Y', strtotime($pag['proxima_renovacao'])); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a href="index.php" class="btn">Voltar</a>
</div>

<?php include '../includes/footer.php'; ?>