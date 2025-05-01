<?php
/* Path: public/index.php */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/helpers/debug_helper.php';

$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    // Display a clear, developer-friendly error message
    echo '<div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Composer Dependencies Not Installed</h2>';
    echo '<p>The application cannot find the required Composer packages.</p>';
    echo '<p><strong>Problem:</strong> The file at <code>' . $autoloadPath . '</code> does not exist.</p>';
    echo '<p><strong>Solution:</strong> Run <code>composer install</code> in the project root directory.</p>';
    echo '<p>If you\'re in a production environment, make sure to deploy the vendor directory or run Composer on the server.</p>';
    echo '</div>';
    exit(1);
}

require_once($autoloadPath);

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
