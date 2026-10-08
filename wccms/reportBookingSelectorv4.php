<!-- START reportBookingSelectorv4 -->
<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(0);
        // Turn on error reporting
        // error_reporting(1);

        include('setting/main-top-files.php'); // Loads Session, Database Connections, functions, and more
        include("include/header-code.php");

        // Optional parameters: `frm` and `id`
        $formnumber = isset($_GET['frm']) ? securityCheck($_GET['frm'], 'number') : null;
        $recordnumber = isset($_GET['id']) ? securityCheck($_GET['id'], 'number') : null;

        $currweeknum = date("W") ;
    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php
        include("include/header.php"); // Include header
        include("include/sidebar.php"); // Include sidebar
        $showrows = 8 ;
    ?>

    <!-- Custom styles for alignment and indentation -->
    <style>
        .form-control {
            font-size:.8rem;
        } 
        .form-group label {
            font-weight: bold;
        }
        .card {
            border: 1px solid #ccc;
        }
        .card-header {
            background-color: #f7f7f7;
            font-weight: bold;
        }
        .shortcuticons .far {
            font-size:26px;
        }
        .tankers p a {
            color:#198754;
        }
    </style>

    <section id="main-content">
        <section class="wrapper site-min-height">
            <div class="row">
                <!--
                <div class="col-8">
                    <h1>North Park Exports</h1>
                    <?php
                      //  echo "<p> Debug on/off: " . htmlspecialchars($prefs["prefCMSDebugOn"]) . "</p>";
                    ?>
                </div>
                <div class="col-4">
                    <h3>Additional Info Area</h3>
                </div>
                -->
                <div class="col-12">
                    <h2>Select Report Criteria</h2>
                    <h5>This Week Number is <?php echo  $currweeknum;?><h5>
                </div>

                <!-- Quick Reports Section -->
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">Quick Reports
                        </div>
                        <div class="card-body">
                            <select id="quickReport" class="form-control" onchange="loadQuickReport()">
                                <option value="">Select a report...</option>
                                <option value="7">Today (<?php echo date("d M Y"); ?>)</option>
                                <option value="1">This Week (Week: <?php echo $currweeknum;?>)</option>
                                <option value="4">Current Month</option>
                                <option value="9">Tomorrow (<?php echo date("d M Y", strtotime("tomorrow")); ?>)</option>
                                <option value="2">Next Week (Week: <?php echo $currweeknum + 1;?>)</option>
                                
                                <!-- Add Next Month -->
                                <option value="10">Next Month</option>                             

                                <option value="8">Yesterday (<?php echo date("d M Y", strtotime("yesterday")); ?>)</option>                          

                                <option value="3">Last Week (Week: <?php echo $currweeknum - 1;?>)</option>

                                <option value="5">Previous Month</option>

                                <option value="6">Previous+</option>
                                
                                <!--    <option value="11">Tanker Report</option> -->



                                <!-- Add more options as needed -->
                            </select>
                           <!-- <p>&nbsp;</p><p>* NC - Not Completed</p> -->
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row  shortcuticons">
                        <div class="col-md-8">
                            <p>Booking Report Shortcuts</p>
                        </div>
                        <div class="col-md-4" style="background-color:#b2ffb2; ">
                            <p><strong>TANKERS</strong></p>
                        </div>
                    </div>
                    <div class="row  shortcuticons">
                        <div class="col-6 col-md-4 ">
                            <p><a href="/wccms/report_bookingv4.php?frm=1&id=7" target="_blank"> <i class="far fa-calendar-day"></i> TODAY</a></p>

                            <p><a href="/wccms/report_bookingv4.php?frm=1&id=1" target="_blank"> <i class="far fa-calendar-week"></i> THIS WEEK</a></p>
                        </div>
                        <div class="col-6 col-md-4">
                            <p><a href="/wccms/report_bookingv4.php?frm=1&id=2" target="_blank"> <i class="far fa-calendar-plus"></i> NEXT WEEK</a></p>

                        </div>

                        <div class="col-6 col-md-4 tankers" style="background-color:#b2ffb2; ">

                            <?php
                                // Ensure UK weeks/dates
                                date_default_timezone_set('Europe/London');
                                $thisMon = date('Y-m-d', strtotime('monday this week'));
                                $thisSat = date('Y-m-d', strtotime('sunday this week'));
                                $nextMon = date('Y-m-d', strtotime('monday next week'));
                                $nextSun = date('Y-m-d', strtotime('sunday next week'));
                            ?>
                            <p>
                                <a href="#" onclick="document.getElementById('tankQuick').submit(); return false;" target="_blank">
                                    <i class="far fa-calendar-week"></i> THIS WEEK
                                </a>
                            </p>


                        
                            <form id="tankQuick" method="POST" action="report_1T.php" target="_blank" style="display:none;">
                                <input type="hidden" name="frm" value="2">
                                <input type="hidden" name="id" value="11">
                                <!-- Set your defaults -->
                                <input type="hidden" name="fromDate" value="<?= $thisMon ?>">
                                <input type="hidden" name="toDate"   value="<?= $thisSat ?>">
                                <input type="hidden" name="customer[]" value="17"> 
                            </form>

                            <p>
                                <a href="#" onclick="document.getElementById('tankQuick2').submit(); return false;" target="_blank">
                                    <i class="far fa-calendar-plus"></i> NEXT WEEK
                                </a>
                            </p>

                            <form id="tankQuick2" method="POST" action="report_1T.php" target="_blank" style="display:none;">
                                <input type="hidden" name="frm" value="2">
                                <input type="hidden" name="id" value="11">
                                <!-- Set your defaults -->
                                <input type="hidden" name="fromDate" value="<?= $nextMon ?>">
                                <input type="hidden" name="toDate"   value="<?= $nextSun ?>">
                                <input type="hidden" name="customer[]" value="17"> 
                            </form>

                        </div>

                    </div>
                </div>

                <?php

                    // Mange multipne button form destinations
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        // Common form data processing
                        $fromDate = $_POST['fromDate'] ?? null;
                        $toDate = $_POST['toDate'] ?? null;

                        // Determine which button was clicked
                        if (isset($_POST['action'])) {
                            switch ($_POST['action']) {
                                case 'generateReport1':
                                    // Logic for Report 1
                                    header("Location: report1.php"); // Redirect to Report 1 generator
                                    exit;

                                case 'generateReport2':
                                    // Logic for Report 2
                                    header("Location: report2.php"); // Redirect to Report 2 generator
                                    exit;

                                default:
                                    echo "Unknown action!";
                            }
                        }
                    }
                ?>



                <!-- Advanced Filters Section -->
                <div class="col-md-12">
                    <h1>Advanced Filters</h1>
                </div>
                
            <?php
            // Fetch dynamic options
            $depotOptions = getDepotOptions($conn);        // Fetch depot options
            $statusOptions = getStatusOptions($conn);      // Fetch status options
            $ehcOptions = getEhcOptions($conn);            // Fetch EHC location options
            $vetNameOptions = getVetOptions($conn);                // Vet name options
            $sortableColumns = getSortableFields($conn, 'products');   // Fetch sortable fields
            $customer = getCustomer($conn);   // Fetch Customer fields
            ?>



<form id="advancedFiltersForm" method="POST" action="report_bookingv4.php">
<!-- <form id="advancedFiltersForm" method="POST" action="reportMasterv4.php"> -->
 
    <!-- Hidden fields for frm and id -->
    <input type="hidden" id="frm" name="frm" value="2"> <!-- Default to advanced filters -->
    <input type="hidden" id="id" name="id" value="1"> <!-- Default id -->

    <div class="row mt-4">

        <!-- Column 1: Date Range -->
        <div class="col-md-6 col-lg-3">

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Date Range</h5>
                    <div class="form-group">
                        <label for="fromDate">From Date</label>
                        <input type="date" id="fromDate" name="fromDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="toDate">To Date</label>
                        <input type="date" id="toDate" name="toDate" class="form-control">
                    </div>
                </div>
            

                <div class="card-body">
                      <!-- <h5 class="card-title">Vet</h5> -->
                        <div class="form-group">
                            <label for="vetName">Select Vet</label>
                            <select id="vetName" name="vetName[]" class="form-control"  multiple size="<?php echo  $showrows ;?>">
                                <?php foreach ($vetNameOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                </div>
            </div>

        </div>

        <!-- Column 2: Include and Exclude Depots -->
        <div class="col-md-6 col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Depots</h5>

                    <div class="form-group">
                        <label for="includeDepots">Include Depots</label>
                        <select id="includeDepots" name="includeDepots[]" class="form-control"  multiple size="<?php echo  $showrows ;?>">
                            <?php foreach ($depotOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="excludeDepots">Exclude Depots</label>
                        <select id="excludeDepots" name="excludeDepots[]" class="form-control"  multiple size="<?php echo  $showrows ;?>">
                            <?php foreach ($depotOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <!-- Column 3: EHC Locations and Status -->
        <div class="col-md-6 col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">EHC Locations and Status</h5>

                    <div class="form-group">
                        <label for="ehcLocations">EHC Locations</label>
                        <select id="ehcLocations" name="ehcLocations[]" class="form-control" multiple size="<?php echo  $showrows ;?>">
                            <?php foreach ($ehcOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status[]" class="form-control"  multiple size="<?php echo  $showrows ;?>">
                            <?php foreach ($statusOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </div>
        </div>
    

        <!-- Column 4:  Customer -->
        <div class="col-md-6 col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Customer</h5>

                    <div class="form-group">
                        <label for="customer">Customer </label>
                        <select id="customer" name="customer[]" class="form-control" multiple size="<?php echo  $showrows ;?>">
                            <?php foreach ($customer as $option): ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <!--
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status[]" class="form-control"  multiple size="<?php echo  $showrows ;?>">
                            <?php foreach ($statusOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                -->
                </div>
            </div>
        </div>




<!-- HIDDEN -->
    <!-- Sorting Options and Submit Buttons -->
   
        <!-- Sorting Options -->
        <div class="col-md-3" style="display:none;">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Sorting Options</h5>
                    <div class="alert alert-danger" role="alert">
                        <span style="color:red;font-size:20px"><i class="fa-solid fa-triangle-exclamation"></i></span> Do not use sorting tools for standard weekly reports.
                    </div>

                    <!-- Sort 1 -->
                    <div class="form-group">
                        <label for="sortColumn1">Sort 1</label>
                        <select id="sortColumn1" name="sortColumn1" class="form-control">
                            <option value="" selected>-- Select Field --</option>
                            <?php foreach ($sortableColumns as $column): ?>
                                <option value="<?= htmlspecialchars($column['field']) ?>"><?= htmlspecialchars($column['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="sortOrder1" name="sortOrder1" class="form-control mt-1">
                            <option value="ASC">ASC</option>
                            <option value="DESC">DESC</option>
                        </select>
                    </div>

                    <!-- Sort 2 -->
                    <div class="form-group">
                        <label for="sortColumn2">Sort 2</label>
                        <select id="sortColumn2" name="sortColumn2" class="form-control">
                            <option value="" selected>-- Select Field --</option>
                            <?php foreach ($sortableColumns as $column): ?>
                                <option value="<?= htmlspecialchars($column['field']) ?>"><?= htmlspecialchars($column['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="sortOrder2" name="sortOrder2" class="form-control mt-1">
                            <option value="ASC">ASC</option>
                            <option value="DESC">DESC</option>
                        </select>
                    </div>

                    <!-- Sort 3 -->
                    <div class="form-group">
                        <label for="sortColumn3">Sort 3</label>
                        <select id="sortColumn3" name="sortColumn3" class="form-control">
                            <option value="" selected>-- Select Field --</option>
                            <?php foreach ($sortableColumns as $column): ?>
                                <option value="<?= htmlspecialchars($column['field']) ?>"><?= htmlspecialchars($column['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="sortOrder3" name="sortOrder3" class="form-control mt-1">
                            <option value="ASC">ASC</option>
                            <option value="DESC">DESC</option>
                        </select>
                    </div>


                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="col-md-12">
            <div class="text-center">
                <button type="submit" name="action" value="generateReport1" class="btn btn-primary mx-2" formaction="report_1.php">Generate Consignment Report</button>
                <button type="submit" name="action" value="generateReport2" class="btn btn-secondary mx-2" formaction="report_2.php">Generate Summary Report</button>
                <button type="submit" name="action" value="generateReport1T" class="btn btn-success mx-2" formaction="report_1T.php">Generate TANKER Report</button>
            </div>
        </div>
    </div>
</form>





        </section>
    </section>

    <?php
        include("include/footer-code.php"); // Include footer
        include("include-tinymce.php"); // Include TinyMCE or other scripts
    ?>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

<script>
    function loadQuickReport() {
        const reportId = document.getElementById('quickReport').value;
        if (reportId) {
            window.location.href = `report_bookingv4.php?frm=1&id=${reportId}`;
           // window.location.href = `reportMasterv4.php?frm=1&id=${reportId}`;
        }
    }

    function addFrmAndIdToUrl() {
        const form = document.getElementById('advancedFiltersForm');
        const frmValue = document.getElementById('frm').value;
        const idValue = document.getElementById('id').value;

        // Append frm and id to the form action URL
        form.action = `report_bookingv4v4.php?frm=${frmValue}&id=${idValue}`;
        //form.action = `reportMasterv4.php?frm=${frmValue}&id=${idValue}`;
    }


    
</script>
</html>
<!-- END reportBookingSelectorv4 -->
