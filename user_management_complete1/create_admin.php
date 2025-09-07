<?php
// create_admin.php - run once to create an admin user (visit in browser).
// After creating admin, delete this file for security.

require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $error = "Provide username & password.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        try {
            $stmt->execute([$username, $hash]);
            echo "Admin created. Please delete create_admin.php now.";
            exit;
        } catch (Exception $e) {
            $error = "Could not create admin (maybe username exists).";
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Create Admin</title></head><body>
<h1>Create Admin Account</h1>
<?php if(!empty($error)) echo '<p style="color:red">'.htmlspecialchars($error).'</p>'; ?>
<form method="post">
  <label>Username</label><input name="username" required>
  <label>Password</label><input name="password" type="password" required>
  <input type="submit" value="Create Admin">
</form>
</body></html>
