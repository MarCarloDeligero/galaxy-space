<?php
require '../db.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    if($course_id && $course_name) {
        $stmt = $pdo->prepare("INSERT INTO courses (id, name) VALUES (?, ?)");
        try {
            $stmt->execute([$course_id, $course_name]);
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Error: ".$e->getMessage()."</p>";
        }
        header('Location: dashboard.php'); exit;
    }
}
$courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<link rel='stylesheet' href='styles.css'>
<title>Admin Dashboard</title>
</head>
<body>
<div class='container'>
  <header><h1>Lanao School of Science & Technology incorporated - Admin Site</h1></header>
  <aside>
    <form method='POST'>
      <input type='number' name='course_id' placeholder='Course ID' required>
      <input type='text' name='course_name' placeholder='Course Name' required>
      <input type='submit' value='Add Course'>
    </form>
  </aside>
  <main>
    <h2>All Courses</h2>
    <table>
      <tr><th>ID</th><th>Course Name</th></tr>
      <?php foreach($courses as $course): ?>
        <tr><td><?= $course['id'] ?></td><td><?= $course['name'] ?></td></tr>
      <?php endforeach; ?>
    </table>
  </main>
</div>
<script src='app.js'></script>
</body>
</html>
