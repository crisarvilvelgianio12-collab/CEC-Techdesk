<?php
require_once __DIR__ . '/config.php';

require_login();
if (!is_admin()) {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $status = $_POST['status'] ?? 'Open';

    // only accept certain statuses
    $allowed = ['Open', 'In Progress', 'Closed'];
    if ($id > 0 && in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    header("Location: admin_tickets.php");
    exit;
}

// fallback
header("Location: admin_tickets.php?status=" . urlencode($status));
exit;
