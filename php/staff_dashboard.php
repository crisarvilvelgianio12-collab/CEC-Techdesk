<?php
require_once __DIR__ . '/config.php';
require_login();
if (!is_staff()) { http_response_code(403); exit('Forbidden'); }

// Ticket counts
$ticketCounts = $pdo->query("
    SELECT 
        SUM(status='Open') AS open_tickets,
        SUM(status='In Progress') AS inprogress_tickets,
        SUM(status='Closed') AS closed_tickets
    FROM tickets
")->fetch(PDO::FETCH_ASSOC);

$open = $ticketCounts['open_tickets'] ?? 0;
$inprogress = $ticketCounts['inprogress_tickets'] ?? 0;
$closed = $ticketCounts['closed_tickets'] ?? 0;

// Incidents last 7 days
$incidents = $pdo->query("
    SELECT DATE(reported_at) AS day, COUNT(*) AS total
    FROM incidents
    GROUP BY day
    ORDER BY day DESC
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);

$incidentLabels = json_encode(array_reverse(array_column($incidents, 'day')));
$incidentData   = json_encode(array_reverse(array_column($incidents, 'total')));
$totalIncidents = array_sum(array_column($incidents, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Match student dashboard chart sizes */
    .chart-box, .chart-box-lg {
        width: 100%;
        max-width: 400px;
        height: 300px;
    }
</style>
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


<main class="flex-1 ml-64 p-10 space-y-8">
    <div class="bg-white shadow-lg rounded-xl p-8">
        <h2 class="text-3xl font-bold mb-2 text-gray-800">👋 Welcome, Staff</h2>
        <p class="text-gray-700">Overview of Tickets and Incidents.</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-blue-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">Open</h3>
            <p class="text-3xl mt-2"><?= $open ?></p>
        </div>
        <div class="bg-yellow-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">In Progress</h3>
            <p class="text-3xl mt-2"><?= $inprogress ?></p>
        </div>
        <div class="bg-green-600 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">Closed</h3>
            <p class="text-3xl mt-2"><?= $closed ?></p>
        </div>
        <div class="bg-red-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold">Incidents</h3>
            <p class="text-3xl mt-2"><?= $totalIncidents ?></p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tickets Doughnut Chart -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold mb-4">Ticket Overview</h3>
            <div class="chart-box">
                <canvas id="ticketsChart"></canvas>
            </div>
        </div>

        <!-- Incidents Bar Chart -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold mb-4">Incidents Trend</h3>
            <div class="chart-box-lg">
                <canvas id="incidentsChart"></canvas>
            </div>
        </div>
    </div>
</main>

<script>
// Doughnut chart for tickets
new Chart(document.getElementById('ticketsChart'), {
    type: 'doughnut',
    data: {
        labels: ['Open', 'In Progress', 'Closed'],
        datasets: [{
            data: [<?= $open ?>, <?= $inprogress ?>, <?= $closed ?>],
            backgroundColor: ['#0992dc','#f4a261','#048236'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Bar chart for incidents
new Chart(document.getElementById('incidentsChart'), {
    type: 'bar',
    data: {
        labels: <?= $incidentLabels ?>,
        datasets: [{
            label: 'Incidents',
            data: <?= $incidentData ?>,
            backgroundColor: '#ef4444',
            borderColor: '#8eb921',
            borderWidth: 1
        }] 
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
