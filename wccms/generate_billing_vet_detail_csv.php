<?php 
ob_start(); // Start output buffering

include('setting/main-top-files.php');

// Get parameters
$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

// Fetch data
$data = fetchBillingDataByVet($month, $year);
$rates = fetchRates(); // Fetch rates for financial calculations

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
            number_format($entry['time_ov'] * ($rates['time_ov']['rate'] ?? 0), 2),

            $entry['time_cso'],
            number_format($entry['time_cso'] * ($rates['time_cso']['rate'] ?? 0), 2),

            $entry['travel_units'],
            number_format($entry['travel_units'] * ($rates['travel_units']['rate'] ?? 0), 2),

            $entry['travel_miles'],
            number_format($entry['travel_miles'] * ($rates['travel_miles']['rate'] ?? 0), 2),

            $entry['certs'],
            number_format($entry['certs'] * ($rates['certs']['rate'] ?? 0), 2),

            $entry['tanker_cert'],
            number_format($entry['tanker_cert'] * ($rates['tanker_cert']['rate'] ?? 0), 2),

            $entry['sha_sa'],
            number_format($entry['sha_sa'] * ($rates['sha_sa']['rate'] ?? 0), 2),

            $entry['courier'],
            number_format($entry['courier'] * ($rates['courier']['rate'] ?? 0), 2)
        ], ",", '"', "\\");
    }
}

fclose($output);
ob_end_flush();
exit();
?>
