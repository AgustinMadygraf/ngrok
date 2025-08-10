<?php
/*
Path: database.php
*/

require_once __DIR__ . '/env.php';

class Database {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            throw new Exception('Database connection failed');
        }
    }

    public function ensureTableExists($table) {
        $result = $this->conn->query("SELECT url FROM $table ORDER BY fecha_registro DESC LIMIT 1");
        if (!$result && strpos($this->conn->error, "doesn't exist") !== false) {
            $createTableSQL = "CREATE TABLE $table (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url VARCHAR(255) NOT NULL,
                fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            if (!$this->conn->query($createTableSQL)) {
                throw new Exception('Table creation failed: ' . $this->conn->error);
            }
            // Reintenta la consulta
            $result = $this->conn->query("SELECT url FROM $table ORDER BY fecha_registro DESC LIMIT 1");
        }
        return $result;
    }

    public function getLatestUrl($table) {
        $result = $this->ensureTableExists($table);
        if ($result && $row = $result->fetch_assoc()) {
            return $row['url'];
        }
        return null;
    }

    public function insertUrl($table, $url) {
        $stmt = $this->conn->prepare("INSERT INTO $table (url) VALUES (?)");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $this->conn->error);
        }
        $stmt->bind_param('s', $url);
        if (!$stmt->execute()) {
            throw new Exception('Insert failed: ' . $stmt->error);
        }
        $stmt->close();
    }

    public function close() {
        $this->conn->close();
    }
}