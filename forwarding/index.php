<?php
// Declarar el modo estricto de PHP
declare(strict_types=1);

// Archivo: forwarding/index.php
// Propósito: Redirigir todas las solicitudes a una URL especificada.

// Incluir archivo de configuración
require_once __DIR__ . '/../config.php';

// Validar que la URL sea válida antes de redirigir
if (filter_var($forwardingUrl, FILTER_VALIDATE_URL)) {
    header('Location: ' . $forwardingUrl);
    exit();
} else {
    // Manejar el caso de una URL inválida
    echo 'URL de redirección no válida.';
    exit();
}