<?php
session_start();
$error = '';
include('db_connect.php'); // your DB connection file

// Helper functions
function is_safe_input($input) {
    $pattern = '/[\'"\\\;\(\)<>]/'; 
    return !preg_match($pattern, $input);
}

function has_unwanted_patterns($input) {
    $unwanted_patterns = ['/svg/i', '/png/i', '/<script>/i']; 
    foreach ($unwanted_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } elseif (!is_safe_input($username) || !is_safe_input($password) ||
              has_unwanted_patterns($username) || has_unwanted_patterns($password)) {
        $error = "Invalid characters detected in username or password.";
    } else {
        // Prepare SQL to prevent injection
        $stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($db_username, $db_password, $role);
            $stmt->fetch();

            // Plain text check (use password_verify for hashed passwords)
            if ($password === $db_password) {
                $_SESSION['username'] = $db_username;
                $_SESSION['role'] = $role;

                switch ($role) {
                    case 'student':
                        header('Location: student_dashboard.php'); break;
                    case 'staff':
                        header('Location: staff_dashboard.php'); break;
                    case 'admin':
                        header('Location: admin_dashboard.php'); break;
                    default:
                        $error = "Invalid role assigned.";
                }
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Username not found.";
        }

        $stmt->close();
    }
}

$conn->close();
?>
