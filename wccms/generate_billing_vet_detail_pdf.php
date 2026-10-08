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
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('North Park Vets - Export System');
$pdf->SetTitle("Vet Billing Detail Report - $month/$year");
$pdf->SetHeaderData('', '', "Vet Billing Detail Report - " . date('F Y', strtotime("$year-$month-01")), '');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(10, 20, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Title - hidden
//$pdf->SetFont('helvetica', 'N', 14);
//$pdf->Cell(0, 10, 'Vet Billing Detail Report - ' . date('F Y', strtotime("$year-$month-01")), 0, 1, 'C');

// Table Header
$tableHeader = '<tr style="background-color: #f2f2f2;">
                    <th align="left">PO</th>
                    <th align="left">Customer</th>
                    <th align="left">Date</th>
                    <th align="right">OV</th>
                    <th align="right">CSO</th>
                    <th align="right">Travel Units</th>
                    <th align="right">Travel Miles</th>
                    <th align="right">Certs</th>
                    <th align="right">Tanker Certs</th>
                    <th align="right">SHA/SA</th>
                    <th align="right">Courier</th>
                </tr>';

$html = '<table border="1" cellpadding="4" cellspacing="0" style="font-size: 9pt;">' . $tableHeader;

$periodTotals = initializeTotals();

// Vet Sections
foreach ($data as $vetName => $vetData) {
    // Vet Totals above data
    $html .= '<tr style="background-color: #ffcccb; font-weight: bold;">
                <td colspan="3">Total for ' . htmlspecialchars($vetName) . ' (Units)</td>
                <td align="right">' . $vetData['totals']['numeric']['time_ov'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['time_cso'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['travel_units'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['travel_miles'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['certs'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['tanker_cert'] . '</td>
                <td align="right">' . $vetData['totals']['numeric']['sha_sa'] . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['numeric']['courier'], 2) . '</td>
              </tr>';

    $html .= '<tr style="background-color: #ffaaaa; font-weight: bold;">
                <td colspan="3">Total for ' . htmlspecialchars($vetName) . ' (Price)</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['time_ov'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['time_cso'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['travel_units'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['travel_miles'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['certs'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['tanker_cert'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['sha_sa'], 2) . '</td>
                <td align="right">£ ' . number_format($vetData['totals']['monetary']['courier'], 2) . '</td>
              </tr>';

    foreach ($vetData['entries'] as $entry) {
        $formattedDate = date('d-m-Y', strtotime($entry['date']));
        $html .= "<tr>
                    <td>{$entry['po']}</td>
                    <td>{$entry['customer']}</td>
                    <td>{$formattedDate}</td>
                    <td align='right'>{$entry['time_ov']}</td>
                    <td align='right'>{$entry['time_cso']}</td>
                    <td align='right'>{$entry['travel_units']}</td>
                    <td align='right'>{$entry['travel_miles']}</td>
                    <td align='right'>{$entry['certs']}</td>
                    <td align='right'>{$entry['tanker_cert']}</td>
                    <td align='right'>{$entry['sha_sa']}</td>
                    <td align='right'>£ " . number_format($entry['courier'], 2) . "</td>
                  </tr>";
    }
    
    accumulateTotals($vetData['totals'], $periodTotals);
    $html .= '<tr style="background-color: #ffffff;"><td colspan="10"></td></tr>';
}

// Period Totals
$html .= '<tr style="background-color: #f2f2f2; font-weight: bold;">
            <td colspan="10"><strong>Overall Period Totals</strong></td>
          </tr>';
$html .= '<tr style="background-color: #ffcccb; font-weight: bold;">
            <td colspan="3">Overall Total (Units)</td>
            <td align="right">' . $periodTotals['numeric']['time_ov'] . '</td>
            <td align="right">' . $periodTotals['numeric']['time_cso'] . '</td>
            <td align="right">' . $periodTotals['numeric']['travel_units'] . '</td>
            <td align="right">' . $periodTotals['numeric']['travel_miles'] . '</td>
            <td align="right">' . $periodTotals['numeric']['certs'] . '</td>
            <td align="right">' . $periodTotals['numeric']['tanker_cert'] . '</td>
            <td align="right">' . $periodTotals['numeric']['sha_sa'] . '</td>
            <td align="right">£ ' . number_format($periodTotals['numeric']['courier'], 2) . '</td>
          </tr>';
$html .= '<tr style="background-color: #ffaaaa; font-weight: bold;">
            <td colspan="3">Overall Total (Price)</td>
            <td align="right">£ ' . number_format(array_sum($periodTotals['monetary']), 2) . '</td>
          </tr>';
$html .= '</table>';

// Add HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Footer
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Report generated on: ' . date('d/m/Y H:i:s') . ' by ' . $user["username"], 0, 1, 'L');

// Output the PDF
ob_end_clean();
$pdf->Output("{$year}_{$month}_Vet_Billing_Detail_Report.pdf", 'I');
?>
