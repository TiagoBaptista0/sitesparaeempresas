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

$result = $conn->query("
    SELECT c.*, u.email, u.telefone, u.nome, p.nome as plano_nome, p.preco
    FROM clientes c
    JOIN users u ON c.user_id = u.id
    JOIN planos p ON c.plano_id = p.id
    WHERE c.id = $cliente_id
");

$cliente = $result->fetch_assoc();

if (!$cliente) {
    redirect('/admin/clientes.php');
}

$page_title = 'Detalhes do Cliente - Admin';
include '../includes/header.php';
?>

<div class="container">
    <h1><?php echo $cliente['nome_empresa']; ?></h1>

    <div class="card">
        <h3>Informações</h3>
        <p><strong>Contato:</strong> <?php echo $cliente['nome']; ?></p>
        <p><strong>E-mail:</strong> <?php echo $cliente['email']; ?></p>
        <p><strong>Telefone:</strong> <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $cliente['telefone']); ?>" target="_blank"><?php echo $cliente['telefone']; ?></a></p>
        <p><strong>CNPJ:</strong> <?php echo $cliente['cnpj'] ?? '-'; ?></p>
        <p><strong>Ramo:</strong> <?php echo $cliente['ramo']; ?></p>
    </div>

    <div class="card">
        <h3>Contratação</h3>
        <p><strong>Plano:</strong> <?php echo $cliente['plano_nome']; ?> (R$ <?php echo $cliente['preco']; ?>/mês)</p>
        <p><strong>Domínio:</strong> <?php echo $cliente['dominio'] ?? 'Não registrado'; ?></p>
        <p><strong>Status:</strong> <span class="status <?php echo $cliente['status']; ?>"><?php echo STATUS_SITE[$cliente['status']]; ?></span></p>
        <p><strong>Cadastro:</strong> <?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></p>
    </div>

    <div class="card">
        <h3>Ações</h3>
        <form method="POST" action="../api/atualizar-cliente.php" class="form">
            <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
            <select name="status" required>
                <option value="">Selecione um novo status</option>
                <?php foreach (STATUS_SITE as $key => $label): ?>
                <option value="<?php echo $key; ?>" <?php echo $cliente['status'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Atualizar Status</button>
        </form>
    </div>

    <a href="clientes.php" class="btn">Voltar</a>
</div>

<?php include '../includes/footer.php'; ?>