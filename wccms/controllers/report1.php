<?php

function fetchProductData($conn, $whereClause) {
    // Construct the SQL query dynamically
    $sql = "SELECT 
    p.*, 
    d.code AS depot_code, 
    d.name AS depot_name, 
    c.name AS customer_name, 
    v.name AS vet_name,
    dest.name AS destination_country_name, 
    dest.code AS destination_country_code, 
    ds.staff_type AS destination_status_code,
    s.name AS status_name,
    ss.code AS snag_code  -- Added snag code
    FROM `products` p
    LEFT JOIN `npe_depot` d ON p.depot = d.id AND d.showonweb = 'Yes'
    LEFT JOIN `npe_customer` c ON p.account = c.id AND c.showonweb = 'Yes'
    LEFT JOIN `npe_vet` v ON p.vet = v.id AND v.showonweb = 'Yes'
    LEFT JOIN `npe_destination` dest ON p.destination_country = dest.id AND dest.showonweb = 'Yes'
    LEFT JOIN `npe_destination_status` ds ON dest.destination_status_id = ds.id
    LEFT JOIN `npe_status` s ON p.status = s.id 
    LEFT JOIN `npe_snag_status` ss ON p.snag_status = ss.id  -- Join to snag status
    WHERE $whereClause
    ORDER BY p.expected_date, d.code, p.expected_time, p.name";

    // Execute the query
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    // Organize the results into a structured array
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $date = $row['expected_date'];
        $depot = $row['depot'];
        $depot_name = $row['depot_name'];

        if (!isset($data[$date])) {
            $data[$date] = [];
        }
        if (!isset($data[$date][$depot])) {
            $data[$date][$depot] = [];
        }
        $data[$date][$depot][] = $row;
    }
    return $data;
}


function getNamesFromIds($conn, $table, $idField, $nameField, $ids) {
    if (empty($ids)) {
        return [];
    }

    $idList = implode(",", array_map('intval', $ids)); // Sanitize IDs
    $sql = "SELECT $idField AS id, $nameField AS name FROM $table WHERE $idField IN ($idList)";
    $result = $conn->query($sql);

    $names = [];
    while ($row = $result->fetch_assoc()) {
        $names[] = "{$row['id']} [{$row['name']}]";
    }
    return $names;
}


function preprocessTotals($data) {
    $processedData = [];

    foreach ($data as $date => $depots) {
        $dateTotals = ['CSO' => 0, 'OV' => 0, 'Total' => 0]; // Totals for the date
        foreach ($depots as $depot => $rows) {
            $depotTotals = ['CSO' => 0, 'OV' => 0, 'Total' => 0]; // Totals for the depot

            // Ensure depot_name is fetched before iterating rows
            $depot_name = $rows[0]['depot_name'] ?? 'Unknown Depot';

            foreach ($rows as &$row) {
                // Calculate totals for each row
                $row['CSO'] = ($row['destination_status_code'] === 'CSO') ? 1 : 0;
                $row['OV'] = ($row['destination_status_code'] === 'OV') ? 1 : 0;
                $row['RowTotal'] = $row['CSO'] + $row['OV'];

                // Add to depot totals
                $depotTotals['CSO'] += $row['CSO'];
                $depotTotals['OV'] += $row['OV'];
                $depotTotals['Total'] += $row['RowTotal'];
            }

            // Add depot totals to date totals
            $dateTotals['CSO'] += $depotTotals['CSO'];
            $dateTotals['OV'] += $depotTotals['OV'];
            $dateTotals['Total'] += $depotTotals['Total'];

            // Store depot totals and rows
            $processedData[$date][$depot] = [
                'rows' => $rows,
                'totals' => $depotTotals,
                'depot_name' => $depot_name, // Include depot_name for the depot
            ];
        }

        // Store date totals
        $processedData[$date]['totals'] = $dateTotals;
    }

    return $processedData;
}

function getWeekNumber($expected_date) {
    return date("W", strtotime($expected_date));
}


function getDepotOptions($conn) {
    $sql = "SELECT id, name, code FROM npe_depot WHERE `showonweb` = 'Yes' AND `archived` = 0  ORDER BY `sort` ,`name`";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'value' => $row['id'],
            'label' => "{$row['name']} - ({$row['code']} ({$row['id']}))"
        ];
    }
    return $options;
}

function getStatusOptions($conn) {
    $sql = "SELECT id, name, code FROM npe_status WHERE `showonweb` = 'Yes' AND `archived` = 0 ORDER BY `sort`, `name` ";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'value' => $row['id'],
            'label' => "{$row['name']} - ({$row['code']} ({$row['id']}))"
        ];
    }
    return $options;
}

function getEhcOptions($conn) {
    $sql = "SELECT id, name, code FROM npe_ehc_location WHERE `showonweb` = 'Yes' AND `archived` = 0 ORDER BY `sort` , `name` ";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'value' => $row['id'],
            'label' => "{$row['name']} - ({$row['code']} ({$row['id']}))"
        ];
    }
    return $options;
}

function getVetOptions($conn) {
    $sql = "SELECT id, name, code FROM npe_vet ORDER BY `sort` , `name` ";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'value' => $row['id'],
            'label' => "{$row['name']} - ({$row['code']} ({$row['id']}))"
        ];
    }
    return $options;
}

function getCustomer($conn) {
    $sql = "SELECT id, name, code FROM npe_customer WHERE `showonweb` = 'Yes' AND `archived` = 0 ORDER BY `sort` , `name` ";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'value' => $row['id'],
            'label' => "{$row['name']} - ({$row['code']} ({$row['id']}))"
        ];
    }
    return $options;
}

// Get field names for table sort
function getSortableFields($db, $tableName) {
    $sql = "SELECT field_name AS field, field_label AS label 
            FROM sortable_fields 
            WHERE table_name = ? AND is_sortable = 'Yes' 
            ORDER BY field_label";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $tableName);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $fields = [];
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row;
    }
    return $fields;
}


function fetchReportData($conn, $whereClause, $sortClause = '') {
    $sql = "SELECT * FROM products WHERE $whereClause $sortClause";
    $result = $conn->query($sql);
    if (!$result) {
        die("SQL Error: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function renderReport($data) {
    foreach ($data as $row) {
        //echo "<p>Product: {$row['name']} | Depot: {$row['depot']}</p>";
    }
}
 
// report_2
function getClassBasedOnValue($value) {
    if ($value < 8) {
        return 'low';
    } elseif ($value >= 8 && $value <= 14) {
        return 'medium';
    } elseif ($value >= 15 && $value <= 21) {
        return 'high';
    } else {
        return 'critical';
    }
}

function getTimesheetCount($conn, $productId) {
    $sql = "SELECT COUNT(*) AS count FROM npe_timesheets WHERE productid = ? AND showonweb = 'Yes' AND archived = 0 ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
    //    error_log("SQL preparation failed in getTimesheetCount: " . $conn->error);
        return 0;
    }

    $stmt->bind_param('i', $productId);
    if (!$stmt->execute()) {
    //    error_log("SQL execution failed in getTimesheetCount: " . $stmt->error);
        return 0;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}


function fetchWeekCommentsXXX($conn, $weeknums) {
    if (empty($weeknums)) {
        return ["No Comments for this report"];
    }

    $placeholders = implode(",", array_fill(0, count($weeknums), "?"));
    $sql = "SELECT `text` 
        FROM npe_comments 
        WHERE `place` = 'week' 
        AND `showonweb` = 'Yes' 
        AND `archived` = 0 
        AND CAST(`placeref` AS UNSIGNED) IN ($placeholders)";

    error_log("Executing SQL: $sql");

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param(str_repeat('s', count($weeknums)), ...$weeknums);
    $stmt->execute();

    $result = $stmt->get_result();
    if (!$result) {
        die("SQL Execution Error: " . $stmt->error);
    }

   
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = trim($row['text']); // Ensure text is properly trimmed
        error_log("Fetched Comment: " . $row['text']); // Debug log
    }
    /*   
        $commentsMap = [];
        while ($row = $result->fetch_assoc()) {
            $week = ltrim($row['placeref'], '0'); // Normalize for consistency
            if (!isset($commentsMap[$week])) {
                $commentsMap[$week] = trim($row['text']);
            }
        }
    */

    if (empty($comments)) {
        error_log("No comments found for week(s): " . implode(", ", $weeknums));
        return ["No Comments for this report"];
    }

    return $comments;
    //return $commentsMap;
}

function fetchWeekComments($conn, $weeknums) {
    if (empty($weeknums)) {
        return ["No Comments for this report"];
    }

    $placeholders = implode(",", array_fill(0, count($weeknums), "?"));
    $sql = "SELECT CAST(`placeref` AS UNSIGNED) AS weeknum, `text` 
            FROM npe_comments 
            WHERE `place` = 'week' 
              AND `showonweb` = 'Yes' 
              AND `archived` = 0 
              AND CAST(`placeref` AS UNSIGNED) IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param(str_repeat('s', count($weeknums)), ...$weeknums);
    $stmt->execute();
    $result = $stmt->get_result();

    // Build weeknum => comment map
    $commentsMap = [];
    while ($row = $result->fetch_assoc()) {
        $wk = (int)$row['weeknum'];
        if (!isset($commentsMap[$wk])) {
            $commentsMap[$wk] = trim($row['text']);
        }
    }

    return $commentsMap;
}

// New function to handle Year rollovers fre week numbers from Dec to jan
function isoWeekDateRange(int $isoYear, int $isoWeek): array
{
    $dt = new DateTime();
    $dt->setISODate($isoYear, $isoWeek, 1);     // Monday
    $start = $dt->format('Y-m-d');

    $dt->modify('+6 days');                     // Sunday
    $end = $dt->format('Y-m-d');

    return [$start, $end];
}


function buildReportFilter($formnumber, $recordnumber, $postData, $conn)
{
    // Base defaults
    $whereClause = "p.showonweb = 'Yes' AND p.archived = 0";
    $subtitle = "";
    $weeknums = [];

    // -------------------------------------------------------------------------
    // FORMNUMBER 1: Predefined filters (This week, Next week, etc.)
    // -------------------------------------------------------------------------
    if ($formnumber == 1) {

        $currWeekNum  = (int) date("W");
        $currWeekYear = (int) date('o'); // ISO week-year
        $hasWeek53    = (int) date('W', strtotime("$currWeekYear-12-28")) === 53;

        switch ($recordnumber) {
            case 1:  // This week
                $targetWeekNum  = $currWeekNum;
                $targetWeekYear = $currWeekYear;
            
                [$startDate, $endDate] = isoWeekDateRange($targetWeekYear, $targetWeekNum);
            
                $whereClause = "
                    p.expected_date BETWEEN '$startDate' AND '$endDate'
                    AND p.showonweb = 'Yes'
                    AND p.archived = 0
                ";
                $subtitle = "This Week [Week: $targetWeekNum, $targetWeekYear] ($startDate to $endDate)";
                $weeknums[] = $targetWeekNum;
                break;
            
            case 2:      // Next week
                $targetWeekNum  = $currWeekNum + 1;
                $targetWeekYear = $currWeekYear;
            
                if ($targetWeekNum > ($hasWeek53 ? 53 : 52)) { // Example: /wccms/report_bookingv4.php?frm=1&id=1
                    $targetWeekNum  = 1;
                    $targetWeekYear = $currWeekYear + 1;
                }
            
                [$startDate, $endDate] = isoWeekDateRange($targetWeekYear, $targetWeekNum);
            
                $whereClause = "
                    p.expected_date BETWEEN '$startDate' AND '$endDate'
                    AND p.showonweb = 'Yes'
                    AND p.archived = 0
                ";
                $subtitle = "Next Week [Week: $targetWeekNum, $targetWeekYear] ($startDate to $endDate)";
                $weeknums[] = $targetWeekNum;
                break;

            case 3:      // Last week
                $targetWeekNum  = $currWeekNum - 1;
                $targetWeekYear = $currWeekYear;
            
                $hasWeek53Prev = (int) date('W', strtotime(($currWeekYear - 1) . '-12-28')) === 53;
            
                if ($targetWeekNum < 1) {
                    $targetWeekNum  = $hasWeek53Prev ? 53 : 52;
                    $targetWeekYear = $currWeekYear - 1;
                }
            
                [$startDate, $endDate] = isoWeekDateRange($targetWeekYear, $targetWeekNum);
            
                $whereClause = "
                    p.expected_date BETWEEN '$startDate' AND '$endDate'
                    AND p.showonweb = 'Yes'
                    AND p.archived = 0
                ";
                $subtitle = "Last Week [Week: $targetWeekNum, $targetWeekYear] ($startDate to $endDate)";
                $weeknums[] = $targetWeekNum;
                break;
                
            /*   
                case 1: // This week
                    $targetWeekNum  = $currWeekNum;
                    $targetWeekYear = $currWeekYear;

                    $whereClause = "
                        p.weeknum = $targetWeekNum 
                        AND YEAR(p.expected_date) = $targetWeekYear 
                        AND p.showonweb = 'Yes' 
                        AND p.archived = 0
                    ";
                    $subtitle = "This Week [Week: $targetWeekNum, $targetWeekYear]";
                    $weeknums[] = $targetWeekNum;
                    break;

                case 2: // Next week
                    $targetWeekNum  = $currWeekNum + 1;
                    $targetWeekYear = $currWeekYear;

                    // rollover logic including week 53 years
                    if ($targetWeekNum > ($hasWeek53 ? 53 : 52)) {
                        $targetWeekNum  = 1;
                        $targetWeekYear = $currWeekYear + 1;
                    }

                    $whereClause = "
                        p.weeknum = $targetWeekNum 
                        AND YEAR(p.expected_date) = $targetWeekYear 
                        AND p.showonweb = 'Yes' 
                        AND p.archived = 0
                    ";
                    $subtitle = "Next Week [Week: $targetWeekNum, $targetWeekYear]";
                    $weeknums[] = $targetWeekNum;
                    break;

                case 3: // Last week
                    $targetWeekNum  = $currWeekNum - 1;
                    $targetWeekYear = $currWeekYear;

                    // find if previous year had a week 53
                    $hasWeek53Prev = (int) date('W', strtotime(($currWeekYear - 1) . '-12-28')) === 53;

                    if ($targetWeekNum < 1) {
                        $targetWeekNum  = $hasWeek53Prev ? 53 : 52;
                        $targetWeekYear = $currWeekYear - 1;
                    }

                    $whereClause = "
                        p.weeknum = $targetWeekNum 
                        AND YEAR(p.expected_date) = $targetWeekYear 
                        AND p.showonweb = 'Yes' 
                        AND p.archived = 0
                    ";
                    $subtitle = "Last Week [Week: $targetWeekNum, $targetWeekYear]";
                    $weeknums[] = $targetWeekNum;
                    break;
            */
            case 4: // Current Month
                $whereClause = "
                    MONTH(p.expected_date) = MONTH(CURDATE()) 
                    AND YEAR(p.expected_date) = YEAR(CURDATE()) 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Current Month [" . date("F Y") . "]";
                $startWeek = date("W", strtotime("first day of this month"));
                $endWeek   = date("W", strtotime("last day of this month"));
                for ($w = $startWeek; $w <= $endWeek; $w++) {
                    $weeknums[] = $w;
                }
                break;

            case 5: // Previous Month
                $whereClause = "
                    MONTH(p.expected_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                    AND YEAR(p.expected_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Previous Month [" . date("F Y", strtotime("-1 month")) . "]";
                $startWeek = date("W", strtotime("first day of this month"));
                $endWeek   = date("W", strtotime("last day of this month"));
                for ($w = $startWeek; $w <= $endWeek; $w++) {
                    $weeknums[] = $w;
                }
                break;

            case 6: // Previous+ Months (4 months before the previous month)
                $whereClause = "
                    p.expected_date BETWEEN 
                        DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), INTERVAL 4 MONTH) 
                        AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Previous+ Months [" 
                    . date("M Y", strtotime("-5 month")) . " - " 
                    . date("M Y", strtotime("-2 month")) . "]";
                break;

            case 7: // Today's report
                $whereClause = "
                    p.expected_date = CURDATE() 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Today's Report [" . date("d M Y") . "]";
                $weeknums[] = $currWeekNum;
                break;

            case 8: // Yesterday's report
                $whereClause = "
                    p.expected_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Yesterday's Report [" . date("d M Y", strtotime("yesterday")) . "]";
                $yesterday = date("Y-m-d", strtotime("-1 day"));
                $weeknums[] = date("W", strtotime($yesterday));
                break;

            case 9: // Tomorrow's report
                $whereClause = "
                    p.expected_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Tomorrow's Report [" . date("d M Y", strtotime("tomorrow")) . "]";
                $tomorrow = date("Y-m-d", strtotime("+1 day"));
                $weeknums[] = date("W", strtotime($tomorrow));
                break;

            case 10: // Next Month
                $whereClause = "
                    MONTH(p.expected_date) = MONTH(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) 
                    AND YEAR(p.expected_date) = YEAR(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Next Month [" . date("F Y", strtotime("+1 month")) . "]";
                $startWeek = date("W", strtotime("first day of next month"));
                $endWeek   = date("W", strtotime("last day of next month"));
                for ($w = $startWeek; $w <= $endWeek; $w++) {
                    $weeknums[] = $w;
                }
                break;

            default:
                $whereClause = "
                    p.weeknum = " . ($currWeekNum + 1) . " 
                    AND p.showonweb = 'Yes' 
                    AND p.archived = 0
                ";
                $subtitle = "Next Week [Week: " . ($currWeekNum + 1) . "]";
                break;
        }
    }

    // -------------------------------------------------------------------------
    // FORMNUMBER 2: Dynamic filters (POST-based)
    // -------------------------------------------------------------------------
    elseif ($formnumber == 2) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filters = [];
            $subtitleParts = [];

            if (!empty($postData['fromDate']) && !empty($postData['toDate'])) {
                $from = $conn->real_escape_string($postData['fromDate']);
                $to   = $conn->real_escape_string($postData['toDate']);
                $filters[] = "p.expected_date BETWEEN '$from' AND '$to'";
                $subtitleParts[] = "From " . htmlspecialchars($from) . " to " . htmlspecialchars($to);
            }

            if (!empty($postData['includeDepots'])) {
                $includeDepots = array_map('intval', $postData['includeDepots']);
                $filters[] = "p.depot IN (" . implode(",", $includeDepots) . ")";
                $depotNames = getNamesFromIds($conn, 'npe_depot', 'id', 'name', $includeDepots);
                $subtitleParts[] = "Including depots: " . implode(", ", $depotNames);
            }

            if (!empty($postData['excludeDepots'])) {
                $excludeDepots = array_map('intval', $postData['excludeDepots']);
                $filters[] = "p.depot NOT IN (" . implode(",", $excludeDepots) . ")";
                $depotNames = getNamesFromIds($conn, 'npe_depot', 'id', 'name', $excludeDepots);
                $subtitleParts[] = "Excluding depots: " . implode(", ", $depotNames);
            }

            if (!empty($postData['ehcLocations'])) {
                $ehcLocations = array_map('intval', $postData['ehcLocations']);
                $filters[] = "p.ehc_location IN (" . implode(",", $ehcLocations) . ")";
                $ehcNames = getNamesFromIds($conn, 'npe_ehc_locations', 'id', 'name', $ehcLocations);
                $subtitleParts[] = "EHC Locations: " . implode(", ", $ehcNames);
            }

        // --- Vet filter
        if (!empty($postData['vetName'])) {
            $vetIds = array_map('intval', $postData['vetName']);
            $filters[] = "p.vet IN (" . implode(",", $vetIds) . ")";
            $vetNames = getNamesFromIds($conn, 'npe_vet', 'id', 'name', $vetIds);
            $subtitleParts[] = "Vet(s): " . implode(", ", $vetNames);
        }





            if (!empty($postData['status'])) {
                $statuses = array_map(function ($status) use ($conn) {
                    return "'" . $conn->real_escape_string($status) . "'";
                }, $postData['status']);
                $filters[] = "p.status IN (" . implode(",", $statuses) . ")";
                $statusNames = getNamesFromIds($conn, 'npe_status', 'id', 'name', $postData['status']);
                $subtitleParts[] = "Statuses: " . implode(", ", $statusNames);
            }

            // Combine filters and subtitle parts
            if (!empty($filters)) {
                $whereClause = implode(" AND ", $filters);
            }

            $whereClause .= " AND p.showonweb = 'Yes' AND p.archived = 0";

            $subtitle = !empty($subtitleParts)
                ? implode("; ", $subtitleParts)
                : "No specific filters applied.";
        }
    }

    // -------------------------------------------------------------------------
    // SORT CLAUSES (optional)
    // -------------------------------------------------------------------------
    $sortClauses = [];
    for ($i = 1; $i <= 3; $i++) {
        $sortColumn = $postData["sortColumn$i"] ?? '';
        $sortOrder  = $postData["sortOrder$i"] ?? '';
        if ($sortColumn && $sortOrder) {
            $sortClauses[] = "$sortColumn $sortOrder";
        }
    }

    // Return all relevant values
    return compact('whereClause', 'subtitle', 'weeknums', 'sortClauses');
}


?>
