<?php
require '../config/db.php';
require '../config/functions.php';
require '../config/config.php';

if (!is_logged() || !is_admin()) {
    redirect('/login.php');
}

$result = $conn->query("
    SELECT p.*, u.nome as nome_empresa
    FROM pagamentos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.data_criacao DESC
");

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

$page_title = 'Pagamentos - Admin';
include '../includes/header.php';
?>

<div class="container">
    <h1>Pagamentos</h1>

    <?php if ($result->num_rows > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Status</th>
                <th>Método</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($pag = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($pag['nome_empresa']); ?></td>
                <td>R$ <?php echo number_format($pag['valor'], 2, ',', '.'); ?></td>
                <td><?php echo $pag['data_pagamento'] ? date('d/m/Y', strtotime($pag['data_pagamento'])) : '-'; ?></td>
                <td><span class="status <?php echo $pag['status']; ?>"><?php echo ucfirst($pag['status']); ?></span></td>
                <td><?php echo htmlspecialchars($pag['metodo_pagamento'] ?? '-'); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>Nenhum pagamento encontrado.</p>
    <?php endif; ?>

    <a href="index.php" class="btn">Voltar</a>
</div>

<?php include '../includes/footer.php'; ?>