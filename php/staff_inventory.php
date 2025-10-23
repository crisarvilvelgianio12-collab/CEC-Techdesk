<?php
require_once __DIR__ . '/config.php';
require_login();
if (!is_staff()) { http_response_code(403); exit('Forbidden'); }

$labs = ["Lab 1","Lab 2","Lab 3","Lab 4","Lab 5","Lab 6","OCL"];
$lab_counts = [];
$total = 0;

foreach ($labs as $lab) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE lab_room = ?");
    $stmt->execute([$lab]);
    $count = $stmt->fetchColumn();
    $lab_counts[$lab] = $count;
    $total += $count;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Inventory</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

<!-- Main Content -->
<main class="flex-1 ml-64 p-10">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-gray-800">📦 Inventory Overview</h1>

        <!-- Lab Boxes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($labs as $lab): ?>
                <div class="bg-white p-8 rounded-xl shadow text-center hover:shadow-2xl transition cursor-pointer lab-box" data-lab="<?= htmlspecialchars($lab) ?>">
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($lab) ?></h3>
                    <p class="text-3xl font-bold text-green-700"><?= $lab_counts[$lab] ?></p>
                </div>
            <?php endforeach; ?>
            <!-- Total -->
            <div class="bg-green-100 p-8 rounded-xl shadow text-center">
                <h3 class="text-lg font-semibold mb-2">TOTAL</h3>
                <p class="text-3xl font-bold text-green-700"><?= $total ?></p>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-xl shadow-lg w-11/12 md:w-3/4 max-h-[80vh] overflow-y-auto p-6 relative">
            <button id="closeModal" class="absolute top-2 right-4 text-gray-700 font-bold text-xl">&times;</button>
            <h2 id="modalTitle" class="text-2xl font-bold mb-4"></h2>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-2 mb-4">
                <button id="printBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">🖨 Print</button>
                <button id="downloadBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">⬇ Download CSV</button>
            </div>

            <table class="w-full table-auto border-collapse" id="modalTable">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">ID</th>
                        <th class="border px-4 py-2">Comment</th>
                        <th class="border px-4 py-2">Problem</th>
                        <th class="border px-4 py-2">Urgency</th>
                        <th class="border px-4 py-2">Teacher</th>
                        <th class="border px-4 py-2">Student</th>
                        <th class="border px-4 py-2">Date</th>
                    </tr>
                </thead>
                <tbody id="modalBody"></tbody>
            </table>
        </div>
    </div>

<script>
const modal = document.getElementById('modal');
const modalTitle = document.getElementById('modalTitle');
const modalBody = document.getElementById('modalBody');
const closeModal = document.getElementById('closeModal');
const printBtn = document.getElementById('printBtn');
const downloadBtn = document.getElementById('downloadBtn');

let currentLab = '';
let currentData = [];
let currentPage = 1;
let totalPages = 1;
let totalTickets = 0;
const limit = 10;

document.querySelectorAll('.lab-box').forEach(box => {
    box.addEventListener('click', () => {
        currentLab = box.dataset.lab;
        currentPage = 1;
        loadTickets();
    });
});

function loadTickets(page = 1) {
    modalTitle.textContent = "Tickets for " + currentLab;
    modalBody.innerHTML = "<tr><td colspan='7' class='text-center py-4'>Loading...</td></tr>";
    modal.classList.remove('hidden');

    fetch(`fetch_lab_tickets.php?lab=${encodeURIComponent(currentLab)}&page=${page}`)
    .then(res => res.json())
    .then(data => {
        currentData = data.tickets;
        totalPages = data.totalPages || 1;
        currentPage = data.currentPage || 1;
        totalTickets = data.totalTickets || 0;

        if (currentData.length === 0) {
            modalBody.innerHTML = "<tr><td colspan='7' class='text-center py-4'>No tickets found</td></tr>";
        } else {
            let start = (currentPage - 1) * limit + 1;
            modalBody.innerHTML = currentData.map((t, i) => `
                <tr>
                    <td class="border px-4 py-2 font-semibold">${start + i}</td>
                    <td class="border px-4 py-2">${t.teacher_name}</td>
                    <td class="border px-4 py-2">${t.student_name}</td>
                    <td class="border px-4 py-2">${t.problem}</td>
                    <td class="border px-4 py-2">${t.comment}</td>
                    <td class="border px-4 py-2">${t.urgency}</td>
                    <td class="border px-4 py-2">${t.ticket_date}</td>
                </tr>
            `).join('');
        }

        renderFooter();
    });
}

function renderFooter() {
    const existingFooter = document.getElementById('modalFooter');
    if (existingFooter) existingFooter.remove();

    const footer = document.createElement('div');
    footer.id = 'modalFooter';
    footer.className = "flex flex-col items-center mt-4 space-y-2";

    // ✅ Result count text
    const showingText = document.createElement('div');
    const start = totalTickets === 0 ? 0 : (currentPage - 1) * limit + 1;
    const end = Math.min(currentPage * limit, totalTickets);
    showingText.textContent = `Showing ${start}–${end} of ${totalTickets} results`;
    showingText.className = "text-gray-700 font-medium";

    // ✅ Pagination buttons
    const pagination = document.createElement('div');
    pagination.className = "flex justify-center items-center space-x-3";

    if (totalPages > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = "← Prev";
        prevBtn.className = "bg-gray-300 px-3 py-1 rounded hover:bg-gray-400";
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => loadTickets(currentPage - 1);

        const nextBtn = document.createElement('button');
        nextBtn.textContent = "Next →";
        nextBtn.className = "bg-gray-300 px-3 py-1 rounded hover:bg-gray-400";
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => loadTickets(currentPage + 1);

        const pageInfo = document.createElement('span');
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        pageInfo.className = "text-gray-700 font-semibold";

        pagination.append(prevBtn, pageInfo, nextBtn);
    }

    footer.append(showingText, pagination);
    modal.querySelector('.bg-white').appendChild(footer);
}

// Close modal
closeModal.addEventListener('click', () => modal.classList.add('hidden'));
modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

// Print
printBtn.addEventListener('click', () => {
    if (currentData.length === 0) return;
    let printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>' + modalTitle.textContent + '</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2 class="text-2xl font-bold mb-4">' + modalTitle.textContent + '</h2>');
    printWindow.document.write(document.getElementById('modalTable').outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
});

// Download CSV
downloadBtn.addEventListener('click', () => {
    if (currentData.length === 0) return;
    const csvRows = [];
    const headers = ['#','Teacher','Student','Problem','Comment','Urgency','Date'];
    csvRows.push(headers.join(','));
    currentData.forEach((t, i) => {
        const values = [((currentPage - 1) * limit) + i + 1, t.teacher_name, t.student_name, t.problem, t.comment, t.urgency, t.ticket_date];
        csvRows.push(values.map(v => `"${v}"`).join(','));
    });
    const csvData = csvRows.join('\n');
    const blob = new Blob([csvData], {type: 'text/csv'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = modalTitle.textContent.replace(/\s+/g, '_') + '_page' + currentPage + '.csv';
    a.click();
});
</script>
</main>
</body>
</html>
