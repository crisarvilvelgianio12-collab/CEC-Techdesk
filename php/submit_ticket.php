<?php
require_once __DIR__ . '/config.php';
require_login();
$username = $_SESSION['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['problem'], $_POST['urgency'])) {
    $stmt = $pdo->prepare("INSERT INTO tickets (comment, problem, urgency, status, created_by, lab_room, teacher_name, student_name, pc_number, ticket_date, created_at) VALUES (?, ?, ?, 'Open', ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_POST['comment'],
        $_POST['problem'],
        $_POST['urgency'],
        $username,
        $_POST['lab_room'],
        $_POST['teacher_name'],
        $_POST['student_name'],
        $_POST['pc_number'],
        $_POST['ticket_date']
    ]);
    header("Location: tickets.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Ticket</title>
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
        <div class="bg-white shadow-lg rounded-xl p-10 max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">➕ Submit a New Ticket</h2>

            <form method="post" class="space-y-6">

                <!-- Lab Room & PC Number -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Computer Lab Room</label>
                        <select name="lab_room" required class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none">
                            <option value="">Select Room</option>
                            <option value="CL 1">CL 1</option>
                            <option value="CL 2">CL 2</option>
                            <option value="CL 3">CL 3</option>
                            <option value="CL 4">CL 4</option>
                            <option value="CL 5">CL 5</option>
                            <option value="CL 6">CL 6</option>
                            <option value="OCL">OCL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">PC Number</label>
                        <input type="number" name="pc_number" min="1" required class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none">
                    </div>
                </div>

                <!-- Teacher & Student Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Teacher's Name</label>
                        <input type="text" name="teacher_name" required class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Working Student's Name</label>
                        <input type="text" name="student_name" required class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none">
                    </div>
                </div>

                <!-- Problem -->
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Issue</label>
                    <textarea name="problem" required class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none" rows="4"></textarea>
                </div>

                <!-- Urgency Level -->
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Urgency</label>
                    <select name="urgency" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <!-- Comment -->
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Details : (Include: Who use the PC?)</label>
                    <input type="text" name="comment" required class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none">
                </div>

                <!-- Date -->
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="ticket_date" required class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-400 focus:outline-none">
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition duration-200">Submit Ticket</button>
                </div>

            </form>
        </div>
    </main>
</body>
</html>
