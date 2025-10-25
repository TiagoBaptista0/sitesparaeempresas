<?php
// Test database connection
require_once 'config/db.php';

try {
    if (isset($conn) && $conn instanceof mysqli) {
        if ($conn->connect_errno) {
            throw new Exception('Erro na conexão MySQLi: ' . $conn->connect_error);
        }

        echo "✅ Conexão com o banco de dados estabelecida com sucesso!\n";
        echo "Host: " . DB_HOST . "\n";
        echo "Database: " . DB_NAME . "\n";
        echo "User: " . DB_USER . "\n";

        // Test a simple query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            echo "✅ Query de teste executada com sucesso!\n";
            $result->free();
        } else {
            echo "❌ Falha ao executar a query de teste: " . $conn->error . "\n";
        }
    } else {
        echo "❌ Conexão mysqli não encontrada\n";
        echo "Verifique se config/db.php está retornando uma conexão mysqli válida\n";
    }
} catch (Exception $e) {
    echo "❌ Erro na conexão com o banco de dados: " . $e->getMessage() . "\n";
    echo "Verifique as configurações em config/db.php ou .env\n";
}
?>