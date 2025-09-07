<?php
// delete_user.php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = (int)$_GET['id'];

// Must be owner or admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    $_SESSION['message'] = "You must be logged in to delete.";
    header('Location: index.php');
    exit;
}
if (!$is_admin && (int)$_SESSION['user_id'] !== $id) {
    $_SESSION['message'] = "You can only delete your own account.";
    header('Location: index.php');
    exit;
}

// delete
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

// If user deleted their own account, log them out
if (!$is_admin && (int)$_SESSION['user_id'] === $id) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

$_SESSION['message'] = "User deleted.";
header('Location: index.php');
exit;
?>