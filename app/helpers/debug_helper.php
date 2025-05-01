<?php
/* Path: app/helpers/debug_helper.php */


$autoloadPath = __DIR__ . '/../../vendor/autoload.php';

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
