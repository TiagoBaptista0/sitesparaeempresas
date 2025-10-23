<?php
require 'config/db.php';

echo "<h2>Criar Usuário Admin</h2>";

// Dados do admin
$nome = 'Administrador';
$email = 'admin@sitesparaempresas.local';
$senha = 'admin123'; // Mude isso em produção!
$tipo = 'admin';
$status = 'ativo';

// Hash da senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Verificar se o email já existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Usuário com este email já existe!</p>";
    $stmt->close();
} else {
    $stmt->close();
    
    // Inserir novo admin
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo, status, email_confirmed) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo, $status);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Usuário admin criado com sucesso!</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        echo "<p><strong>Senha:</strong> " . htmlspecialchars($senha) . "</p>";
        echo "<p style='color: red;'><strong>⚠️ IMPORTANTE:</strong> Mude a senha após o primeiro login!</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao criar usuário: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
}

echo "<hr>";
echo "<p><a href='login.php'>Ir para Login</a> | <a href='test-admin.php'>Verificar Status</a></p>";
?>
