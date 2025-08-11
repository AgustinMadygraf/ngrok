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
        // Verifica el endpoint ngrok en el backend usando CURL
        $root = (parse_url($url, PHP_URL_SCHEME) ? '' : 'https://') . parse_url($url, PHP_URL_HOST);

        $ch = curl_init($root);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $rootContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || $rootContent === false ||
            strpos($rootContent, 'ERR_NGROK_3200') !== false ||
            (strpos($rootContent, 'endpoint') !== false && strpos($rootContent, 'offline') !== false)) {
            echo json_encode([
                'url' => null,
                'redirect' => 'form.html',
                'error' => 'El endpoint ngrok está offline o no disponible.'
            ]);
            exit;
        }

        echo json_encode(['url' => $url]);
    } else {
        echo json_encode(['url' => null, 'redirect' => 'form.html']);
    }
} catch (Exception $e) {
    echo json_encode(['url' => null, 'error' => $e->getMessage()]);
}
?>