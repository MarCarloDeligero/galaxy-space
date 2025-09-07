<?php
// register.php
session_start();
require_once __DIR__ . '/includes/config.php';

// Grab and trim inputs
$firstname = trim($_POST['firstname'] ?? '');
$middlename = trim($_POST['middlename'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$gender_id = $_POST['gender_id'] ?? '';
$course_id = $_POST['course_id'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation and error array
$errors = [];

// Required fields validation
if (!$firstname) $errors['firstname'] = 'First name is required.';
if (!$lastname) $errors['lastname'] = 'Last name is required.';
if (!$gender_id) $errors['gender_id'] = 'Please select your gender.';
if (!$course_id) $errors['course_id'] = 'Please select your course.';
if (!$email) {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address.';
}
if (!$password) $errors['password'] = 'Password is required.';

// Check if email already exists
if (!$errors) { // only if no previous errors
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors['email'] = 'Email already registered.';
    }
}

if ($errors) {
    // Store errors and old inputs in session to repopulate form
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = [
        'firstname' => $firstname,
        'middlename' => $middlename,
        'lastname' => $lastname,
        'gender_id' => $gender_id,
        'course_id' => $course_id,
        'email' => $email,
    ];
    $_SESSION['show_register'] = true;
    header('Location: index.php');
    exit;
}

// If no errors, hash password and insert
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (firstname, middlename, lastname, gender_id, course_id, email, password)
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$firstname, $middlename, $lastname, $gender_id, $course_id, $email, $hash]);

$_SESSION['message'] = "Registration successful. You may now log in.";
header('Location: index.php');
exit;
?>
