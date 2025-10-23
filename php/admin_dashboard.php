<?php
require_once __DIR__ . '/config.php';
require_login();

if (!is_admin()) {
    http_response_code(403);
    exit('Forbidden');
}

$username = $_SESSION['username'] ?? '';

// Get current admin availability
$stmt = $pdo->prepare("SELECT availability FROM users WHERE username = ?");
$stmt->execute([$username]);
$currentAvailability = $stmt->fetchColumn() ?: 'Available';

// Ticket counts
$ticketCounts = $pdo->query("
    SELECT 
        SUM(CASE WHEN status='Open' THEN 1 ELSE 0 END) AS open_tickets,
        SUM(CASE WHEN status='In Progress' THEN 1 ELSE 0 END) AS inprogress_tickets,
        SUM(CASE WHEN status='Closed' THEN 1 ELSE 0 END) AS closed_tickets
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
<title>Admin TechDesk Dashboard</title>
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
        <a href="admin_dashboard.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🏠 Dashboard</a>
        <a href="admin_tickets.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">📋 Manage Tickets</a>
        <a href="admin_incidents.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">🚨 View Incidents</a>
        <a href="admin_inventory.php" class="block py-2 px-3 rounded hover:bg-green-600 transition-colors duration-200">📦 Inventory</a>
        <a href="logout.php" class="block py-2 px-3 rounded bg-red-600 hover:bg-red-700 transition-colors duration-200">🚪 Logout</a>
    </nav>
</aside>


<main class="flex-1 ml-64 p-10 space-y-8">

    <!-- Welcome + Availability -->
    <div class="bg-white shadow-lg rounded-xl p-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Welcome, <?= htmlspecialchars($username) ?> 👋</h1>
            <p class="text-gray-700">Overview of Tickets and Incidents</p>
        </div>
        <div class="relative">
            <button id="availabilityBtn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded inline-flex items-center">
                <span id="currentStatus"><?= htmlspecialchars($currentAvailability) ?></span>
                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="availabilityMenu" class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg hidden z-10">
                <div class="py-2">
                    <?php
                    $statuses = ['Available','On Break','At Lunch','In Training/Teaching','Working on an Issue','Offline'];
                    foreach ($statuses as $status) {
                        echo '<a href="#" class="block px-4 py-2 hover:bg-gray-100" data-status="'.$status.'">'.$status.'</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
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

// Availability dropdown & AJAX save
const btn = document.getElementById('availabilityBtn');
const menu = document.getElementById('availabilityMenu');
const currentStatus = document.getElementById('currentStatus');

btn.addEventListener('click', () => menu.classList.toggle('hidden'));

menu.querySelectorAll('a').forEach(item => {
    item.addEventListener('click', e => {
        e.preventDefault();
        const status = item.dataset.status;
        currentStatus.textContent = status;
        menu.classList.add('hidden');

        // Save via AJAX
        fetch('update_availability.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/x-www-form-urlencoded' },
            body: 'availability=' + encodeURIComponent(status)
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                // Show small "Updated" tooltip
                const tooltip = document.createElement('span');
                tooltip.textContent = 'Updated ✅';
                tooltip.className = 'ml-2 text-green-600 font-bold';
                btn.appendChild(tooltip);
                setTimeout(() => tooltip.remove(), 2000);
            }
        });
    });
});
document.addEventListener('click', e => { if(!btn.contains(e.target) && !menu.contains(e.target)) menu.classList.add('hidden'); });
</script>
</body>
</html>
