<?php
ob_start();
include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');
include('functions.php');

// Fetch and process data
$whereClause = "p.status < 4  
                AND DATEDIFF(CURDATE(), p.load_date) > 6                 
                AND ds.code = 'OS'
                AND p.showonweb = 'Yes' 
                AND p.archived = 0 " ; 

$sortClause = "ORDER BY outstandingdays DESC, destination_country, po";

$sql = "SELECT 
            p.id,
            DATEDIFF(CURDATE(), p.load_date) AS outstandingdays,
            p.load_date,
            p.status,
            p.name AS po, 
            d.name AS depot_name,
            s.name AS status_name,
            v.name AS vet_name,
            c.name AS customer_name,
            dest.code AS destination_country,
            ds.code AS destination_status_code,
            p.destination_customer AS customer,
            p.ehc_ref,
            p.product_type,
            p.pcloud AS pcloud
        FROM `products` p
        LEFT JOIN `npe_status` s ON p.status = s.id
        LEFT JOIN `npe_vet` v ON p.vet = v.id
        LEFT JOIN `npe_customer` c ON p.account = c.id
        LEFT JOIN `npe_destination` dest ON p.destination_country = dest.id
        LEFT JOIN `npe_destination_status` ds ON dest.destination_status_id = ds.id
        LEFT JOIN `npe_depot` d ON p.depot = d.id
        WHERE $whereClause 
        $sortClause ";

$whereDebug = $whereClause ;
$sqlDebug = $sql ;

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Outstanding Report</title>
    <?php include("include/header-codev4.php"); ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
<?php include("include/headerv4.php"); ?>
<?php include("include/sidebarv4.php"); ?>
    <style>
        .coldays {
            font-size:16px;
            font-weight: 600;
        }
        .coldays.low { color: green; font-weight: 200; background-color:none; }        /* < 8 */
        .coldays.medium { color: orange; font-weight: 400; background-color:none; }    /* 8 - 14 */
        .coldays.high { color: red; font-weight: 600; background-color:none; }         /* 15 - 21  */
        .coldays.critical { color: black; font-weight: 700;  background-color:none;}   /* > 21 */

        .table thead input {
            width: 100%;
            box-sizing: border-box;
        }

        #exportButtons .btn {
            margin-right: 5px;
        }
        /* Styling Pagination Controls */
        .dataTables_paginate {
            margin-top: 10px;
        }

        .dataTables_paginate .pagination {
            justify-content: end; /* Align pagination to the right */
        }

        .dataTables_paginate .pagination .page-item .page-link {
            border: 1px solid #dee2e6;
            margin: 0 2px;
            border-radius: 4px;
            color: #495057;
        }

        .dataTables_paginate .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .dataTables_paginate .pagination .page-item.disabled .page-link {
            color: #ced4da;
        }
        .dataTables_paginate .paginate_button  {
            padding-right:10px;
        }

        /* Styling 'Show Entries' Dropdown */
        .dataTables_length label {
            font-weight: 500;
            margin-bottom: 0;
        }

        .dataTables_length select {
            width: auto;
            margin-left: 5px;
            display: inline-block;
            padding: 4px 8px;
        }

        .table thead select {
            width: 100%;
            box-sizing: border-box;
            height: 31px;
        }

    </style>
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="col-8">
                    <h1>Outstanding Report</h1>
                    <p>Showing records with <strong><?php echo $whereClause ; ?></strong>.</p>
                 <!--   <p>SQL: <strong><?php echo $sql ; ?></strong></p> -->

                </div>
                <div class="col-4 text-end">
                    <!-- Generate PDF Button -->
                     <!--
                    <form id="generatePdfForm" method="POST" action="report_2_pdf.php" target="_blank">
                        <button type="submit" class="btn btn-primary">Generate PDF</button>
                    </form>
                    -->
                </div>
            </div>
            <hr>


    <!-- Data Table -->
    <div class="table-responsive">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Export Options:</h5>
        <div id="exportButtons" class="btn-group"></div>
    </div>



            </div>
        </section>
    </section>

    <?php include("include/footer-codev4.php"); ?>


<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
