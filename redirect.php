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

    if ($url === null) {
        echo json_encode(['url' => null, 'redirect' => 'form.html']);
        exit;
    }

    echo json_encode(['url' => $url]);
} catch (Exception $e) {
    echo json_encode(['url' => null, 'error' => $e->getMessage()]);
}