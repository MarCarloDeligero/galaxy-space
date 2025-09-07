<?php
session_start();
require_once __DIR__ . '/includes/config.php';

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$genders = $pdo->query("SELECT id, name FROM genders ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();

$stmt = $pdo->query("SELECT u.id, u.firstname, u.middlename, u.lastname, u.email, u.created_at, g.name AS gender, c.name AS course
    FROM users u
    LEFT JOIN genders g ON u.gender_id = g.id
    LEFT JOIN courses c ON u.course_id = c.id
    ORDER BY u.id ASC");
$users = $stmt->fetchAll();

$logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$current_user_id = $logged_in ? (int)$_SESSION['user_id'] : null;

$register_errors = $_SESSION['register_errors'] ?? [];
$register_old = $_SESSION['register_old'] ?? [];
$showRegister = $_SESSION['show_register'] ?? false;
unset($_SESSION['register_errors'], $_SESSION['register_old'], $_SESSION['show_register']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Management System</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .error {
      color: red;
      font-size: 0.7em;
      margin-top: 4px;
      margin-bottom: 8px;
    }
    .form-error {
      color: red;
      font-weight: bold;
      margin-bottom: 1em;
    }
    .success-message {
      color: green;
      font-weight: bold;
      margin-bottom: 1em;
    }

    /* Modal styles */
    #delete-confirm.modal {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
    }
    #delete-confirm .modal-content {
      background: #fff;
      padding: 1.5em;
      border-radius: 8px;
      max-width: 90%;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
      text-align: center;
    }

    /* Popup message for success centered */
    .popup-message {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: rgba(0, 128, 0, 0.9);
      color: #fff;
      padding: 1em 2em;
      border-radius: 8px;
      font-weight: bold;
      font-size: 1.2em;
      z-index: 9999;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
      opacity: 1;
      transition: opacity 0.5s ease;
      max-width: 90%;
      text-align: center;
    }
  </style>
</head>
<body>
<header>
  <h1>User Management</h1>
</header>

<nav>
  <?php if($logged_in): ?>
    Welcome, <strong><?php echo e($_SESSION['user_name']); ?></strong>
    | <a href="logout.php" style="color:#fff">Logout</a>
    <?php if($is_admin): ?> | <a href="admin/dashboard.php" style="color:#fff">Admin Panel</a><?php endif; ?>
  <?php else: ?>
    Not logged in? You can register or login from the left.
    
  <?php endif; ?>
</nav>

<aside>
  <h2 id="form-title">Login</h2>

  <div id="login-container">
    <form id="login-form" action="login.php" method="post">
      <label for="login-email">Email</label>
      <input id="login-email" name="email" type="email" required />
      <label for="login-password">Password</label>
      <input id="login-password" name="password" type="password" required />
      <input type="submit" value="Login" />
    </form>

    <form id="register-form" action="register.php" method="post" style="display:none;">
  <input id="firstname" name="firstname" type="text" required 
         placeholder="Firstname"
         value="<?php echo e($register_old['firstname'] ?? ''); ?>" />
  <?php if (!empty($register_errors['firstname'])): ?>
    <div class="error"><?php echo e($register_errors['firstname']); ?></div>
  <?php endif; ?>

  <input id="middlename" name="middlename" type="text" 
         placeholder="Middlename"
         value="<?php echo e($register_old['middlename'] ?? ''); ?>" />

  <input id="lastname" name="lastname" type="text" required 
         placeholder="Lastname"
         value="<?php echo e($register_old['lastname'] ?? ''); ?>" />
  <?php if (!empty($register_errors['lastname'])): ?>
    <div class="error"><?php echo e($register_errors['lastname']); ?></div>
  <?php endif; ?>

  <select id="gender" name="gender_id" required>
    <option value="">-- Select Gender --</option>
    <?php foreach($genders as $g): ?>
      <option value="<?php echo $g['id']; ?>" 
        <?php echo (isset($register_old['gender_id']) && $register_old['gender_id'] == $g['id']) ? 'selected' : ''; ?>>
        <?php echo e($g['name']); ?>
      </option>
    <?php endforeach; ?>
  </select>
  <?php if (!empty($register_errors['gender_id'])): ?>
    <div class="error"><?php echo e($register_errors['gender_id']); ?></div>
  <?php endif; ?>

  <select id="course" name="course_id" required>
    <option value="">-- Select Course --</option>
    <?php foreach($courses as $c): ?>
      <option value="<?php echo $c['id']; ?>" 
        <?php echo (isset($register_old['course_id']) && $register_old['course_id'] == $c['id']) ? 'selected' : ''; ?>>
        <?php echo e($c['name']); ?>
      </option>
    <?php endforeach; ?>
  </select>
  <?php if (!empty($register_errors['course_id'])): ?>
    <div class="error"><?php echo e($register_errors['course_id']); ?></div>
  <?php endif; ?>

  <input id="reg-email" name="email" type="email" required 
         placeholder="Email"
         value="<?php echo e($register_old['email'] ?? ''); ?>" />
  <?php if (!empty($register_errors['email'])): ?>
    <div class="error"><?php echo e($register_errors['email']); ?></div>
  <?php endif; ?>

  <input id="reg-password" name="password" type="password" required 
         placeholder="Password" />
  <?php if (!empty($register_errors['password'])): ?>
    <div class="error"><?php echo e($register_errors['password']); ?></div>
  <?php endif; ?>

  <input type="submit" value="Register" />
</form>

  </div>

  <div id="edit-container" style="display:none;">
    <!-- Edit form injected here -->
  </div>

  <div class="horizontal-or">or</div>
  <button id="toggle-btn" class="secondary">Switch to Register</button>
</aside>

<main>
  <h2>Registered Users</h2>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Email</th><th>Gender</th><th>Course</th><th>Joined</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $u): ?>
          <tr>
            <td><?php echo (int)$u['id']; ?></td>
            <td><?php echo e($u['firstname'] . ' ' . ($u['middlename'] ? $u['middlename'] . ' ' : '') . $u['lastname']); ?></td>
            <td><?php echo e($u['email']); ?></td>
            <td><?php echo e($u['gender'] ?? '-'); ?></td>
            <td><?php echo e($u['course'] ?? '-'); ?></td>
            <td><?php echo e($u['created_at']); ?></td>
            <td>
              <a href="view_user.php?id=<?php echo (int)$u['id']; ?>">View</a>
              <?php if($logged_in && ($current_user_id === (int)$u['id'] || $is_admin)): ?>
                | <a href="#" class="edit-user-btn" data-user-id="<?= (int)$u['id'] ?>">Edit</a>
                | <a href="delete_user.php?id=<?php echo (int)$u['id']; ?>" class="delete-user-link" data-href="delete_user.php?id=<?php echo (int)$u['id']; ?>">Delete</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<footer>
  <small>&copy; 2025 Created by Deligero Mar Carlo. Collaboration with AI</small>
</footer>

<div id="delete-confirm" class="modal" style="display:none;">
  <div class="modal-content">
    <p>Are you sure you want to delete this user?</p>
    <div style="display:flex; justify-content:center; gap: 10px;">
      <button id="confirm-delete">Yes, Delete</button>
      <button id="cancel-delete">Cancel</button>
    </div>
  </div>
</div>

<script>
  const toggle = document.getElementById('toggle-btn');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');
  const title = document.getElementById('form-title');
  const horizontalOr = document.querySelector('.horizontal-or');
  const loginContainer = document.getElementById('login-container');
  const editContainer = document.getElementById('edit-container');
  const toggleBtn = toggle;

  function toggleToggleBtn(show) {
    toggleBtn.style.display = show ? '' : 'none';
  }

  function escapeHtml(text) {
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function buildEditForm(data) {
    const { user, genders, courses } = data;
    return `
      <form id="edit-user-form" method="post" action="update_user.php?id=${user.id}">
        <input type="hidden" name="id" value="${user.id}">
        <label>First Name:
          <input type="text" name="firstname" value="${escapeHtml(user.firstname)}" required>
        </label>
        <label>Middle Name:
          <input type="text" name="middlename" value="${escapeHtml(user.middlename || '')}">
        </label>
        <label>Last Name:
          <input type="text" name="lastname" value="${escapeHtml(user.lastname)}" required>
        </label>
        <label>Gender:
          <select name="gender_id">
            <option value="">-- Select Gender --</option>
            ${genders.map(g => `<option value="${g.id}" ${g.id == user.gender_id ? 'selected' : ''}>${escapeHtml(g.name)}</option>`).join('')}
          </select>
        </label>
        <label>Course:
          <select name="course_id">
            <option value="">-- Select Course --</option>
            ${courses.map(c => `<option value="${c.id}" ${c.id == user.course_id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`).join('')}
          </select>
        </label>
        <label>Email:
          <input type="email" name="email" value="${escapeHtml(user.email)}" required>
          <div id="email-error" class="error" style="display:none;"></div>
        </label>
        <label>Password:
          <input type="password" name="password" placeholder="Leave blank to keep current">
        </label>
        <div style="display: flex; gap: 10px; margin-top: 10px;">
          <button type="submit">Update Profile</button>
          <button type="button" id="cancel-edit">Cancel</button>
        </div>
        <div id="form-message" style="margin-top:10px;"></div>
      </form>
    `;
  }

  function showOrSeparator(show) {
    if (horizontalOr) horizontalOr.style.display = show ? '' : 'none';
  }

  // Toggle login/register form
  toggle.addEventListener('click', () => {
    if (loginForm.style.display === 'none') {
      loginForm.style.display = '';
      registerForm.style.display = 'none';
      title.textContent = 'Login';
      toggle.textContent = 'Switch to Register';
      horizontalOr.style.display = '';
    } else {
      loginForm.style.display = 'none';
      registerForm.style.display = '';
      title.textContent = 'Register';
      toggle.textContent = 'Switch to Login';
      horizontalOr.style.display = '';
    }
  });

  // Show register if PHP says so
  <?php if ($showRegister): ?>
    loginForm.style.display = 'none';
    registerForm.style.display = '';
    title.textContent = 'Register';
    toggle.textContent = 'Switch to Login';
    horizontalOr.style.display = '';
  <?php endif; ?>

  // Edit button click
  document.querySelectorAll('.edit-user-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const userId = btn.getAttribute('data-user-id');

      fetch(`get_user.php?id=${userId}`)
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            alert(data.error);
            return;
          }

          loginContainer.style.display = 'none';
          registerForm.style.display = 'none';
          editContainer.innerHTML = buildEditForm(data);
          editContainer.style.display = 'block';
          title.textContent = 'Edit Profile';
          toggleToggleBtn(false);
          showOrSeparator(false);

          // Cancel edit handler
          document.getElementById('cancel-edit').addEventListener('click', () => {
            editContainer.style.display = 'none';
            editContainer.innerHTML = '';
            loginContainer.style.display = '';
            registerForm.style.display = 'none';
            title.textContent = 'Login';
            toggleToggleBtn(true);
            showOrSeparator(true);
          });

          // Attach submit handler for edit form AJAX
          const editForm = document.getElementById('edit-user-form');
          editForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Clear previous messages
            document.getElementById('email-error').style.display = 'none';
            document.getElementById('email-error').textContent = '';
            const formMessage = document.getElementById('form-message');
            formMessage.textContent = '';
            formMessage.className = '';

            const formData = new FormData(editForm);

            try {
              const response = await fetch(`update_user.php?id=${encodeURIComponent(userId)}`, {
                method: 'POST',
                body: formData,
                headers: {
                  'Accept': 'application/json'
                }
              });

              const data = await response.json();

              if (data.error) {
                // If error is related to email, show below email input
                if (data.error.toLowerCase().includes('email')) {
                  const emailErrorDiv = document.getElementById('email-error');
                  emailErrorDiv.textContent = data.error;
                  emailErrorDiv.style.display = 'block';
                } else {
                  formMessage.textContent = data.error;
                  formMessage.className = 'form-error error';
                }
              } else if (data.success) {
                showPopup(data.success);

                // Reload after delay to update user list and session data
                setTimeout(() => window.location.reload(), 1500);
              }
            } catch (err) {
              formMessage.textContent = 'An error occurred. Please try again.';
              formMessage.className = 'form-error error';
            }
          });
        })
        .catch(() => alert('Failed to load user data.'));
    });
  });

  // Delete modal logic
  const modal = document.getElementById('delete-confirm');
  const confirmBtn = document.getElementById('confirm-delete');
  const cancelBtn = document.getElementById('cancel-delete');
  let deleteUrl = null;

  document.querySelectorAll('.delete-user-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      deleteUrl = link.getAttribute('data-href');
      modal.style.display = 'flex';
    });
  });

  confirmBtn.addEventListener('click', () => {
    if (deleteUrl) window.location.href = deleteUrl;
  });

  cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
    deleteUrl = null;
  });

  // Popup flash message from PHP session (normal bottom-right popup)
  const message = "<?php echo addslashes($_SESSION['message'] ?? ''); ?>";
  <?php unset($_SESSION['message']); ?>

  if (message) {
    const popup = document.createElement('div');
    popup.className = 'popup-message';
    popup.textContent = message;
    document.body.appendChild(popup);

    setTimeout(() => {
      popup.style.opacity = '0';
      setTimeout(() => popup.remove(), 500);
    }, 1500);
  }

  // New helper function to show centered success popup
  function showPopup(message) {
    // Remove any existing popup first
    const existingPopup = document.querySelector('.popup-message');
    if (existingPopup) existingPopup.remove();

    const popup = document.createElement('div');
    popup.className = 'popup-message';
    popup.textContent = message;

    // Apply inline styles (also defined in CSS, but keep here for certainty)
    Object.assign(popup.style, {
      position: 'fixed',
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
      backgroundColor: 'rgba(0, 128, 0, 0.9)',
      color: '#fff',
      padding: '1em 2em',
      borderRadius: '8px',
      fontWeight: 'bold',
      fontSize: '1.2em',
      zIndex: 9999,
      boxShadow: '0 0 15px rgba(0,0,0,0.3)',
      opacity: '1',
      transition: 'opacity 0.5s ease',
      maxWidth: '90%',
      textAlign: 'center',
    });

    document.body.appendChild(popup);

    setTimeout(() => {
      popup.style.opacity = '0';
      setTimeout(() => popup.remove(), 500);
    }, 2000);
  }
	
</script>

</body>
</html>
