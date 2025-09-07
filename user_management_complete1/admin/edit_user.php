<?php
// admin/edit_user.php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: dashboard.php');
    exit;
}

// load user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: dashboard.php');
    exit;
}

$genders = $pdo->query("SELECT id, name FROM genders ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? null);
    $lastname = trim($_POST['lastname'] ?? '');
    $gender_id = $_POST['gender_id'] ?? null;
    $course_id = $_POST['course_id'] ?? null;
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$firstname || !$lastname || !$email) {
        $error = "Missing fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $error = "Email used by another account.";
        } else {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=?, password=? WHERE id=?");
                $stmt->execute([$firstname, $middlename, $lastname, $gender_id ?: null, $course_id ?: null, $email, $hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=? WHERE id=?");
                $stmt->execute([$firstname, $middlename, $lastname, $gender_id ?: null, $course_id ?: null, $email, $id]);
            }
            header('Location: dashboard.php');
            exit;
        }
    }
}
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit User (Admin)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; }
        .error { color: red; margin-top: 10px; }
        button { margin-top: 15px; padding: 10px 15px; }
        a { display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>

<h2>Edit User (Admin)</h2>

<?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post">
    <label>First Name:
        <input type="text" name="firstname" value="<?= e($user['firstname']) ?>" required>
    </label>

    <label>Middle Name:
        <input type="text" name="middlename" value="<?= e($user['middlename']) ?>">
    </label>

    <label>Last Name:
        <input type="text" name="lastname" value="<?= e($user['lastname']) ?>" required>
    </label>

    <label>Gender:
        <select name="gender_id">
            <option value="">-- Select Gender --</option>
            <?php foreach ($genders as $g): ?>
                <option value="<?= $g['id'] ?>" <?= $g['id'] == $user['gender_id'] ? 'selected' : '' ?>>
                    <?= e($g['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Course:
        <select name="course_id">
            <option value="">-- Select Course --</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $user['course_id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Email:
        <input type="email" name="email" value="<?= e($user['email']) ?>" required>
    </label>

    <label>Password (leave blank to keep current):
        <input type="password" name="password">
    </label>

    <button type="submit">Update User</button>
</form>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
