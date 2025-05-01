<?php
/* Path: public/index.php */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/helpers/debug_helper.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/controllers/URLController.php';
require_once __DIR__ . '/../app/models/URLModel.php';
require_once __DIR__ . '/../app/services/URLValidator.php';

debug_trace("Iniciando la ejecución de index.php");

// Crear instancias de las dependencias
$model = new URLModel();
$validator = new URLValidator();
$controller = new URLController($model, $validator);

$urls = [];
$message = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
        debug_trace("Método POST recibido con URL: " . htmlspecialchars($_POST['url']));
        $controller->saveURL($_POST['url']);
        $message = "<p style='color:green;'>URL guardada exitosamente</p>";
    }
    $urls = $controller->getAllURLs();
} catch (Exception $e) {
    debug_trace("Excepción capturada: " . htmlspecialchars($e->getMessage()));
    $message = "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

require_once __DIR__ . '/../app/views/index.php';
