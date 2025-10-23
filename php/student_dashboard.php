<?php
require_once __DIR__ . '/config.php';
require_login();

$username = $_SESSION['username'] ?? '';

// Ticket counts
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) AS open_tickets,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS inprogress_tickets,
        SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) AS closed_tickets
    FROM tickets
    WHERE created_by = ?
");
$stmt->execute([$username]);
$ticketCounts = $stmt->fetch(PDO::FETCH_ASSOC);

$open = $ticketCounts['open_tickets'] ?? 0;
$inprogress = $ticketCounts['inprogress_tickets'] ?? 0;
$closed = $ticketCounts['closed_tickets'] ?? 0;

// Incidents last 7 days
$stmt = $pdo->prepare("
    SELECT DATE(reported_at) AS day, COUNT(*) AS total
    FROM incidents
    WHERE reported_by = ?
    GROUP BY day 
    ORDER BY day DESC
    LIMIT 7
");
$stmt->execute([$username]);
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$incidentLabels = json_encode(array_reverse(array_column($incidents, 'day')));
$incidentData   = json_encode(array_reverse(array_column($incidents, 'total')));
$totalIncidents = array_sum(array_column($incidents, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student TechDesk Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.chart-box, .chart-box-lg { width: 100%; max-width: 400px; height: 300px; }
</style>
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




<main class="flex-1 ml-64 p-10 space-y-8">

    <!-- Welcome -->
    <div class="bg-white shadow-lg rounded-xl p-8">
        <h1 class="text-3xl font-bold mb-2 text-gray-800">Welcome, <?= htmlspecialchars($username) ?> 👋</h1>
        <p class="text-gray-700">Overview of your Tickets and Incidents</p>
    </div>

    <!-- Admin Availability -->
    <div id="adminAvailabilityBox" class="bg-white shadow-lg rounded-xl p-4">
    <h3 class="font-bold mb-2">Admin Availability</h3>
    <button id="adminAvailabilityText" class="bg-green-100 text-green-800 font-bold px-4 py-2 rounded-lg hover:bg-green-600 hover:text-white transition">
        Loading...
    </button>
</div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-blue-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">Open Tickets</h3>
            <p class="text-3xl mt-2"><?= $open ?></p>
        </div>
        <div class="bg-yellow-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">In Progress</h3>
            <p class="text-3xl mt-2"><?= $inprogress ?></p>
        </div>
        <div class="bg-green-600 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">Closed Tickets</h3>
            <p class="text-3xl mt-2"><?= $closed ?></p>
        </div>
        <div class="bg-red-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">Incidents</h3>
            <p class="text-3xl mt-2"><?= $totalIncidents ?></p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold mb-4">Ticket Overview</h3>
            <div class="chart-box"><canvas id="ticketsChart"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold mb-4">Incidents Trend</h3>
            <div class="chart-box-lg"><canvas id="incidentsChart"></canvas></div>
        </div>
    </div>
</main>

<script>
// Charts
new Chart(document.getElementById('ticketsChart'), {
    type: 'doughnut',
    data: { labels:['Open','In Progress','Closed'], datasets:[{ data:[<?= $open ?>,<?= $inprogress ?>,<?= $closed ?>], backgroundColor:['#0992dc','#f4a261','#048236'], borderWidth:0 }] },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
});
new Chart(document.getElementById('incidentsChart'), {
    type:'bar',
    data:{ labels:<?= $incidentLabels ?>, datasets:[{ label:'Incidents', data:<?= $incidentData ?>, backgroundColor:'#ef4444', borderColor:'#8eb921', borderWidth:1 }] },
    options:{ responsive:true, scales:{ y:{ beginAtZero:true } } }
});

// Live admin availability fetch
function fetchAdminAvailability() {
    fetch('get_admin_availability.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('adminAvailabilityText').textContent = data.availability;
        });
}

// Initial fetch + every 5 seconds
fetchAdminAvailability();
setInterval(fetchAdminAvailability, 5000);
</script>
</body>
</html>
