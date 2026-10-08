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

// Initialize TCPDF (Landscape for better readability)
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('North Park Exports');
$pdf->SetTitle("Billing Detail Report - $month/$year");
$pdf->SetHeaderData('', '', "Billing Detail Report (header) - " . date('F Y', strtotime("$year-$month-01")), ' ');
$pdf->setHeaderFont(['helvetica', '', 12]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(10, 20, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'N', 10);
$pdf->Cell(0, 10, 'Billing Detail Report (title) - ' . date('F Y', strtotime("$year-$month-01")) . ' | ' . $custnameheader, 0, 1);

// **Table Header (Repeats on Each Page)**
$tableHeader = '
    <tr style="background-color: pink; color:#ffffff;">
        <th align="left">PO</th>
        <th align="left">Vet</th>
        <th align="left">Date</th>
        <th style="text-align: right;">OV</th>
        <th style="text-align: right;">CSO</th>
        <th style="text-align: right;">Travel Units</th>
        <th style="text-align: right;">Travel Miles</th>
        <th style="text-align: right;">Certs</th>
        <th style="text-align: right;">SHA/SA</th>
        <th style="text-align: right;">Courier</th>
    </tr>';

$blankRow = '<tr><td colspan="10"></td></tr>';

// **Start Table (Only Once)**
$html = '<table border="1" cellpadding="5" cellspacing="0">';

// **Customer Sections**
$firstCustomer = true;

foreach ($data as $customer) {
    $customerName = htmlspecialchars($customer['name'] . ' (' . $customer['code'] . ')');

    // **Ensure new page starts correctly for each customer**
    if ($firstCustomer === false) {
        $pdf->AddPage();
        $html .= $blankRow; // Add spacing
        $html .= $tableHeader; // Re-add table header on new page
    }
    else
    {
        $html .= $tableHeader; // Re-add table header on new page
    }
    $firstCustomer = false;

    // **Customer Header Row**
    $html .= '<tr style="background-color: orange;">
                <td colspan="10"><strong>' . $customerName . '</strong></td>
              </tr>';

    // **Unit Totals Row**
    $html .= '<tr style="background-color:lime; font-weight:bold;">
                <td colspan="3">Units</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['time_ov'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['time_cso'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['travel_units'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['travel_miles'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['certs'] . '</td>
                <td style="text-align: right;">' . $customer['totals']['numeric']['sha_sa'] . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['numeric']['courier'], 2) . '</td>
              </tr>';

    // **Monetary Totals Row**
    $html .= '<tr style="background-color:cyan; font-weight:bold;">
                <td colspan="3">Price</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['time_ov'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['time_cso'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['travel_units'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['travel_miles'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['certs'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['sha_sa'], 2) . '</td>
                <td style="text-align: right;">£ ' . number_format($customer['totals']['monetary']['courier'], 2) . '</td>
              </tr>';

    // **Detailed Data Rows**
    foreach ($customer['entries'] as $entry) {
        $formattedDate = date('d-m-Y', strtotime($entry['date']));

        $html .= "<tr style='background-color:#dddddd;'>
                    <td>{$entry['po']}</td>
                    <td>{$entry['vet']}</td>
                    <td>{$formattedDate}</td>
                    <td style='text-align: right;'>{$entry['time_ov']}</td>
                    <td style='text-align: right;'>{$entry['time_cso']}</td>
                    <td style='text-align: right;'>{$entry['travel_units']}</td>
                    <td style='text-align: right;'>{$entry['travel_miles']}</td>
                    <td style='text-align: right;'>{$entry['certs']}</td>
                    <td style='text-align: right;'>{$entry['sha_sa']}</td>
                    <td style='text-align: right;'>£ " . number_format($entry['courier'], 2) . "</td>
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
