<?php
ob_start(); // Start output buffering

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');

// Get parameters
$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');
$customerId = isset($_GET['c']) && $_GET['c'] !== 'all' ? intval($_GET['c']) : null;

// Fetch data
$data = fetchBillingData($month, $year, $customerId);

// Initialize TCPDF
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, "Billing Report - " . date('F Y', strtotime($_GET['y'] . '-' . $_GET['m'] . '-01')), 0, 1, 'C');
        
        // Repeating Table Header
        $this->SetFont('helvetica', 'B', 10);
        $headerHtml = '<table border="1" cellpadding="5" cellspacing="0">
                        <tr style="background-color: #f2f2f2;">
                            <td align="right"><strong>OV</strong></td>
                            <td align="right"><strong>CSO</strong></td>
                            <td align="right"><strong>Travel Units</strong></td>
                            <td align="right"><strong>Travel Miles</strong></td>
                            <td align="right"><strong>Certs</strong></td>
                            <td align="right"><strong>Tanker Certs</strong></td>
                            <td align="right"><strong>SHA/SA</strong></td>
                            <td align="right"><strong>Courier</strong></td>
                        </tr>
                    </table>';
        $this->writeHTML($headerHtml, false, false, false, false, '');
    }
}

// Use the custom class
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle("Billing Report - $month/$year");
$pdf->SetMargins(15, 30, 15); // Adjust top margin for header
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Billing Report - ' . date('F Y', strtotime("$year-$month-01")), 0, 1, 'C');

// Prepare Table Content
$pdf->SetFont('helvetica', '', 10);
$html = '<table border="1" cellpadding="5" cellspacing="0">';

// Header Row (This is included in the Header function)
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

// Spacer Row
$html .= '<tr style="background-color: #ffffff;"><td colspan="7"></td></tr>';

$periodTotals = [
    'numeric' => [
        'time_ov' => 0,
        'time_cso' => 0,
        'travel_units' => 0,
        'travel_miles' => 0,
        'certs' => 0,
        'tanker_cert' => 0,
        'sha_sa' => 0,
        'courier' => 0,
    ],
    'monetary' => [
        'time_ov' => 0,
        'time_cso' => 0,
        'travel_units' => 0,
        'travel_miles' => 0,
        'certs' => 0,
        'tanker_cert' => 0,
        'sha_sa' => 0,
        'courier' => 0,
    ],
];

// Customer Sections
foreach ($data as $customer) {
    $customerName = htmlspecialchars($customer['name'] . ' (' . $customer['code'] . ')');

    // Customer Name Row
    $html .= '<tr style="background-color: #f2f2f2;">
                <td colspan="8"><strong>' . $customerName . '</strong></td>
              </tr>';

    // Unit Totals Row
    $html .= '<tr>
                <td align="right">' . $customer['totals']['numeric']['time_ov'] . '</td>
                <td align="right">' . $customer['totals']['numeric']['time_cso'] . '</td>
                <td align="right">' . $customer['totals']['numeric']['travel_units'] . '</td>
                <td align="right">' . $customer['totals']['numeric']['travel_miles'] . '</td>
                <td align="right">' . $customer['totals']['numeric']['certs'] . '</td>
                <td align="right">' . $customer['totals']['numeric']['tanker_cert'] . '</td>
                <td align="right">' . $customer['totals']['numeric']['sha_sa'] . '</td>
                <td align="right">£ ' . number_format($customer['totals']['numeric']['courier'], 2) . '</td>
              </tr>';

    // Monetary Totals Row
    $html .= '<tr>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['time_ov'], 2) . '</td>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['time_cso'], 2) . '</td>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['travel_units'], 2) . '</td>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['travel_miles'], 2) . '</td>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['certs'], 2) . '</td>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['tanker_cert'], 2) . '</td>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['sha_sa'], 2) . '</td>
                <td align="right">£ ' . number_format($customer['totals']['monetary']['courier'], 2) . '</td>
              </tr>';

    // Total for customer
    $html .= '<tr>
                <td align="right" colspan="8"><strong>' . $customerName . ' Total: £ ' . number_format(array_sum($customer['totals']['monetary']), 2) . '</strong></td>
              </tr>';

    // Spacer Row
    $html .= '<tr style="background-color: #ffffff;"><td colspan="7"></td></tr>';

    // Accumulate Period Totals
    foreach ($customer['totals']['numeric'] as $key => $value) {
        $periodTotals['numeric'][$key] += $value;
    }
    foreach ($customer['totals']['monetary'] as $key => $value) {
        $periodTotals['monetary'][$key] += $value;
    }
}

// Period Totals Section
$html .= '<tr style="background-color: #f2f2f2;">
            <td colspan="8"><strong>Period Totals</strong></td>
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

$html .= '</table>';

// Add HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
ob_end_clean();
$pdf->Output("Billing_Report_{$month}_{$year}.pdf", 'I');
?>
