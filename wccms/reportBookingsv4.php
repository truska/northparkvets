<!-- START reportBookingsv4 -->
 <!-- ConCat the data field -->
  
<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(0);
        // Turn on error reporting
        // error_reporting(1);

        include ('setting/main-top-files.php'); // Added by salva TDR | 9.12.2022
        include("include/header-code.php");


        //Bring Fwd variables - Edited by salva TDR | 12.12.2022
        $baseURL = $BASE_URL;

        if (!$formnumber = securityCheck($_GET['frm'], 'number')) {
            die('Error in the form'); // If the user try to insert something different from a number, we kill the script
        }
        if (!$recordnumber = securityCheck($_GET['id'], 'number')) {
            die('Error in the id'); // If the user try to insert something different from a number, we kill the script
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
    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="row">
                <div class="col-8">
                    <h1>North Park Exports - Booking Report</h1>
                    <?php
                        echo "<p> Debug on/off: ".$prefs["prefCMSDebugOn"]."</p>" ;
                    ?>
                    </div>
                <div class="col-4">

                <form method="post" action="reportWeekly_generate_pdf.php" target="_blank">
                    <button type="submit" class="btn btn-primary">Generate PDF</button>
                </form>

                </div>

                <?php
                    echo "<hr>" ;

                    // Fetch the structured array
                    $data = fetchProductData($conn);

                    // Custom styles for alignment and indentation
                        echo '<style>
                            .table-title th {
                                text-align: left;
                                padding-top:30px;
                                background-color:#555;
                                color:#fff;
                            }
                            .table-primary th {
                                text-align: left;
                                padding-top:30px;
                            }
                            .table-secondary th {
                                text-align: left;
                                /* padding-left: 20px;  Indent Level 2 headers */
                                padding-top:20px;
                            }
                            .table tbody td {
                                text-align: left; /* Left-align the main table rows */
                            }
                        </style>';
                    //


                    $processedData = preprocessTotals($data);

                    echo "<div class='table-responsive'>";

                        echo "<table class='table table-bordered table-striped-rows'>";
                            echo '<thead class="table-title">';

                                echo "<tr>
                                    <th>data</th>
                                    <th>PCloud</th>
                                    <th>Vet</th>
                                    <th>CSO</th>
                                    <th>OV</th>
                                    <th>Total</th>
                                </tr>" ;
                            echo '</thead>';

                            foreach ($processedData as $date => $dateData) {
                                // Skip the 'totals' key in the date level
                                if ($date === 'totals') continue;

                                    //$expected_date = $row["expected_date"]; // e.g., '2024-11-29'
                                    $expected_date = $date ; // e.g., '2024-11-29'
                                    $formatted_date = date("d/m/Y", strtotime($expected_date));
                                    $day_only = date("D", strtotime($expected_date));



                                // Render Date Header with Totals
                                $dateTotals = $dateData['totals'];
                                echo '<thead class="table-primary">';
                                echo "<tr>
                                <th colspan='3'>$formatted_date | $day_only</th><th class='text-center'>{$dateTotals['CSO']}</th><th class='text-center'>{$dateTotals['OV']}</th><th class='text-center'>{$dateTotals['Total']}</th></tr>";
                                echo '</thead>';

                                foreach ($dateData as $depot => $depotData) {
                                
                                    if ($depot === 'totals') continue;

                                    // Render Depot Header with Totals
                                    $depotTotals = $depotData['totals'];
                                    $depot_name = $depotData['depot_name'] ?? 'Unknown Depot';

                                    echo '<thead class="table-secondary">';
                                    echo "<tr><th colspan='3'>$depot_name</th><th class='text-center'>{$depotTotals['CSO']}</th><th class='text-center'>{$depotTotals['OV']}</th><th class='text-center'>{$depotTotals['Total']}</th></tr>";
                                    echo '</thead>';

                                    // Render Rows
                                    echo '<tbody>';
                                    foreach ($depotData['rows'] as $row) {

                                        $expected_time = $row['expected_time'] ?? '00:00:00'; // e.g., '14:30:00'
                                        $time_object = new DateTime($expected_time) ;
                                        $weekNumber = getWeekNumber($date);

                                        
                                        echo '<tr>';
                                        //  echo "<td>".$time_object->format('H:i')." [$expected_time]</td>";
                                            echo "<td>".$time_object->format('H:i')." | ";
                                            echo "{$row['customer_name']} | ";
                                            echo "{$row['destination_country_name']} | ";
                                            echo "{$row['destination_customer']} | ";
                                            echo "{$row['pallet_count']} x {$row['pallet_type']} | ";
                                            echo "{$row['product_type']} | ";
                                            echo "<td>{$row['pcloud']}</td>";
                                            echo "<td>{$row['vet_name']}</td>";
                                            echo "<td class='text-center'>{$row['CSO']}</td>";
                                            echo "<td class='text-center'>{$row['OV']}</td>";
                                            echo "<td class='text-center'>{$row['RowTotal']}</td>";
                                            if($prefs["prefCMSDebugOn"] == 'Yes') {
                                                echo "<td>Wk #: $weekNumber</td>";
                                                echo "<td>{$row['sku']}</td>";
                                            }
                                        echo '</tr>';
                                    }
                                    echo '</tbody>';
                                }
                            }

                            echo '</table>';
                    echo '</div>';

                    if($prefs["prefCMSDebugOn"] == 'Yes') {
                        echo "<hr>" ;
                            echo "<pre>";
                            print_r($data);
                            echo "</pre>";
                        echo "<hr>" ;              
                            echo "<pre>";
                            print_r($processedData);
                            echo "</pre>";

                        }
                ?>


                <?php
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
                ?>
            </div>



            <?php
                // include("include/footer.php"); // - footer.php doesn't exist inside wccms/include folder - Salva TDR | 17.1.2023
                // echo "</div>"; // Removed by salva TDR | 17.1.2023
                include ("include/footer-code.php");
                include ("include-tinymce.php");
            ?>

        </section>
    </section>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>

<!-- END recordBookingsv4 -->