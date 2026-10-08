<!-- START exceptionsv4 -->

<?php

    include ('setting/main-top-files.php');

    $formnumber = isset($_GET['frm']) ? securityCheck($_GET['frm'], 'number') : 0;
    $recordnumber = isset($_GET['id']) ? securityCheck($_GET['id'], 'number') : 0;

    // Handle duplicate timesheet actions before any page output
    if (isset($_POST['duplicateTimeAction']) && isset($_POST['timesheetid'])) {

        $timesheetid = securityCheck($_POST['timesheetid'], 'number');
        $action = securityCheck($_POST['duplicateTimeAction']);

        if ($timesheetid > 0) {

            if ($action == 'approve') {
                $stmt = $conn->prepare("UPDATE `npe_timesheets` SET `approved` = 1 WHERE `id` = ?");
                $stmt->bind_param("i", $timesheetid);
                $stmt->execute();
                $stmt->close();
            }

            if ($action == 'archive') {
                $stmt = $conn->prepare("UPDATE `npe_timesheets` SET `archived` = 1 WHERE `id` = ?");
                $stmt->bind_param("i", $timesheetid);
                $stmt->execute();
                $stmt->close();
            }
        }

        header("Location: ".$_SERVER['PHP_SELF']."?frm=".$formnumber."&id=".$recordnumber);
        exit;
    }
?>


<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(0);
        // Turn on error reporting
        // error_reporting(1);

      //  include ('setting/main-top-files.php'); // loads Session, Datebase Connectons, functions and more
        include("include/header-code.php");

        //Requires `frm` and `id` parameteres on url - if not needed comment out next lines
        /*
                if (!$formnumber = securityCheck($_GET['frm'], 'number')) {
                    die('Error in the form'); // If the user try to insert something different from a number, we kill the script
                }
                if (!$recordnumber = securityCheck($_GET['id'], 'number')) {
                    die('Error in the id'); // If the user try to insert something different from a number, we kill the script
                }
        */ 
    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php
        include("include/header.php"); // Added by salva TDR | 2.12.2022
        include("include/sidebar.php");

        // Function to fetch lookup names based on table, ID, and field
        function getLookupName($conn, $table, $idField, $idValue, $nameField) {
            if (!$idValue) return "Not Assigned"; // Handle empty values
            $stmt = $conn->prepare("SELECT $nameField FROM $table WHERE $idField = ?");
            $stmt->bind_param("i", $idValue);
            $stmt->execute();
            $stmt->bind_result($name);
            $stmt->fetch();
            $stmt->close();
            return $name ? htmlspecialchars($name) : "Unknown"; // Return name or default
        }
    ?>



    <?php
        // Handle duplicate timesheet actions
        if (isset($_POST['duplicateTimeAction']) && isset($_POST['timesheetid'])) {

            $timesheetid = securityCheck($_POST['timesheetid'], 'number');
            $action = securityCheck($_POST['duplicateTimeAction']);

            if ($timesheetid > 0) {

                if ($action == 'approve') {
                    $stmt = $conn->prepare("UPDATE `npe_timesheets` SET `approved` = 1 WHERE `id` = ?");
                    $stmt->bind_param("i", $timesheetid);
                    $stmt->execute();
                    $stmt->close();
                }

                if ($action == 'archive') {
                    $stmt = $conn->prepare("UPDATE `npe_timesheets` SET `archived` = 1 WHERE `id` = ?");
                    $stmt->bind_param("i", $timesheetid);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            header("Location: ".$_SERVER['PHP_SELF']."?frm=".$formnumber."&id=".$recordnumber);
            exit;
        }
    ?>

    <!--/ Any Custom styles for alignment and indentation -->
    <style>
        .card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
    </style>

    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="row">

                <div class="col-8 mainbody">
                    <?php 
                        echo "<h1>Exceptions Report</h1>" ;
                        //   echo "<p> Debug on/off: ".$prefs["prefCMSDebugOn"]."</p>" ;
                    ?>
                </div>
                <div class="col-4">
                    <h3>Area for more info</h3>
                </div>

                <!-- 1. Loading Date / Week number Not matching -->

                <?php

                    // Check 1
                    // mismatches between Expected Loading date and Week Number
                    $where = "WEEK(products.expected_date, 1) != products.weeknum AND `showonweb` = 'Yes' AND `archived` = 0 ;";
                    $sql = "SELECT COUNT(*) AS `date_mismatch` FROM products WHERE WEEK(products.expected_date, 1) != products.weeknum AND `showonweb` = 'Yes' AND `archived` = 0 ";
                    $result = $conn->query($sql);

                    // Fetch the count
                    $date_mismatch = 0;
                    if ($result) {
                        $row = $result->fetch_assoc();
                        $date_mismatch = $row['date_mismatch'];
                    }
                
                ?>
                    <div class="col-12 col-md-4 mb-4">
                    
                        <div class="card text-center">

                            <?php 
                                // Determine the background color based on the number
                                $bgColor = ($date_mismatch > 0) ? 'bg-danger' : 'bg-success';
                            ?>
                            <div class="card-header text-white <?php echo $bgColor; ?>">
                                <h2 class="m-0" style="font-size:4rem;"><?php echo $date_mismatch; ?></h2>
                            </div>


                            <div class="card-body">
                                
                                <h5 class="card-title">Loading Date / Week Number</h5>
                                <h6 class="card-subtitle mb-2 text-muted">Not Matching</h6>
                                <?php
                                    if($date_mismatch > 0) {
                                        echo "<p class='card-text'>A number of Consignements have a mismatch between the Expected Date and the Week Number<br>" ;
                                        $buttonstatus = '' ;
                                    }
                                    else
                                    {
                                        echo "All Good - All consignments Dates and week numbers mact up</p>";
                                        $buttonstatus = 'disabled' ;
                                    }
                                ?>

                                <span style="font-size:10px;"><em>[ <?php echo $where ;?> ]</em></style></p>                                
                                <a href="#" class="btn btn-primary w-100 <?php echo $buttonstatus;?>" data-bs-toggle="modal" data-bs-target="#discrepancyModal">See List</a>

                            </div>
                        </div>
                    </div>

                <?php

                    // SQL query to fetch the list of mismatched products
                    $sql = "SELECT id, name, expected_date, WEEK(expected_date, 1) AS calculated_week, weeknum, vet, status 
                            FROM products 
                                WHERE ".$where ;

                    $result = $conn->query($sql);

                    // Store the fetched records in an array
                    $discrepancies = [];
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $discrepancies[] = $row;
                        }
                    }

                    // Close the connection
                    //$conn->close();
                ?>

                <!-- 2. No Vat on Time Sheet -->

                <?php
                    // Check 2
                    // Time sheets with no vet allocated
                    $where = "`vet` < 1 AND `showonweb` = 'Yes' AND `archived` = 0 ";
                    $sql = "SELECT COUNT(*) AS `novet2` FROM `npe_timesheets` WHERE `vet` < 1 AND `showonweb` = 'Yes' AND `archived` = 0 ";
                    //   echo "<p>check 2 SQL: ".$sql."</p>" ;
                    $result = $conn->query($sql) ;
                
                    // Fetch the count
                    $novet2 = 0;
                    if ($result) {
                        if ($row = $result->fetch_assoc()) {
                            $novet2 = $row['novet2'];
                        } else {
                            die("No rows fetched.");
                        }
                    } else {
                        die("Query failed.");
                    }

                ?>

                    <div class="col-12 col-md-4 mb-4">
                        <div class="card text-center">
                
                            <?php 
                                // Determine the background color based on the number
                                $bgColor = ($novet2 > 0) ? 'bg-danger' : 'bg-success';
                            ?>
                            <div class="card-header text-white <?php echo $bgColor; ?>">
                                <h2 class="m-0" style="font-size:4rem;"><?php echo $novet2; ?></h2>
                            </div>

                            <div class="card-body">

                                <h5 class="card-title">No Vet</h5>
                                <h6 class="card-subtitle mb-2 text-muted">On Time Sheet</h6>
                                <?php
                                    if($novet2 > 0) {
                                        echo "<p class='card-text'>A number of Time Sheet entries do NOT have a Vet allocated to them<br>" ;
                                        $buttonstatus = '' ;
                                    }
                                    else
                                    {
                                        echo "ALL GOOD - ALl Timesheets have an allocated Vet</p>" ;
                                        $buttonstatus = 'disabled' ;
                                    }
                                ?>

                                <span style="font-size:10px;"><em>[ <?php echo $where ;?> ]</em></style></p>
                                <a href="#" class="btn btn-primary w-100 <?php echo $buttonstatus ;?>" data-bs-toggle="modal" data-bs-target="#timesheetModal">See List</a>
                            </div>
                        </div>
                    </div>

                <?php
                    // SQL query to fetch the list of time sheets missing a vet
                    $sql = "SELECT id, name, vet, productid, date, notes 
                            FROM npe_timesheets 
                            WHERE ".$where ;


                    $result = $conn->query($sql);

                    // Store the fetched records in an array
                    $timesheet_discrepancies = [];
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $timesheet_discrepancies[] = $row;
                        }
                    }
                ?>

                <!-- 3. No Vet on Listed Live Consignements-->

                <?php
                    // Check 3
                    // Consignment that is past and not rolled with no vet 
                    $where = "`expected_date` < NOW() AND `status` > 0 AND `status` < 9 AND Vet < 1 AND `showonweb` = 'Yes' AND `archived` = 0 ;" ;
                    $sql = "SELECT COUNT(*) AS `novet3` FROM `products` WHERE ".$where ;
                    $result = $conn->query($sql);

                    // Fetch the count
                    $novet3 = 0;
                    if ($result) {
                        $row = $result->fetch_assoc();
                        $novet3 = $row['novet3'];
                    }
                ?>
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card text-center">

                            <?php 
                                // Determine the background color based on the number
                                $bgColor = ($novet3 > 0) ? 'bg-danger' : 'bg-success';
                            ?>
                            <div class="card-header text-white <?php echo $bgColor; ?>">
                                <h2 class="m-0" style="font-size:4rem;"><?php echo $novet3; ?></h2>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">No Vet</h5>
                                <h6 class="card-subtitle mb-2 text-muted">On Listed Live Consignments</h6>
                                <?php
                                if($novet3 > 0) {
                                    echo "<p class='card-text'>A number of Consignements that are live do NOT have a Vet allocated to them<br>" ;
                                    $buttonstatus = '' ;
                                }
                                else
                                {
                                    echo "ALL GOOD - All Live Consignments have an allocated Vet</p>" ;
                                    $buttonstatus = 'disabled' ;
                                }
                                ?>
                                <span style="font-size:10px;"><em>[ <?php echo $where ;?> ]</em></style></p>
                                <a href="#" class="btn btn-primary w-100 <?php echo $buttonstatus ; ?>" data-bs-toggle="modal" data-bs-target="#pastDueModal">See List</a>
                            </div>
                        </div>
                    </div>
              
                <?php
                    // SQL query to fetch past due consignments with no vet
                    $sql = "SELECT id, name, vet, expected_date, status, depot, account 
                            FROM products 
                            WHERE ". $where ;

                    $result = $conn->query($sql);

                    // Store the fetched records in an array
                    $past_due_discrepancies = [];
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $past_due_discrepancies[] = $row;
                        }
                    }
                ?>

                <!-- 4. Live Resolved Snags -->

                <?php
                    // Check 4: Snag Issues (snag_status 1-98)
                    $where = "p.snag_status > 0 AND s.code > 0 AND s.code < 99 AND p.showonweb = 'Yes' AND p.archived = 0";

                    $sql = "SELECT COUNT(*) AS snag_issues 
                            FROM products p 
                            LEFT JOIN npe_snag_status s ON p.snag_status = s.id 
                            WHERE $where";
                    
                    $result = $conn->query($sql);
                    $snag_issues = 0;
                    if ($result && $row = $result->fetch_assoc()) {
                        $snag_issues = $row['snag_issues'];
                    }
                ?>

                <div class="col-12 col-md-4 mb-4">
                    <div class="card text-center">
                        <?php $bgColor = ($snag_issues > 0) ? 'bg-danger' : 'bg-success'; ?>
                        <div class="card-header text-white <?php echo $bgColor; ?>">
                            <h2 class="m-0" style="font-size:4rem;"><?php echo $snag_issues; ?></h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Snag Records</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Red Flag Items</h6>
                            <?php
                                if ($snag_issues > 0) {
                                    echo "<p class='card-text'>There are consignments with snag issues flagged in the system.</p>";
                                    $buttonstatus = '';
                                } else {
                                    echo "All Good - No active snag issues</p>";
                                    $buttonstatus = 'disabled';
                                }
                            ?>
                            <span style="font-size:10px;"><em>[ <?php echo $where; ?> ]</em></span>

                            <!-- Force button to appear on new line with margin -->
                            <div class="mt-3">
                                <a href="#" class="btn btn-primary w-100 <?php echo $buttonstatus; ?>" data-bs-toggle="modal" data-bs-target="#snagModal">See List</a>
                            </div>
                        </div>

                    </div>
                </div>

                <?php
                    // Snag Details Query
                    $sql = "SELECT 
                        p.id, 
                        p.name AS product_name,
                        p.load_date, 
                        p.account, 
                        p.depot,
                        p.snag_title, 
                        p.status,
                        s.name AS snag_status_name,
                        d.name AS depot_name,
                        c.name AS customer_name
                    FROM products p
                    LEFT JOIN npe_snag_status s ON p.snag_status = s.id
                    LEFT JOIN npe_depot d ON p.depot = d.id
                    LEFT JOIN npe_customer c ON p.account = c.id
                    WHERE $where";
                    $result = $conn->query($sql);

                    $snag_discrepancies = [];
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $snag_discrepancies[] = $row;
                        }
                    }
                ?>

                <!-- 5. Resolved Snags -->

                <?php
                    // Check 5: Resolved Snags (npe_snag_status.code = 99)
                    $where = "p.snag_status > 0 AND s.code = 99 AND p.showonweb = 'Yes' AND p.archived = 0";
                    $sql = "SELECT COUNT(*) AS resolved_snags 
                            FROM products p 
                            LEFT JOIN npe_snag_status s ON p.snag_status = s.id 
                            WHERE $where";

                    $resolved_snags = 0;
                    $result = $conn->query($sql);
                    if ($result && $row = $result->fetch_assoc()) {
                        $resolved_snags = $row['resolved_snags'];
                    }
                ?>

                <div class="col-12 col-md-4 mb-4">
                    <div class="card text-center">
                        <?php 
                            $bgColor = ($resolved_snags > 0) ? 'bg-warning' : 'bg-light'; 
                            $buttonstatus = ($resolved_snags > 0) ? '' : 'disabled';
                        ?>
                        <div class="card-header text-dark <?php echo $bgColor; ?>">
                            <h2 class="m-0" style="font-size:4rem;"><?php echo $resolved_snags; ?></h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Resolved Snags</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Flagged as Fixed</h6>
                            <?php
                                if ($resolved_snags > 0) {
                                    echo "<p class='card-text'>Consignments that were flagged with a snag and are now resolved.</p>";
                                } else {
                                    echo "All Good - No resolved snags available</p>";
                                }
                            ?>
                            <span style="font-size:10px;"><em>[ <?php echo $where; ?> ]</em></span>
                            <div class="mt-3">
                                <a href="#" class="btn btn-primary w-100 <?php echo $buttonstatus; ?>" data-bs-toggle="modal" data-bs-target="#resolvedSnagModal">See List</a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                    $sql = "SELECT 
                                p.id, 
                                p.name AS product_name,
                                p.load_date, 
                                p.account, 
                                p.depot,
                                p.snag_title, 
                                p.status,
                                s.name AS snag_status_name,
                                d.name AS depot_name
                            FROM products p
                            LEFT JOIN npe_snag_status s ON p.snag_status = s.id
                            LEFT JOIN npe_depot d ON p.depot = d.id
                            WHERE $where";

                    $resolved_snag_discrepancies = [];
                    $result = $conn->query($sql);
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $resolved_snag_discrepancies[] = $row;
                        }
                    }
                ?>


            <!-- 6. Duplicate Time Sheet Names -->

            <?php
                $where = "`name` IN (
                                SELECT `name`
                                FROM `npe_timesheets`
                                WHERE `name` IS NOT NULL
                                AND `name` != ''
                                AND `approved` = 0
                                AND `archived` = 0
                                GROUP BY `name`
                                HAVING COUNT(*) > 1
                            )
                            AND `name` NOT LIKE '%admin%'
                            AND `approved` = 0
                            AND `archived` = 0";

                $sql = "SELECT COUNT(*) AS duplicate_time_records
                        FROM `npe_timesheets`
                        WHERE ".$where;

                $result = $conn->query($sql);

                $duplicate_time_records = 0;
                if ($result && $row = $result->fetch_assoc()) {
                    $duplicate_time_records = $row['duplicate_time_records'];
                }
            ?>

            <div class="col-12 col-md-4 mb-4">
                <div class="card text-center">

                    <?php
                        $bgColor = ($duplicate_time_records > 0) ? 'bg-danger' : 'bg-success';
                        $buttonstatus = ($duplicate_time_records > 0) ? '' : 'disabled';
                    ?>

                    <div class="card-header text-white <?php echo $bgColor; ?>">
                        <h2 class="m-0" style="font-size:4rem;"><?php echo $duplicate_time_records; ?></h2>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">Duplicate Time Records</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Same Name Used More Than Once</h6>

                        <?php
                            if ($duplicate_time_records > 0) {
                                echo "<p class='card-text'>There are duplicate time sheet records that need reviewed.</p>";
                            } else {
                                echo "<p>All Good - No unapproved duplicate time sheet names found</p>";
                            }
                        ?>

                        <span style="font-size:10px;"><em>[ <?php echo $where; ?> ]</em></span>

                        <div class="mt-3">
                            <a href="#" class="btn btn-primary w-100 <?php echo $buttonstatus; ?>" data-bs-toggle="modal" data-bs-target="#duplicateTimeRecordsModal">See List</a>
                        </div>
                    </div>

                </div>
            </div>

            <?php
                $sql = "SELECT
                id,
                name,
                productid,
                user,
                vet,
                date,
                created
                FROM `npe_timesheets`
                WHERE ".$where."
                ORDER BY `name`, `date`, `created`";

                $duplicate_time_discrepancies = [];
                $result = $conn->query($sql);

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $duplicate_time_discrepancies[] = $row;
                    }
                }
            ?>




            </div>

            <?php
                include ("include/footer-code.php");
                include ("include-tinymce.php");
            ?>

        </section>
    </section>

    <!-- Bootstrap Modal Check 1 (Loading Date / Week Number -->
    <div class="modal fade" id="discrepancyModal" tabindex="-1" aria-labelledby="discrepancyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="discrepancyModalLabel">Discrepancy List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PO</th>
                                <th>Expected Date</th>
                                <th>Expected Week</th>
                                <th>Week Num</th>
                                <th>Vet</th>
                                <th>Status</th>
                            </tr>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($discrepancies as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <a href="<?php echo $baseURL."/wccms/recordEditv4.php?frm=1&id=".$row['id'];?>" target="_blank">
                                            <?php echo htmlspecialchars($row['name']) ; ?>
                                        </a>
                                    </td>
                                    <td><?php echo date("d-m-Y", strtotime($row['expected_date'])); ?></td>
                                    <td class="text-danger fw-bold"><?php echo htmlspecialchars($row['calculated_week']); ?></td>  <!-- Highlight -->

                                    <td><?php echo htmlspecialchars($row['weeknum']); ?></td>
                                    <td><?php echo getLookupName($conn, 'npe_vet', 'id', $row['vet'], 'name'); ?></td>
                                
                                    <td><?php echo getLookupName($conn, 'npe_status', 'id', $row['status'], 'name'); ?></td> <!-- Status -->
                                <!--  <td><?php echo htmlspecialchars($row['vet']); ?></td> -->
                                <!--   <td><?php echo htmlspecialchars($row['status']); ?></td> -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Check 2 (No Vet on Time Sheets) -->
    <div class="modal fade" id="timesheetModal" tabindex="-1" aria-labelledby="timesheetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="timesheetModalLabel">Time Sheets Missing Vet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PO</th>
                                <th>Vet</th>
                                <th>Product ID</th>
                                <th>Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timesheet_discrepancies as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <a href="<?php echo $baseURL."/wccms/recordEditv4.php?frm=13&id=".$row['id'];?>" target="_blank">
                                            <?php echo htmlspecialchars($row['name']) ; ?>
                                        </a>
                                    </td>

                                    <td><?php echo getLookupName($conn, 'npe_vet', 'id', $row['vet'], 'name'); ?></td> <!-- Vet Name -->
                                    <td><?php echo htmlspecialchars($row['productid']); ?></td>
                                    <td><?php echo date("D d-m-Y", strtotime(htmlspecialchars($row['date']))); ?></td>
                                    <td><?php echo htmlspecialchars(strip_tags($row['notes'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Check 3 (Past Due Consignments Without Vet) -->
    <div class="modal fade" id="pastDueModal" tabindex="-1" aria-labelledby="pastDueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pastDueModalLabel">Past Due Consignments Without Vet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PO</th>
                                <th>Vet</th>
                                <th>Expected Date</th>
                                <th>Status</th>
                                <th>Depot</th>
                                <th>Account</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($past_due_discrepancies as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <a href="<?php echo $baseURL."/wccms/recordEditv4.php?frm=1&id=".$row['id'];?>" target="_blank">
                                            <?php echo htmlspecialchars($row['name']) ; ?>
                                        </a>
                                    </td>
                                    <td><?php echo getLookupName($conn, 'npe_vet', 'id', $row['vet'], 'name'); ?></td> <!-- Vet Name -->
                                    <!-- <td><?php echo htmlspecialchars($row['vet']); ?></td> -->
                                    <td><?php echo date("d-M-Y", strtotime($row['expected_date'])); ?></td>
                                    <td><?php echo getLookupName($conn, 'npe_status', 'id', $row['status'], 'name'); ?></td> <!-- Status -->
                                    <td><?php echo getLookupName($conn, 'npe_depot', 'id', $row['depot'], 'name'); ?></td> <!-- Depot -->
                                    <!--<td><?php echo htmlspecialchars($row['depot']); ?></td>-->
                                    <td><?php echo getLookupName($conn, 'npe_customer', 'id', $row['account'], 'name'); ?></td> <!-- Account -->
                                    
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Check 4 (Snag Records Live) -->
    <div class="modal fade" id="snagModal" tabindex="-1" aria-labelledby="snagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="snagModalLabel">Snag Reported Consignments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PO</th>
                                <th>Depot</th> <!-- NEW -->
                                <th>Load Date</th>
                                <th>Account</th>
                                <th>Snag Title</th>
                                <th>Snag Status</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($snag_discrepancies as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <a href="<?php echo $baseURL . "/wccms/recordEditv4.php?frm=1&id=" . $row['id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($row['product_name']); ?>
                                        </a>
                                    </td>

                                    <td><?php echo htmlspecialchars($row['depot_name']); ?></td>

                                    <td>
                                        <?php 
                                            if (!empty($row['load_date']) && $row['load_date'] !== '0000-00-00') {
                                                echo date("d-m-Y", strtotime($row['load_date']));
                                            } else {
                                                echo "Not Loaded";
                                            }
                                        ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <!-- <td><?php echo getLookupName($conn, 'npe_customer', 'id', $row['account'], 'name'); ?></td> -->
                                    <td><?php echo htmlspecialchars($row['snag_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['snag_status_name']); ?></td>
                                    <td><?php echo getLookupName($conn, 'npe_status', 'id', $row['status'], 'name'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Check 5 (Snag Records Resolved) -->
    <div class="modal fade" id="resolvedSnagModal" tabindex="-1" aria-labelledby="resolvedSnagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resolvedSnagModalLabel">Resolved Snag Consignments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PO</th>
                                <th>Depot</th>
                                <th>Load Date</th>
                                <th>Account</th>
                                <th>Snag Title</th>
                                <th>Snag Status</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resolved_snag_discrepancies as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <a href="<?php echo $baseURL . "/wccms/recordEditv4.php?frm=1&id=" . $row['id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($row['product_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['depot_name']); ?></td>
                                    <td>
                                        <?php 
                                            echo (!empty($row['load_date']) && $row['load_date'] !== '0000-00-00') 
                                                ? date("d-m-Y", strtotime($row['load_date'])) 
                                                : 'Not Loaded'; 
                                        ?>
                                    </td>
                                    <td><?php echo getLookupName($conn, 'npe_customer', 'id', $row['account'], 'name'); ?></td>
                                    <td><?php echo htmlspecialchars($row['snag_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['snag_status_name']); ?></td>
                                    <td><?php echo getLookupName($conn, 'npe_status', 'id', $row['status'], 'name'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Bootstrap Modal for Check 6 (Duplicate Time Records) -->
    <div class="modal fade" id="duplicateTimeRecordsModal" tabindex="-1" aria-labelledby="duplicateTimeRecordsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="duplicateTimeRecordsModalLabel">Duplicate Time Records</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <table class="table table-striped table-sm">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Product ID</th>
                            <th>User</th>
                            <th>Vet</th>
                            <th>Date</th>
                            <th>Created</th>
                            <th style="width:220px;">Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($duplicate_time_discrepancies as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>

                                    <td>
                                        <a href="<?php echo $baseURL."/wccms/recordEditv4.php?frm=13&id=".$row['id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </a>
                                    </td>

                                    <td><?php echo htmlspecialchars($row['productid']); ?></td>

                                    <td><?= getLookupName($conn, 'cms_adminlogin', 'id', $row['user'], 'username'); ?></td>

                                    <td><?php echo getLookupName($conn, 'npe_vet', 'id', $row['vet'], 'name'); ?></td>

                                    <td><?php echo htmlspecialchars($row['date']); ?></td>

                                    <td><?php echo htmlspecialchars($row['created']); ?></td>

                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="timesheetid" value="<?php echo htmlspecialchars($row['id']); ?>">
                                            <input type="hidden" name="duplicateTimeAction" value="approve">
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this duplicate record and exclude it from future reports?');">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="timesheetid" value="<?php echo htmlspecialchars($row['id']); ?>">
                                            <input type="hidden" name="duplicateTimeAction" value="archive">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Archive this time record and hide it from the system?');">
                                                Archive
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>    

    <?php
        $conn->close();
    ?>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
<!-- END exceptionsv4 -->