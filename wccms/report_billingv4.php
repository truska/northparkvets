<!-- START report_billingv4 -->

<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(1);
        // Turn on error reporting
        // error_reporting(1);

        include ('setting/main-top-files.php'); // loads Session, Datebase Connectons, functions and more
        include("include/header-code.php");

        $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        //Requires `frm` and `id` parameteres on url - if not needed comment out next lines
        if (!$month = securityCheck($_GET['m'], 'number')) {
            die('Error in the Month'); // If the user try to insert something different from a number, we kill the script
        }
        if (!$year = securityCheck($_GET['y'], 'number')) {
            die('Error in the Year'); // If the user try to insert something different from a number, we kill the script
        }

        $customerFilter = '';
            $customerId = isset($_GET['c']) && $_GET['c'] !== 'all' ? (int) $_GET['c'] : null;
          //  error_log("report_billingv4.php - Retrieved customerId: " . var_export($customerId, true));

            if ($customerId) {
                $customerFilter = " AND p.account = $customerId";
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
    <!--/ Any Custom styles for alignment and indentation -->
    <style>
        /* Align numeric columns to the right */
        .text-right {
            text-align: right;
        }

        /* Bold styling for subtotal and monetary rows */
        .subtotal-row {
            font-weight: bold;
        }

        .monetary-row {
            font-weight: bold;
        }
        .table-responsive {
            overflow-y: auto; /* Enables vertical scrolling */
            max-height: 100%; /* Adjust this to control the table height */
        }
        /* Enable vertical scrolling */
        .table-wrapper {
            max-height: 100%; /* Adjust height as needed */
            overflow-y: auto; /* Enables scrolling */
            position: relative; /* Required for sticky header to work */
        }


        /* Sticky table header */
        /* Ensure the table container is scrollable */
        #billing-container {
            /*max-height: 100%; /* Adjust height as needed */
            height: 90vh; /* 80% of the viewport height */
            overflow-y: auto;
            border: 1px solid #ddd; /* Optional border */
            margin-bottom:30px;
        }

        /* Make the table full width */
        #billing-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Make the table header sticky */
        #billing-table thead {
            position: sticky;
            top: 0;
            background: white; /* Keeps the header visible */
            z-index: 100;
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
        /*
        thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #ffffff; 
            box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.1); 
        }
        */
        .totalaffix {
            font-weight: 200;
        }
    </style>

    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="row">

                <div class="col-7">
                    <?php
                        echo "<h1>".$prefs["prefSiteName"]."</h1>" ;
                        echo "<p> Debug on/off: ".$prefs["prefCMSDebugOn"]."</p>" ;
                    ?>
                </div>
                

                <div class="col-2 text-right">                    
                    <a href="generate_billing_pdf.php?m=<?= $month ?>&y=<?= $year ?>&c=<?= $customerId ?>" target="_blank" class="btn btn-primary" style="margin-bottom:10px;">Summary PDF</a>
                </div>

                <div class="col-2 text-right">
                    <a href="generate_billing_detail_pdf.php?m=<?= $month ?>&y=<?= $year ?>&c=<?= $customerId ?>" target="_blank" class="btn btn-primary" style="margin-bottom:10px;">Detailed PDF</a>
                    <a href="generate_billing_detail_csv.php?m=<?= $month ?>&y=<?= $year ?>&c=<?= $customerId ?>" target="_blank" class="btn btn-primary" style="margin-bottom:10px;">Detailed CSV</a>
                </div>

            </div>
            <?php

                echo "<div class='table-wrapper' id='main-data-area'>";
            ?>
                    <h2>Billing Report - <?= date('F Y', strtotime("$year-$month-01")) ?></h2>
                        
                    <div id="billing-container">
                        <table id="billing-table" class="table table-responsive table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>PO</th>
                                        <th>Vet</th>
                                        <th>Date</th>
                                        <th>Time OV</th>
                                        <th>Time CSO</th>
                                        <th>Travel Units</th>
                                        <th>Travel Miles</th>
                                        <th>Certs</th>
                                        <th>Tanker Certs</th>
                                        <th>SHA SA</th>
                                        <th>Courier</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php


                                        $data = fetchBillingData($month, $year, $customerId);

                                        $customerCounter = 0; // Unique ID for each customer for toggling rows

                                        //Add Aggregation for Full Report Totals
                                        $periodTotals = [
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

                                        foreach ($data as $customer) {
                                            $customerCounter++;
                                            $customerId = "customer-$customerCounter";

                                            // Add blank row for spacing
                                            if ($customerCounter > 1) {
                                                echo "<tr style='background-color: white;'><td colspan='12'></td></tr>";
                                            }

                                            // Customer summary Header Row - With Expand Button
                                            echo "<tr class='table-primary'>
                                                <td colspan='12'>
                                                    <div class='d-flex justify-content-between align-items-center'>
                                                        <strong>Customer: {$customer['name']} ({$customer['code']})</strong>
                                                        <button class='btn btn-sm btn-outline-secondary toggle-details' data-customer-id='{$customerId}'>Expand/Collapse</button>
                                                    </div>
                                                </td>
                                            </tr>";

                                            // Totals Rows
                                            echo "<tr class='table-secondary subtotal-row'>
                                                <td colspan='4'>Units  <span class='totalaffix'>[Customer SubTotal]</span></td>
                                                <td class='text-right'>{$customer['totals']['numeric']['time_ov']}</td>
                                                <td class='text-right'>{$customer['totals']['numeric']['time_cso']}</td>
                                                <td class='text-right'>{$customer['totals']['numeric']['travel_units']}</td>
                                                <td class='text-right'>{$customer['totals']['numeric']['travel_miles']}</td>
                                                <td class='text-right'>{$customer['totals']['numeric']['certs']}</td>
                                                <td class='text-right'>{$customer['totals']['numeric']['tanker_cert']}</td>
                                                <td class='text-right'>{$customer['totals']['numeric']['sha_sa']}</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['numeric']['courier'], 2) . "</td>
                                            </tr>";

                                            echo "<tr class='table-warning monetary-row'>
                                                <td colspan='4'>Monetary  <span class='totalaffix'>[Customer Subtotal]</span></td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['time_ov'], 2) . "</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['time_cso'], 2) . "</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['travel_units'], 2) . "</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['travel_miles'], 2) . "</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['certs'], 2) . "</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['tanker_cert'], 2) . "</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['sha_sa'], 2) . "</td>
                                                <td class='text-right'>£ " . number_format($customer['totals']['monetary']['courier'], 2) . "</td>
                                            </tr>";

                                                // New Row: Customer Monetary Total
                                            echo "<tr class='table-success monetary-row'>
                                                <td colspan='12' class='text-right'><strong>".$customer['name']." Total: £ " . number_format(array_sum($customer['totals']['monetary']), 2) . "</strong></td>
                                            </tr>";

                                            // Detailed Rows
                                            foreach ($customer['entries'] as $entry) {
                                                // Format the date as mm-dd-yyyy
                                                $formattedDate = date('d-m-Y', strtotime($entry['date']));

                                                echo "<tr class='details-{$customerId} d-none'>
                                                    <td>{$entry['id']}</td>
                                                    <td>{$entry['po']}</td>
                                                    <td>{$entry['vet']}</td>
                                                    <td>{$formattedDate}</td>
                                                    <td class='text-right'>{$entry['time_ov']}</td>
                                                    <td class='text-right'>{$entry['time_cso']}</td>
                                                    <td class='text-right'>{$entry['travel_units']}</td>
                                                    <td class='text-right'>{$entry['travel_miles']}</td>
                                                    <td class='text-right'>{$entry['certs']}</td>
                                                    <td class='text-right'>{$entry['tanker_cert']}</td>
                                                    <td class='text-right'>{$entry['sha_sa']}</td>
                                                    <td class='text-right'>£ " . number_format($entry['courier'], 2) . "</td>
                                                </tr>";
                                            }

                                            // Accumulate Period Totals
                                            foreach ($customer['totals']['numeric'] as $key => $value) {
                                                $periodTotals['numeric'][$key] += $value;
                                            }
                                            foreach ($customer['totals']['monetary'] as $key => $value) {
                                                $periodTotals['monetary'][$key] += $value;
                                            }
                                        }
                                    ?>
                                        <!-- Add space before the period totals -->
                                        <tr style='background-color: white;'><td colspan='12'></td></tr>

                                        <!-- Period Totals Row -->
                                        <tr class='table-primary'>
                                            <td colspan='12'><strong>Period Totals</strong></td>
                                            
                                        </tr>
                                        <tr class='table-secondary subtotal-row'>
                                            <td colspan='4'>Unit Totals <span class='totalaffix'>[Report Total]</span></td>
                                            <td class='text-right'><?= $periodTotals['numeric']['time_ov'] ?></td>
                                            <td class='text-right'><?= $periodTotals['numeric']['time_cso'] ?></td>
                                            <td class='text-right'><?= $periodTotals['numeric']['travel_units'] ?></td>
                                            <td class='text-right'><?= $periodTotals['numeric']['travel_miles'] ?></td>
                                            <td class='text-right'><?= $periodTotals['numeric']['certs'] ?></td>
                                            <td class='text-right'><?= $periodTotals['numeric']['tanker_cert'] ?></td>
                                            <td class='text-right'><?= $periodTotals['numeric']['sha_sa'] ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['numeric']['courier'], 2) ?></td>
                                        </tr>
                                        <tr class='table-warning monetary-row'>
                                            <td colspan='4'>Monetary Totals <span class='totalaffix'>[Report Subtotal]</span></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['time_ov'], 2) ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['time_cso'], 2) ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['travel_units'], 2) ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['travel_miles'], 2) ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['certs'], 2) ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['tanker_cert'], 2) ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['sha_sa'], 2) ?></td>
                                            <td class='text-right'>£ <?= number_format($periodTotals['monetary']['courier'], 2) ?></td>
                                        </tr>

                                        <!-- Monetary Total for All -->
                                        <tr class='table-success monetary-row'>
                                            <td colspan='11'>Grand Total</td>
                                            <td class='text-right'><strong>£ <?= number_format(array_sum($periodTotals['monetary']), 2) ?></strong></td>
                                        </tr>

                                    <?php

                                        // Add space before overall totals
                                        echo "<tr style='background-color: white;'><td colspan='12'></td></tr>";
                                            
                                        //  error_log("Rates on Company Billing Page: " . json_encode($rates));
                                           


                                        // Generate overall rate/unit headings
                                        echo billingSavedRateRow($data);

                                    ?>
                                </tbody>

                            </table>
                    </div>
            <?php
                echo '</div>';
            ?>

            </div>

            <?php
            include ("include/footer-code.php");
            include ("include-tinymce.php");
            ?>

        </section>
    </section>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".toggle-details").forEach((toggle) => {
            toggle.addEventListener("click", () => {
                const customerId = toggle.getAttribute("data-customer-id");
                const rows = document.querySelectorAll(`.details-${customerId}`);
                rows.forEach(row => row.classList.toggle("d-none"));
            });
        });
    });
</script>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>

<!-- END report_billingv4 -->