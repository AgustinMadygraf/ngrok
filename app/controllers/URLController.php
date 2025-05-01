<?php
/* Path: app/controllers/URLController.php */

debug_trace("Incluyendo dependencias en URLController");
require_once __DIR__ . '/../helpers/debug_helper.php';
debug_trace("Dependencias incluidas en URLController");
require_once __DIR__ . '/../models/URLModel.php';
debug_trace("Modelo URLModel incluido en URLController");
require_once __DIR__ . '/../services/URLValidator.php';
debug_trace("Servicio URLValidator incluido en URLController");

/**
 * Clase URLController
 * Controlador para manejar operaciones relacionadas con URLs.
 */
class URLController {
    private $model;
    private $validator;

    /**
     * Constructor
     * Inyecta las dependencias del modelo y el validador.
     *
     * @param URLModel $model El modelo para manejar URLs.
     * @param URLValidator $validator El validador para validar URLs.
     */
    public function __construct(URLModel $model, URLValidator $validator) {
        debug_trace("Inicializando el controlador URLController");
        $this->model = $model;
        $this->validator = $validator;
    }

    /**
     * Guarda una URL después de validarla.
     *
     * @param string $url La URL a guardar.
     * @return bool True si se guardó exitosamente.
     * @throws Exception Si la URL no es válida.
     */
    public function saveURL(string $url): bool {
        debug_trace("Intentando guardar la URL: " . htmlspecialchars($url));
        if ($this->validator->validate($url)) {
            debug_trace("URL válida");
            return $this->model->saveURL($url);
        } else {
            debug_trace("URL inválida: " . htmlspecialchars($url));
            throw new Exception("La URL ingresada no es válida");
        }
    }

    /**
     * Recupera todas las URLs almacenadas.
     *
     * @return array Arreglo de URLs.
     */
    public function getAllURLs(): array {
        debug_trace("Recuperando todas las URLs");
        $urls = $this->model->getAllURLs();
        debug_trace("URLs recuperadas: " . json_encode($urls));
        return $urls;
    }
}
