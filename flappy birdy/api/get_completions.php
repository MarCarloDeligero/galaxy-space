<?php
require '../db.php';

$stmt = $pdo->query("SELECT player_name, difficulty, score, completion_date 
                    FROM completions 
                    ORDER BY score DESC, completion_date ASC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results);
?>