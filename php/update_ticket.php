<?php
require_once __DIR__ . '/config.php';
require_login();

if (!is_admin()) {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'Open';
    $currentStatus = $_POST['current_status'] ?? $status; // default to new status if not set

    // validate allowed statuses
    $allowed = ['Open', 'In Progress', 'Closed'];
    if ($id > 0 && in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    // redirect back to the same tab
    header("Location: admin_tickets.php?status=" . urlencode($currentStatus));
    exit;
}

// fallback redirect
header("Location: admin_tickets.php");
exit;