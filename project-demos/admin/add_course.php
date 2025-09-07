<?php
require '../db.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    if($course_id && $course_name) {
        $stmt = $pdo->prepare("INSERT INTO courses (id, name) VALUES (?, ?)");
        $stmt->execute([$course_id, $course_name]);
    }
}
header('Location: dashboard.php');
exit;
