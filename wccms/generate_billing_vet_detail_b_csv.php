<?php
ob_start(); // Start output buffering

include('setting/main-top-files.php');

    // Get parameters
        // Set default values for fromDate and toDate
        $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : date('Y-m-01'); // First day of current month
        $toDate = isset($_GET['toDate']) ? $_GET['toDate'] : date('Y-m-t'); // Last day of current month
        $vetNames = isset($_GET['vetName']) ? $_GET['vetName'] : []; // Array for multiple vet selection

            // Ensure vetName is retrieved as an array
          //  $selectedVets = isset($_GET['vetName']) ? (array) $_GET['vetName'] : ['ALL'];
            $selectedVets = isset($_GET['vetName']) ? (is_array($_GET['vetName']) ? $_GET['vetName'] : explode(',', $_GET['vetName'])) : ['ALL'];


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
    $data = fetchBillingDataByVetDateRange($fromDate, $toDate, $selectedVets) ;
    $rates = fetchRates() ; // Fetch rates for financial calculations

    // Set CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    //header("Content-Disposition: attachment; filename={$year}_{$month}_Vet_Billing_Detail_Report.csv") ;
    header("Content-Disposition: attachment; filename=Vet_Billing_Period_Detail_Report.csv");

    $output = fopen('php://output', 'w') ;

    // Column Headers - Added financial values
    fputcsv($output, [
        'Vet', 'Vet ID', 'PO', 'Customer', 'Customer ID', 'Date', 
        'OV Units', 'OV Amount', 
        'CSO Units', 'CSO Amount', 
        'Travel Units', 'Travel Amount', 
        'Miles', 'Miles Amount', 
        'Certs Units', 'Certs Amount', 
        'SHA/SA Units', 'SHA/SA Amount', 
        'Courier Units', 'Courier Amount',
        'Tanker Units','Tanker Amount','Notes'
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

                $entry['sha_sa'],
                number_format($entry['sha_sa'] * ($rates['sha_sa']['rate'] ?? 0), 2),

                $entry['courier'],
                number_format($entry['courier'] * ($rates['courier']['rate'] ?? 0), 2),

                $entry['tanker_cert'],
                number_format($entry['tanker_cert'] * ($rates['tanker_cert']['rate'] ?? 0), 2),

                $entry['notes']
            ], ",", '"', "\\");
        }
    }

fclose($output);
ob_end_flush();
exit();
?>