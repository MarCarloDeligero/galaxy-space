<?php
// login.php
session_start();
require_once __DIR__ . '/includes/config.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $_SESSION['message'] = "Please provide email and password.";
    header('Location: index.php');
    exit;
}

// Try normal users first
$stmt = $pdo->prepare("SELECT id, firstname, lastname, email, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // login success (normal user)
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
    $_SESSION['is_admin'] = false;
    $_SESSION['message'] = "Logged in successfully.";
    header('Location: index.php');
    exit;
}

// If not user, try admins (if admin login attempt)
$stmt2 = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
$stmt2->execute([$email]); // admin logs in with username in the same field
$admin = $stmt2->fetch();
if ($admin && password_verify($password, $admin['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['user_name'] = $admin['username'];
    $_SESSION['is_admin'] = true;
    $_SESSION['message'] = "Admin logged in.";
    header('Location: admin/dashboard.php');
    exit;
}

$_SESSION['message'] = "Invalid credentials.";
header('Location: index.php');
exit;
?>