<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = "localhost";
$dbname = "cec_techdesk";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Helper functions
function require_login() {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// ✅ Staff check
function is_staff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}
