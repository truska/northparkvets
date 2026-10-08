<?php
require_once __DIR__ . '/billingAmounts.php';

function fetchBillingDataGeneric($month, $year, $groupBy = 'customer', $customerId = null) {
    global $conn;

    //  error_log("fetchBillingDataGeneric called with month: $month, year: $year, groupBy: $groupBy, customerId: " . var_export($customerId, true));

    $startDate = "$year-$month-01";
    $endDate = date("Y-m-t", strtotime($startDate));


    $customerFilter = '';
    if ($customerId && $customerId !== 'all') {
        $customerFilter = " AND c.id = " . intval($customerId);
    }
    //   error_log("Cust Filter in function: ".$customerFilter );
    

    $query = "SELECT 
        nt.rate_time_ov, nt.rate_time_cso, nt.rate_travel_units, nt.rate_travel_miles,
        nt.rate_certs, nt.rate_tanker_cert, nt.rate_sha_sa, nt.rate_courier,
        nt.id, 
        nt.date, 
        nt.time_ov, 
        nt.time_cso, 
        nt.travel_units, 
        nt.travel_miles, 
        nt.certs, 
        nt.tanker_cert, 
        nt.sha_sa, 
        nt.courier, 
        nt.showonweb,
        nt.archived,
        p.showonweb,   
        p.archived,             
        p.name AS po, 
        c.name AS customer, 
        c.code AS customer_code, c.id AS customer_id, v.id AS vet_id,
        v.name AS vet
        FROM npe_timesheets nt
        JOIN products p ON nt.productid = p.id
        JOIN npe_customer c ON p.account = c.id
        JOIN npe_vet v ON nt.vet = v.id
        WHERE nt.date BETWEEN '$startDate' AND '$endDate'
        AND nt.showonweb = 'Yes'
        AND p.showonweb = 'Yes'
        AND nt.archived = 0 
        AND p.archived = 0
        $customerFilter
    ORDER BY " . ($groupBy === 'vet' ? 'v.name, nt.date' : 'c.name, nt.date');

    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("Error fetching billing data: " . mysqli_error($conn));
    }
    
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $groupKey = ($groupBy === 'vet') ? $row['vet'] : $row['customer'];
        if (!isset($data[$groupKey])) {
            $data[$groupKey] = [
                'entries' => [],
                'totals' => initializeTotals()
            ];

            if ($groupBy === 'customer') {
                $data[$groupKey]['name'] = $row['customer'];
                $data[$groupKey]['code'] = $row['customer_code'];
            }
        }

        try { $row['amounts'] = billingEntryAmounts($row); }
        catch (BillingRateException $exception) { billingReportUnavailable($exception); }
        $data[$groupKey]['entries'][] = $row;

        addBillingEntryTotals($data[$groupKey]['totals'], $row);
    }
    return $data;
}


function fetchBillingDataByVet($month, $year) {
    return fetchBillingDataGeneric($month, $year, 'vet');
}


function fetchBillingData($month, $year, $customerId = null) {
  //  error_log("fetchBillingData called with month: $month, year: $year, customerId: " . var_export($customerId, true)); 
    return fetchBillingDataGeneric($month, $year, 'customer', $customerId);
}


function fetchBillingDataByVetDateRange($fromDate, $toDate, $vetNames = []) {
    global $conn;

    if (!$conn) {
        error_log("Database Failed");
        die('Database connection is not available.');
    } else {
     //   error_log("Database OK");
    }

   // error_log("fetchBillingDataByVetDateRange: from {$fromDate}, to {$toDate}, vets: " . implode(',', $vetNames));

    // Ensure valid date format
    $fromDate = date('Y-m-d', strtotime($fromDate));
    $toDate = date('Y-m-d', strtotime($toDate));

    // Handle vet filtering
    $vetFilter = '';
    if (!empty($vetNames) && !(count($vetNames) === 1 && $vetNames[0] === 'ALL')) {
        $escapedVetNames = array_map(function($vet) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $vet) . "'";
        }, $vetNames);
        $vetFilter = " AND v.id IN (" . implode(',', $escapedVetNames) . ")";
    }

    //  error_log("Vet Filter: " . $vetFilter);

    // SQL Query with proper vet filtering
    $query = "SELECT 
        nt.rate_time_ov, nt.rate_time_cso, nt.rate_travel_units, nt.rate_travel_miles,
        nt.rate_certs, nt.rate_tanker_cert, nt.rate_sha_sa, nt.rate_courier,
        nt.id, nt.date, nt.time_ov, nt.time_cso, nt.travel_units, nt.travel_miles, 
        nt.certs, nt.tanker_cert, nt.sha_sa, nt.courier, nt.notes, nt.showonweb, nt.archived,
        p.name AS po, c.name AS customer, c.id AS customer_id, 
        v.name AS vet, v.id AS vet_id
        FROM npe_timesheets nt
        JOIN products p ON nt.productid = p.id
        JOIN npe_customer c ON p.account = c.id
        JOIN npe_vet v ON nt.vet = v.id
        WHERE nt.date BETWEEN '$fromDate' AND '$toDate'
        AND nt.showonweb = 'Yes' 
        AND p.showonweb = 'Yes'
        AND nt.archived = 0 
        AND p.archived = 0
        $vetFilter
    ORDER BY v.name, nt.date";

    //  error_log("Select vet date sql: " . $query);

    // Execute the query
    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("SQL Error: " . mysqli_error($conn));
        die("DEBUG: SQL Error - Check Logs");
    }

    $num_rows = mysqli_num_rows($result);
   // error_log("Number of rows fetched: " . $num_rows);

    if ($num_rows == 0) {
        return [];
    }


    if (!is_object($result)) {
        error_log("DEBUG: Query result is not an object! Something is wrong.");
        return [];
    }
    
    // Process results
    $data = [];
    $counter = 0; // Debug counter

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;
       // error_log("DEBUG: Processing row #{$counter}: " . print_r($row, true));

        // Extract vet name properly
        $vetKey = trim($row['vet']); // Ensuring no whitespace issues
     //   error_log("DEBUG: VetKey Extracted: " . $vetKey);

        if (empty($vetKey)) {
            error_log("DEBUG: VetKey is EMPTY for row #{$counter}, skipping...");
            continue;
        }

        // Initialize vet data if first time seeing this vet
        if (!isset($data[$vetKey])) {
          //  error_log("DEBUG: Initializing data for Vet: " . $vetKey);
            $data[$vetKey] = [
                'vet_id' => $row['vet_id'],
                'entries' => [],
                'totals' => initializeTotals()
            ];
        }

        // Add row to vet's entries
        try { $row['amounts'] = billingEntryAmounts($row); }
        catch (BillingRateException $exception) { billingReportUnavailable($exception); }
        $data[$vetKey]['entries'][] = $row;
        //error_log("DEBUG: Added row to entries for Vet: " . $vetKey);

        addBillingEntryTotals($data[$vetKey]['totals'], $row);

    // Final Debug before returning
    //error_log("DEBUG: Final Data Array Before Return: " . print_r($data, true));
    }
    return $data;    
}


function initializeTotals() {
    return [
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
        ]
    ];
}


function generateSubtotalRows($data, $type) {
    $html = "<tr class='table-secondary subtotal-row'>
        <td colspan='4'>Units</td>
        <td class='text-right'>{$data['totals']['numeric']['time_ov']}</td>
        <td class='text-right'>{$data['totals']['numeric']['time_cso']}</td>
        <td class='text-right'>{$data['totals']['numeric']['travel_units']}</td>
        <td class='text-right'>{$data['totals']['numeric']['travel_miles']}</td>
        <td class='text-right'>{$data['totals']['numeric']['certs']}</td>
        <td class='text-right'>{$data['totals']['numeric']['tanker_cert']}</td>
        <td class='text-right'>{$data['totals']['numeric']['sha_sa']}</td>
        <td class='text-right'>£ " . number_format($data['totals']['numeric']['courier'], 2) . "</td>
    </tr>";

    $html .= "<tr class='table-warning monetary-row'>
        <td colspan='4'>Monetary Subtotal</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['time_ov'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['time_cso'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['travel_units'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['travel_miles'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['certs'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['tanker_cert'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['sha_sa'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['courier'], 2) . "</td>
    </tr>";

    return $html;
}


function accumulateTotals($source, &$destination) {
    foreach (['numeric', 'monetary'] as $type) {
        foreach ($source[$type] as $key => $value) {
            $destination[$type][$key] = $type === 'monetary'
                ? ((int)round($destination[$type][$key] * 100) + (int)round($value * 100)) / 100
                : $destination[$type][$key] + $value;
        }
    }
}


function generatePeriodTotalRows($totals) {
    $html = "<tr class='table-primary'>
        <td colspan='12'><strong>Period Total</strong></td>
    </tr>";

    $html .= "<tr class='table-secondary subtotal-row'>
        <td colspan='4'>Unit Totals</td>
        <td class='text-right'>{$totals['numeric']['time_ov']}</td>
        <td class='text-right'>{$totals['numeric']['time_cso']}</td>
        <td class='text-right'>{$totals['numeric']['travel_units']}</td>
        <td class='text-right'>{$totals['numeric']['travel_miles']}</td>
        <td class='text-right'>{$totals['numeric']['certs']}</td>
        <td class='text-right'>{$totals['numeric']['tanker_cert']}</td>
        <td class='text-right'>{$totals['numeric']['sha_sa']}</td>
        <td class='text-right'>£ " . number_format($totals['numeric']['courier'], 2) . "</td>
    </tr>";

    $html .= "<tr class='table-warning monetary-row'>
        <td colspan='4'>Monetary Totals</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['time_ov'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['time_cso'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['travel_units'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['travel_miles'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['certs'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['tanker_cert'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['sha_sa'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['courier'], 2) . "</td>
    </tr>";

    $html .= "<tr class='table-success monetary-row'>
        <td colspan='11'><strong>Total</strong></td>
        <td class='text-right'><strong>£ " . number_format(array_sum($totals['monetary']), 2) . "</strong></td>
    </tr>";

    return $html;
}


function getVetNamesByIds($vetIds) {
    global $conn;  // Ensure database connection is available

    // If "ALL" is selected, return all vets
    if (in_array('ALL', $vetIds)) {
        $query = "SELECT name FROM npe_vet ORDER BY name";
    } else {
        // Sanitize vet IDs for SQL query
        $escapedIds = array_map('intval', $vetIds);
        $query = "SELECT name FROM npe_vet WHERE id IN (" . implode(',', $escapedIds) . ") ORDER BY name";
    }

    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("SQL Error fetching vet names: " . mysqli_error($conn));
        return [];
    }

    $vetNames = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $vetNames[] = $row['name'];
    }
    return $vetNames;
}


?>