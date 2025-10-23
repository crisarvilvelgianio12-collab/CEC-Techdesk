<?php
require_once __DIR__ . '/config.php';
require_login();

if (!is_admin() && !is_staff()) {
    http_response_code(403);
    exit('Forbidden');
}

$lab = $_GET['lab'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if (!$lab) {
    echo json_encode(['tickets' => [], 'totalPages' => 0]);
    exit;
}

// Count total tickets
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE lab_room = ?");
$countStmt->execute([$lab]);
$totalTickets = $countStmt->fetchColumn();
$totalPages = ceil($totalTickets / $limit);

// Fetch paginated tickets
$stmt = $pdo->prepare("
    SELECT id, comment, problem, urgency, teacher_name, student_name, ticket_date
    FROM tickets
    WHERE lab_room = ?
    ORDER BY ticket_date DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute([$lab]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'tickets' => $tickets,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'totalTickets' => $totalTickets
]);
