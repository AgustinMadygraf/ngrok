<?php
/* Path: config.php */

// Declarar el modo estricto de PHP
declare(strict_types=1);

// Archivo: config.php
// Propósito: Centralizar configuraciones del proyecto.

// Incluir la clase Logger
require_once __DIR__ . '/lib/Logger.php';
use App\Lib\Logger;

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

// Modificar la función getForwardingUrl para usar caché
function getForwardingUrl(): string {
    try {
        $cachedUrl = getUrlFromCache();
        if ($cachedUrl !== null) {
            return $cachedUrl;
        }
    } catch (Exception $e) {
        $logger = Logger::getInstance();
        $logger->error(sprintf('Error al obtener la URL desde la caché: %s', $e->getMessage()));
    }

    try {
        $url = getenv('ENDPOINT_URL') ?: ($_ENV['ENDPOINT_URL'] ?? null);
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('La variable ENDPOINT_URL no está definida o no es una URL válida.');
        }

        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception('No se pudo obtener la URL de redirección.');
        }

        $data = json_decode($response, true);
        if (!isset($data['endpoint']) || !filter_var($data['endpoint'], FILTER_VALIDATE_URL)) {
            throw new Exception('La respuesta no contiene un endpoint válido.');
        }

        $endpointUrl = $data['endpoint'];
        saveUrlToCache($endpointUrl);
        return $endpointUrl;
    } catch (Exception $e) {
        $logger = Logger::getInstance();
        $logger->error(sprintf('Error al obtener la URL de redirección: %s', $e->getMessage()));
        throw $e;
    }
}

// Función para guardar la URL en caché con un timestamp
function saveUrlToCache(string $url): bool {
    $cacheFile = __DIR__ . '/cache/url_cache.json';
    $now = time();
    $expirationTime = $now + (int)(getenv('CACHE_EXPIRATION') ?: 3600); // Tiempo de expiración por defecto: 1 hora

    $cacheData = [
        'url' => $url,
        'timestamp' => $now,
        'expires_at' => $expirationTime
    ];

    return file_put_contents(
        $cacheFile,
        json_encode($cacheData, JSON_PRETTY_PRINT),
        LOCK_EX
    ) !== false;
}

// Definir la constante FORWARDING_URL
define('FORWARDING_URL', getForwardingUrl());