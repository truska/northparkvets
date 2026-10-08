<?php
ob_start(); // Start output buffering

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');

    // Get parameters
        // Set default values for fromDate and toDate
        $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : date('Y-m-01'); // First day of current month
        $toDate = isset($_GET['toDate']) ? $_GET['toDate'] : date('Y-m-t'); // Last day of current month
        $vetNames = isset($_GET['vetName']) ? $_GET['vetName'] : []; // Array for multiple vet selection

            // Ensure vetName is retrieved as an array
          //  $selectedVets = isset($_GET['vetName']) ? (array) $_GET['vetName'] : ['ALL'];
            $selectedVets = isset($_GET['vetName']) ? (is_array($_GET['vetName']) ? $_GET['vetName'] : explode(',', $_GET['vetName'])) : ['ALL'];

            error_log("DEBUG selectedVets: " . print_r($selectedVets, true));


            // Get vet names from database
            $vetNames = getVetNamesByIds($selectedVets);
    
            if (!empty($vetNames)) {
                if (count($vetNames) > 1) {
                    // Replace the last comma with " & "
                    $vetList = implode(', ', array_slice($vetNames, 0, -1)) . ' & ' . end($vetNames) ;
                } 
                else 
                {
                    // Only one vet, no need for comma
                    $vetList = $vetNames[0] ;
                }
            } 
            else 
            {
                $vetList = "No vets selected"; // Fallback message
            }

// Fetch data
$data = fetchBillingDataByVetDateRange($fromDate, $toDate, $selectedVets);

// Initialize TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('North Park Vets');
$pdf->SetTitle("Vet Billing Report - ". date('d m y', strtotime($fromDate)). " to ".date('d m y', strtotime($toDate)));
//$pdf->SetHeaderData('', '', "Vet Billing Report - " . date('F Y', strtotime("$year-$month-01")), '');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(15, 12, 15);
//$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Title (slightly larger)
$pdf->SetFont('helvetica', 'B', 12);
//pdf->Cell(0, 8, 'Vet Billing Report - ' . date('F Y', strtotime("$year-$month-01")), 0, 1, 'C');
$pdf->Cell(0, 8, 'Vet Billing Report - ' . date('d F Y', strtotime($fromDate)). ' - ' . date('d F Y', strtotime($toDate)), 0, 1, 'C');

$vetNamesWithIds = [];
foreach ($selectedVets as $vetId) {
    $vetNameResult = mysqli_query($conn, "SELECT name FROM npe_vet WHERE id = " . intval($vetId));
    if ($row = mysqli_fetch_assoc($vetNameResult)) {
        $vetNamesWithIds[] = $row['name'] . " ($vetId)";
    }
}

if (!empty($vetNamesWithIds)) {
    if (count($vetNamesWithIds) > 1) {
        $vetNamesString = implode(', ', array_slice($vetNamesWithIds, 0, -1)) . ' and ' . end($vetNamesWithIds);
    } else {
        $vetNamesString = $vetNamesWithIds[0];
    }

    // Output as second header line in PDF
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Selected Vets: ' . $vetNamesString, 0, 1, 'C');
    $pdf->Ln(2);
}

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


if (empty($data)) {
    error_log("PDF Report: No data returned for vets: " . print_r($selectedVets, true));
}

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
    $html .= '<tr style="background-color: #ffffff; height:10px;"><td colspan="8"></td></tr>';

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
$pdf->Output("Vet_Billing_Report_{$fromDate}_{$toDate}.pdf", 'I');
exit();
?>
