<?php
/* Path: app/models/URLModel.php */

debug_trace("Incluyendo dependencias en URLModel");
require_once __DIR__ . '/../helpers/debug_helper.php';
debug_trace("Dependencias incluidas en URLModel");
require_once __DIR__ . '/../interfaces/IURLRepository.php';
debug_trace("Interfaz IURLRepository incluida en URLModel");
require_once __DIR__ . '/database.php';
debug_trace("Clase Database incluida en URLModel");

/**
 * Clase URLModel
 * Implementa la interfaz IURLRepository para manejar operaciones de URLs.
 */
class URLModel implements IURLRepository {
    private $conn;

    public function __construct() {
        debug_trace("Inicializando el modelo URLModel");
        $database = new Database();
        $this->conn = $database->getConnection();
        if ($this->conn) {
            debug_trace("Conexión a la base de datos establecida");
        } else {
            debug_trace("Error al establecer la conexión a la base de datos");
        }
    }

    /**
     * Guarda una URL en el repositorio.
     *
     * @param string $url La URL a guardar.
     * @return bool True si se guardó correctamente, False en caso contrario.
     * @throws Exception Si ocurre un error durante el guardado.
     */
    public function saveURL(string $url): bool {
        debug_trace("Intentando guardar la URL: " . htmlspecialchars($url));
        try {
            $query = "INSERT INTO urls (url, fecha_registro) VALUES (:url, NOW())";
            debug_trace("Consulta SQL para insertar: " . $query);
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':url', $url);
            $resultado = $stmt->execute();
            debug_trace($resultado ? "URL guardada exitosamente" : "Fallo al guardar la URL");
            return $resultado;
        } catch (PDOException $e) {
            debug_trace("Excepción al guardar la URL: " . htmlspecialchars($e->getMessage()));
            throw new Exception("Error al guardar la URL: " . $e->getMessage());
        }
    }

    /**
     * Recupera todas las URLs almacenadas en el repositorio.
     *
     * @return array Un arreglo de URLs.
     * @throws Exception Si ocurre un error durante la recuperación.
     */
    public function getAllURLs(): array {
        debug_trace("Intentando recuperar todas las URLs");
        try {
            $query = "SELECT * FROM urls ORDER BY id DESC";
            debug_trace("Consulta SQL para obtener: " . $query);
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);
            debug_trace("URLs recuperadas: " . json_encode($urls));
            return $urls;
        } catch (PDOException $e) {
            debug_trace("Excepción al obtener las URLs: " . htmlspecialchars($e->getMessage()));
            throw new Exception("Error al obtener las URLs: " . $e->getMessage());
        }
    }

    /**
     * Recupera la última URL almacenada en el repositorio.
     *
     * @return string|null La última URL o null si no hay registros.
     * @throws Exception Si ocurre un error durante la recuperación.
     */
    public function getLastURL(): ?string {
        debug_trace("Intentando recuperar la última URL");
        try {
            $query = "SELECT url FROM urls ORDER BY id DESC LIMIT 1";
            debug_trace("Consulta SQL para obtener: " . $query);
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastURL = $result ? $result['url'] : null;
            debug_trace("Última URL recuperada: " . ($lastURL ?? "null"));
            return $lastURL;
        } catch (PDOException $e) {
            debug_trace("Excepción al obtener la última URL: " . htmlspecialchars($e->getMessage()));
            throw new Exception("Error al obtener la última URL: " . $e->getMessage());
        }
    }
}
