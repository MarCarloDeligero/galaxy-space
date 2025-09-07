<!doctype html>
<html><head><meta charset="utf-8"><title>Edit Profile</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><h1>Edit Profile</h1></header>
<main>
  <form method="post" action="update_user.php?id=<?php echo $user['id']; ?>">
    <label>Firstname</label>
    <input name="firstname" value="<?php echo e($user['firstname']); ?>" required>
    <label>Middlename</label>
    <input name="middlename" value="<?php echo e($user['middlename']); ?>">
    <label>Lastname</label>
    <input name="lastname" value="<?php echo e($user['lastname']); ?>" required>

    <label>Gender</label>
    <select name="gender_id" required>
      <option value="">-- select --</option>
      <?php foreach($genders as $g): ?>
        <option value="<?php echo $g['id']; ?>" <?php if($g['id']==$user['gender_id']) echo 'selected'; ?>>
          <?php echo e($g['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Course</label>
    <select name="course_id" required>
      <option value="">-- select --</option>
      <?php foreach($courses as $c): ?>
        <option value="<?php echo $c['id']; ?>" <?php if($c['id']==$user['course_id']) echo 'selected'; ?>>
          <?php echo e($c['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Email</label>
    <input name="email" type="email" value="<?php echo e($user['email']); ?>" required>

    <label>New Password (leave blank to keep current)</label>
    <input name="password" type="password">

    <input type="submit" value="Save changes">
    <a href="index.php" style="margin-left:10px;">Cancel</a>
  </form>
</main>
</body>
</html>
