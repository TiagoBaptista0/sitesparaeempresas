<?php
require '../config/db.php';
require '../config/functions.php';
require '../config/config.php';

if (!is_logged() || !is_admin()) {
    redirect('/login.php');
}

$result = $conn->query("
    SELECT c.id, c.nome_empresa, c.dominio, c.status, c.data_criacao, u.telefone, p.nome as plano_nome
    FROM clientes c
    JOIN usuarios u ON c.user_id = u.id
    LEFT JOIN planos p ON c.plano_id = p.id
    ORDER BY c.data_criacao DESC
");

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

$page_title = 'Clientes - Admin';
include '../includes/header.php';
?>

<div class="container">
    <h1>Clientes</h1>

    <?php if ($result->num_rows > 0): ?>
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
                <td><?php echo htmlspecialchars($cliente['nome_empresa'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($cliente['plano_nome'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($cliente['dominio'] ?? '-'); ?></td>
                <td><span class="status <?php echo $cliente['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $cliente['status'])); ?></span></td>
                <td><?php echo htmlspecialchars($cliente['telefone'] ?? '-'); ?></td>
                <td><?php echo date('d/m/Y', strtotime($cliente['data_criacao'])); ?></td>
                <td><a href="cliente.php?id=<?php echo $cliente['id']; ?>" class="btn-small">Ver</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>Nenhum cliente encontrado.</p>
    <?php endif; ?>

    <a href="index.php" class="btn">Voltar</a>
</div>

<?php include '../includes/footer.php'; ?>