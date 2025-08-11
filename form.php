<?php
header('Content-Type: application/json');
$max_seconds = 3; // Cambiado de 5 a 3 segundos
$start_time = microtime(true);

require_once __DIR__ . '/database.php';

$response_json = [
    'processed' => [],
    'timeout' => false,
    'errors' => [] // Ahora almacena detalles de errores por URL
];

if (!isset($_GET['urls']) || empty($_GET['urls'])) {
    echo json_encode(['error' => 'No URLs provided']);
    exit;
}

$urls = explode(',', $_GET['urls']);

try {
    $db = new Database();
    $db->ensureTableExists(DB_TABLE);
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Init: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    echo json_encode(['error' => 'Init error']);
    exit;
}

// Verificar tiempo después de la inicialización
if ((microtime(true) - $start_time) > $max_seconds) {
    $response_json['timeout'] = true;
    $db->close(); // Cerrar conexión antes de responder
    echo json_encode($response_json);
    exit;
}

foreach ($urls as $url) {
    // Verificar tiempo en cada iteración
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
        // Registrar en log y en la respuesta
        error_log(date('[Y-m-d H:i:s] ') . "Insert [$url]: " . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
        $response_json['errors'][] = [
            'url' => $url,
            'message' => $e->getMessage()
        ];
    }
}

$db->close();
echo json_encode($response_json);