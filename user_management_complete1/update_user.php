<?php
session_start();
require_once __DIR__ . '/includes/config.php';

function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Require login: must be logged in as user or admin
$logged_in_user_id = $_SESSION['user_id'] ?? null;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

if (!$logged_in_user_id && !$is_admin) {
    if (isAjax()) {
        jsonResponse(['error' => "You must be logged in to edit."]);
    } else {
        $_SESSION['message'] = "You must be logged in to edit.";
        header('Location: index.php');
        exit;
    }
}

if (!$is_admin && (int)$logged_in_user_id !== $id) {
    if (isAjax()) {
        jsonResponse(['error' => "You can only edit your own profile."]);
    } else {
        $_SESSION['message'] = "You can only edit your own profile.";
        header('Location: index.php');
        exit;
    }
}

// Fetch genders & courses for form dropdowns
$genders = $pdo->query("SELECT id, name FROM genders ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();

// Fetch user from DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    if (isAjax()) {
        jsonResponse(['error' => "User not found."]);
    } else {
        $_SESSION['message'] = "User not found.";
        header('Location: index.php');
        exit;
    }
}

// Initialize form data with DB user info
$formData = [
    'firstname' => $user['firstname'],
    'middlename' => $user['middlename'],
    'lastname' => $user['lastname'],
    'gender_id' => $user['gender_id'],
    'course_id' => $user['course_id'],
    'email' => $user['email'],
    'password' => '',
];

// Utility function to check if request expects JSON (AJAX)
function isAjax() {
    return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
}

// Utility function to send JSON response and exit
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['firstname'] = trim($_POST['firstname'] ?? '');
    $formData['middlename'] = trim($_POST['middlename'] ?? null);
    $formData['lastname'] = trim($_POST['lastname'] ?? '');
    $formData['gender_id'] = $_POST['gender_id'] ?? null;
    $formData['course_id'] = $_POST['course_id'] ?? null;
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['password'] = $_POST['password'] ?? '';

    $error = '';

    if (!$formData['firstname'] || !$formData['lastname'] || !$formData['email']) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$formData['email'], $id]);
        if ($stmt->fetch()) {
            $error = "Email is already registered by another account.";
        }
    }

    if ($error) {
        if (isAjax()) {
            jsonResponse(['error' => $error]);
        } else {
            // show error below in HTML
        }
    } else {
        if ($formData['password']) {
            $hash = password_hash($formData['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=?, password=? WHERE id=?");
            $stmt->execute([
                $formData['firstname'],
                $formData['middlename'] ?: null,
                $formData['lastname'],
                $formData['gender_id'] ?: null,
                $formData['course_id'] ?: null,
                $formData['email'],
                $hash,
                $id,
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=? WHERE id=?");
            $stmt->execute([
                $formData['firstname'],
                $formData['middlename'] ?: null,
                $formData['lastname'],
                $formData['gender_id'] ?: null,
                $formData['course_id'] ?: null,
                $formData['email'],
                $id,
            ]);
        }

        // If editing own profile, update session user_name if email or name changed
        if ((int)$logged_in_user_id === $id) {
            $_SESSION['user_name'] = $formData['firstname'] . ' ' . ($formData['middlename'] ? $formData['middlename'] . ' ' : '') . $formData['lastname'];
        }

        if (isAjax()) {
            jsonResponse(['success' => "Profile updated successfully."]);
        } else {
            $_SESSION['message'] = "Profile updated successfully.";
            header('Location: index.php');
            exit;
        }
    }
}

$logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
?>

<?php if (!isAjax()): ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Profile - User Management</title>
<link rel="stylesheet" href="style.css" />
<style>
  main form label {
    display: block;
    margin-bottom: 12px;
  }
  main form input, main form select {
    width: 100%;
    padding: 6px;
    margin-top: 4px;
    box-sizing: border-box;
  }
  main form button {
    padding: 10px 15px;
    margin-top: 12px;
  }
  .error {
    color: red;
    margin-bottom: 1em;
  }
  nav a {
    color: #fff;
    margin-left: 1em;
  }
</style>
</head>
<body>

<header>
  <h1>User Management</h1>
</header>

<nav>
  <?php if ($logged_in): ?>
    Welcome, <strong><?= e($user_name) ?></strong> | 
    <a href="index.php">Home</a> | 
    <a href="logout.php" style="color:#fff">Logout</a>
  <?php else: ?>
    <a href="index.php">Home</a>
  <?php endif; ?>
</nav>

<aside>
  <h2>Login / Register</h2>
  <p><a href="index.php">Go to login/register</a></p>
</aside>

<main>
  <h2>Edit Profile</h2>

  <?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <label>First Name:
      <input type="text" name="firstname" value="<?= e($formData['firstname']) ?>" required />
    </label>

    <label>Middle Name:
      <input type="text" name="middlename" value="<?= e($formData['middlename']) ?>" />
    </label>

    <label>Last Name:
      <input type="text" name="lastname" value="<?= e($formData['lastname']) ?>" required />
    </label>

    <label>Gender:
      <select name="gender_id">
        <option value="">-- Select Gender --</option>
        <?php foreach ($genders as $g): ?>
          <option value="<?= e($g['id']) ?>" <?= $g['id'] == $formData['gender_id'] ? 'selected' : '' ?>>
            <?= e($g['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Course:
      <select name="course_id">
        <option value="">-- Select Course --</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= e($c['id']) ?>" <?= $c['id'] == $formData['course_id'] ? 'selected' : '' ?>>
            <?= e($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Email:
      <input type="email" name="email" value="<?= e($formData['email']) ?>" required />
    </label>

    <label>Password (leave blank to keep current):
      <input type="password" name="password" value="" />
    </label>

    <button type="submit">Update Profile</button>
  </form>

</main>

<footer>
  <small>&copy; 2025 Created by Deligero Mar Carlo. Collaboration with AI</small>
</footer>

</body>
</html>
<?php endif; ?>
