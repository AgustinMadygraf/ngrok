<?php
// Declarar el modo estricto de PHP
declare(strict_types=1);

// Archivo: config.php
// Propósito: Centralizar configuraciones del proyecto.

// Función para cargar variables de entorno desde un archivo .env
function loadEnv(string $envPath = __DIR__ . '/.env'): void {
    if (!file_exists($envPath)) {
        throw new Exception("El archivo .env no existe. Por favor, crea uno basado en .env.example.");
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        if (!array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

// Cargar variables de entorno
loadEnv();

// Obtener la URL del endpoint desde la variable de entorno
function getForwardingUrl(): string {
    $url = getenv('ENDPOINT_URL') ?: ($_ENV['ENDPOINT_URL'] ?? null);
    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('La variable ENDPOINT_URL no está definida o no es una URL válida en el archivo .env.');
    }
    // Realizar la solicitud HTTP
    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception('No se pudo obtener la URL de redirección.');
    }
    // Decodificar la respuesta JSON
    $data = json_decode($response, true);
    if (!isset($data['endpoint']) || !filter_var($data['endpoint'], FILTER_VALIDATE_URL)) {
        throw new Exception('La respuesta no contiene un endpoint válido.');
    }
    return $data['endpoint'];
}

// Definir la constante FORWARDING_URL
define('FORWARDING_URL', getForwardingUrl());