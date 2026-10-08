<?php 
ob_start(); // Start output buffering

include('setting/main-top-files.php');

// Get parameters
$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

// Fetch data
$data = fetchBillingDataByVet($month, $year);

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename={$year}_{$month}_Vet_Billing_Detail_Report.csv");

$output = fopen('php://output', 'w');

// Column Headers - Added financial values
fputcsv($output, [
    'Vet', 'Vet ID', 'PO', 'Customer', 'Customer ID', 'Date', 
    'OV Units', 'OV Amount', 
    'CSO Units', 'CSO Amount', 
    'Travel Units', 'Travel Amount', 
    'Miles', 'Miles Amount', 
    'Certs Units', 'Certs Amount', 
    'Tanker Certs Units', 'Tanker Certs Amount', 
    'SHA/SA Units', 'SHA/SA Amount', 
    'Courier Units', 'Courier Amount'
], ",", '"', "\\");

// Data Rows - Adding calculated monetary totals
foreach ($data as $vetName => $vetData) {
    foreach ($vetData['entries'] as $entry) {
        fputcsv($output, [
            $vetName,
            $entry['vet_id'],
            $entry['po'],
            $entry['customer'],
            $entry['customer_id'],
            date('d-m-Y', strtotime($entry['date'])),

            // Numeric Values
            $entry['time_ov'],
            number_format($entry['amounts']['time_ov'], 2),

            $entry['time_cso'],
            number_format($entry['amounts']['time_cso'], 2),

            $entry['travel_units'],
            number_format($entry['amounts']['travel_units'], 2),

            $entry['travel_miles'],
            number_format($entry['amounts']['travel_miles'], 2),

            $entry['certs'],
            number_format($entry['amounts']['certs'], 2),

            $entry['tanker_cert'],
            number_format($entry['amounts']['tanker_cert'], 2),

            $entry['sha_sa'],
            number_format($entry['amounts']['sha_sa'], 2),

            $entry['courier'],
            number_format($entry['amounts']['courier'], 2)
        ], ",", '"', "\\");
    }
}

fclose($output);
ob_end_flush();
exit();
?>
