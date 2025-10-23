<?php
require_once __DIR__ . '/../config.php';
require_login();
if (!is_admin()) exit('Forbidden');

$fpdfPath = __DIR__ . '/../vendor/fpdf/fpdf.php';
if (!file_exists($fpdfPath)) exit('Error: FPDF library not found.');
require_once $fpdfPath;

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) exit('Invalid incident ID.');

$stmt = $pdo->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->execute([$id]);
$incident = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$incident) exit('Incident not found.');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Incident #{$incident['id']} Details", 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
foreach ($incident as $key => $value) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 8, ucfirst(str_replace('_', ' ', $key)) . ':', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, $value);
}

$pdf->Output('D', "Incident_{$incident['id']}.pdf");
