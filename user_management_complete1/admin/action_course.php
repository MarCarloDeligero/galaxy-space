<?php
// admin/action_course.php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';
if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO courses (name) VALUES (?)");
        try {
            $stmt->execute([$name]);
        } catch (Exception $e) { /* ignore duplicate error for simplicity */ }
    }
    header('Location: dashboard.php?modal=coursesModal&msg=Added Successfully');
    exit;
} elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: dashboard.php?modal=coursesModal&msg=Deleted Successfully ');
    exit;
}
header('Location: dashboard.php');
exit;
?>
