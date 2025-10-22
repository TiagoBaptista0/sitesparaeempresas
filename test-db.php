<?php
require_once 'config/db.php';

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully<br>";

// Test query
$result = $conn->query("DESCRIBE usuarios");
if ($result) {
    echo "Table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>