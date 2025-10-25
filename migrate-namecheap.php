<?php
// Corrigir o caminho para o arquivo de configuração do banco de dados
$configPath = __DIR__ . '/config/db.php';

if (!file_exists($configPath)) {
    die("❌ Arquivo de configuração do banco de dados não encontrado em: $configPath\n");
}

require $configPath;

// Verificar se a conexão mysqli foi estabelecida
if (!isset($conn) || !$conn) {
    die("❌ Conexão com o banco de dados não estabelecida. Verifique config/db.php\n");
}

echo "🔄 Iniciando migração do banco de dados...\n\n";

$migrations = [
    "ALTER TABLE clientes ADD COLUMN IF NOT EXISTS namecheap_domain_id VARCHAR(50) AFTER status",
    "ALTER TABLE clientes ADD COLUMN IF NOT EXISTS namecheap_order_id VARCHAR(50) AFTER namecheap_domain_id",
    "ALTER TABLE clientes MODIFY COLUMN status ENUM('aguardando_email', 'aguardando_dominio', 'aguardando_pagamento', 'aguardando_dominio_registro', 'dominio_registrado', 'dns_configurado', 'ativo', 'inativo', 'cancelado') DEFAULT 'aguardando_email'",
    "ALTER TABLE pagamentos ADD COLUMN IF NOT EXISTS plano_id INT AFTER assinatura_id",
    "ALTER TABLE pagamentos ADD COLUMN IF NOT EXISTS dominio VARCHAR(100) AFTER plano_id",
    "ALTER TABLE pagamentos ADD COLUMN IF NOT EXISTS valor_plano DECIMAL(10,2) AFTER valor",
    "ALTER TABLE pagamentos ADD COLUMN IF NOT EXISTS valor_dominio DECIMAL(10,2) AFTER valor_plano"
];

foreach ($migrations as $sql) {
    try {
        if ($conn->query($sql) === TRUE) {
            echo "✅ " . substr($sql, 0, 60) . "...\n";
        } else {
            echo "⚠️  " . substr($sql, 0, 60) . "... (pode já existir)\n";
        }
    } catch (Exception $e) {
        echo "⚠️  " . substr($sql, 0, 60) . "... (erro: " . $e->getMessage() . ")\n";
    }
}

echo "\n✅ Migração concluída!\n";
?>