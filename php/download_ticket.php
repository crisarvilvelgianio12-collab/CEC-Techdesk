<?php
require_once __DIR__ . '/config.php';
require_login();

// ✅ Allow both Admin and Staff
if (!(is_admin() || is_staff())) {
    http_response_code(403);
    exit('Forbidden');
}

// Path to FPDF library
$fpdfPath = __DIR__ . '/../vendor/fpdf/fpdf.php';
if (!file_exists($fpdfPath)) {
    exit('Error: FPDF library not found. Please download it from http://www.fpdf.org/ and place it in vendor/fpdf/');
}
require_once $fpdfPath;

// Get ticket ID
$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    exit('Invalid ticket ID.');
}

// Fetch ticket
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    exit('Ticket not found.');
}

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Ticket #{$ticket['id']} Details", 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
foreach ($ticket as $key => $value) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 8, ucfirst(str_replace('_', ' ', $key)) . ':', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, $value);
}

$pdf->Output('D', "Ticket_{$ticket['id']}.pdf"); // Force download
