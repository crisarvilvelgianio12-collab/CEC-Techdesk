<?php 
require_once __DIR__ . '/config.php'; 
require_login();  

if (!is_admin()) {
    http_response_code(403);
    exit('Forbidden');
}

// -----------------------------
// INITIALIZE FILTER VARIABLES
// -----------------------------
$statusFilter  = $_GET['status'] ?? 'Open';
$search        = trim($_GET['q'] ?? '');
$selectedYear  = $_GET['year'] ?? '';
$selectedMonth = $_GET['month'] ?? '';
$selectedDay   = $_GET['day'] ?? '';

// -----------------------------
// BUILD FILTER CONDITIONS
// -----------------------------
$where  = ["status = ?"];
$params = [$statusFilter];

// Search filter
if ($search !== '') {
    $where[] = "(
        id LIKE ? OR
        comment LIKE ? OR
        problem LIKE ? OR
        urgency LIKE ? OR
        status LIKE ? OR
        created_by LIKE ? OR
        created_at LIKE ? OR
        ticket_date LIKE ? OR
        lab_room LIKE ? OR
        teacher_name LIKE ? OR
        student_name LIKE ? OR
        pc_number LIKE ?
    )";
    $param = "%$search%";
    for ($i = 0; $i < 12; $i++) $params[] = $param;
}

// Date filters (by ticket_date)
if ($selectedYear !== '') {
    $where[] = "YEAR(ticket_date) = ?";
    $params[] = $selectedYear;
}
if ($selectedMonth !== '') {
    $where[] = "MONTH(ticket_date) = ?";
    $params[] = $selectedMonth;
}
if ($selectedDay !== '') {
    $where[] = "DAY(ticket_date) = ?";
    $params[] = $selectedDay;
}

// -----------------------------
// PAGINATION SETTINGS
// -----------------------------
$limit = 10; // tickets per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// -----------------------------
// COUNT TOTAL FOR PAGINATION
// -----------------------------
$countSql = "SELECT COUNT(*) FROM tickets";
if ($where) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalTickets = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalTickets / $limit));

// -----------------------------
// MAIN QUERY (oldest first)
// -----------------------------
$sql = "SELECT * FROM tickets";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at ASC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct years from tickets table for dropdown
$yearStmt = $pdo->query("SELECT DISTINCT YEAR(ticket_date) AS year FROM tickets ORDER BY year DESC");
$years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 min-h-screen p-6 fixed text-white" style="background: linear-gradient(180deg, #0b534f, #145a32);">
    <div class="flex items-center mb-8">
            <img src="../images/cec_logo.png" alt="CEC Logo" class="w-12 h-12 rounded-full bg-white p-1 mr-3">
            <h2 class="text-2xl font-bold">TechDesk</h2>
        </div>
    <nav class="space-y-4">
        <a href="admin_dashboard.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🏠 Dashboard</a>
        <a href="admin_tickets.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">📋 Manage Tickets</a>
        <a href="admin_incidents.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🚨 View Incidents</a>
        <a href="admin_inventory.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">📦 Inventory</a>
        <a href="logout.php" class="block py-2 px-3 rounded bg-red-600 hover:bg-red-700 transition-colors duration-200">🚪 Logout</a>
    </nav>
</aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-10">
        <div class="bg-white shadow-lg rounded-xl p-8 max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">📋 All Tickets</h2>
            <div class="flex items-center space-x-3 mb-4">
                <a href="admin_tickets.php?status=Open" class="px-4 py-2 rounded-lg text-white <?= ($statusFilter == 'Open') ? 'bg-green-600' : 'bg-gray-400 hover:bg-gray-500' ?>">Open</a>
                <a href="admin_tickets.php?status=In%20Progress" class="px-4 py-2 rounded-lg text-white <?= ($statusFilter == 'In Progress') ? 'bg-yellow-500' : 'bg-gray-400 hover:bg-gray-500' ?>">In Progress</a>
                <a href="admin_tickets.php?status=Closed" class="px-4 py-2 rounded-lg text-white <?= ($statusFilter == 'Closed') ? 'bg-red-600' : 'bg-gray-400 hover:bg-gray-500' ?>">Closed</a>
            </div>

            <!-- Single Search Bar -->
            <form method="get" class="mb-6 flex">
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                <input type="text" name="q" placeholder="Search all columns..." value="<?= htmlspecialchars($search) ?>" class="border rounded p-2 w-full">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded ml-2 hover:bg-green-700">Search</button>
            </form>
            <!-- Date Filters -->
<form method="get" class="mb-6 flex flex-wrap gap-2">
    <div>
        <label class="block text-gray-700 text-sm">Year</label>
        <select name="year" class="border rounded p-2">
    <option value="">All Years</option>
    <?php foreach ($years as $year): ?>
        <option value="<?= $year ?>" <?= $selectedYear == $year ? 'selected' : '' ?>>
            <?= $year ?>
        </option>
    <?php endforeach; ?>
</select>
    </div>

    <div>
        <label class="block text-gray-700 text-sm">Month</label>
        <select name="month" class="border rounded p-2">
            <option value="">All</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $selectedMonth == $m ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <div>
        <label class="block text-gray-700 text-sm">Day</label>
        <select name="day" class="border rounded p-2">
            <option value="">All</option>
            <?php for ($d = 1; $d <= 31; $d++): ?>
                <option value="<?= $d ?>" <?= $selectedDay == $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="flex items-end">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Filter</button>
        <?php if ($selectedYear || $selectedMonth || $selectedDay): ?>
            <a href="admin_tickets.php" class="text-blue-600 underline px-3 py-2">Clear</a>
        <?php endif; ?>
    </div>
</form>
            <!-- Tickets Table -->
            <!-- Tickets Table -->
<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead class="bg-green-600">
            <tr>
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Lab Room</th>
                <th class="px-4 py-2">PC Number</th>
                <th class="px-4 py-2">Teacher</th>
                <th class="px-4 py-2">Working Student</th>
                <th class="px-4 py-2">Issue</th>
                <th class="px-4 py-2">Details</th>               
                <th class="px-4 py-2">Urgency</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Submitted On</th>
                <th class="px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($tickets): ?>
            <?php $i = 1; ?>
            <?php foreach ($tickets as $t): ?>  
            <tr 
                class="border-b hover:bg-gray-50 cursor-pointer transition-colors duration-150" 
                onclick="window.location='ticket_detail.php?id=<?= $t['id'] ?>'"
            >
                <td class="px-4 py-2 font-semibold text-green-600"><?= $i++ ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($t['lab_room']) ?></td>
                <td class="px-4 py-2"><?= $t['pc_number'] ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($t['teacher_name']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($t['student_name']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($t['problem']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($t['comment']) ?></td>               
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
                            <td class="px-4 py-2">
                                <form method="post" action="update_ticket.php" class="flex flex-col space-y-1" onclick="event.stopPropagation();">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($statusFilter) ?>">
                                    <select name="status" class="border rounded p-1">
                                        <option value="Open" <?= $t['status']=='Open'?'selected':'' ?>>Open</option>
                                        <option value="In Progress" <?= $t['status']=='In Progress'?'selected':'' ?>>In Progress</option>
                                        <option value="Closed" <?= $t['status']=='Closed'?'selected':'' ?>>Closed</option>
                                    </select>
                                    <button type="submit" class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 text-sm">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="12" class="px-4 py-2 text-center text-gray-500">No tickets found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <!-- Pagination -->
            <div class="flex justify-center items-center mt-6 space-x-2">
                <div class="text-center mt-6 border-t pt-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Previous</a>
                <?php endif; ?>

                <span class="px-4 py-2 text-gray-700">
                    Page <?= $page ?> of <?= $totalPages ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Next</a>
                <?php endif; ?>
                </div>
</div>
</div>
</div>
    </main>
</body>
</html>
