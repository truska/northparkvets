<?php


function fetchBillingData($month, $year) {
    global $conn;

    $startDate = "$year-$month-01";
    $endDate = date("Y-m-t", strtotime($startDate));

    // Fetch rate mapping
    $rateQuery = "SELECT timeid, rate, units FROM npe_rates WHERE showonweb = 'Yes' AND archived = 0 ";
    //error_log("SQL for rates: $rateQuery");

    $rateResult = mysqli_query($conn, $rateQuery);
    $rates = [];
    while ($rateRow = mysqli_fetch_assoc($rateResult)) {
        $rates[$rateRow['timeid']] = [
            'rate' => $rateRow['rate'],
            'units' => $rateRow['units']
        ];
    }

    //error_log("Rate Mapping: " . json_encode($rates));

    // Main query for billing data
    $query = "SELECT 
                nt.id, 
                nt.date, 
                nt.time_ov, 
                nt.time_cso, 
                nt.travel_units, 
                nt.travel_miles, 
                nt.certs, 
                nt.sha_sa, 
                nt.courier, 
                nt.showonweb,
                nt.archived,
                p.showonweb,
                p.archived,
                p.name AS po, 
                v.name AS vet, 
                c.name AS customer_name, 
                c.code AS customer_code
              FROM npe_timesheets nt
              JOIN products p ON nt.productid = p.id
              JOIN npe_customer c ON p.account = c.id
              JOIN npe_vet v ON nt.vet = v.id
              WHERE nt.date BETWEEN '$startDate' AND '$endDate'
                AND nt.showonweb = 'Yes'
                AND p.showonweb = 'Yes'
                AND p.archived = 0
                AND nt.archived = 0
              ORDER BY c.name, po, nt.date";
   // error_log("Main SQL Query: $query");

    $result = mysqli_query($conn, $query);
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $customerId = $row['customer_name'];
        if (!isset($data[$customerId])) {
            $data[$customerId] = [
                'name' => $row['customer_name'],
                'code' => $row['customer_code'],
                'entries' => [],
                'totals' => [
                    'numeric' => [
                        'time_ov' => 0,
                        'time_cso' => 0,
                        'travel_units' => 0,
                        'travel_miles' => 0,
                        'certs' => 0,
                        'sha_sa' => 0,
                        'courier' => 0,
                    ],
                    'rates' => [
                        'time_ov' => $rates['time_ov'] ?? ['rate' => 0, 'units' => 0],
                        'time_cso' => $rates['time_cso'] ?? ['rate' => 0, 'units' => 0],
                        'travel_units' => $rates['travel_units'] ?? ['rate' => 0, 'units' => 0],
                        'travel_miles' => $rates['travel_miles'] ?? ['rate' => 0, 'units' => 0],
                        'certs' => $rates['certs'] ?? ['rate' => 0, 'units' => 0],
                        'sha_sa' => $rates['sha_sa'] ?? ['rate' => 0, 'units' => 0],
                        'courier' => $rates['courier'] ?? ['rate' => 0, 'units' => 0],
                    ],
                    'monetary' => [
                        'time_ov' => 0,
                        'time_cso' => 0,
                        'travel_units' => 0,
                        'travel_miles' => 0,
                        'certs' => 0,
                        'sha_sa' => 0,
                        'courier' => 0,
                    ],
                ]
            ];
        }

        $data[$customerId]['entries'][] = $row;

        // Add to numeric totals and monetary totals
        foreach (['time_ov', 'time_cso', 'travel_units', 'travel_miles', 'certs', 'sha_sa', 'courier'] as $col) {
            $data[$customerId]['totals']['numeric'][$col] += $row[$col];
            if (isset($rates[$col])) {
                $data[$customerId]['totals']['monetary'][$col] += $row[$col] * $rates[$col]['rate'];
            }
        }
    }

    return $data;
}

function fetchBillingDataByVet($month, $year) {
    global $conn;
    $startDate = "$year-$month-01";
    $endDate = date("Y-m-t", strtotime($startDate));

    $query = "SELECT 
                nt.id, 
                nt.date, 
                nt.time_ov, 
                nt.time_cso, 
                nt.travel_units, 
                nt.travel_miles, 
                nt.certs, 
                nt.sha_sa, 
                nt.courier, 
                nt.showonweb,
                nt.archived,
                p.showonweb,   
                p.archived,             
                p.name AS po, 
                c.name AS customer, 
                v.name AS vet
              FROM npe_timesheets nt
              JOIN products p ON nt.productid = p.id
              JOIN npe_customer c ON p.account = c.id
              JOIN npe_vet v ON nt.vet = v.id
              WHERE nt.date BETWEEN '$startDate' AND '$endDate'
                AND nt.showonweb = 'Yes'
                AND p.showonweb = 'Yes'
                AND nt.archived = 0 
              ORDER BY v.name, nt.date";

    $result = mysqli_query($conn, $query);
    $data = [];
    $rates = fetchRates();

    while ($row = mysqli_fetch_assoc($result)) {
        $vetName = $row['vet'];
        if (!isset($data[$vetName])) {
            $data[$vetName] = [
                'entries' => [],
                'totals' => initializeTotals()
            ];
        }

        $data[$vetName]['entries'][] = $row;

        foreach (['time_ov', 'time_cso', 'travel_units', 'travel_miles', 'certs', 'sha_sa', 'courier'] as $col) {
            $data[$vetName]['totals']['numeric'][$col] += $row[$col];
            $data[$vetName]['totals']['monetary'][$col] += $row[$col] * ($rates[$col]['rate'] ?? 0);
        }
    }

    return $data;
}

function fetchRates() {
    global $conn;
    $query = "SELECT timeid, rate, units FROM npe_rates WHERE showonweb = 'Yes' AND archived = 0 ";
    $result = mysqli_query($conn, $query);
    $rates = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rates[$row['timeid']] = [
            'rate' => $row['rate'],
            'units' => $row['units']
        ];
    }
    return $rates;
}

function initializeTotals() {
    return [
        'numeric' => [
            'time_ov' => 0,
            'time_cso' => 0,
            'travel_units' => 0,
            'travel_miles' => 0,
            'certs' => 0,
            'sha_sa' => 0,
            'courier' => 0,
        ],
        'monetary' => [
            'time_ov' => 0,
            'time_cso' => 0,
            'travel_units' => 0,
            'travel_miles' => 0,
            'certs' => 0,
            'sha_sa' => 0,
            'courier' => 0,
        ]
    ];
}

function accumulateTotals($source, &$destination) {
    foreach (['numeric', 'monetary'] as $type) {
        foreach ($source[$type] as $key => $value) {
            $destination[$type][$key] += $value;
        }
    }
}

function generateSubtotalRows($data, $type) {
    $html = "<tr class='table-secondary subtotal-row'>
        <td colspan='4'>Units</td>
        <td class='text-right'>{$data['totals']['numeric']['time_ov']}</td>
        <td class='text-right'>{$data['totals']['numeric']['time_cso']}</td>
        <td class='text-right'>{$data['totals']['numeric']['travel_units']}</td>
        <td class='text-right'>{$data['totals']['numeric']['travel_miles']}</td>
        <td class='text-right'>{$data['totals']['numeric']['certs']}</td>
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
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['sha_sa'], 2) . "</td>
        <td class='text-right'>£ " . number_format($data['totals']['monetary']['courier'], 2) . "</td>
    </tr>";

    return $html;
}

function generatePeriodTotalRows($totals) {
    $html = "<tr class='table-primary'>
        <td colspan='11'><strong>Period Total</strong></td>
    </tr>";

    $html .= "<tr class='table-secondary subtotal-row'>
        <td colspan='4'>Unit Totals</td>
        <td class='text-right'>{$totals['numeric']['time_ov']}</td>
        <td class='text-right'>{$totals['numeric']['time_cso']}</td>
        <td class='text-right'>{$totals['numeric']['travel_units']}</td>
        <td class='text-right'>{$totals['numeric']['travel_miles']}</td>
        <td class='text-right'>{$totals['numeric']['certs']}</td>
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
        <td class='text-right'>£ " . number_format($totals['monetary']['sha_sa'], 2) . "</td>
        <td class='text-right'>£ " . number_format($totals['monetary']['courier'], 2) . "</td>
    </tr>";

    $html .= "<tr class='table-success monetary-row'>
        <td colspan='10'></td>
        <td class='text-right'><strong>£ " . number_format(array_sum($totals['monetary']), 2) . "</strong></td>
    </tr>";

    return $html;
}

?>
