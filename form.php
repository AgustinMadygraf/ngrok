<?php
header('Content-Type: application/json');

require_once __DIR__ . '/database.php';

if (!isset($_GET['url']) || empty($_GET['url'])) {
    error_log(date('[Y-m-d H:i:s] ') . 'Missing url parameter' . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Missing url parameter']);
    exit;
}

$url = trim($_GET['url']);

try {
    $db = new Database();
    $db->ensureTableExists(DB_TABLE);

    // Inserta la nueva URL usando el método público
    $db->insertUrl(DB_TABLE, $url);
    $db->close();

    echo json_encode(['success' => true, 'url' => $url]);
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => $e->getMessage()]);
}