<?php
// admin/index.php - admin login form
session_start();
require_once __DIR__ . '/../includes/config.php';

// If already logged in as admin redirect
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $error = "Enter username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['user_name'] = $admin['username'];
            $_SESSION['is_admin'] = true;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Admin Login</title><link rel="stylesheet" href="../style.css"></head>
<body>
<header><h1>Admin Login</h1></header>
<main>
  <?php if(!empty($error)) echo '<p class="error">'.htmlspecialchars($error).'</p>'; ?>
  <form method="post" action="index.php">
    <label>Username</label><input name="username" required>
    <label>Password</label><input type="password" name="password" required>
    <input type="submit" value="Login">
  </form>
  <p><a href="../index.php">Back to site</a></p>
</main>
</body></html>
