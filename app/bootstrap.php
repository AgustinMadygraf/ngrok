<?php
/* Path: app/bootstrap.php */

require_once __DIR__ . '/helpers/debug_helper.php';
require_once __DIR__ . '/models/URLModel.php';
require_once __DIR__ . '/services/URLValidator.php';
require_once __DIR__ . '/controllers/URLController.php';

class Bootstrap {
    public static function initialize() {
        debug_trace("Inicializando dependencias");

        // Crear instancias de las dependencias
        $model = new URLModel();
        debug_trace("Modelo URLModel instanciado");

        $validator = new URLValidator();
        debug_trace("Servicio URLValidator instanciado");

        // Pasar las dependencias al controlador
        $controller = new URLController($model, $validator);
        debug_trace("Controlador URLController instanciado con dependencias inyectadas");

        return $controller;
    }
}
