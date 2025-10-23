<?php
session_start();
$error = '';

// Database connection (XAMPP defaults)
$host = 'localhost';
$db   = 'cec_techdesk';   // your DB name
$user = 'root';           // default XAMPP user
$pass = '';               // default XAMPP password
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare SQL to prevent injection
    $stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($db_username, $db_password, $role);
        $stmt->fetch();

        // Check password (plain text for now, you can hash later)
        if ($password === $db_password) {
            $_SESSION['username'] = $db_username;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role === 'student') {
                header('Location: student_dashboard.php');
                exit;
            } elseif ($role === 'admin') {
                header('Location: admin_dashboard.php');
                exit;
            } elseif ($role === 'staff') {
                header('Location: staff_dashboard.php');
                exit;
            }
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid username";
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - TechDesk</title>
  <link rel="stylesheet" href="../css/logins.css">
</head>
<body>
  <div class="login-card">
    <h2>🔐 TechDesk Login</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="login-form">
      <label for="username">Username</label>
      <input type="text" name="username" placeholder="Enter your username" required>

      <label for="password">Password</label>
      <input type="password" name="password" placeholder="Enter your password" required>

      <button type="submit" class="btn-primary">Login</button>
    </form>

    <div class="help-text">
      Forgot your password? <a href="#">Reset here</a>
    </div>
  </div>
</body>
</html>
