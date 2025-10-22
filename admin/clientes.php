<?php
require '../config/db.php';
require '../config/functions.php';
require '../config/config.php';

if (!is_logged() || !is_admin()) {
    redirect('/login.php');
}

$result = $conn->query("
    SELECT c.id, c.nome_empresa, c.dominio, c.status, c.created_at, u.telefone, p.nome as plano_nome
    FROM clientes c
    JOIN users u ON c.user_id = u.id
    JOIN planos p ON c.plano_id = p.id
    ORDER BY c.created_at DESC
");

$page_title = 'Clientes - Admin';
include '../includes/header.php';
?>

<div class="container">
    <h1>Clientes</h1>

    <table class="table">
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Plano</th>
                <th>Domínio</th>
                <th>Status</th>
                <th>Telefone</th>
                <th>Data</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($cliente = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $cliente['nome_empresa']; ?></td>
                <td><?php echo $cliente['plano_nome']; ?></td>
                <td><?php echo $cliente['dominio'] ?? '-'; ?></td>
                <td><span class="status <?php echo $cliente['status']; ?>"><?php echo STATUS_SITE[$cliente['status']]; ?></span></td>
                <td><?php echo $cliente['telefone']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></td>
                <td><a href="cliente.php?id=<?php echo $cliente['id']; ?>" class="btn-small">Ver</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a href="index.php" class="btn">Voltar</a>
</div>

<?php include '../includes/footer.php'; ?>