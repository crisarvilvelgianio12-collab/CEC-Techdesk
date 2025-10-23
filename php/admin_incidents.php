<?php
require_once __DIR__ . '/config.php';
require_login();
if (!is_admin()) {
    http_response_code(403);
    exit('Forbidden');
}

// Filters and search
$search = trim($_GET['q'] ?? '');
$selectedYear = $_GET['year'] ?? '';
$selectedMonth = $_GET['month'] ?? '';
$selectedDay = $_GET['day'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

// Year, Month, Day filter
if ($selectedYear !== '') {
    $where[] = "YEAR(reported_at) = ?";
    $params[] = $selectedYear;
}
if ($selectedMonth !== '') {
    $where[] = "MONTH(reported_at) = ?";
    $params[] = $selectedMonth;
}
if ($selectedDay !== '') {
    $where[] = "DAY(reported_at) = ?";
    $params[] = $selectedDay;
}

// Search filter
if ($search !== '') {
    $where[] = "(id LIKE ? OR incident_type LIKE ? OR incident_details LIKE ? OR reported_by LIKE ? OR reported_at LIKE ?)";
    $param = "%$search%";
    $params = array_merge($params, array_fill(0, 5, $param));
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// Count for pagination
$countSql = "SELECT COUNT(*) FROM incidents $whereSQL";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalIncidents = $countStmt->fetchColumn();
$totalPages = ceil($totalIncidents / $limit);

// Fetch current page incidents
$sql = "SELECT * FROM incidents $whereSQL ORDER BY reported_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Incident Reports</title>
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
        <a href="admin_dashboard.php" class="block py-2 px-3 rounded hover:bg-green-600 transition">🏠 Dashboard</a>
        <a href="admin_tickets.php" class="block py-2 px-3 rounded hover:bg-green-600 transition">📋 Manage Tickets</a>
        <a href="admin_incidents.php" class="block py-2 px-3 rounded bg-green-700 transition">🚨 View Incidents</a>
        <a href="admin_inventory.php" class="block py-2 px-3 rounded hover:bg-green-600 transition">📦 Inventory</a>
        <a href="logout.php" class="block py-2 px-3 rounded bg-red-600 hover:bg-red-700 transition">🚪 Logout</a>
    </nav>
</aside>

<!-- Main -->
<main class="flex-1 ml-64 p-10">
    <div class="max-w-6xl mx-auto bg-white shadow-lg rounded-xl p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">🚨 Incident Reports</h1>

        <!-- Filters -->
        <form method="get" class="mb-4 flex flex-wrap gap-2 justify-between items-center">
            <div class="flex-grow flex">
                <input type="text" name="q" placeholder="Search incidents..." value="<?= htmlspecialchars($search) ?>" class="border rounded p-2 w-full">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded ml-2 hover:bg-green-700">Search</button>
            </div>

            <div class="flex gap-2">
                <select name="year" class="border rounded p-2">
                    <option value="">Year</option>
                    <?php
                    $years = $pdo->query("SELECT DISTINCT YEAR(reported_at) AS y FROM incidents ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="month" class="border rounded p-2">
                    <option value="">Month</option>
                    <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $selectedMonth == $m ? 'selected' : '' ?>><?= date("F", mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>

                <select name="day" class="border rounded p-2">
                    <option value="">Day</option>
                    <?php for ($d=1; $d<=31; $d++): ?>
                        <option value="<?= $d ?>" <?= $selectedDay == $d ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">Filter</button>
                <?php if ($selectedYear || $selectedMonth || $selectedDay): ?>
                    <a href="admin_incidents.php" class="text-blue-600 underline px-2 py-1">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-green-600 text-white">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">Details</th>
                        <th class="px-4 py-2">Working Student's Name</th>

                        <!--<th class="px-4 py-2">Reported By</th>-->
                        <th class="px-4 py-2">Reported On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($incidents): ?>
                        <?php $rowNumber = $offset + 1; foreach ($incidents as $i): ?>
                        <tr class="border-b hover:bg-gray-50 cursor-pointer" onclick="window.location='incident_detail.php?id=<?= $i['id'] ?>'">
                            <td class="px-4 py-2 font-semibold"><?= $rowNumber++ ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($i['incident_type']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($i['incident_details']) ?></td>
                            <td class='px-4 py-2'><?=htmlspecialchars($i['student_name'])?></td>
                            <td class="px-4 py-2"><?= date('F j, Y - h:i A', strtotime($i['reported_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-gray-500">No incidents found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            
        <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center mt-6 space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                       class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">⬅ Prev</a>
                <?php endif; ?>

                <?php       
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                if ($start > 1) echo '<span class="px-3 py-2 text-gray-500">...</span>';
                for ($i = $start; $i <= $end; $i++):
                    if ($i == $page): ?>
                        <span class="px-3 py-2 bg-green-600 text-white rounded"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                           class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300"><?= $i ?></a>
                    <?php endif;
                endfor;
                if ($end < $totalPages) echo '<span class="px-3 py-2 text-gray-500">...</span>';
                ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                       class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Next ➡</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>

        
</main>
</body>
</html>
