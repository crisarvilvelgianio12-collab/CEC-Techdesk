<?php
require_once __DIR__ . '/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incident_type = trim($_POST['incident_type']);
    $incident_details = trim($_POST['incident_details']);
    $student_name = trim($_POST['student_name']);
    $reported_by = $_SESSION['username'] ?? 'Unknown';

    if ($incident_type && $incident_details && $student_name) {
        $stmt = $pdo->prepare("
            INSERT INTO incidents (incident_type, incident_details, student_name, reported_by, reported_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$incident_type, $incident_details, $student_name, $reported_by]);

        header('Location: incidents.php?success=1');
        exit;
    } else {
        echo "<p class='text-red-600 font-semibold'>Please fill in all required fields.</p>";
    }
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Incident</title>
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
        <div class="bg-white shadow-lg rounded-xl p-8 max-w-3xl mx-auto">
            <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">➕ Report a New Incident</h2>

            <form method="post" class="space-y-6">
    <!-- Student Name -->
    <div>
        <label class="block font-medium text-gray-700 mb-1">Working Student's Name</label>
        <input 
            type="text" 
            name="student_name" 
            required 
            placeholder="Enter your full name" 
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-red-400 focus:outline-none"
        >
    </div>

    <!-- Incident Type -->
    <div>
        <label class="block font-medium text-gray-700 mb-1">Incident Type</label>
        <input 
            type="text" 
            name="incident_type" 
            required 
            placeholder="e.g., Network Issue" 
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-red-400 focus:outline-none"
        >
    </div>

    <!-- Incident Details -->
    <div>
        <label class="block font-medium text-gray-700 mb-1">Details</label>
        <textarea 
            name="incident_details" 
            required 
            placeholder="Describe what happened..." 
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-red-400 focus:outline-none" 
            rows="5"
        ></textarea>
    </div>

    <!-- Submit Button -->
    <div>
        <button 
            type="submit" 
            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition duration-200"
        >
            Report Incident
        </button>
    </div>
</form>
        </div>
    </main>
</body>
</html>
