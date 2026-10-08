<!-- START reportMasterv4 -->
<?php
/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
*/
?>

<!DOCTYPE html>
<head>
<?php
// Turn off error reporting
error_reporting(0);
// Turn on error reporting
// error_reporting(1);

include('setting/main-top-files.php'); // Added by salva TDR | 9.12.2022
include("include/header-code.php");

// Bring forward variables
$baseURL = $BASE_URL;

$formnumber = isset($_POST['frm']) ? securityCheck($_POST['frm'], 'number') : null;
$recordnumber = isset($_POST['id']) ? securityCheck($_POST['id'], 'number') : null;

if (!$formnumber) {
    die('Error in the form: Missing or invalid "frm" value. ['.$_POST['frm'].']');
}
if ($formnumber == 1 && !$recordnumber) {
    die('Error in the form: Missing "id" value for frm=1.');
}

?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>

<?php
include("include/header.php");
include("include/sidebar.php");
?>
<style>
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
$whereClause = ""; // Initialize as an empty string
// Example: /wccms/report_1T.php?frm=1&id=11&date_from=2025-09-08&date_to=2025-09-08&customer=ALL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data
    $fromDate = $_POST['fromDate'] ?? null;
    $toDate = $_POST['toDate'] ?? null;
    $action = $_POST['action'] ?? null;

    // Default to last Monday and this Friday if dates are blank
    if (empty($fromDate) || empty($toDate)) {
        $currentDay = date('w'); // Day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)

        // Calculate last Monday
        $fromDate = date('Y-m-d', strtotime('last Monday', strtotime('Sunday this week')));

        // Calculate this Friday
        $toDate = date('Y-m-d', strtotime('next Friday', strtotime('Sunday this week')));
    }

    // Debugging output to confirm dates
   // error_log("From Date: $fromDate, To Date: $toDate");

    // Validate input data
    if (!$fromDate || !$toDate) {
        die("Error: Missing required date fields.");
    }

    // Generate the dynamic WHERE clause
    $whereClause = "p.expected_date BETWEEN '$fromDate' AND '$toDate' AND p.showonweb = 'Yes' AND p.archived = 0";
} else {
    die("Invalid request.");
}

// Debugging the WHERE clause
// error_log("WHERE Clause: $whereClause");

// Fetch data using the WHERE clause
$data = fetchProductData($conn, $whereClause);

// Render the data
renderReport($data);

// Define default WHERE clause
//$whereClause = "p.showonweb = 'Yes' AND p.archived = 0 ";
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


            
        default:
            $whereClause = "p.expected_date >= CURDATE() AND p.expected_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)  AND p.showonweb = 'Yes'  AND p.archived = 0  "; 
            $subtitle  = "Default show a lot...." ;
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

    if (!empty($_POST['customer'])) {
        $customer = array_map('intval', $_POST['customer']);
        $filters[] = "p.account IN (" . implode(",", $customer) . ")";
        $customerNames = getNamesFromIds($conn, 'npe_customer', 'id', 'name', $customer);
        $subtitleParts[] = "Customer: " . implode(", ", $customerNames);
    }


    if (!empty($_POST['vetName'])) {
        $vetName = array_map('intval', $_POST['vetName']);
        $filters[] = "p.vet IN (" . implode(",", $vetName) . ")";
        $ehcNames = getNamesFromIds($conn, 'npe_vet', 'id', 'name', $ehcLocations);
        $subtitleParts[] = "Vet Name: " . implode(", ", $vetName);
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

        $whereClause = $whereClause. " AND `p`.`showonweb` = 'Yes' AND `p`.`archived` = 0 " ;

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
            <h1>North Park TANKER Exports</h1>
           <?php
                if($formnumber == 1 ){           
                    echo "<h3>".htmlspecialchars($subtitle)." <span style='font-weight:200'>[Quick Report]</style></h3>" ;
                }
                elseif ($formnumber == 2 ) {
                        echo "<p>".htmlspecialchars($subtitle)."<span style='font-weight:200'> [Custom Report]</style></p>" ;
                }
            ?>
            <?php
              //  echo "<p> Debug on/off: " . htmlspecialchars($prefs["prefCMSDebugOn"]) . "</p>";
            ?>
        </div>
        <div class="col-4">
        <form id="generatePdfForm" method="POST" action="reportBookingT_pdf.php" target="_blank">
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

                <?php if (!empty($_POST['customer'])): ?>
                    <?php foreach ($_POST['customer'] as $customer): ?>
                        <input type="hidden" name="customer[]" value="<?= htmlspecialchars($customer) ?>">
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

            <button type="submit" 
                class="btn btn-primary">
                Weekly PDF
            </button>

            <button type="submit" 
                class="btn btn-secondary"
                formaction="reportBookingT_preload_pdf.php">
                PreLoad PDF
            </button>

            <button type="submit"
                    class="btn btn-success"
                    formaction="reportBookingT_csv.php"
                    formtarget="_self">
                Export CSV
            </button>
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
        echo "<thead class='table-title sticky-header'>";
            echo "<tr>
                <th>Time</th>
                <th>Depot</th>
                <th>PO</th>
                <th>Customer</th>
                <th>Country</th>
                <th>EHC</th>
                <th>Pallets</th>
                <th>Tanker</th>
                <th>PCloud</th>
                <th>Status</th>
                <th>Vet</th>
                <th>CSO</th>
                <th>OV</th>
                <th>Total</th>
            </tr>";
        echo '</thead>';

    foreach ($processedData as $date => $dateData) {
        if ($date === 'totals') continue;

        $formatted_date = date("d M Y", strtotime($date));
        $day_only = date("D", strtotime($date));
        
        echo '<thead class="table-primary">';
            echo "<tr>
            <th colspan='11'>$formatted_date | $day_only</th><th class='text-center'>{$dateTotals['CSO']}</th><th class='text-center'>{$dateTotals['OV']}</th><th class='text-center'>{$dateTotals['Total']}</th>
            </tr>";
        echo '</thead>';

        foreach ($dateData as $depot => $depotData) {
            if ($depot === 'totals') continue;

            $depotTotals = $depotData['totals'];
            $depot_name = $depotData['depot_name'] ?? 'Unknown Depot';

            echo '<thead class="table-secondary">';
                echo "<tr><th>&nbsp;</th><th colspan='10'>$depot_name</th><th class='text-center'>{$depotTotals['CSO']}</th><th class='text-center'>{$depotTotals['OV']}</th><th class='text-center'>{$depotTotals['Total']}</th></tr>";
            echo '</thead>';

            echo '<tbody>';
            foreach ($depotData['rows'] as $row) {

                //Check for country code
                if($row['destination_country_code']) {$country_code = $row['destination_country_code'];} else {$country_code = $row['destination_country_name'];} 
                
                echo '<tr>';
                echo "<td>{$row['expected_time']}</td>";
                echo "<td>{$row['depot_code']}</td>";
                echo "<td><a href='/wccms/recordEditv4.php?frm=1&id={$row['id']}' target='_blank'>{$row['name']}</a></td>";
                echo "<td>{$row['customer_name']}</td>";
                echo "<td>{$row['destination_country_name']} [{$country_code}]</td>";
                echo "<td>{$row['ehc_ref']}</td>";
                echo "<td>{$row['pallet_count']} x {$row['pallet_type']}</td>";
                echo "<td>{$row['tanker']}</td>";
                echo "<td>{$row['pcloud']}</td>";
                echo "<td class='text-center'>{$row['status_name']}</td>";
                echo "<td>{$row['vet_name']}</td>";
                echo "<td class='text-center'>{$row['CSO']}</td>";
                echo "<td class='text-center'>{$row['OV']}</td>";
                echo "<td class='text-center'>{$row['RowTotal']}</td>";
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

<?php
    include("include/footer-code.php");
?>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>