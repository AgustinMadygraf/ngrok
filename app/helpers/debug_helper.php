<?php
/* Path: app/helpers/debug_helper.php */

require_once __DIR__ . '/../../vendor/autoload.php';

// Determinar el archivo .env a cargar
$envFile = __DIR__ . '/../../.env';
if (file_exists(__DIR__ . '/../../.env.development')) {
    $envFile = __DIR__ . '/../../.env.development';
} elseif (file_exists(__DIR__ . '/../../.env.production')) {
    $envFile = __DIR__ . '/../../.env.production';
}

$dotenv = Dotenv\Dotenv::createImmutable(dirname($envFile), basename($envFile));
$dotenv->load();

function debug_trace($message) {
    if ($_ENV['APP_ENV_PRODUCTION'] !== 'true') {
        $trace = debug_backtrace();
        $caller = $trace[0];
        echo "<p style='color:blue;'><strong>Debug:</strong> $message<br>";
        echo "Archivo: " . $caller['file'] . "<br>";
        echo "Línea: " . $caller['line'] . "</p>";
    }
}
