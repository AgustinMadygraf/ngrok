<?php
/* Path: app/models/database.php */

require_once __DIR__ . '/../helpers/debug_helper.php';
debug_trace("Inicializando el archivo de configuración de la base de datos");

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    // Display a clear, developer-friendly error message
    echo '<div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Composer Dependencies Not Installed</h2>';
    echo '<p>The application cannot find the required Composer packages.</p>';
    echo '<p><strong>Problem:</strong> The file at <code>' . $autoloadPath . '</code> does not exist.</p>';
    echo '<p><strong>Solution:</strong> Run <code>composer install</code> in the project root directory.</p>';
    echo '<p>If you\'re in a production environment, make sure to deploy the vendor directory or run Composer on the server.</p>';
    echo '</div>';
    exit(1);
}

require_once($autoloadPath);

debug_trace("Requerido el cargador de clases de Composer");

// Determinar el archivo .env a cargar
$envFile = __DIR__ . '/../../.env';
if (file_exists(__DIR__ . '/../../.env.development')) {
    $envFile = __DIR__ . '/../../.env.development';
} elseif (file_exists(__DIR__ . '/../../.env.production')) {
    $envFile = __DIR__ . '/../../.env.production';
}

$dotenv = Dotenv\Dotenv::createImmutable(dirname($envFile), basename($envFile));
debug_trace("Cargando las variables de entorno");

$dotenv->load();

debug_trace("Cargadas las variables de entorno");

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        debug_trace("Inicializando la clase Database");
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        debug_trace("Configuración de conexión cargada");
    }

    public function getConnection() {
        $this->conn = null;
        try {
            debug_trace("Intentando establecer la conexión a la base de datos");
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            debug_trace("Conexión a la base de datos establecida exitosamente");

            // Verificar si la tabla existe y crearla si no
            $this->createTable();

        } catch (PDOException $exception) {
            debug_trace("Error al conectar con la base de datos: " . $exception->getMessage());
            if (strpos($exception->getMessage(), 'Unknown database') !== false) {
                debug_trace("La base de datos no existe, intentando crearla");
                $this->createDatabase();
                return $this->getConnection(); // Reintentar la conexión después de crear la base de datos
            }
            $errorInfo = $exception->errorInfo;
            $errorCode = $exception->getCode();
            debug_trace("Código de error: " . $errorCode . " | Información del error: " . json_encode($errorInfo));
            throw new Exception("Error de conexión: " . $exception->getMessage() . " | Código de error: " . $errorCode . " | Información del error: " . json_encode($errorInfo));
        }
        return $this->conn;
    }

    private function createDatabase() {
        try {
            $dsn = "mysql:host=" . $this->host;
            $conn = new PDO($dsn, $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8 COLLATE utf8_general_ci";
            debug_trace("Ejecutando consulta para crear la base de datos: " . $query);
            $conn->exec($query);
            debug_trace("Base de datos creada exitosamente: " . $this->db_name);
        } catch (PDOException $exception) {
            debug_trace("Error al crear la base de datos: " . $exception->getMessage());
            throw new Exception("Error al crear la base de datos: " . $exception->getMessage());
        }
    }

    private function createTable() {
        try {
            $query = "
                CREATE TABLE IF NOT EXISTS urls (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    url VARCHAR(255) NOT NULL,
                    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;
            ";
            debug_trace("Ejecutando consulta para crear la tabla: " . $query);
            $this->conn->exec($query);
            debug_trace("Tabla 'urls' creada o ya existía");
        } catch (PDOException $exception) {
            debug_trace("Error al crear la tabla: " . $exception->getMessage());
            throw new Exception("Error al crear la tabla: " . $exception->getMessage());
        }
    }
}
