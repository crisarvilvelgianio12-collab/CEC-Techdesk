<?php
require_once __DIR__ . '/config.php';
require_login();

// Allow both admin and staff
if (!is_admin() && !is_staff()) {
    http_response_code(403);
    exit('Forbidden');
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) exit('Invalid incident ID.');

$stmt = $pdo->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->execute([$id]);
$incident = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$incident) exit('Incident not found.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Incident #<?= $incident['id'] ?> Details</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
<main class="flex-1 p-10 max-w-4xl mx-auto bg-white shadow-lg rounded-xl">
    <h2 class="text-3xl font-bold mb-4">Incident #<?= $incident['id'] ?> Details</h2>

    <div class="space-y-3">
        <p><strong>Type:</strong> <?= htmlspecialchars($incident['incident_type']) ?></p>
        <p><strong>Details:</strong> <?= htmlspecialchars($incident['incident_details']) ?></p>
        <p><strong>Reported By:</strong> <?= htmlspecialchars($incident['reported_by']) ?></p>
        <p><strong>Reported On:</strong> <?= $incident['reported_at'] ?></p>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row sm:space-x-3 space-y-3 sm:space-y-0 no-print">
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">🖨 Print</button>

        <form method="post" action="php/download_incident.php">
            <input type="hidden" name="id" value="<?= $incident['id'] ?>">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">📄 Download PDF</button>
        </form>

        <a href="<?= is_admin() ? 'admin_incidents.php' : 'staff_incidents.php' ?>" 
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-center">← Back to Incidents</a>
    </div>
</main>
</body>
</html>
