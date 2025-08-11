<?php
header('Content-Type: application/json');
$max_seconds = 5;
$start_time = microtime(true);

require_once __DIR__ . '/database.php';

$response_json = [
    'processed' => [],
    'timeout' => false
];

if (!isset($_GET['urls']) || empty($_GET['urls'])) {
    echo json_encode(['error' => 'Missing urls parameter']);
    exit;
}

// Supongamos que recibes un array de URLs por GET: ?urls=url1,url2,url3
$urls = explode(',', $_GET['urls']);

try {
    $db = new Database();
    $db->ensureTableExists(DB_TABLE);
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Init: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Init error']);
    exit;
}

foreach ($urls as $url) {
    if ((microtime(true) - $start_time) > $max_seconds) {
        $response_json['timeout'] = true;
        break;
    }
    $url = trim($url);
    if ($url === '') continue;
    try {
        $db->insertUrl(DB_TABLE, $url);
        $response_json['processed'][] = $url;
    } catch (Exception $e) {
        error_log(date('[Y-m-d H:i:s] ') . 'Insert: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
        // Puedes agregar errores individuales si lo deseas
    }
}

$db->close();
echo json_encode($response_json);