<?php
/* Path: public/get_data.php */

require_once __DIR__ . '/../app/helpers/debug_helper.php';
require_once __DIR__ . '/../app/models/database.php';


/**
 * Clase EndpointValidator
 * Valida la URL del endpoint.
 */
class EndpointValidator {
    /**
     * Valida si un endpoint es válido.
     *
     * @param string $endpoint La URL del endpoint a validar.
     * @return bool True si es válido, False en caso contrario.
     */
    public function validate(string $endpoint): bool {
        return !empty($endpoint) && filter_var($endpoint, FILTER_VALIDATE_URL) !== false;
    }
}

/**
 * Clase GetDataService 
 * Maneja la lógica para devolver datos del endpoint.
 */
class GetDataService {
    private $validator;
    private $conn;

    /**
     * Constructor
     * @param EndpointValidator $validator Instancia del validador.
     */
    public function __construct(EndpointValidator $validator) {
        $this->validator = $validator;
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene la última URL registrada en la base de datos
     */
    private function getLastUrl() {
        try {
            $query = "SELECT url FROM urls ORDER BY id DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['url'] : null;
        } catch (PDOException $e) {
            debug_trace("Error al obtener la última URL: " . $e->getMessage());
            throw new Exception("Error al obtener la última URL");
        }
    }

    /**
     * Procesa la solicitud y genera una respuesta JSON.
     */
    public function processRequest() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $endpoint = $this->getLastUrl() ?? 'http://192.168.0.118:5000';
            debug_trace("Endpoint obtenido: " . $endpoint);

            if (!$this->validator->validate($endpoint)) {
                throw new Exception('El endpoint es inválido o está vacío');
            }

            $data = [
                'endpoint' => $endpoint,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            $errorData = [
                'error' => 'Error al obtener endpoint',
                'message' => $e->getMessage()
            ];
            echo json_encode($errorData);
        }
    }
}

// Inicializar dependencias
$validator = new EndpointValidator();
$service = new GetDataService($validator);

// Procesar la solicitud
$service->processRequest();