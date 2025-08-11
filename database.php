<?php
/*
Path: database.php
*/

$configPath = __DIR__ . '/env.php';
$config = require $configPath;

class Database {
    private $conn;

    public function __construct() {
        global $config;
        $this->conn = new mysqli(
            $config['DB_HOST'],
            $config['DB_USER'],
            $config['DB_PASS'],
            $config['DB_NAME']
        );
        if ($this->conn->connect_error) {
            throw new Exception('Database connection failed');
        }
        // Asegura que la tabla exista al crear la instancia
        $this->ensureTableExists($config['TABLE']);
    }

    public function ensureTableExists($table) {
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `url` VARCHAR(255) NOT NULL,
            `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        if (!$this->conn->query($createTableSQL)) {
            throw new Exception('Table creation failed: ' . $this->conn->error);
        }
    }

    public function getLatestUrl($table) {
        $result = $this->conn->query("SELECT url FROM `$table` ORDER BY fecha_registro DESC LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            return $row['url'];
        }
        return null;
    }

    public function insertUrl($table, $url) {
        $stmt = $this->conn->prepare("INSERT INTO `$table` (url) VALUES (?)");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $this->conn->error);
        }
        $stmt->bind_param('s', $url);
        if (!$stmt->execute()) {
            throw new Exception('Insert failed: ' . $stmt->error);
        }
        $stmt->close();
    }

    public function getConnection() {
        return $this->conn;
    }

    public function close() {
        $this->conn->close();
    }
}