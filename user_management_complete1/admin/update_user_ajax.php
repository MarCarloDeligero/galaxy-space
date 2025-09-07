<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$firstname = trim($_POST['firstname'] ?? '');
$middlename = trim($_POST['middlename'] ?? null);
$lastname = trim($_POST['lastname'] ?? '');
$gender_id = $_POST['gender_id'] ?? null;
$course_id = $_POST['course_id'] ?? null;
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$id || !$firstname || !$lastname || !$email) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

// Check if email belongs to another user
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $id]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Email already used by another account']);
    exit;
}

// Update the user
if ($password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=?, password=? WHERE id=?");
    $stmt->execute([$firstname, $middlename, $lastname, $gender_id ?: null, $course_id ?: null, $email, $hash, $id]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=? WHERE id=?");
    $stmt->execute([$firstname, $middlename, $lastname, $gender_id ?: null, $course_id ?: null, $email, $id]);
}

// Return updated user data with joined gender and course names
$stmt = $pdo->prepare("
    SELECT u.id, u.firstname, u.middlename, u.lastname, u.email, g.name AS gender, c.name AS course, u.created_at
    FROM users u
    LEFT JOIN genders g ON u.gender_id = g.id
    LEFT JOIN courses c ON u.course_id = c.id
    WHERE u.id = ?
");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['error' => 'User not found']);
}
