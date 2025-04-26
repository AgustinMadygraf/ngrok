<?php
// Declarar el modo estricto de PHP
declare(strict_types=1);

// Archivo: index.php
// Propósito: Página principal con opciones de navegación.

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .button {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Bienvenido!!!</h1>
    <p>Seleccione una de las siguientes opciones:</p>

    <a href="forwarding/index.php" class="button">Forwarding</a>
    <p>El módulo de Forwarding permite redirigir solicitudes a otros servicios o URLs.</p>

    <a href="webhook/index.php" class="button">Webhook</a>
    <p>El módulo de Webhook permite manejar eventos entrantes desde servicios externos.</p>
</body>
</html>