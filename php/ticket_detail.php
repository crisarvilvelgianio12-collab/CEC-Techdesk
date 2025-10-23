<?php
require_once __DIR__ . '/config.php';
require_login();

// Allow both Admin and Staff
if (!is_admin() && !is_staff()) {
    http_response_code(403);
    exit('Forbidden');
}

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    exit('Ticket not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= $ticket['id'] ?> Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-10">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-3xl">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">
            Ticket #<?= $ticket['id'] ?> Details
        </h2>   

        <div class="space-y-2">
            <p><strong>Lab Room:</strong> <?= htmlspecialchars($ticket['lab_room']) ?></p>
            <p><strong>PC Number:</strong> <?= $ticket['pc_number'] ?></p>
            <p><strong>Teacher:</strong> <?= htmlspecialchars($ticket['teacher_name']) ?></p>
            <p><strong>Working Student:</strong> <?= htmlspecialchars($ticket['student_name']) ?></p>
            <p><strong>Issue:</strong> <?= htmlspecialchars($ticket['problem']) ?></p>
            <p><strong>Details : (Included: Who use the PC? ):</strong> <?= htmlspecialchars($ticket['comment']) ?></p>
            <p><strong>Urgency:</strong> <?= htmlspecialchars($ticket['urgency']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?></p>
            <p><strong>Ticket Date:</strong> <?= $ticket['ticket_date'] ?></p>
            <p><strong>Submitted On:</strong> <?= $ticket['created_at'] ?></p>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0 no-print">
            <!-- Print Button -->
            <button onclick="window.print()" 
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                🖨 Print
            </button>

            <!-- Download PDF -->
            <form method="post" action="download_ticket.php">
                <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
                <button type="submit" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    📄 Download PDF
                </button>
            </form>

            <!-- Back Button -->
            <?php if (is_admin()): ?>
                <a href="admin_tickets.php" 
                   class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-center">
                   ← Back to Tickets
                </a>
            <?php elseif (is_staff()): ?>
                <a href="staff_tickets.php" 
                   class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-center">
                   ← Back to Tickets
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
