<?php
require 'db.php';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $course_id = $_POST['course_id'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if($first_name && $last_name && $email && $password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (first_name, middle_name, last_name, course_id, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $middle_name, $last_name, $course_id, $email, $password_hash]);
    }
}

// Fetch users (include password hash)
$users = $pdo->query("
    SELECT u.id, u.first_name, u.middle_name, u.last_name, 
           c.name AS course, u.email, u.password_hash 
    FROM users u 
    LEFT JOIN courses c ON u.course_id = c.id 
    ORDER BY u.id DESC
")->fetchAll();

// Fetch courses
$courses = $pdo->query("SELECT * FROM courses")->fetchAll();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<link rel='stylesheet' href='styles.css'>
<title>User Sign Up</title>
</head>
<body>
<div class='container'>
  <header><h1>Lanao School of Science & Technology incorporated - User Site</h1></header>
  <aside>
    <form method='POST' id='signupForm'>
      <input type='text' name='first_name' placeholder='First Name' required>
      <input type='text' name='middle_name' placeholder='Middle Name'>
      <input type='text' name='last_name' placeholder='Last Name' required>
      <select name='course_id' required>
        <option value=''>Select Course</option>
        <?php foreach($courses as $course): ?>
          <option value='<?= $course['id'] ?>'><?= $course['name'] ?></option>
        <?php endforeach; ?>
      </select>
      <input type='email' name='email' placeholder='Email' required>
      <input type='password' name='password' placeholder='Password' required>
      <input type='submit' value='Sign Up'>
    </form>
  </aside>
  <main>
    <h2>All Users</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Course</th>
        <th>Email</th>
        <th>Password (Hashed)</th>
      </tr>
      <?php foreach($users as $user): ?>
      <tr>
        <td><?= $user['id'] ?></td>
        <td><?= $user['first_name'].' '.$user['middle_name'].' '.$user['last_name'] ?></td>
        <td><?= $user['course'] ?></td>
        <td><?= $user['email'] ?></td>
        <td><?= $user['password_hash'] ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </main>
</div>
<script src='app.js'></script>
</body>
</html>
