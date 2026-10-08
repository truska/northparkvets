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

    // Moved from main-top-files until we get it working as effecting other live tasks  // MOVED BACK
    //error_log("Including controllers/timeadmin.php");
    //require_once ("controllers/timeAdmin.php");

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

// Define default WHERE clause
$whereClause = "p.showonweb = 'Yes' AND p.archived = 0 ";
$currweeknum = date("W");
// Process frm logic
if ($formnumber == 1) {
    // Hardcoded SQL based on id
    switch ($recordnumber) {
        case 1:
            $whereClause = "p.weeknum = $currweeknum  AND p.showonweb = 'Yes'  AND p.archived = 0 " ;             // This week
            $subtitle  = "This Week [Week: ".$currweeknum."]" ;
            break;
        case 2:
            $whereClause = "p.weeknum = " . ($currweeknum + 1 )." AND p.showonweb = 'Yes' AND p.archived = 0 " ;     // Next week
            $subtitle  = "Next Week [Week: ".($currweeknum + 1 )."]" ;
            break;
        case 3:
            $whereClause = "p.weeknum = " . ($currweeknum - 1)." AND p.showonweb = 'Yes' AND p.archived = 0  " ;    // Last Week
            $subtitle  = "Last Week [Week: ".($currweeknum - 1)."]" ;
            break;
        case 4:
            $whereClause = "p.status < 4 AND p.showonweb = 'Yes' AND p.archived = 0  " ;     // less than approved
            $subtitle  = "Status < 4 ??" ;
            break;
        case 5:
            $whereClause = "p.depot = 11 AND p.showonweb = 'Yes' AND p.archived = 0  " ;     // Heathfield
            $subtitle  = "Heathfield Depot" ;
            break;
        case 6:
            $whereClause = "p.depot = 13 AND p.showonweb = 'Yes' AND p.archived = 0 " ;     // North Tawton
            $subtitle  = "North Tawton Depot" ;
            break;
        case 7: // Today's report
            $whereClause = "p.expected_date = CURDATE() AND p.showonweb = 'Yes' AND p.archived = 0";
            $subtitle = "Today's Report [" . date("d M Y") . "]";
            break;
        
        case 8: // Yesterday's report
            $whereClause = "p.expected_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
            $subtitle = "Yesterday's Report [" . date("d M Y", strtotime("yesterday")) . "]";
            break;

        case 9: // Tomorrow's report
            $whereClause = "p.expected_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
            $subtitle = "Tomorrow's Report [" . date("d M Y", strtotime("tomorrow")) . "]";
            break;            
            
        default:
            $whereClause = "p.weeknum = " . ($currweeknum + 1 )." AND p.showonweb = 'Yes' AND p.archived = 0 " ;     // Next week
            $subtitle  = "Next Week [Week: ".($currweeknum + 1 )."]" ;
    }
} elseif ($formnumber == 2) {

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

$sortClause = !empty($sortClauses) ? "ORDER BY " . implode(", ", $sortClauses) : '';

// Add debug info for WHERE clause
$sqlDebug = "SELECT * FROM products WHERE $whereClause $sortClause";
?>
<section id="main-content">
    <section class="wrapper site-min-height">

    <div class="row">
        <div class="col-8">
            <h1>North Park Exports</h1>
           <?php
                if($formnumber == 1 ){           
                    echo "<h3>".htmlspecialchars($subtitle)." <span style='font-size:1rem;font-weight:200'>[Quick Report]</style></h3>" ;
                }
                elseif ($formnumber == 2 ) {
                        echo "<p>".htmlspecialchars($subtitle)."<span style='font-size:1rem;font-weight:200'> [Custom Report]</style></p>" ;
                }
            ?>
            <?php
              //  echo "<p> Debug on/off: " . htmlspecialchars($prefs["prefCMSDebugOn"]) . "</p>";
            ?>
        </div>
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

echo "<table class='table table-bordered table-striped-rows'>";
    echo '<thead class="table-title">';
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

    //         <th>CSO</th>
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
            //  echo "<td class='text-center'>{$row['CSO']}</td>";
            //  echo "<td class='text-center'>{$row['OV']}</td>";
                echo "<td class='text-center'>{$row['RowTotal']}</td>";
                echo "<td class='text-center'>" ;
                    echo "<button class='btn btn-link p-0' 
                        data-bs-toggle='modal' 
                        data-bs-target='#timeModal' 
                        data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' 
                        data-vet='" . htmlspecialchars($row['vet'], ENT_QUOTES) . "' 
                        data-user='" . htmlspecialchars($user["id"], ENT_QUOTES) . "' 
                        data-id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "'>";
                            echo "<i class='far fa-watch' aria-hidden='true'></i>";
                    echo "</button> | ";

                    echo "<button class='btn btn-link p-0' 
                        data-bs-toggle='modal' 
                        data-bs-target='#timereportModal' 
                        data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' 
                        data-vet='" . htmlspecialchars($row['vet'], ENT_QUOTES) . "' 
                        data-user='" . htmlspecialchars($user["id"], ENT_QUOTES) . "' 
                        data-id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "'>";
                            echo "<i class='fad fa-file-chart-line' aria-hidden='true'></i>";
                    echo "</button> | ";

                    echo "#" ; // Count value in here

                echo "</td>";
            echo '</tr>';
        }
        echo '</tbody>';
    }
}

echo '</table>';
echo '</div>';
?>
    </section>
</section>

<?php
include("include/footer-code.php");
?>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
<!-- report_bookingv4 -->