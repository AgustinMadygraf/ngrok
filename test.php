<?php
// test.php
header('Content-Type: application/json');

$status = null;
require_once __DIR__ . '/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();

    // Check if the connection was successful
    if (!$conn) {
        $status = 'missing_connection';
    }

    // Simulate a query (mysqli style)
    $userId = 1; // ejemplo
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->fetch_assoc()) {
        $status = 'success';
    } else {
        $status = 'not_found';
    }
    $stmt->close();
    $db->close();
} catch (Exception $e) {
    $status = 'error: '. $e->getMessage();
}

echo json_encode(['status' => $status]);