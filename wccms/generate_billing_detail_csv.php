<?php
ob_start(); // Start output buffering

include('setting/main-top-files.php');

// Get parameters
$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

// Fetch data
$customerId = $_GET['c'] ?? null;
$data = fetchBillingData($month, $year, $customerId);

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename={$year}_{$month}_Billing_Detail_Report.csv");

$output = fopen('php://output', 'w');

// Column Headers
fputcsv($output, ['PO', 'Customer', 'Customer ID', 'Vet', 'Date', 'OV', 'CSO', 'Travel Units', 'Travel Miles', 'Certs', 'Tanker Certs', 'SHA/SA', 'Courier'], ",", '"', "\\");

// Data Rows
foreach ($data as $customer) {
    foreach ($customer['entries'] as $entry) {
        fputcsv($output, [
            $entry['po'],
            $entry['customer'],
            $entry['customer_id'],
            $entry['vet'],
            date('d-m-Y', strtotime($entry['date'])),
            $entry['time_ov'],
            $entry['time_cso'],
            $entry['travel_units'],
            $entry['travel_miles'],
            $entry['certs'],
            $entry['tanker_cert'],
            $entry['sha_sa'],
            number_format($entry['courier'], 2)
        ], ",", '"', "\\");
    }
}

fclose($output);
ob_end_flush();
exit();
?>