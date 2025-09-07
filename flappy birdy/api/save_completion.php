<?php
require '../db.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];
    $score = $data['score'];
    $difficulty = $data['difficulty'];

    $pdo->beginTransaction();

    // Check existing record
    $stmt = $pdo->prepare("SELECT id, score FROM completions 
                          WHERE player_name = ? AND difficulty = ?");
    $stmt->execute([$name, $difficulty]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($score > $existing['score']) {
            $stmt = $pdo->prepare("UPDATE completions SET 
                                  score = ?, completion_date = NOW()
                                  WHERE id = ?");
            $stmt->execute([$score, $existing['id']]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO completions 
                              (player_name, score, difficulty, completion_date)
                              VALUES (?, ?, ?, NOW())");
        $stmt->execute([$name, $score, $difficulty]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>