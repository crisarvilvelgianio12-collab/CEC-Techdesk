<?php
require_once __DIR__ . '/config.php';
require_login();

// Get the first admin
$stmt = $pdo->query("SELECT username, availability FROM users WHERE role = 'admin' LIMIT 1");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
if($admin){
    echo json_encode(['username' => $admin['username'], 'availability' => $admin['availability'] ?? 'Available']);
} else {
    echo json_encode(['username' => '', 'availability' => 'Unavailable']);
}
