<?php
header('Content-Type: application/json');

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
    $db->ensureTableExists(DB_TABLE);
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Table check: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Table check error']);
    exit;
}

try {
    $db->insertUrl(DB_TABLE, $url);
    $db->close();
    echo json_encode(['success' => true, 'url' => $url]);
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Insert: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Insert error']);
}