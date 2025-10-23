<?php
require_once __DIR__ . '/config.php';
require_login();

if (!is_admin()) {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $availability = $_POST['availability'] ?? 'Available';
    $allowed = ['Available','On Break','At Lunch','In Training/Teaching','Working on an Issue','Offline'];

    if (in_array($availability, $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE users SET availability = ? WHERE username = ?");
        $stmt->execute([$availability, $_SESSION['username']]);

        // Return success response
        echo json_encode(['status' => 'success', 'availability' => $availability]);
        exit;
    }
}

// fallback
echo json_encode(['status' => 'error']);
