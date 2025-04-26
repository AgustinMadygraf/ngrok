<?php
// Declarar el modo estricto de PHP
declare(strict_types=1);

// Archivo: config.php
// Propósito: Centralizar configuraciones del proyecto.

// Obtener la URL desde el enlace proporcionado
function getForwardingUrl(): string {
    $url = 'https://chat.profebustos.com.ar/coopebot_PHP/public/get_data.php';

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