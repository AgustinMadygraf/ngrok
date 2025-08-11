<?php
header('Content-Type: application/json');

$config = require __DIR__ . '/env.php'; 
$USUARIO = $config['USUARIO'];
$CLAVE   = $config['CLAVE'];

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $USUARIO || $_SERVER['PHP_AUTH_PW'] !== $CLAVE) {
    header('WWW-Authenticate: Basic realm="Acceso restringido"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

require_once __DIR__ . '/database.php';

if (!isset($_GET['url']) || empty($_GET['url'])) {
    echo json_encode(['error' => 'Missing url parameter']);
    exit;
}

$url = trim($_GET['url']);

try {
    $db = new Database();
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Database init: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Database init error']);
    exit;
}

try {
    $db->ensureTableExists($config['TABLE']);
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Table check: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Table check error']);
    exit;
}

try {
    $db->insertUrl($config['TABLE'], $url);
    $db->close();
    echo json_encode(['success' => true, 'url' => $url]);
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Insert: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Insert error']);
}