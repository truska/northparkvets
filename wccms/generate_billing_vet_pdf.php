<?php
ob_start(); // Start output buffering

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');

// Get parameters
$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

// Fetch data
$data = fetchBillingDataByVet($month, $year);

// Initialize TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle("Vet Billing Report - $month/$year");
//$pdf->SetHeaderData('', '', "Vet Billing Report - " . date('F Y', strtotime("$year-$month-01")), '');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(15, 12, 15);
//$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Title (slightly larger)
$pdf->SetFont('helvetica', 'B', 15);
$pdf->Cell(0, 8, 'Vet Billing Report - ' . date('F Y', strtotime("$year-$month-01")), 0, 1, 'C');

// Reduced space under title
$pdf->Ln(1);

// Table styling
$pdf->SetFont('helvetica', '', 10);
$html = '<table border="1" cellpadding="4" cellspacing="0">';

// Table Header
$html .= '<tr style="background-color: #f2f2f2;">
            <td align="right">OV</td>
            <td align="right">CSO</td>
            <td align="right">Travel Units</td>
            <td align="right">Travel Miles</td>
            <td align="right">Certs</td>
            <td align="right">Tanker Certs</td>
            <td align="right">SHA/SA</td>
            <td align="right">Courier</td>
          </tr>';

$periodTotals = initializeTotals();

// Vet Sections
foreach ($data as $vetName => $vetData) {
    $html .= '<tr style="background-color: #f2f2f2;">
                <td colspan="8"><strong>Vet: ' . htmlspecialchars($vetName) . '</strong></td>
              </tr>';

    // Unit Totals
    $html .= '<tr>
                <td align="right">' . $vetData['totals']['numeric']['time_ov'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['time_cso'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['travel_units'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['travel_miles'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['certs'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['tanker_cert'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['sha_sa'] . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['numeric']['courier'], 2) . '</td>
              </tr>';

    // Monetary Totals
    $html .= '<tr>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['time_ov'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['time_cso'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['travel_units'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['travel_miles'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['certs'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['tanker_cert'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['sha_sa'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['courier'], 2) . '</td>
              </tr>';

    // Total for Vet
    $html .= '<tr>
                <td align="right" colspan="8"><strong>Total for ' . htmlspecialchars($vetName) . ': £ ' . number_format(array_sum($vetData['totals']['monetary']), 2) . '</strong></td>
              </tr>';

    // Slightly reduced spacing row
    $html .= '<tr style="background-color: #ffffff; height:10px;"><td colspan="7"></td></tr>';

    accumulateTotals($vetData['totals'], $periodTotals);
}

// Period Totals
$html .= '<tr style="background-color: #f2f2f2;">
            <td colspan="8"><strong>Overall Period Totals</strong></td>
          </tr>';
$html .= '<tr>
            <td align="right">' . $periodTotals['numeric']['time_ov'] . '</td>
            <td align="right">' . $periodTotals['numeric']['time_cso'] . '</td>
            <td align="right">' . $periodTotals['numeric']['travel_units'] . '</td>
            <td align="right">' . $periodTotals['numeric']['travel_miles'] . '</td>
            <td align="right">' . $periodTotals['numeric']['certs'] . '</td>
            <td align="right">' . $periodTotals['numeric']['tanker_cert'] . '</td>
            <td align="right">' . $periodTotals['numeric']['sha_sa'] . '</td>
            <td align="right">£ ' . number_format($periodTotals['numeric']['courier'], 2) . '</td>
          </tr>';
$html .= '<tr>
            <td align="right" colspan="8"><strong>Overall Total: £ ' . number_format(array_sum($periodTotals['monetary']), 2) . '</strong></td>
          </tr>';

$html .= '</table>';

// Add HTML content to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Footer (smaller space and 2-line format)
$pdf->Ln(1);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->MultiCell(0, 2,
    "Generated on: " . date('d/m/Y H:i') . " by " . $user["username"],
    0, 'R', false);

// Output PDF
ob_end_clean();
$pdf->Output("{$year}_{$month}_Vet_Billing_Report.pdf", 'I');
exit();
?>
