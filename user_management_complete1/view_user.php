<?php
// view_user.php - simple public profile view (no password)
session_start();
require_once __DIR__ . '/includes/config.php';
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->prepare("SELECT u.id, u.firstname, u.middlename, u.lastname, u.email, g.name AS gender, c.name AS course, u.created_at
    FROM users u
    LEFT JOIN genders g ON u.gender_id=g.id
    LEFT JOIN courses c ON u.course_id=c.id
    WHERE u.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>View User</title><link rel="stylesheet" href="style.css"></head><body>
<header><h1>View User</h1></header>
<main>
  <p><strong>Name:</strong> <?php echo e($user['firstname'].' '.($user['middlename'] ? $user['middlename'].' ' : '').$user['lastname']); ?></p>
  <p><strong>Email:</strong> <?php echo e($user['email']); ?></p>
  <p><strong>Gender:</strong> <?php echo e($user['gender'] ?? '-'); ?></p>
  <p><strong>Course:</strong> <?php echo e($user['course'] ?? '-'); ?></p>
  <p><strong>Joined:</strong> <?php echo e($user['created_at']); ?></p>
  <p><a href="index.php">Back</a></p>
</main></body></html>
