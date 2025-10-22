<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';
require_once 'config/functions.php';
require_once 'config/config.php';

echo "Testando o sistema de cadastro...\n\n";

// Verificar se as constantes estão definidas
echo "Constantes definidas:\n";
echo "SMTP_HOST: " . (defined('SMTP_HOST') ? SMTP_HOST : 'NÃO DEFINIDA') . "\n";
echo "SMTP_PORT: " . (defined('SMTP_PORT') ? SMTP_PORT : 'NÃO DEFINIDA') . "\n";
echo "SMTP_USERNAME: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NÃO DEFINIDA') . "\n";
echo "EMAIL_FROM: " . (defined('EMAIL_FROM') ? EMAIL_FROM : 'NÃO DEFINIDA') . "\n";
echo "EMAIL_NAME: " . (defined('EMAIL_NAME') ? EMAIL_NAME : 'NÃO DEFINIDA') . "\n";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NÃO DEFINIDA') . "\n";
echo "PLANOS: " . (defined('PLANOS') ? 'DEFINIDA' : 'NÃO DEFINIDA') . "\n\n";

// Verificar se a função enviarEmail existe
echo "Função enviarEmail: " . (function_exists('enviarEmail') ? 'EXISTE' : 'NÃO EXISTE') . "\n";

// Verificar conexão com banco de dados
echo "Conexão com banco de dados: " . ($conn->connect_errno === 0 ? 'OK' : 'ERRO') . "\n";

// Verificar se a tabela usuarios existe
$result = $conn->query("SHOW TABLES LIKE 'usuarios'");
echo "Tabela usuarios: " . ($result && $result->num_rows > 0 ? 'EXISTE' : 'NÃO EXISTE') . "\n";

// Verificar se a tabela clientes existe
$result = $conn->query("SHOW TABLES LIKE 'clientes'");
echo "Tabela clientes: " . ($result && $result->num_rows > 0 ? 'EXISTE' : 'NÃO EXISTE') . "\n";

// Verificar se a tabela logs existe
$result = $conn->query("SHOW TABLES LIKE 'logs'");
echo "Tabela logs: " . ($result && $result->num_rows > 0 ? 'EXISTE' : 'NÃO EXISTE') . "\n";

echo "\n✓ Sistema de cadastro está pronto!\n";
?>
