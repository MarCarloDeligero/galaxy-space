<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'galactic_defender');

// Admin credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'securepassword123');

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle login
if (isset($_POST['login']) && $_POST['login'] === '1') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Invalid credentials!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        die("Unauthorized!");
    }
    
    $stmt = $conn->prepare("DELETE FROM completions WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();

    $_SESSION['notification'] = "Record #{$_GET['id']} deleted successfully!";
    header("Location: admin.php");
    exit;

    
    header("Location: admin.php");
    exit;
}

// Check if logged in
$logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
?>
<!DOCTYPE html>
<html>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<head>
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .logout { float: right; }
    </style>
</head>

<style>
    /* === Galactic Admin Theme === */
    body {
        background: #0a0e2a;
        color: #fff;
        font-family: 'Orbitron', sans-serif;
        margin: 0;
        padding: 20px;
        min-height: 100vh;
        background-image: radial-gradient(circle at center, #1a1f4a 0%, #000033 100%);
    }

    .admin-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 20px;
        border: 1px solid #4CAF50;
        border-radius: 10px;
        box-shadow: 0 0 30px rgba(76, 175, 80, 0.3);
        background: rgba(0, 0, 40, 0.9);
    }

    h2 {
        color: #4CAF50;
        text-shadow: 0 0 15px #4CAF50;
        border-bottom: 2px solid #4CAF50;
        padding-bottom: 10px;
        font-size: 2rem;
        letter-spacing: 2px;
    }

    form {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 900px;
        margin: 2rem auto;
    }

    input[type="text"],
    input[type="password"] {
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid #4CAF50;
        border-radius: 5px;
        color: #fff;
        font-family: 'Orbitron';
        transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        box-shadow: 0 0 15px #4CAF50;
    }

    button[type="submit"] {
        background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
        color: #000;
        padding: 12px;
        border: none;
        border-radius: 5px;
        font-family: 'Orbitron';
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    button[type="submit"]:hover {
        box-shadow: 0 0 20px #4CAF50;
        transform: translateY(-2px);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
        background: rgba(0, 0, 0, 0.7);
        border: 1px solid #4CAF50;
    }

    th {
        background: #1a1f4a;
        color: #4CAF50;
        padding: 15px;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid rgba(76, 175, 80, 0.3);
    }

    tr:nth-child(even) {
        background: rgba(10, 14, 42, 0.5);
    }

    tr:hover {
        background: rgba(76, 175, 80, 0.1);
        box-shadow: 0 0 15px rgba(76, 175, 80, 0.2);
    }

    .logout {
        background: #F44336;
        color: #fff;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        margin-top: 24px;
        float: right;
        transition: all 0.3s ease;
        border: 1px solid #ff5722;
    }

    .logout:hover {
        background: #ff5722;
        box-shadow: 0 0 15px rgba(244, 67, 54, 0.5);
    }

    .actions a {
        color: #F44336;
        text-decoration: none;
        padding: 5px 10px;
        border: 1px solid #F44336;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .actions a:hover {
        background: #F44336;
        color: #fff;
        box-shadow: 0 0 10px #F44336;
    }

    .notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #4CAF50;
    color: #000;
    padding: 15px 25px;
    border-radius: 5px;
    font-weight: bold;
    box-shadow: 0 0 20px #4CAF50;
    animation: pulse-glow 1.5s infinite;
    border: 1px solid #8BC34A;
    z-index: 1000; /* Ensure it's above other elements */
    display: none; /* Start hidden */
}

/* Add animation for smooth appearance */
@keyframes slideIn {
    from { top: -50px; opacity: 0; }
    to { top: 20px; opacity: 1; }
}

.notification.show {
    display: block;
    animation: slideIn 0.5s ease-out;
}

    @keyframes pulse-glow {
        0% { box-shadow: 0 0 10px #4CAF50; }
        50% { box-shadow: 0 0 30px #4CAF50; }
        100% { box-shadow: 0 0 10px #4CAF50; }
    }

    .error-message {
        color: #F44336;
        text-shadow: 0 0 10px #F44336;
        padding: 10px;
        border: 1px solid #F44336;
        border-radius: 5px;
        background: rgba(244, 67, 54, 0.1);
    }

    @media (max-width: 768px) {
        .admin-container {
            padding: 10px;
            margin: 1rem;
        }
        
        table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
    
<body>

<?php if (isset($_SESSION['notification'])): ?>
    <div class="notification show">
        <?= $_SESSION['notification'] ?>
    </div>
    <?php unset($_SESSION['notification']); ?>

    <script>
        setTimeout(() => {
            document.querySelector('.notification').style.display = 'none';
        }, 3000);
    </script>

    <?php endif; ?>

    <?php if (!$logged_in): ?>
        <h2>Admin Login</h2>
        <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="login" value="1">Login</button>
        </form>
    <?php else: ?>
        <a href="admin.php?logout=1" class="logout">Logout</a>
        <h2>Completion Records</h2>
        <table>
        <tr>
            <th>ID</th>
            <th>Player Name</th>
            <th>Difficulty</th>
            <th>Score</th>
            <th>Completion Date</th>
            <th>Actions</th>
        </tr>

            <?php
            $result = $conn->query("SELECT * FROM completions ORDER BY completion_date DESC");
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['player_name']) ?></td>
                <td><?= $row['difficulty'] ?></td>
                <td><?= number_format($row['score']) ?></td>
                <td><?= date('Y-m-d H:i:s', strtotime($row['completion_date'])) ?></td>
                <td>
                    <a href="admin.php?action=delete&id=<?= $row['id'] ?>" 
                       onclick="return confirm('Delete this record?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</body>
</html>