<?php
header('Content-Type: application/json');

// Incluye las credenciales
require_once __DIR__ . '/env.php';

// Conexión a MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Consulta la URL (ajusta la tabla/campo según tu estructura)
$result = $conn->query("SELECT url FROM " . DB_TABLE . " ORDER BY fecha_registro DESC LIMIT 1");
if (!$result) {
    // Si la tabla no existe, la crea con el formato correcto
    if (strpos($conn->error, "doesn't exist") !== false) {
        $createTableSQL = "CREATE TABLE " . DB_TABLE . " (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            url VARCHAR(255) NOT NULL,
            fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        if ($conn->query($createTableSQL)) {
            // Tabla creada, intenta la consulta de nuevo
            $result = $conn->query("SELECT url FROM " . DB_TABLE . " ORDER BY fecha_registro DESC LIMIT 1");
        } else {
            echo json_encode(['error' => 'Table creation failed: ' . $conn->error]);
            $conn->close();
            exit;
        }
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
        $conn->close();
        exit;
    }
}

if ($row = $result->fetch_assoc()) {
    $url = $row['url'];
} else {
    $url = null;
}

$conn->close();

echo json_encode([
    'url' => $url
]);