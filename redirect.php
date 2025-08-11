<?php
/*
Path: redirect.php
*/

header('Content-Type: application/json');
require_once __DIR__ . '/database.php';
$configPath = __DIR__ . '/env.php';
if (!file_exists($configPath)) {
    echo json_encode(['url' => null, 'error' => 'Archivo de configuración no encontrado']);
    exit;
}

try {
    $db = new Database();
    $url = $db->getLatestUrl( $config['TABLE']);
    $db->close();

    // Recibe el parámetro 'url' y lo usa directamente, sin concatenar
    $url = isset($_GET['url']) ? $_GET['url'] : null;

    if ($url) {
        echo json_encode(['url' => $url]);
    } else {
        // Si no hay URL, puedes redirigir o mostrar error
        echo json_encode(['url' => null, 'redirect' => 'form.html']);
    }
} catch (Exception $e) {
    echo json_encode(['url' => null, 'error' => $e->getMessage()]);
}
?>