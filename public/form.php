<?php
/* Path: public/form.php */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../app/helpers/debug_helper.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/controllers/URLController.php';
require_once __DIR__ . '/../app/models/URLModel.php';
require_once __DIR__ . '/../app/services/URLValidator.php';

debug_trace("Iniciando la ejecución de form.php");

// Crear instancias de las dependencias
$model = new URLModel();
$validator = new URLValidator();
$controller = new URLController($model, $validator);

// Verificar si se recibe una URL en la query string
if (!isset($_GET['url']) || empty($_GET['url'])) {
    echo json_encode(["error" => "Falta el parámetro 'url'."]);
    exit;
}

$url = $_GET['url'];
debug_trace("Recibida URL: " . htmlspecialchars($url));

try {
    $controller->saveURL($url);
    echo json_encode(["success" => "URL guardada exitosamente."]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
