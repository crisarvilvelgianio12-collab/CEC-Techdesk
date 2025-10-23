<?php
// One-time seeder to create demo users
require_once __DIR__ . '/config.php';
$pdo->exec("DELETE FROM users");
$ins = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
$ins->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
$ins->execute(['student', password_hash('student123', PASSWORD_DEFAULT), 'student']);
echo "Seeded users: admin/admin123, student/student123";
