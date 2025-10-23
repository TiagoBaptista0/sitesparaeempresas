<?php
require '../config/db.php';
require '../config/functions.php';
require '../config/config.php';

if (!is_logged() || !is_admin()) {
    redirect('/login.php');
}

$cliente_id = (int)($_GET['id'] ?? 0);

if ($cliente_id === 0) {
    redirect('/admin/clientes.php');
}

$stmt = $conn->prepare("
    SELECT c.*, u.email, u.telefone, u.nome, p.nome as plano_nome, p.preco
    FROM clientes c
    JOIN usuarios u ON c.user_id = u.id
    LEFT JOIN planos p ON c.plano_id = p.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

$cliente = $result->fetch_assoc();
$stmt->close();

if (!$cliente) {
    redirect('/admin/clientes.php');
}

$page_title = 'Detalhes do Cliente - Admin';
include '../includes/header.php';
?>

<div class="container">
    <h1><?php echo htmlspecialchars($cliente['nome_empresa'] ?? 'Cliente'); ?></h1>

    <div class="card">
        <h3>Informações</h3>
        <p><strong>Contato:</strong> <?php echo htmlspecialchars($cliente['nome']); ?></p>
        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
        <p><strong>Telefone:</strong> <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $cliente['telefone']); ?>" target="_blank"><?php echo htmlspecialchars($cliente['telefone']); ?></a></p>
        <p><strong>Ramo:</strong> <?php echo htmlspecialchars($cliente['ramo'] ?? '-'); ?></p>
    </div>

    <div class="card">
        <h3>Contratação</h3>
        <p><strong>Plano:</strong> <?php echo htmlspecialchars($cliente['plano_nome'] ?? '-'); ?> (R$ <?php echo number_format($cliente['preco'] ?? 0, 2, ',', '.'); ?>/mês)</p>
        <p><strong>Domínio:</strong> <?php echo htmlspecialchars($cliente['dominio'] ?? 'Não registrado'); ?></p>
        <p><strong>Status:</strong> <span class="status <?php echo $cliente['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $cliente['status'])); ?></span></p>
        <p><strong>Cadastro:</strong> <?php echo date('d/m/Y', strtotime($cliente['data_criacao'])); ?></p>
    </div>

    <div class="card">
        <h3>Ações</h3>
        <form method="POST" action="../api/atualizar-cliente.php" class="form">
            <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
            <select name="status" required>
                <option value="">Selecione um novo status</option>
                <option value="aguardando_email" <?php echo $cliente['status'] === 'aguardando_email' ? 'selected' : ''; ?>>Aguardando Email</option>
                <option value="aguardando_dominio" <?php echo $cliente['status'] === 'aguardando_dominio' ? 'selected' : ''; ?>>Aguardando Domínio</option>
                <option value="ativo" <?php echo $cliente['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                <option value="inativo" <?php echo $cliente['status'] === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                <option value="cancelado" <?php echo $cliente['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
            </select>
            <button type="submit" class="btn">Atualizar Status</button>
        </form>
    </div>

    <a href="clientes.php" class="btn">Voltar</a>
</div>

<?php include '../includes/footer.php'; ?>