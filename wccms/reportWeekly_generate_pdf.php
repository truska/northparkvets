<?php
ob_start(); // Start output buffering

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');

// Fetch and process data
include('functions.php'); // Include shared functions
$data = fetchProductData($conn);
$processedData = preprocessTotals($data);

// Create a new PDF instance
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set PDF metadata
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('North Park');
$pdf->SetTitle('Weekly Report');
$pdf->SetSubject('Weekly Report');
$pdf->SetKeywords('TCPDF, PDF, report, weekly');

// Add a page
$pdf->AddPage();

// Add a Title
$pdf->SetFont('helvetica', 'B', 14); // Bold, larger font for the title
$pdf->Cell(0, 10, 'North Park Exports Weekly Report', 0, 1, 'C');
$pdf->Ln(5); // Add some spacing after the title

// Set font for the table
$pdf->SetFont('helvetica', '', 10);

// Table Column Headers
$columnWidths = [
    'Time' => 15,
    'Depot' => 20,
    'Customer' => 30,
    'Country' => 30,
    'Destination' => 30,
    'Pallets' => 30,
    'Goods' => 30,
    'PCloud' => 20,
    'Vet' => 20,
    'CSO' => 12,
    'OV' => 12,
    'Total' => 15,
];

// Add the table headers
$pdf->SetFillColor(220, 220, 220); // Light gray for headers
$pdf->SetTextColor(0, 0, 0); // Black text
$pdf->SetFont('', 'B'); // Bold font

// Header row
foreach ($columnWidths as $header => $width) {
    $pdf->Cell($width, 7, $header, 1, 0, 'C', 1);
}
$pdf->Ln();

// Reset font for data rows
$pdf->SetFont('', '');

// Add Data Rows
$fill = false; // Alternating row colors
foreach ($processedData as $date => $dateData) {
    if ($date === 'totals') continue;

    $formatted_date = date("d/m/Y", strtotime($date));
    $day_only = date("D", strtotime($date));

    // Date Header (Level 1)
    $pdf->SetFillColor(200, 200, 255); // Light blue for Level 1
    $pdf->Cell(array_sum($columnWidths), 7, "Date: $formatted_date ($day_only)", 1, 1, 'L', 1);

    foreach ($dateData as $depot => $depotData) {
        if ($depot === 'totals') continue;

        $depot_name = $depotData['depot_name'] ?? 'Unknown Depot';

        // Depot Header (Level 2)
        $pdf->SetFillColor(220, 240, 255); // Lighter blue for Level 2
        $pdf->Cell(array_sum($columnWidths), 7, "Depot: $depot_name", 1, 1, 'L', 1);

        foreach ($depotData['rows'] as $row) {
            $expected_time = $row['expected_time'] ?? '00:00';
            $time_object = new DateTime($expected_time);

            // Data Row
            $pdf->SetFillColor($fill ? 240 : 255, 240, 240); // Alternating row colors
            $pdf->Cell($columnWidths['Time'], 7, $time_object->format('H:i'), 1, 0, 'C', $fill);
            $pdf->Cell($columnWidths['Depot'], 7, $row['depot_code'], 1, 0, 'L', $fill);
            $pdf->Cell($columnWidths['Customer'], 7, $row['customer_name'], 1, 0, 'L', $fill);
            $pdf->Cell($columnWidths['Country'], 7, $row['destination_country_name'], 1, 0, 'L', $fill);
            $pdf->Cell($columnWidths['Destination'], 7, $row['destination_customer'], 1, 0, 'L', $fill);
            $pdf->Cell($columnWidths['Pallets'], 7, "{$row['pallet_count']} x {$row['pallet_type']}", 1, 0, 'L', $fill);
            $pdf->Cell($columnWidths['Goods'], 7, $row['product_type'], 1, 0, 'L', $fill);
            $pdf->Cell($columnWidths['PCloud'], 7, $row['pcloud'], 1, 0, 'L', $fill);
            $pdf->Cell($columnWidths['Vet'], 7, $row['vet_name'], 1, 0, 'C', $fill);
            $pdf->Cell($columnWidths['CSO'], 7, $row['CSO'], 1, 0, 'C', $fill);
            $pdf->Cell($columnWidths['OV'], 7, $row['OV'], 1, 0, 'C', $fill);
            $pdf->Cell($columnWidths['Total'], 7, $row['RowTotal'], 1, 1, 'C', $fill);

            // Toggle fill for alternating colors
            $fill = !$fill;
        }
    }
}
// Add Footer Text Below Table
$pdf->Ln(5); // Add some space
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Report generated on: ' . date('d/m/Y H:i:s').' by '.$user["username"], 0, 1, 'L');

// Output the PDF
ob_end_clean(); // Clear any previous output
$pdf->Output('weekly_report.pdf', 'I'); // Inline display
?>
