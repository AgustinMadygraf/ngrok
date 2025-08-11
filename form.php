<?php
header('Content-Type: application/json');

function log_error($context, $message, $extra = []) {
    $log = date('[Y-m-d H:i:s] ') . $context . ': ' . $message;
    if (!empty($extra)) {
        $log .= ' | Extra: ' . json_encode($extra);
    }
    $log .= PHP_EOL;
    error_log($log, 3, __DIR__ . '/error.log');
}

$config = require __DIR__ . '/env.php'; 
$USUARIO = $config['USUARIO'] ?? null;
$CLAVE   = $config['CLAVE'] ?? null;

if (!$USUARIO || !$CLAVE) {
    log_error('Config', 'Credenciales no encontradas', $config);
    echo json_encode(['error' => 'Credenciales no encontradas']);
    exit;
}

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $USUARIO || $_SERVER['PHP_AUTH_PW'] !== $CLAVE) {
    log_error('Auth', 'Acceso no autorizado', [
        'PHP_AUTH_USER' => $_SERVER['PHP_AUTH_USER'] ?? null,
        'PHP_AUTH_PW'   => $_SERVER['PHP_AUTH_PW'] ?? null
    ]);
    header('WWW-Authenticate: Basic realm="Acceso restringido"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

require_once __DIR__ . '/database.php';

if (!isset($_GET['url']) || empty($_GET['url'])) {
    log_error('Input', 'Missing url parameter', $_GET);
    echo json_encode(['error' => 'Missing url parameter']);
    exit;
}

$url = trim($_GET['url']);

try {
    $db = new Database();
} catch (Exception $e) {
    log_error('Database init', $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    echo json_encode(['error' => 'Database init error']);
    exit;
}

try {
    $db->ensureTableExists($config['TABLE']);
} catch (Exception $e) {
    log_error('Table check', $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    echo json_encode(['error' => 'Table check error']);
    exit;
}

try {
    $db->insertUrl($config['TABLE'], $url);
    $db->close();
    log_error('Insert', 'URL insertada correctamente', ['url' => $url]);
    echo json_encode(['success' => true, 'url' => $url]);
} catch (Exception $e) {
    log_error('Insert', $e->getMessage(), ['url' => $url, 'trace' => $e->getTraceAsString()]);
    echo json_encode(['error' => 'Insert error']);
}