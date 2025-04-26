<?php
// Declarar el modo estricto de PHP
declare(strict_types=1);

// Archivo: webhook/index.php
// Propósito: Manejar eventos entrantes desde servicios externos.

// Incluir archivo de configuración
require_once __DIR__ . '/../config.php';

// Aquí se manejarán los eventos entrantes
// Por ejemplo, procesar un webhook recibido

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer el cuerpo de la solicitud
    $input = file_get_contents('php://input');

    // Procesar el contenido del webhook
    // ... lógica personalizada ...

    echo 'Webhook procesado correctamente';
} else {
    http_response_code(405);
    echo 'Método no permitido';
}