<?php
require_once __DIR__ . '/config.php';
require_login();
if (!is_staff()) {
    http_response_code(403);
    exit('Forbidden');
}

// --- Pagination Setup ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Filters ---
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$month = $_GET['month'] ?? '';
$day = $_GET['day'] ?? '';
$year = $_GET['year'] ?? '';

$columns = ['problem', 'urgency', 'status', 'lab_room', 'teacher_name', 'student_name', 'pc_number'];

$query = "SELECT * FROM tickets WHERE 1";
$params = [];

// Search
if (!empty($search)) {
    $query .= " AND (";
    $search_conditions = [];
    foreach ($columns as $col) {
        $search_conditions[] = "$col LIKE ?";
        $params[] = "%$search%";
    }
    $query .= implode(" OR ", $search_conditions) . ")";
}

// Status filter
if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

// Date filters
if (!empty($month)) {
    $query .= " AND MONTH(created_at) = ?";
    $params[] = $month;
}
if (!empty($day)) {
    $query .= " AND DAY(created_at) = ?";
    $params[] = $day;
}
if (!empty($year)) {
    $query .= " AND YEAR(created_at) = ?";
    $params[] = $year;
}

// Count total for pagination
$countQuery = preg_replace('/SELECT \* FROM/', 'SELECT COUNT(*) FROM', $query, 1);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total_tickets = $countStmt->fetchColumn();
$total_pages = ceil($total_tickets / $limit);

// Add pagination to main query
$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff - Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>.clickable-row { cursor: pointer; }</style>
    <script>
        function goToDetail(id) {
            window.location.href = "ticket_detail.php?id=" + id;
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- Sidebar -->
<aside class="w-64 p-6 fixed h-full text-white" style="background: linear-gradient(180deg, #0b534f, #145a32);">
    <div class="flex items-center mb-8">
            <img src="../images/cec_logo.png" alt="CEC Logo" class="w-12 h-12 rounded-full bg-white p-1 mr-3">
            <h2 class="text-2xl font-bold">TechDesk</h2>
        </div>
    <nav class="space-y-4">
        <a href="staff_dashboard.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🏠 Dashboard</a>
        <a href="staff_tickets.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">📋 View Tickets</a>
        <a href="staff_incidents.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🚨 View Incidents</a>
        <a href="staff_inventory.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">📦 Inventory</a>
        <a href="logout.php" class="block py-2 px-3 rounded bg-red-600 hover:bg-red-700 transition-colors duration-200">🚪 Logout</a>
    </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 ml-64 p-10">
    <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">📋 Ticket List</h1>

    <!-- Filters -->
    <form method="GET" class="flex space-x-2 mb-6">
    <input type="text" name="search" placeholder="Search tickets..." value="<?= htmlspecialchars($search) ?>"
        class="border p-2 rounded w-1/2">

    <!-- Search button: copy this next to the input -->
    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        🔍 Search
    </button>

        <select name="status" class="border p-2 rounded">
            <option value="">All Status</option>
            <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="in-progress" <?= $status === 'in-progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>

        <select name="month" class="border p-2 rounded">
            <option value="">Month</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>

        <select name="day" class="border p-2 rounded">
            <option value="">Day</option>
            <?php for ($d = 1; $d <= 31; $d++): ?>
                <option value="<?= $d ?>" <?= ($day == $d) ? 'selected' : '' ?>><?= $d ?></option>
            <?php endfor; ?>
        </select>

        <select name="year" class="border p-2 rounded">
            <option value="">Year</option>
            <?php 
            $currentYear = date('Y');
            for ($y = $currentYear; $y >= 2020; $y--): ?>
                <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Filter</button>
        <a href="staff_tickets.php" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Reset</a>
    </form>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-xl p-6 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-green-600 text-white">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Lab</th>
                    <th class="px-4 py-2">PC #</th>
                    <th class="px-4 py-2">Teacher</th>
                    <th class="px-4 py-2">Working Student</th>
                    <th class="px-4 py-2">Issue</th>
                    <th class="px-4 py-2">Urgency</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Submitted On</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tickets): $i = $offset + 1; foreach ($tickets as $t): ?>
                    <tr class="border-b hover:bg-gray-50 clickable-row"
                        onclick="goToDetail(<?= $t['id'] ?>)">
                        <td class="px-4 py-2 font-semibold text-green-700"><?= $i++ ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['lab_room']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['pc_number']) ?></td>
                         <td class="px-4 py-2"><?= htmlspecialchars($t['teacher_name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['student_name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['problem']) ?></td>
                        <!-- Urgency with color -->
                        <?php
                            $urgencyKey = strtolower(trim((string)($t['urgency'] ?? '')));
                            if ($urgencyKey === 'high') {
                            $badge = 'bg-red-500 text-white';
                            $label = 'High';
                            } elseif ($urgencyKey === 'medium') {
                                $badge = 'bg-yellow-400 text-black';
                                $label = 'Medium';
                            } elseif ($urgencyKey === 'low') {
                                $badge = 'bg-green-400 text-black';
                                $label = 'Low';
                            } else {
                                $badge = 'bg-gray-400 text-white';
                                $label = $urgencyKey ? htmlspecialchars($urgencyKey) : 'Unknown';
                            }
                            ?>
                            <td class="px-4 py-2"><span class="px-3 py-1 rounded-full font-semibold <?= $badge ?>"><?= $label ?></span></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($t['status']) ?></td>

                       <?php
$submitted_on = (!empty($t['ticket_date']) && !empty($t['created_at']))
    ? date('F j, Y - h:i A', strtotime($t['ticket_date'] . ' ' . date('H:i:s', strtotime($t['created_at']))))
    : 'N/A';
?>
<td class="px-4 py-2"><?= $submitted_on ?></td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr><td colspan="9" class="text-center text-gray-500 py-4">No tickets found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="flex justify-center items-center space-x-2 mt-6">
                                <?php if ($page > 1): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Prev</a>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
                                    class="px-3 py-1 rounded <?= $p == $page ? 'bg-green-700 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

</main>
</body>
</html>
