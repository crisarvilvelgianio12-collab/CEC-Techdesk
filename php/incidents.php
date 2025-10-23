<?php
require_once __DIR__ . '/config.php';
require_login();

$username = $_SESSION['username'] ?? '';

// --- Filters ---
$selectedYear = $_GET['year'] ?? '';
$selectedMonth = $_GET['month'] ?? '';
$selectedDay = $_GET['day'] ?? '';
$search = trim($_GET['search'] ?? '');

// --- Pagination Setup ---
$recordsPerPage = 10;
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// --- Base Query ---
$countSql = "SELECT COUNT(*) FROM incidents WHERE reported_by = ?";
$sql = "SELECT * FROM incidents WHERE reported_by = ?";
$params = [$username];
$countParams = [$username];

// 🔍 Search Filter
if ($search !== '') {
    $countSql .= " AND (incident_type LIKE ? OR incident_details LIKE ?)";
    $sql .= " AND (incident_type LIKE ? OR incident_details LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%"]);
    $countParams = array_merge($countParams, ["%$search%", "%$search%"]);
}

// 📅 Date Filters
if ($selectedYear !== '') {
    $countSql .= " AND YEAR(reported_at) = ?";
    $sql .= " AND YEAR(reported_at) = ?";
    $params[] = $selectedYear;
    $countParams[] = $selectedYear;
}

if ($selectedMonth !== '') {
    $countSql .= " AND MONTH(reported_at) = ?";
    $sql .= " AND MONTH(reported_at) = ?";
    $params[] = $selectedMonth;
    $countParams[] = $selectedMonth;
}

if ($selectedDay !== '') {
    $countSql .= " AND DAY(reported_at) = ?";
    $sql .= " AND DAY(reported_at) = ?";
    $params[] = $selectedDay;
    $countParams[] = $selectedDay;
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

$sql .= " ORDER BY reported_at DESC LIMIT $recordsPerPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incident Reports</title>
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
            <a href="incidents.php" class="block py-2 px-3 rounded bg-green-700 transition-colors duration-200">🚨 Incident Reports</a>
            <a href="report_incident.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">➕ Report Incident</a>
            <a href="logout.php" class="block py-2 px-3 rounded bg-red-600 hover:bg-red-700 transition-colors duration-200">🚪 Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-10">
        <div class="bg-white shadow-lg rounded-xl p-8 max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">🚨 My Incident Reports</h2>

            <!-- 🔍 Search Bar -->
            <form method="GET" class="mb-4 flex items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search incidents..." 
                    value="<?= htmlspecialchars($search) ?>" 
                    class="border p-2 rounded w-64"
                >
                <button 
                    type="submit" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                >
                    Search
                </button>

                <?php if ($search || $selectedYear || $selectedMonth || $selectedDay): ?>
                    <a href="incidents.php" class="text-blue-600 underline px-2 py-1">Clear</a>
                <?php endif; ?>
            </form>

            <!-- 📅 Filter Section -->
            <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
                <div>
                    <label class="mr-1 font-semibold">Year:</label>
                    <input type="number" name="year" value="<?= htmlspecialchars($selectedYear) ?>" placeholder="e.g. 2025" class="border px-2 py-1 rounded w-24">
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

                <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded">Filter</button>
            </form>

            <!-- 📋 Incidents Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-green-500 text-white">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Type</th>
                            <th class="px-4 py-2">Details</th>
                            <th class="px-4 py-2">Reported On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($incidents) {
                            $rowNumber = $offset + 1;
                            foreach ($incidents as $i) {
                                $reportedAt = !empty($i['reported_at']) 
                                    ? date('F j, Y - h:i A', strtotime($i['reported_at']))
                                    : 'N/A';

                                echo "<tr class='border-b hover:bg-gray-50'>
                                    <td class='px-4 py-2'>{$rowNumber}</td>
                                    <td class='px-4 py-2'>".htmlspecialchars($i['incident_type'])."</td>
                                    <td class='px-4 py-2'>".htmlspecialchars($i['incident_details'])."</td>
                                    <td class='px-4 py-2'>{$reportedAt}</td>
                                </tr>";
                                $rowNumber++;
                            }
                        } else {
                            echo "<tr><td colspan='4' class='px-4 py-2 text-center text-gray-500'>No incident reports submitted yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex justify-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&year=<?= $selectedYear ?>&month=<?= $selectedMonth ?>&day=<?= $selectedDay ?>" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Prev</a>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&year=<?= $selectedYear ?>&month=<?= $selectedMonth ?>&day=<?= $selectedDay ?>" 
                           class="px-3 py-1 rounded <?= $p == $page ? 'bg-green-600 text-white' : 'bg-gray-300 hover:bg-gray-400' ?>">
                           <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&year=<?= $selectedYear ?>&month=<?= $selectedMonth ?>&day=<?= $selectedDay ?>" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
