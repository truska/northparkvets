<!-- START importMasterv4 -->
<!DOCTYPE html>
<head>
    <?php
    // Initialize database connection

        // Turn off error reporting
        error_reporting(0);
        // Uncomment to turn on error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        include('setting/main-top-files.php'); // Added by salva TDR | 9.12.2022
        include("include/header-code.php");

        // Bring forward variables securely
        $baseURL = $BASE_URL;
        $username = $user["firstname"] ;

        if (!$formnumber = securityCheck($_GET['frm'], 'number')) {
            die('Error in the form'); // If the user tries to insert something different from a number, kill the script
        }
        if (!$recordnumber = securityCheck($_GET['id'], 'number')) {
            die('Error in the id'); // If the user tries to insert something different from a number, kill the script
        }

        // Retrieve logID from the URL
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $logID = $_GET['id'];
        } else {
            die('Invalid or missing log ID.');
        }
    global $db;
    $db = DB::connection();
       // var_dump($db);

        // Fetch import parameters from the log
        $sql = "SELECT name, type, source FROM product_import_log WHERE id = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $db->error);
        }
        $stmt->bind_param("i", $logID);
        $stmt->execute();
        $stmt->bind_result($importname, $importtype, $sourcefilename);
        if (!$stmt->fetch()) {
            die('No log entry found with the provided log ID.');
        }
        $stmt->close();

        // Hardcoded values for now
        //$importname = "Import Test";
        //$importtype = "HF"; // Example type (later this will be dynamic)
        //$sourcefilename = "npe_importtext.csv";
        echo "<p>Debug: Import Type: $importtype</p>";

        if (empty($importtype) || !in_array($importtype, ['HF', 'NT', 'NPV', 'TANK'])) {
            echo "<div class='alert alert-danger'>Error: Invalid or missing import type.</div>";
            exit;
        }
    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php
        include("include/header.php"); // Added by salva TDR | 2.12.2022
        include("include/sidebar.php");
    ?>
    <section id="main-content">
        <section class="wrapper site-min-height">

            <!-- Page Header -->
            <div class="row">
                <div class="col-8 mainbody">
                    <h1>North Park Exports</h1>
                    <?php
                        echo "<p> Debug on/off: " . $prefs["prefCMSDebugOn"] . "</p>";
                    ?>
                </div>

                <div class="col-4">
                    <h1>Any actions</h1>
                </div>
            

                <!-- Import Process Steps -->
            
                <div class='col-12' id="bodyarea">
                    <?php
                    

                        // Step 1: Initialize the process and log start
                        echo "<h3>Step 1: Initializing Import Process...</h3>";
                        // Update log status to '2' (in progress)
                        logProcessUpdate($logID, 2, 'Import process started.');


                        if (!$logID) {
                            echo "<p>Error: Failed to Import start process . Check for errors above.</p>";
                            exit;
                        } else {
                            echo "<p>Log record Import Sequence Commenced . Log ID: $logID</p>";
                        }

                        // Step 2: Check if the source file exists
                        echo "<h3>Step 2: Checking Source File...</h3>";

                        $sourceFilePath = "importfiles/" . $sourcefilename; // Dynamically define source file path

                        if (!file_exists($sourceFilePath)) {
                            echo "<p>Error: Source file not found at $sourceFilePath</p>";
                            logProcessUpdate($logID, 4, "Source file missing."); // Update log with status 4 (Error)
                            exit;
                        } else 
                        {
                            echo "<p>Source file found at $sourceFilePath.</p>";
                        }

                    // Step 3: Truncate temp table
                    echo "<h3>Step 3: Preparing Temp Table...</h3>";
                    if (!truncateTempTable()) {
                        echo "<p>Error: Failed to truncate temp table.</p>";
                        logProcessUpdate($logID, 'Error', "Failed to truncate temp table.");
                        exit;
                    } else {
                        echo "<p>Temp table prepared successfully.</p>";
                    }

                    // Step 4: Import data from CSV to temp table
                    echo "<h3>Step 4: Importing Data to Temp Table...</h3>";
                    $importResult = importDataToTempTable($sourceFilePath, $importtype);
                    if (!$importResult['success']) {
                        echo "<p>Error: Failed to import data. {$importResult['message']}</p>";
                        logProcessUpdate($logID, 4, $importResult['message']); // Log error
                        exit;
                    } else {
                        echo "<p>Data imported successfully to temp table.</p>";
                    }

                    // Step 5: Process data and apply rules
                    echo "<h3>Step 5: Processing Data...</h3>";
                    $processResult = processImportedData($logID);
                    if (!$processResult['success']) {
                        echo "<p>Error: Failed to process data. {$processResult['message']}</p>";
                        logProcessUpdate($logID, 4, $processResult['message']); // Log the error
                        exit;
                    } else {
                        echo "<p>Data processed successfully.</p>";
                    }

                    // Step 6: Updating Live Table
                    echo "<h3>Step 6: Updating Live Table...</h3>";
                    $updateResult = updateLiveTable($logID);

                    if (!$updateResult['success']) {
                        echo "<p>Error: Failed to update live table. {$updateResult['message']}</p>";
                        logProcessUpdate($logID, 4, $updateResult['message']); // Log the error
                        exit;
                    } else {
                        echo "<p>Live table updated successfully. {$updateResult['message']}</p>";
                    }


                    // Step 7: Archiving Source File
                    echo "<h3>Step 7: Archiving Source File...</h3>";

                    $archiveResult = archiveSourceFile($sourceFilePath, $logID);
                    if (!$archiveResult['success']) {
                        echo "<p>Error: {$archiveResult['message']}</p>";
                        logProcessUpdate($logID, 4, $archiveResult['message']); // Log the error
                        exit;
                    } else {
                        echo "<p>File archived successfully as {$archiveResult['newFileName']}.</p>";
                    }

                    // Update the log to mark completion
                    $logNotes = "IMPORT Completed";
                    if (!logProcessCompletion($logID, $logNotes)) {
                        echo "<p>Error: Failed to update log with completion status.</p>";
                        exit;
                    } else {
                        echo "<p>Log updated with completion status.</p>";
                    }

                    echo "<h3>Step 8: Import Task ".$logID." Completed...</h3>";

                    ?>
                </div>
            </div>
            <!-- Debug Information -->
            <div>
                <?php
                /*
                if (
                    $prefs['prefFooterDebugOn'] == 'Yes' ||
                    ($_SERVER['REMOTE_ADDR'] == $prefs['prefTruskaIP'] ||
                        $_SERVER['REMOTE_ADDR'] == $prefs['prefCoderIP'] ||
                        $_SERVER['REMOTE_ADDR'] == $prefs['prefClientIP'] ||
                        $_SERVER['REMOTE_ADDR'] == $prefs['prefClient1IP']
                    )
                ) { // Show query in admin/debug mode
                    $formQuery = $FORM->getFormQuery();
                    $tableQuery = $FORM->getTableQuery();
                    $formFieldsQuery = $FORM->getFormFieldsQuery();

                    echo "<p><b>Form query: </b>{$formQuery}</p>";
                    echo "<p><b>Table query: </b>{$tableQuery}</p>";
                    echo "<p><b>Form Fields query: </b>{$formFieldsQuery}</p>";
                } 
                */
                ?>
            </div>
           

            <?php
            include("include/footer-code.php");
            include("include-tinymce.php");
            ?>

        </section>
    </section>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>
<!-- END importMasterv4 -->
