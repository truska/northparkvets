<?php
ob_start();

include('setting/main-top-files.php');
include('functions.php');

// ------------------------------------------------------------
// Receive the same report/filter values as the on-screen report
// ------------------------------------------------------------
$formnumber   = isset($_POST['frm']) ? (int) $_POST['frm'] : 0;
$recordnumber = isset($_POST['id']) ? (int) $_POST['id'] : null;

if ($formnumber < 1) {
    die('Invalid report request.');
}

// Use exactly the same filtering logic as the source report.
extract(buildReportFilter($formnumber, $recordnumber, $_POST, $conn));

// Fetch and process the same records shown on the report.
$data = fetchProductData($conn, $whereClause);
$processedData = preprocessTotals($data);

// Nothing from included files must be sent before CSV headers.
ob_end_clean();

// ------------------------------------------------------------
// CSV download headers
// ------------------------------------------------------------
$filename = 'tanker_export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

$output = fopen('php://output', 'w');

// UTF-8 BOM helps Excel open names/characters correctly.
fwrite($output, "\xEF\xBB\xBF");

// ------------------------------------------------------------
// Column headings
// First version: all fields currently available/displayed
// on the source booking report. More fields can be added later.
// ------------------------------------------------------------
fputcsv($output, [
    'ID',
    'Loading Date',
    'Loading Day',
    'Time',
    'Depot',
    'PO',
    'Customer',
    'Country',
    'Country Code',
    'Destination',
    'Pallet Count',
    'Pallet Type',
    'Goods',
    'PCloud',
    'Status',
    'Vet',
    'Total',
    'Timesheet Count',
    'Snag Status Code',
    'Snag Text',
    'Sealed',
    'Notes'
], ",", '"', "\\");

// ------------------------------------------------------------
// Data rows
// ------------------------------------------------------------
foreach ($processedData as $date => $dateData) {

    if ($date === 'totals') {
        continue;
    }

    $loadingDate = '';
    $loadingDay  = '';

    if (!empty($date) && strtotime($date) !== false) {
        $loadingDate = date('d-m-Y', strtotime($date));
        $loadingDay  = date('D', strtotime($date));
    }

    foreach ($dateData as $depot => $depotData) {

        if ($depot === 'totals' || empty($depotData['rows'])) {
            continue;
        }

        foreach ($depotData['rows'] as $row) {

            $timesheetCount = getTimesheetCount($conn, $row['id']);

            fputcsv($output, [
                $row['id'] ?? '',
                $loadingDate,
                $loadingDay,
                $row['expected_time'] ?? '',
                $row['depot_code'] ?? '',
                $row['name'] ?? '',
                $row['customer_name'] ?? '',
                $row['destination_country_name'] ?? '',
                $row['destination_country_code'] ?? '',
                $row['destination_customer'] ?? '',
                $row['pallet_count'] ?? '',
                $row['pallet_type'] ?? '',
                $row['product_type'] ?? '',
                $row['pcloud'] ?? '',
                $row['status_name'] ?? '',
                $row['vet_name'] ?? '',
                $row['RowTotal'] ?? '',
                $timesheetCount,
                $row['snag_code'] ?? '',
                $row['snag_text'] ?? '',
                $row['sealed'] ?? '',
                $row['notes'] ?? ''
            ], ",", '"', "\\");
        }
    }
}

fclose($output);
exit;
?>
