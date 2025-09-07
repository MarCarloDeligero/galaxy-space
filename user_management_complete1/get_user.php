<?php
// get_user.php
session_start();
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!$is_admin && ((int)$_SESSION['user_id'] !== $id)) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

$genders = $pdo->query("SELECT id, name FROM genders ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();

echo json_encode([
    'user' => $user,
    'genders' => $genders,
    'courses' => $courses,
]);
