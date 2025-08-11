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

$config = require $configPath;

try {
    $db = new Database();
    $latestUrl = $db->getLatestUrl($config['TABLE']);
    $db->close();

    // Si se pasa el parámetro 'url' por GET, úsalo; si no, usa la última URL de la base de datos
    $url = isset($_GET['url']) && $_GET['url'] !== '' ? $_GET['url'] : $latestUrl;

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