<!-- START report_reportbookingv4 --> 

<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(1);
        // Turn on error reporting
        // error_reporting(1);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Bring forward variables
        $baseURL = $BASE_URL;

        //error_log("Including setting/main-top-files.php");
        include('setting/main-top-files.php'); 
        //error_log("Including include/header-code.php");
        include("include/header-code.php");

        //error_log("Including report-booking-time-inc.php");
        include("report-booking-time-inc.php"); // Modal file for time recording
        //include("timeModal.php"); 


        if (!$formnumber = securityCheck($_GET['frm'], 'number')) {
            die('Error in the form'); // Kill script if frm is invalid
        }
        if (!$recordnumber = securityCheck($_GET['id'], 'number')) {
            $recordnumber = null; // Allow frm=2 without id
        }
    ?>

    <?php
    //error_log("report_bookingv4.php triggered: " . $_SERVER['REQUEST_URI']);
    //error_log("GET Parameters: " . print_r($_GET, true));
    //error_log("POST Parameters: " . print_r($_POST, true));
    ?>

<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <style>
        .modal-title .static-text {
            font-weight: 400;
        }

        .modal-title .dynamic-name {
            font-weight: 700;
        }
        .modal-title .dynamic-id {
            font-weight: 200;
        }
        .notes-row {
        background-color: #f9f9f9; /* Light background for notes */
        font-style: italic;
        display: none; /* Initially hidden */
        }

        .notes-content {
            padding: 10px;
            font-size:1rem;
            color: #555;
        }
        .comment-area {
            font-weight:400;
            font-size:.9rem;
        }
        .comment-area p  {
            margin-bottom :0px;
        }
        .report-comment {
            font-weight:200;
            font-size:.8rem;
        }

        /* Sticky table header */
        /* Ensure the table container is scrollable */
        /* Sticky table header */
        /* Ensure the table container is scrollable */
        #billing-container {
            /*max-height: 100%; /* Adjust height as needed */
            height: 600px; /* 80% of the viewport height */
            overflow-y: auto;
            border: 1px solid #ddd; /* Optional border */
            margin-bottom:30px;
        }

        /* Make the table full width */
        #billing-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Make only the first thead sticky */
        /* Make only the first thead sticky */
        .sticky-header {
            position: sticky;
            top: 0;
            background: white;
            z-index: 100;
        }

        /* Ensure the other thead elements scroll normally */
        #billing-table thead:not(.sticky-header) {
            position: relative;
        }

        /* Optional: Style to make header stand out */
        #billing-table th {
            background: #f8f9fa; /* Light background */
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        /* Ensure table rows don't collapse */
        #billing-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
    </style>

    <?php
        include("include/header.php");
        include("include/sidebar.php");

        // Prevent handling AJAX requests meant for other scripts
        // Ignore requests with an action parameter
        if (isset($_GET['action'])) {
            //error_log("Ignoring request in report_bookingv4.php. Action detected: " . $_GET['action']);
            http_response_code(400); // Send a Bad Request response
            exit;
        }


        //Moved define WHERE abd SORT clause to function

        extract(buildReportFilter($formnumber, $recordnumber, $_POST, $conn));
/*

    // Define default WHERE clause
    $whereClause = "p.showonweb = 'Yes' AND p.archived = 0 ";
    $currWeekNum  = (int) date("W");
    $currWeekYear = (int) date('o'); // ISO week-year (important!)

    // Detect if current ISO year has week 53
    $hasWeek53 = (int) date('W', strtotime("$currWeekYear-12-28")) === 53;

    $weeknums = [];

    // Process form logic
    if ($formnumber == 1) {
        switch ($recordnumber) {

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


                    
                    case 4: // Current Month
                        $whereClause = "MONTH(p.expected_date) = MONTH(CURDATE()) AND YEAR(p.expected_date) = YEAR(CURDATE()) AND p.showonweb = 'Yes' AND p.archived = 0";
                        $subtitle  = "Current Month [" . date("F Y") . "]" ;

                        $startWeek = date("W", strtotime("first day of this month"));
                        $endWeek = date("W", strtotime("last day of this month"));
                        for ($w = $startWeek; $w <= $endWeek; $w++) {
                            $weeknums[] = $w;
                        }
                        break;
                    case 5: // Previous Month
                        $whereClause = "MONTH(p.expected_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(p.expected_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND p.showonweb = 'Yes' AND p.archived = 0";
                        $subtitle  = "Previous Month [" . date("F Y", strtotime("-1 month")) . "]" ;

                        $startWeek = date("W", strtotime("first day of this month"));
                        $endWeek = date("W", strtotime("last day of this month"));
                        for ($w = $startWeek; $w <= $endWeek; $w++) {
                            $weeknums[] = $w;
                        }
                        break;
                    case 6: // Previous+ Months (4 months before the previous month)
                        $whereClause = "p.expected_date BETWEEN DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), INTERVAL 4 MONTH) AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND p.showonweb = 'Yes' AND p.archived = 0";
                        $subtitle  = "Previous+ Months [" . date("M Y", strtotime("-5 month")) . " - " . date("M Y", strtotime("-2 month")) . "]" ;
                        break;

                    case 7: // Today's report
                        $whereClause = "p.expected_date = CURDATE() AND p.showonweb = 'Yes' AND p.archived = 0";
                        $subtitle = "Today's Report [" . date("d M Y") . "]";
                        $weeknums[] = $currweeknum;
                        break;
                
                    case 8: // Yesterday's report
                        $whereClause = "p.expected_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
                        $subtitle = "Yesterday's Report [" . date("d M Y", strtotime("yesterday")) . "]";
                        $yesterday = date("Y-m-d", strtotime("-1 day"));
                        $weeknums[] = date("W", strtotime($yesterday));
                        break;

                    case 9: // Tomorrow's report
                        $whereClause = "p.expected_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
                        $subtitle = "Tomorrow's Report [" . date("d M Y", strtotime("tomorrow")) . "]";
                        $tomorrow = date("Y-m-d", strtotime("+1 day"));
                        $weeknums[] = date("W", strtotime($tomorrow));
                        break;            

                    case 10: // Next Month
                            $whereClause = "MONTH(p.expected_date) = MONTH(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(p.expected_date) = YEAR(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) AND p.showonweb = 'Yes' AND p.archived = 0";
                            $subtitle  = "Next Month [" . date("F Y", strtotime("+1 month")) . "]" ;
                        
                            $startWeek = date("W", strtotime("first day of next month"));
                            $endWeek = date("W", strtotime("last day of next month"));
                            for ($w = $startWeek; $w <= $endWeek; $w++) {
                                $weeknums[] = $w;
                            }
                            break;
                        
                    default:
                        $whereClause = "p.weeknum = " . ($currweeknum + 1 )." AND p.showonweb = 'Yes' AND p.archived = 0 " ;     // Next week
                        $subtitle  = "Next Week [Week: ".($currweeknum + 1 )."]" ;
                }
            } 
            elseif ($formnumber == 2) 
            {

                // Construct SQL dynamically based on POST values
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $filters = [];
                    $subtitleParts = []; // Array to hold subtitle components

                    if (!empty($_POST['fromDate']) && !empty($_POST['toDate'])) {
                        $filters[] = "p.expected_date BETWEEN '" . $conn->real_escape_string($_POST['fromDate']) . "' AND '" . $conn->real_escape_string($_POST['toDate']) . "'";
                        $subtitleParts[] = "From " . htmlspecialchars($_POST['fromDate']) . " to " . htmlspecialchars($_POST['toDate']);
                    }

                    if (!empty($_POST['includeDepots'])) {
                        $includeDepots = array_map('intval', $_POST['includeDepots']);
                        $filters[] = "p.depot IN (" . implode(",", $includeDepots) . ")";
                        $depotNames = getNamesFromIds($conn, 'npe_depot', 'id', 'name', $includeDepots);
                        $subtitleParts[] = "Including depots: " . implode(", ", $depotNames);
                    }

                    if (!empty($_POST['excludeDepots'])) {
                        $excludeDepots = array_map('intval', $_POST['excludeDepots']);
                        $filters[] = "p.depot NOT IN (" . implode(",", $excludeDepots) . ")";
                        $depotNames = getNamesFromIds($conn, 'npe_depot', 'id', 'name', $excludeDepots);
                        $subtitleParts[] = "Excluding depots: " . implode(", ", $depotNames);
                    }

                    if (!empty($_POST['ehcLocations'])) {
                        $ehcLocations = array_map('intval', $_POST['ehcLocations']);
                        $filters[] = "p.ehc_location IN (" . implode(",", $ehcLocations) . ")";
                        $ehcNames = getNamesFromIds($conn, 'npe_ehc_locations', 'id', 'name', $ehcLocations);
                        $subtitleParts[] = "EHC Locations: " . implode(", ", $ehcNames);
                    }

                    if (!empty($_POST['status'])) {
                        $statuses = array_map(function ($status) use ($conn) {
                            return "'" . $conn->real_escape_string($status) . "'";
                        }, $_POST['status']);
                        $filters[] = "p.status IN (" . implode(",", $statuses) . ")";
                        $statusNames = getNamesFromIds($conn, 'npe_status', 'id', 'name', $_POST['status']);
                        $subtitleParts[] = "Statuses: " . implode(", ", $statusNames);
                    }

                    // Combine filters into the WHERE clause
                    if (!empty($filters)) {
                        $whereClause = implode(" AND ", $filters);
                    }

                    // Construct the subtitle
                    if (!empty($subtitleParts)) {
                        $subtitle = implode("; ", $subtitleParts);
                    } else {
                        $subtitle = "No specific filters applied.";
                    }
                }
            }

            $sortClauses = [];
            for ($i = 1; $i <= 3; $i++) {
                $sortColumn = $_POST["sortColumn$i"] ?? '';
                $sortOrder = $_POST["sortOrder$i"] ?? '';
                if ($sortColumn && $sortOrder) {
                    $sortClauses[] = "$sortColumn $sortOrder";
                }
            }
*/
        $sortClause = !empty($sortClauses) ? "ORDER BY " . implode(", ", $sortClauses) : '';

        // Get week num comments
        $comments = fetchWeekComments($conn, $weeknums);


        // Add debug info for WHERE clause
        $sqlDebug = "SELECT * FROM products WHERE $whereClause $sortClause";
    ?>
        <section id="main-content">
            <section class="wrapper site-min-height">
                <!-- Heading Area -LEFT -->
                <div class="row">
                    <div class="col-8">
                        <h1>North Park Exports</h1>
                        <?php
                            if($formnumber == 1 ){           
                                echo "<h3>".htmlspecialchars($subtitle)." <span style='font-size:1rem;font-weight:200'>[Quick Report]</style></h3>" ;
                                                       
                                if (!empty($comments) && is_array($comments)): 
                        ?>
                                    <div class="comment-area">
                                        <p><strong>Comments for Selected Week(s):</strong></p>
                                        <div class="report-comment">
                                            <ul>
                                                <?php 

                                                    foreach ($weeknums as $weeknum) {
                                                        $normalizedWeek = (int)ltrim($weeknum, '0'); // Normalize for map lookup
                                                        $commentText = !empty($comments[$normalizedWeek]) 
                                                            ? nl2br(htmlspecialchars(trim($comments[$normalizedWeek]))) 
                                                            : "<em>No Comment</em>";

                                                        echo "<li><strong>Week: " . htmlspecialchars($normalizedWeek) . "</strong> - " . $commentText . "</li>";
                                                    }

                                                    /*
                                                        foreach ($weeknums as $index => $weeknum) {
                                                            $normalizedWeek = ltrim($weeknum, '0'); // Remove leading zeros

                                                            if (!empty($comments[$index])) { // Ensure only valid comments are displayed
                                                                echo "<li><strong>Week: " . htmlspecialchars($normalizedWeek) . "</strong> - " . nl2br(htmlspecialchars(trim($comments[$index]))) . "</li>";
                                                            }
                                                        }
                                                    */                                                
                                                            
                                                ?>
                                            </ul>
                                        </div>
                                    </div>

                                <?php else: ?>
                                    <p style="font-style: italic; color: #777;">No Comments for this report</p>
                                <?php endif; ?>

                                
                                <?php

                            }
                            elseif ($formnumber == 2 ) {
                                    echo "<p>".htmlspecialchars($subtitle)."<span style='font-size:1rem;font-weight:200'> [Custom Report]</style></p>" ;
                            }
                        ?>

                    </div>

                    <!-- PDF Buttons Heading Area Right -->
                    <div class="col-4">
                        <form id="generatePdfForm" method="POST" action="reportBooking_pdf.php" target="_blank">
                            <!-- Include frm and id -->
                            <input type="hidden" name="frm" value="<?= htmlspecialchars($formnumber) ?>">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($recordnumber) ?>">

                            <!-- Pass filter parameters for frm=2 (Advanced Filters) -->
                            <?php if ($formnumber == 2): ?>
                                <?php if (!empty($_POST['fromDate'])): ?>
                                    <input type="hidden" name="fromDate" value="<?= htmlspecialchars($_POST['fromDate']) ?>">
                                <?php endif; ?>
                                <?php if (!empty($_POST['toDate'])): ?>
                                    <input type="hidden" name="toDate" value="<?= htmlspecialchars($_POST['toDate']) ?>">
                                <?php endif; ?>
                                <?php if (!empty($_POST['includeDepots'])): ?>
                                    <?php foreach ($_POST['includeDepots'] as $depot): ?>
                                        <input type="hidden" name="includeDepots[]" value="<?= htmlspecialchars($depot) ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($_POST['excludeDepots'])): ?>
                                    <?php foreach ($_POST['excludeDepots'] as $depot): ?>
                                        <input type="hidden" name="excludeDepots[]" value="<?= htmlspecialchars($depot) ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($_POST['ehcLocations'])): ?>
                                    <?php foreach ($_POST['ehcLocations'] as $ehc_location): ?>
                                        <input type="hidden" name="ehcLocations[]" value="<?= htmlspecialchars($ehc_location) ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($_POST['status'])): ?>
                                    <?php foreach ($_POST['status'] as $status): ?>
                                        <input type="hidden" name="status[]" value="<?= htmlspecialchars($status) ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Pass parameters for frm=1 (Quick Reports) -->
                            <?php if ($formnumber == 1): ?>
                                <!-- Add specific parameters for quick reports -->
                                <input type="hidden" name="quickReportId" value="<?= htmlspecialchars($recordnumber) ?>">
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary">Generate PDF</button>
                        </form>
                    </div>

                </div>

                <hr>

                <?php

                    // Fetch data using constructed SQL
                    $data = fetchProductData($conn, $whereClause);

                    // Render report
                    $processedData = preprocessTotals($data);

                    // Render table
                    echo "<div class='table-responsive'>";

                        // Add debug output for SQL query
                        echo "<div class='alert alert-info'><strong>SQL Debug:</strong> $sqlDebug</div>";

                        echo "<div id='billing-container'>" ;
                            echo "<table id='billing-table' class='table table-bordered table-striped-rows'>";
                                echo '<thead class="table-title sticky-header">';
                                    echo "<tr>
                                        <th>id</th>
                                        <th>Time</th>
                                        <th>Depot</th>
                                        <th>PO</th>
                                        <th>Customer</th>
                                        <th>Country</th>
                                        <th>Destination</th>
                                        <th>Pallets</th>
                                        <th>Goods</th>
                                        <th>PCloud</th>
                                        <th>Status</th>
                                        <th>Vet</th>

                                        <th>Total</th>
                                        <th>Time</th>
                                        
                                    </tr>";

                                    //     <th>CSO</th>
                                    //<th>OV</th>
                                echo '</thead>';

                                foreach ($processedData as $date => $dateData) {
                                    if ($date === 'totals') continue;

                                    // Ensure $dateTotals is initialized
                                    $dateTotals = $dateData['totals'] ?? ['Total' => 0];

                                    $formatted_date = date("d M Y", strtotime($date));
                                    $day_only = date("D", strtotime($date));
                                    
                                    echo '<thead class="table-primary">';
                                        echo "<tr>
                                                <th colspan='12'>$formatted_date | $day_only</th>
                                                <th class='text-center'>{$dateTotals['Total']}</th>
                                                <th class='text-center'>&nbsp;</th>
                                            </tr>";

                                    //     <th class='text-center'>{$dateTotals['CSO']}</th>
                                    // <th class='text-center'>{$dateTotals['OV']}</th>
                                    echo '</thead>';

                                    foreach ($dateData as $depot => $depotData) {
                                        if ($depot === 'totals') continue;

                                        $depotTotals = $depotData['totals'] ?? ['Total' => 0];
                                        $depot_name = $depotData['depot_name'] ?? 'Unknown Depot';

                                        echo '<thead class="table-secondary">';
                                            echo "<tr>
                                                <th colspan='12'>$depot_name</th>

                                                <th class='text-center'>{$depotTotals['Total']}</th>
                                                <th class='text-center'>&nbsp;</th>
                                            </tr>";

                                            //         <th class='text-center'>{$depotTotals['CSO']}</th>
                                            //<th class='text-center'>{$depotTotals['OV']}</th>
                                        echo '</thead>';

                                        echo '<tbody>';
                                        foreach ($depotData['rows'] as $row) {

                                            //Check for country code
                                            if($row['destination_country_code']) {$country_code = $row['destination_country_code'];} else {$country_code = $row['destination_country_name'];} 
                                            
                                                // Get the timesheet count for the current product ID
                                                $timesheetCount = getTimesheetCount($conn, $row['id']);

                                            echo '<tr>';
                                                echo "<td>{$row['id']}</td>";
                                                echo "<td>{$row['expected_time']}</td>";
                                                echo "<td>{$row['depot_code']}</td>";
                                                echo "<td><a href='/wccms/recordEditv4.php?frm=1&id={$row['id']}' target='_blank'>{$row['name']}</a></td>";
                                                echo "<td>{$row['customer_name']}</td>";
                                                echo "<td>{$row['destination_country_name']} [{$country_code}]</td>";
                                                echo "<td>{$row['destination_customer']}</td>";
                                                echo "<td>{$row['pallet_count']} x {$row['pallet_type']}</td>";
                                                echo "<td>{$row['product_type']}</td>";
                                                echo "<td>{$row['pcloud']}</td>";
                                                echo "<td class='text-center'>{$row['status_name']}</td>";
                                                echo "<td>{$row['vet_name']}</td>";
                                                echo "<td class='text-center'>{$row['RowTotal']}</td>";
                                                echo "<td class='icon-actions'>" ;
                                                    echo "<button class='btn btn-link p-0' 
                                                        data-bs-toggle='modal' 
                                                        data-bs-target='#timeModal' 
                                                        data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' 
                                                        data-vet='" . htmlspecialchars($row['vet'], ENT_QUOTES) . "' 
                                                        data-user='" . htmlspecialchars($user["id"], ENT_QUOTES) . "' 
                                                        data-id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "'>";
                                                            echo "<i class='far fa-watch' aria-hidden='true' style='color:mediumblue;'></i>";
                                                    echo "</button>&nbsp;&nbsp;";

                                                    echo "<button class='btn btn-link p-0' 
                                                        data-bs-toggle='modal' 
                                                        data-bs-target='#timereportModal' 
                                                        data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' 
                                                        data-vet='" . htmlspecialchars($row['vet'], ENT_QUOTES) . "' 
                                                        data-user='" . htmlspecialchars($user["id"], ENT_QUOTES) . "' 
                                                        data-id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "'>";
                                                            echo "<i class='fad fa-file-chart-line' aria-hidden='true' style='color:purple;'></i>";
                                                    echo "</button>&nbsp;&nbsp;";

                                                    ?>

                                                    <style>
                                                        /* Button Styled as Icon */
                                                        .icon-number {
                                                            display: inline-flex;
                                                            justify-content: center;
                                                            align-items: center;
                                                            width: 20px; /* Adjust size */
                                                            height: 20px;
                                                        /* border-radius: 50%;*/
                                                        /*  background-color: #eee; *//* Button background */
                                                            color: #333; /* Text color */
                                                            font-size: 9px;
                                                            font-weight: 700;
                                                            text-decoration: none;
                                                        /* transition: background-color 0.3s; */
                                                            border: none;
                                                        }
                                                        .icon-number:hover {
                                                        /*  background-color: #0056b3; *//* Darker blue on hover */
                                                        }
                                                        .icon-actions {
                                                            display: flex;
                                                            justify-content: flex-start;  /* Left-align icons */
                                                            align-items: center;
                                                            gap: 8px; /* spacing between icons */
                                                        }
                                                        .icon-actions .btn {
                                                            padding: 0;
                                                        }
                                                        .invisible-flag {
                                                            opacity: 0;
                                                            pointer-events: none;
                                                        }
                                                    </style>

                                                <?php
                                                    $countcolor = '#eee' ;
                                                    //  error_log("timesheet count: ".$timesheetCount) ;
                                                    // Display the timesheet count
                                                    if($timesheetCount == 0 ) {
                                                        $countcolor='yellow';
                                                        //echo "<span style='color:".$countcolor.";'>{$timesheetCount}</span>";
                                                    } 
                                                    else 
                                                    {
                                                        $countcolor = '#eee' ;
                                                        //  echo "{$timesheetCount}";
                                                    } 

                                                    echo "<button class='icon-number'  style='background-color: ".$countcolor."'>" ;
                                                        echo "{$timesheetCount}"; 
                                                    echo "</button>" ;

                                                    // Snag Status
                                                    $snagCode = isset($row['snag_code']) ? (int)$row['snag_code'] : 0;
                                                    $snagCode = isset($row['snag_code']) ? (int)$row['snag_code'] : 0;
                                                    $snagText = htmlspecialchars($row['snag_text'] ?? '', ENT_QUOTES);
                                                    
                                                    if ($snagCode >= 1 && $snagCode <= 98) {
                                                        echo "<button 
                                                            class='btn btn-sm' 
                                                            style='color: red; font-size: .8rem;' 
                                                            data-bs-toggle='modal' 
                                                            data-bs-target='#snagModal'
                                                            data-snag='" . htmlspecialchars_decode($snagText, ENT_QUOTES) . "'
                                                            title='Click for Snag Details'>
                                                            <i class='fas fa-flag'></i>
                                                        </button>";
                                                    //   echo "<button class='btn btn-sm' title='Snag: {$snagText}' style='color: red; font-size: .8rem;'><i class='fas fa-flag'></i></button>";
                                                    } elseif ($snagCode === 99) {
                                                        echo "<button 
                                                            class='btn btn-sm' 
                                                            style='color: grey; font-size: .8rem;' 
                                                            data-bs-toggle='modal' 
                                                            data-bs-target='#snagModal'
                                                            data-snag='" . htmlspecialchars_decode($snagText, ENT_QUOTES) . "'
                                                            title='Click for Snag Details'>
                                                            <i class='fas fa-flag'></i>
                                                        </button>";
                                                    //    echo "<button class='btn btn-sm' title='Snag Resolved: {$snagText}' style='color: grey; font-size: .8rem;'><i class='fas fa-flag'></i></button>";
                                                    } else {
                                                        // Transparent placeholder to keep spacing
                                                        echo "<button class='btn btn-sm invisible-flag'><i class='fas fa-flag'></i></button>";
                                                    }
                                                // echo "#" ; // Count value in here

                                                echo "</td>";
                                            echo '</tr>';
                                        }
                                        echo '</tbody>';
                                    }
                                }

                            echo '</table>';
                        echo '</div>';
                    echo '</div>';
                ?>
            </section>
        </section>


    <!-- Snag Text Modal -->
    <div class="modal fade" id="snagModal" tabindex="-1" aria-labelledby="snagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="snagModalLabel">Snag Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="snagModalBody">
                    <!-- snag text will go here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const snagModal = document.getElementById('snagModal');
            snagModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const snagText = button.getAttribute('data-snag');
                const modalBody = document.getElementById('snagModalBody');
                modalBody.innerHTML = snagText || '<em>No snag text available.</em>';

            });
        });
    </script>

    <?php
        include("include/footer-code.php");
    ?>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
<!-- report_bookingv4 -->