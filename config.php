<?php
/* Path: config.php */

// Declarar el modo estricto de PHP
declare(strict_types=1);

// Archivo: config.php
// Propósito: Centralizar configuraciones del proyecto.

// Incluir la clase Logger
require_once __DIR__ . '/lib/Logger.php';
use App\Lib\Logger;

// Incluir la conexión a la base de datos
require_once __DIR__ . '/app/models/database.php';

// Inicializar el logger
$logger = Logger::getInstance();
$logger->info('Configuración cargada correctamente.');

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

// Función para leer la URL desde caché si existe y es válida
function getUrlFromCache(): ?string {
    $cacheFile = __DIR__ . '/cache/url_cache.json';

    if (!file_exists($cacheFile)) {
        return null; // No hay caché disponible
    }

    $cacheContent = file_get_contents($cacheFile);
    $cacheData = json_decode($cacheContent, true);

    if (!$cacheData || !isset($cacheData['url'], $cacheData['expires_at'])) {
        return null; // Caché inválida
    }

    // Verificar si la caché ha expirado
    if ($cacheData['expires_at'] < time()) {
        return null; // Caché expirada
    }

    return $cacheData['url']; // Devolver la URL desde la caché
}

// Función para limpiar archivos de caché antiguos o inválidos
function cleanCache(): void {
    $cacheFile = __DIR__ . '/cache/url_cache.json';

    // Verificar si el archivo de caché existe
    if (file_exists($cacheFile)) {
        $cacheContent = file_get_contents($cacheFile);
        $cacheData = json_decode($cacheContent, true);

        // Eliminar el archivo si está obsoleto
        if (!$cacheData || !isset($cacheData['expires_at']) || $cacheData['expires_at'] < time()) {
            unlink($cacheFile);
        }
    }
}

// Función para obtener la última URL del endpoint desde la base de datos
function getEndpointUrl(): string {
    $database = new Database();
    $conn = $database->getConnection();

    try {
        $query = "SELECT url FROM urls ORDER BY id DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['url'] : 'http://192.168.0.118:5000';
    } catch (PDOException $e) {
        // En caso de error, retornar una URL por defecto
        return 'http://192.168.0.118:5000';
    }
}

// Reemplazar la constante FORWARDING_URL por una variable
$forwardingUrl = getEndpointUrl();