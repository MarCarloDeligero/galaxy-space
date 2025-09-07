<?php
// admin/dashboard.php
session_start();
require_once __DIR__ . '/../includes/config.php';

// admin guard
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Fetch users, courses, genders
$users = $pdo->query("SELECT u.id, u.firstname, u.middlename, u.lastname, u.email, g.name AS gender, c.name AS course, u.created_at
    FROM users u
    LEFT JOIN genders g ON u.gender_id=g.id
    LEFT JOIN courses c ON u.course_id=c.id
    ORDER BY u.id DESC")->fetchAll();

$courses = $pdo->query("SELECT id, name FROM courses ORDER BY id")->fetchAll();
$genders = $pdo->query("SELECT id, name FROM genders ORDER BY id")->fetchAll();

$message = '';
$error = '';

// For popup notification after add/delete course/gender
$popupMessage = '';
if (isset($_GET['msg'])) {
    $popupMessage = $_GET['msg'];
}

// Check if editing user
$editUser = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch();

    if (!$editUser) {
        $error = "User not found for editing.";
    }
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = (int)($_POST['id'] ?? 0);
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? null);
    $lastname = trim($_POST['lastname'] ?? '');
    $gender_id = $_POST['gender_id'] ?? null;
    $course_id = $_POST['course_id'] ?? null;
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (!$id || !$firstname || !$lastname || !$email) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Check if email belongs to another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $error = "Email already used by another account.";
        } else {
            // Update user
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=?, password=? WHERE id=?");
                $stmt->execute([$firstname, $middlename, $lastname, $gender_id ?: null, $course_id ?: null, $email, $hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET firstname=?, middlename=?, lastname=?, gender_id=?, course_id=?, email=? WHERE id=?");
                $stmt->execute([$firstname, $middlename, $lastname, $gender_id ?: null, $course_id ?: null, $email, $id]);
            }
            $message = "User updated successfully.";
            // Reload updated user info
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $editUser = $stmt->fetch();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<link rel="stylesheet" href="../admin/admin-style.css">
<link rel="icon" type="image/png" href="../admin/images/admin.png">
<title>Admin Dashboard</title>
</head>
<body>

<header>
  <h1>Admin Dashboard</h1>
  <div class="header-left">
    <a href="../logout.php">Logout</a> | 
    <a href="logout_and_redirect.php" onclick="return confirm('You will be logged out. Continue to site?');">Site</a> |
    <a href="#" id="manageCoursesBtn">Manage Courses</a> |
    <a href="#" id="manageGendersBtn">Manage Genders</a>
  </div>
  <div class="header-right">
    Logged in as <strong><?= e($_SESSION['user_name']) ?></strong>
  </div>
</header>

<!-- Popup notification -->
<div id="popupNotification" class="popup-notification" aria-live="polite" role="alert" style="display:none;"></div>

<aside>

  <?php if ($message): ?>
    <div class="message"><?= e($message) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if ($editUser): ?>
    <form method="post" name="editUserForm">
      <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">
      <label>First Name:
        <input type="text" name="firstname" value="<?= e($editUser['firstname']) ?>" required>
      </label>

      <label>Middle Name:
        <input type="text" name="middlename" value="<?= e($editUser['middlename']) ?>">
      </label>

      <label>Last Name:
        <input type="text" name="lastname" value="<?= e($editUser['lastname']) ?>" required>
      </label>

      <label>Gender:
        <select name="gender_id">
          <option value="">-- Select Gender --</option>
          <?php foreach($genders as $g): ?>
            <option value="<?= $g['id'] ?>" <?= $g['id'] == $editUser['gender_id'] ? 'selected' : '' ?>>
              <?= e($g['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Course:
        <select name="course_id">
          <option value="">-- Select Course --</option>
          <?php foreach($courses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id'] == $editUser['course_id'] ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Email:
        <input type="email" name="email" value="<?= e($editUser['email']) ?>" required>
      </label>

      <label>Password:
        <input type="password" name="password">
      </label>

      <button type="submit" name="update_user">Update User</button>
    </form>
  <?php else: ?>
    <p>Please select a user from the list to edit.</p>
  <?php endif; ?>
</aside>

<main>
  <h2>All Users</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Gender</th><th>Course</th><th>Joined</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($users as $u): ?>
        <tr data-user-id="<?= (int)$u['id'] ?>" <?= $editUser && $u['id'] == $editUser['id'] ? 'class="highlight"' : '' ?>>
          <td><?= (int)$u['id'] ?></td>
          <td class="name"><?= e($u['firstname'].' '.($u['middlename'] ? $u['middlename'].' ' : '').$u['lastname']) ?></td>
          <td class="email"><?= e($u['email']) ?></td>
          <td class="gender"><?= e($u['gender'] ?? '-') ?></td>
          <td class="course"><?= e($u['course'] ?? '-') ?></td>
          <td><?= e($u['created_at']) ?></td>
          <td>
            <a href="?edit_id=<?= (int)$u['id'] ?>">Edit</a> |
            <a href="delete_user.php?id=<?= (int)$u['id'] ?>" class="delete-user-link">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<footer>
  <small><span id="ftrmssge"></span></small>
</footer>

<script>
  const encoded = "JmNvcHk7IDIwMjUgQ3JlYXRlZCBieSBEZWxpZ2VybyBNYXIgQ2FybG8uIENvbGxhYm9yYXRpb24gd2l0aCBBSQ==";
document.getElementById('ftrmssge').innerHTML = atob(encoded);

</script>

<!-- Manage Courses Modal -->
<div class="modal-overlay" id="coursesModal">
  <div class="modal-content">
    <button class="modal-close" data-close="coursesModal" aria-label="Close modal">&times;</button>
    <h2>Manage Courses</h2>
    <form id="courseForm" method="post" action="action_course.php" class="modal-form">
      <input type="hidden" name="action" value="add">
      <input type="text" name="name" placeholder="Course name" required autocomplete="off">
      <button type="submit" title="Add Course">+</button>
    </form>
    <ul class="modal-list" id="courseList">
      <?php foreach($courses as $c): ?>
        <li>
          <?= e($c['name']) ?>
          <form method="post" action="action_course.php" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="danger" onclick="return confirm('Delete course?');" title="Delete course">&times;</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- Manage Genders Modal -->
<div class="modal-overlay" id="gendersModal">
  <div class="modal-content">
    <button class="modal-close" data-close="gendersModal" aria-label="Close modal">&times;</button>
    <h2>Manage Genders</h2>
    <form id="genderForm" method="post" action="action_gender.php" class="modal-form">
      <input type="hidden" name="action" value="add">
      <input type="text" name="name" placeholder="Gender name" required autocomplete="off">
      <button type="submit" title="Add Gender">+</button>
    </form>
    <ul class="modal-list" id="genderList">
      <?php foreach($genders as $g): ?>
        <li>
          <?= e($g['name']) ?>
          <form method="post" action="action_gender.php" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <button class="danger" onclick="return confirm('Delete gender?');" title="Delete gender">&times;</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<script>
// Modal open/close logic
document.addEventListener('DOMContentLoaded', () => {
  const coursesBtn = document.getElementById('manageCoursesBtn');
  const gendersBtn = document.getElementById('manageGendersBtn');
  const coursesModal = document.getElementById('coursesModal');
  const gendersModal = document.getElementById('gendersModal');

  function openModal(modal) {
    modal.style.display = 'flex';
    const input = modal.querySelector('input[type="text"]');
    if(input) input.focus();
  }

  function closeModal(modal) {
    modal.style.display = 'none';
  }

  coursesBtn.addEventListener('click', e => {
    e.preventDefault();
    openModal(coursesModal);
  });

  gendersBtn.addEventListener('click', e => {
    e.preventDefault();
    openModal(gendersModal);
  });

  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const modalId = btn.getAttribute('data-close');
      closeModal(document.getElementById(modalId));
    });
  });

  // Close modal by clicking outside modal content
  [coursesModal, gendersModal].forEach(modal => {
    modal.addEventListener('click', e => {
      if (e.target === modal) closeModal(modal);
    });
  });

  // ESC key closes modal
  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      [coursesModal, gendersModal].forEach(modal => {
        if (modal.style.display === 'flex') closeModal(modal);
      });
    }
  });

  // Open modal and show popup if URL params are set
  const urlParams = new URLSearchParams(window.location.search);
  const modalId = urlParams.get('modal');
  const popupMsg = <?= json_encode($popupMessage) ?>;
  if (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      openModal(modal);
    }
  }
  if (popupMsg) {
    showPopup(popupMsg);
  }

  // Remove modal/msg params from URL without reloading page to prevent modal reopening on refresh
  if (window.history.replaceState) {
    const url = new URL(window.location);
    url.searchParams.delete('modal');
    url.searchParams.delete('msg');
    window.history.replaceState({}, document.title, url.toString());
  }
});

// Popup notification function
function showPopup(message, duration = 800) {
  const popup = document.getElementById('popupNotification');
  popup.textContent = message;
  popup.style.display = 'block';
  popup.classList.add('show');
  setTimeout(() => {
    popup.classList.remove('show');
    setTimeout(() => popup.style.display = 'none', 400);
  }, duration);
}

// Update user form AJAX submit
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form[name="editUserForm"]');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('update_user_ajax.php', {
      method: 'POST',
      body: formData
    })
    .then(resp => resp.json())
    .then(data => {
      if (data.error) {
        alert('Error: ' + data.error);
      } else if (data.success) {
        showPopup('User updated successfully!');

        const user = data.user;
        const row = document.querySelector(`table tbody tr[data-user-id='${user.id}']`);
        if (row) {
          row.querySelector('td.name').textContent = user.firstname + (user.middlename ? ' ' + user.middlename : '') + ' ' + user.lastname;
          row.querySelector('td.email').textContent = user.email;
          row.querySelector('td.gender').textContent = user.gender || '-';
          row.querySelector('td.course').textContent = user.course || '-';

          // Highlight updated row temporarily
          row.classList.add('updated-highlight');
          setTimeout(() => row.classList.remove('updated-highlight'), 3000);
        }
      }
    })
    .catch(err => {
      alert('Unexpected error');
      console.error(err);
    });
  });
});

// Confirmation popup + notification for deleting user links
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a.delete-user-link').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      if (confirm('Delete user?')) {
        fetch(this.href, { method: 'GET' })
        .then(resp => resp.json())
        .then(data => {
          if (data.success) {
            showPopup('User deleted successfully!');
            // Remove the user's row from table
            const row = document.querySelector(`tr[data-user-id='${data.user_id}']`);
            if (row) row.remove();
          } else if (data.error) {
            alert('Error: ' + data.error);
          }
        })
        .catch(() => alert('Error deleting user.'));
      }
    });
  });
});
</script>

</body>
</html>
