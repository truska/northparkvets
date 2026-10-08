<?php
ob_start(); // Ensure this is the very first output

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');

// Get parameters
$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');
$customerId = isset($_GET['c']) && $_GET['c'] !== 'all' ? intval($_GET['c']) : null;

// Fetch data
$data = fetchBillingData($month, $year, $customerId);

$custnameheader = ($customerId < 1) ? 'ALL' : 'Filtered';

// Custom TCPDF Class to Repeat Headers
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 10, 'Billing Detail Report - ' . date('F Y'), 0, 1, 'C');

        // **Matching Column Widths for Header and Data**
        $headerTable = '
            <table border="1" cellpadding="4" cellspacing="0" width="100%">
                <tr style="background-color: #cccccc; color:#000000;">
                    <th align="left" width="10%">PO</th>
                    <th align="left" width="9%">Vet</th>
                    <th align="left" width="9%">Date</th>
                    <th align="right" width="9%">OV</th>
                    <th align="right" width="9%">CSO</th>
                    <th align="right" width="9%">Travel Units</th>
                    <th align="right" width="9%">Travel Miles</th>
                    <th align="right" width="9%">Certs</th>
                    <th align="right" width="9%">Tanker Certs</th>
                    <th align="right" width="9%">SHA/SA</th>
                    <th align="right" width="9%">Courier</th>
                </tr>
            </table>';
        $this->writeHTML($headerTable, false, false, false, false, '');
    }
}

// Initialize TCPDF (Landscape for better readability)
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(10, 30, 10); // Adjust margins for correct positioning
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Start Table Body
$html = '<table border="1" cellpadding="4" cellspacing="0" width="100%">';

// **Customer Sections**
foreach ($data as $customer) {
    $customerName = htmlspecialchars($customer['name'] . ' (' . $customer['code'] . ')');

    // Customer Header Row
    $html .= '<tr style="background-color: orange;">
                <td colspan="11"><strong>' . $customerName . '</strong></td>
              </tr>';

    // Unit Totals Row
    $html .= '<tr style="background-color:lime; font-weight:bold;">
                <td colspan="3">Units</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['time_ov'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['time_cso'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['travel_units'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['travel_miles'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['certs'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['tanker_cert'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['sha_sa'] . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['numeric']['courier'], 2) . '</td>
              </tr>';

    // Monetary Totals Row
    $html .= '<tr style="background-color:cyan; font-weight:bold;">
                <td colspan="3">Price</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['time_ov'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['time_cso'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['travel_units'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['travel_miles'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['certs'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['tanker_cert'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['sha_sa'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['courier'], 2) . '</td>
              </tr>';

    // **Detailed Data Rows**
    foreach ($customer['entries'] as $entry) {
        $formattedDate = date('d-m-Y', strtotime($entry['date']));

        $html .= "<tr>
                    <td width='12%'>{$entry['po']}</td>
                    <td width='12%'>{$entry['vet']}</td>
                    <td width='10%'>{$formattedDate}</td>
                    <td width='10%' style='text-align: right;'>{$entry['time_ov']}</td>
                    <td width='10%' style='text-align: right;'>{$entry['time_cso']}</td>
                    <td width='10%' style='text-align: right;'>{$entry['travel_units']}</td>
                    <td width='10%' style='text-align: right;'>{$entry['travel_miles']}</td>
                    <td width='10%' style='text-align: right;'>{$entry['certs']}</td>
                    <td width='10%' style='text-align: right;'>{$entry['tanker_cert']}</td>
                    <td width='10%' style='text-align: right;'>{$entry['sha_sa']}</td>
                    <td width='14%' style='text-align: right;'>£ " . number_format($entry['courier'], 2) . "</td>
                  </tr>";
    }
}

// Close Table
$html .= '</table>';

// **Write to PDF**
$pdf->writeHTML($html, true, false, true, false, '');

// **Footer**
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Report generated on: ' . date('d/m/Y H:i:s') . ' by ' . $user["username"], 0, 1, 'L');

// **Output the PDF**
ob_end_clean();
$pdf->Output("Billing_Detail_Report_{$month}_{$year}.pdf", 'I');
exit();
?>
