<?php
require '../db.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $difficulty = $data['difficulty'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM completions 
                          WHERE player_name = ? AND difficulty = ?");
    $stmt->execute([$name, $difficulty]);
    
    echo json_encode(['exists' => $stmt->rowCount() > 0]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>