<!-- START report_billing_vetv4 -->

<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(1); // Turn off error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);;

        include ('setting/main-top-files.php'); // loads Session, Datebase Connectons, functions and more
        include("include/header-code.php");

        // Set default values for fromDate and toDate
        $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : date('Y-m-01'); // First day of current month
        $toDate = isset($_GET['toDate']) ? $_GET['toDate'] : date('Y-m-t'); // Last day of current month
        $vetNames = isset($_GET['vetName']) ? $_GET['vetName'] : []; // Array for multiple vet selection

        // Ensure vetName is retrieved as an array
        $selectedVets = isset($_GET['vetName']) ? (array) $_GET['vetName'] : ['ALL'];

        // Get vet names from database
        $vetNames = getVetNamesByIds($selectedVets);

        if (!empty($vetNames)) {
            if (count($vetNames) > 1) {
                // Replace the last comma with " & "
                $vetList = implode(', ', array_slice($vetNames, 0, -1)) . ' & ' . end($vetNames);
            } else {
                // Only one vet, no need for comma
                $vetList = $vetNames[0];
            }
        } else {
            $vetList = "No vets selected"; // Fallback message
        }

    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

    <body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php
    include("include/header.php"); // Added by salva TDR | 2.12.2022
    include("include/sidebar.php");

    $rates = fetchRates(); // Fetch rates before using them
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

    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="row">
                <div class="col-6">
                    <?php
                        echo "<h1>".$prefs["prefSiteName"]."</h1>" ;
                        //echo "<p> Debug on/off: ".$prefs["prefCMSDebugOn"]."</p>" ;
                        echo "<h5>Billing Report for Vets: ".htmlspecialchars($vetList)."<br>" ;
                        echo "from ".date('d-m-Y', strtotime($fromDate))." to ". date('d-m-Y', strtotime($toDate)) ."</h5>" ;
                    ?>
                </div>
                

                <div class="col-2 text-right">                    
                    <a href="generate_billing_vet_pdf.php?m=<?= $month ?>&y=<?= $year ?>&c=<?= $customerId ?>" target="_blank" class="btn btn-primary" style="margin-bottom:10px;">Summary PDF</a>
                </div>
                <div class="col-2 text-right">
                    <a href="generate_billing_vet_detail_pdf.php?m=<?= $month ?>&y=<?= $year ?>&c=<?= $customerId ?>" target="_blank" class="btn btn-primary" style="margin-bottom:10px;">Detailed PDF</a>
                
                <br>

                    <a href="generate_billing_vet_detail_csv.php?m=<?= $month ?>&y=<?= $year ?>&c=<?= $customerId ?>" target="_blank" class="btn btn-primary">Detailed CSV</a>
                </div>

                

                <?php

                    echo "<div class='table-wrapper' id='main-data-area'>";
?>
            <!--  <h2>Monthly Billing Report by Vet - <?= date('F Y', strtotime("$year-$month-01")) ?></h2> -->


<div id="billing-container">
                <table id="billing-table" class="table table-responsive table-bordered table-striped">
                    <thead class="sticky-header">
                        <tr>
                            <th>ID</th>
                            <th>PO</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Time OV</th>
                            <th>Time CSO</th>
                            <th>Travel Units</th>
                            <th>Travel Miles</th>
                            <th>Certs</th>
                            <th>SHA SA</th>
                            <th>Courier</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $data = fetchBillingDataByVetDateRange($fromDate, $toDate, $selectedVets);
                        $vetCounter = 0;
                        $periodTotals = initializeTotals();

                        foreach ($data as $vetName => $vetData) {
                            $vetCounter++;
                            $vetId = "vet-$vetCounter";

                            if ($vetCounter > 1) {
                                echo "<tr style='background-color: white;'><td colspan='12'></td></tr>";
                            }

                            echo "<tr class='table-primary'>
                                <td colspan='12'>
                                    <div class='d-flex justify-content-between align-items-center'>
                                        <strong>Vet: $vetName</strong>
                                        <button class='btn btn-sm btn-outline-secondary toggle-details' data-vet-id='{$vetId}'>Expand/Collapse</button>
                                    </div>
                                </td>
                            </tr>";

                            echo generateSubtotalRows($vetData, 'vet');

                            foreach ($vetData['entries'] as $entry) {
                                $formattedDate = date('d-m-Y', strtotime($entry['date']));

                                echo "<tr class='details-{$vetId} d-none'>
                                    <td>{$entry['id']}</td>
                                    <td>{$entry['po']}</td>
                                    <td>{$entry['customer']}</td>
                                    <td>$formattedDate</td>
                                    <td class='text-right'>{$entry['time_ov']}</td>
                                    <td class='text-right'>{$entry['time_cso']}</td>
                                    <td class='text-right'>{$entry['travel_units']}</td>
                                    <td class='text-right'>{$entry['travel_miles']}</td>
                                    <td class='text-right'>{$entry['certs']}</td>
                                    <td class='text-right'>{$entry['sha_sa']}</td>
                                    <td class='text-right'>£ " . number_format($entry['courier'], 2) . "</td>
                                </tr>";
                            }

                            // Add Vet Monetary Total Row
                            echo "<tr class='table-success monetary-row'>
                                <td colspan='11' class='text-right'><strong>Total for $vetName: £ " . number_format(array_sum($vetData['totals']['monetary']), 2) . "</strong></td>
                            </tr>";

                            accumulateTotals($vetData['totals'], $periodTotals);
                        }

                        // Add space before overall totals
                        echo "<tr style='background-color: white;'><td colspan='12'></td></tr>";

                        // Generate overall totals
                        echo generatePeriodTotalRows($periodTotals);

                        // Add space before overall totals
                        echo "<tr style='background-color: white;'><td colspan='12'></td></tr>";

                        // Generate overall rate/unit headings
                        echo "<tr class='table-info rate-row'>
                        <td colspan='4'>Rate (per Unit)</td>
                        <td class='text-right'>£ " . number_format($rates['time_ov']['rate'] ?? 0, 2) . " <br> " . htmlspecialchars($rates['time_ov']['units'] ?? 'N/A') . "</td>
                        <td class='text-right'>£ " . number_format($rates['time_cso']['rate'] ?? 0, 2) . " <br> " . htmlspecialchars($rates['time_cso']['units'] ?? 'N/A') . "</td>
                        <td class='text-right'>£ " . number_format($rates['travel_units']['rate'] ?? 0, 2) . " <br> " . htmlspecialchars($rates['travel_units']['units'] ?? 'N/A') . "</td>
                        <td class='text-right'>£ " . number_format($rates['travel_miles']['rate'] ?? 0, 2) . " <br> " . htmlspecialchars($rates['travel_miles']['units'] ?? 'N/A') . "</td>
                        <td class='text-right'>£ " . number_format($rates['certs']['rate'] ?? 0, 2) . " <br> " . htmlspecialchars($rates['certs']['units'] ?? 'N/A') . "</td>
                        <td class='text-right'>£ " . number_format($rates['sha_sa']['rate'] ?? 0, 2) . " <br> " . htmlspecialchars($rates['sha_sa']['units'] ?? 'N/A') . "</td>
                        <td class='text-right'>£ " . number_format($rates['courier']['rate'] ?? 0, 2) . " <br> " . htmlspecialchars($rates['courier']['units'] ?? 'N/A') . "</td>
                    </tr>";
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
                    const vetId = toggle.getAttribute("data-vet-id");
                    const rows = document.querySelectorAll(`.details-${vetId}`);
                    rows.forEach(row => row.classList.toggle("d-none"));
                });
            });
        });
    </script>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>

<!-- END report_billingv4_vet -->