<?php
require_once __DIR__ . '/config.php';
require_login();


$username = $_SESSION['username'] ?? '';

$selectedYear = $_GET['year'] ?? '';
$selectedMonth = $_GET['month'] ?? '';
$selectedDay = $_GET['day'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM tickets WHERE 1";
$params = [];

// 🔍 Search logic (MUST come first)
if ($search !== '') {
    $sql .= " AND (
        problem LIKE ? OR 
        comment LIKE ? OR 
        urgency LIKE ? OR 
        status LIKE ? OR 
        lab_room LIKE ? OR 
        teacher_name LIKE ? OR 
        student_name LIKE ? OR 
        pc_number LIKE ?
    )";
    $params = array_merge($params, array_fill(0, 8, "%$search%"));
}

// 📅 Filter logic
if ($selectedYear !== '') {
    $sql .= " AND YEAR(ticket_date) = ?";
    $params[] = $selectedYear;
}

if ($selectedMonth !== '') {
    $sql .= " AND MONTH(ticket_date) = ?";
    $params[] = $selectedMonth;
}

if ($selectedDay !== '') {
    $sql .= " AND DAY(ticket_date) = ?";
    $params[] = $selectedDay;
}

$sql .= " ORDER BY ticket_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets</title>
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
        <a href="student_dashboard.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🏠 Dashboard</a>
        <a href="tickets.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">📋 My Tickets</a>
        <a href="submit_ticket.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">➕ Submit Ticket</a>
        <a href="incidents.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🚨 Incident Reports</a>
        <a href="report_incident.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">➕ Report Incident</a>
        <a href="logout.php" class="block py-2 px-3 rounded bg-red-600 hover:bg-red-700 transition-colors duration-200">🚪 Logout</a>
    </nav>
</aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-10">
        <div class="max-w-6xl mx-auto bg-white shadow-lg rounded-xl p-8">
            <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">📋 My Tickets</h2>
    <!-- 🔍 Search Bar -->
<form method="GET" class="mb-4 flex items-center gap-2">
    <input 
        type="text" 
        name="search" 
        placeholder="Search tickets..." 
        value="<?= htmlspecialchars($search) ?>" 
        class="border p-2 rounded w-64"
    >
    <button 
        type="submit" 
        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
    >
        Search
    </button>

    <?php if ($search): ?>
        <a href="tickets.php" class="text-blue-600 underline px-2 py-1">Clear</a>
    <?php endif; ?>
</form>

<!-- 📅 Filter by Date -->
<form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
    <div>
        <label class="mr-1 font-semibold">Year:</label>
        <input 
            type="number" 
            name="year" 
            value="<?= htmlspecialchars($selectedYear) ?>" 
            placeholder="e.g. 2025" 
            class="border px-2 py-1 rounded w-24"
        >
    </div>

    <div>
        <label class="mr-1 font-semibold">Month:</label>
        <select name="month" class="border px-2 py-1 rounded">
            <option value="">All</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <div>
        <label class="mr-1 font-semibold">Day:</label>
        <select name="day" class="border px-2 py-1 rounded">
            <option value="">All</option>
            <?php for ($d = 1; $d <= 31; $d++): ?>
                <option value="<?= $d ?>" <?= ($selectedDay == $d) ? 'selected' : '' ?>><?= $d ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <button 
        type="submit" 
        class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700"
    >
        Filter
    </button>

    <?php if ($selectedYear || $selectedMonth || $selectedDay): ?>
        <a href="tickets.php" class="text-blue-600 underline px-2 py-1">Clear</a>
    <?php endif; ?>
</form>
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-green-500">
                        <tr>
                            <th class="border px-4 py-2">ID</th>
                            <th class="border px-4 py-2">Lab Room</th>
                            <th class="border px-4 py-2">PC Number</th>
                            <th class="border px-4 py-2">Teacher</th>
                            <th class="border px-4 py-2">Working Student</th>
                            <th class="border px-4 py-2">Issue</th>
                            <th class="border px-4 py-2">Details</th>
                            <th class="border px-4 py-2">Urgency</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="px-4 py-2">Submitted On</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php
// Pagination setup
$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $itemsPerPage;

// Filters from query string
$selectedYear  = $_GET['year']  ?? '';
$selectedMonth = $_GET['month'] ?? '';
$selectedDay   = $_GET['day']   ?? '';

// Base query setup
$where = "WHERE created_by = ?";
$params = [$username];

// ✅ Filter by ticket_date (you can change to created_at if needed)
if ($selectedYear !== '') {
    $where .= " AND YEAR(ticket_date) = ?";
    $params[] = $selectedYear;
}
if ($selectedMonth !== '') {
    $where .= " AND MONTH(ticket_date) = ?";
    $params[] = $selectedMonth;
}
if ($selectedDay !== '') {
    $where .= " AND DAY(ticket_date) = ?";
    $params[] = $selectedDay;
}

// Fetch filtered tickets
$stmt = $pdo->prepare("
    SELECT * FROM tickets 
    $where 
    ORDER BY ticket_date DESC 
    LIMIT $itemsPerPage OFFSET $offset
");
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tickets $where");
$countStmt->execute($params);
$totalTickets = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalTickets / $itemsPerPage));
$counter = $offset + 1;


                        if ($tickets):
                            foreach ($tickets as $t): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="border px-4 py-2 font-bold text-green-600"><?= $counter++ ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($t['lab_room']) ?></td>
                                    <td class="border px-4 py-2"><?= $t['pc_number'] ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($t['teacher_name']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($t['student_name']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($t['problem']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($t['comment']) ?></td>                                  
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
                                    <td class="border px-4 py-2"><?= $t['status'] ?></td> 
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
                <!-- Pagination -->
<div class="mt-4 flex justify-center gap-2">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&year=<?= urlencode($selectedYear) ?>&month=<?= urlencode($selectedMonth) ?>&day=<?= urlencode($selectedDay) ?>" 
           class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
    <?php endif; ?>

    <span class="px-3 py-1 bg-green-100 rounded">Page <?= $page ?> of <?= $totalPages ?></span>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&year=<?= urlencode($selectedYear) ?>&month=<?= urlencode($selectedMonth) ?>&day=<?= urlencode($selectedDay) ?>" 
           class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
    <?php endif; ?>
</div>  
            </div>
        </div>
    </main>
</body>

</html>
