<?php
ob_start();
include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');
include('functions.php');

// Fetch and process data
$whereClause = "p.status < 4  
                AND DATEDIFF(CURDATE(), p.load_date) > 0                   
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
    <?php include("include/header-code.php"); ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
<?php include("include/header.php"); ?>
<?php include("include/sidebar.php"); ?>
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

.btn {
    background-color:#fff;
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
        <div id="exportButtons" class="btn-group" style="background-color:#fff;"></div>
    </div>

<div id="billing-container">
<table id="outstandingReportTable" class="table table-bordered table-striped">

<thead class="table-dark sticky-header">

    <tr>
        <th>Outstanding<br>Days</th>
        <th>Load Date</th>
        <th>PCloud</th>
        <th>Account</th>
        <th>PO</th>
        <th>EHC Ref</th>
        <th>Status</th>
        <th>Destination<br>Country</th>
        <th>Customer</th>
        <th>Product Type</th>
        <th>Vet</th>
        <th>Time</th>
    </tr>

    <tr class="table-light">
        <th><input type="text" class="form-control form-control-sm" placeholder="Search Days"></th>
        <th><input type="text" class="form-control form-control-sm" placeholder="Search Load Date"></th>
        <th><input type="text" class="form-control form-control-sm" placeholder="Search PCloud"></th>
        <th><select class="form-control form-control-sm"><option value="">All</option></select></th>
        <th><input type="text" class="form-control form-control-sm" placeholder="Search PO"></th>
        <th><input type="text" class="form-control form-control-sm" placeholder="Search EHC Ref"></th>
        <th><select class="form-control form-control-sm"><option value="">All</option></select></th>
        <th><select class="form-control form-control-sm"><option value="">All</option></select></th>
        <th><select class="form-control form-control-sm"><option value="">All</option></select></th>
        <th><input type="text" class="form-control form-control-sm" placeholder="Search Product"></th>
        <th><input type="text" class="form-control form-control-sm" placeholder="Search Vet"></th>
        <th><input type="text" class="form-control form-control-sm" placeholder="Time"></th>
    </tr>

</thead>

    <tbody>
        <?php if (empty($data)): ?>
            <tr>
                <td colspan="12" class="text-center">No records found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td class="text-center coldays <?= getClassBasedOnValue($row['outstandingdays']); ?>">
                        <?= htmlspecialchars($row['outstandingdays']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['load_date']) ?></td>
                    <td><?= htmlspecialchars($row['pcloud']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td>
                        <a href="<?php echo $baseURL;?>/wccms/recordEditv4.php?frm=1&id=<?= htmlspecialchars($row['id']) ?>" target="_blank">
                            <?= htmlspecialchars($row['po']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['ehc_ref']) ?></td>
                    <td><?= htmlspecialchars($row['status_name']) ?></td>
                    <td><?= htmlspecialchars($row['destination_country']) ?></td>
                    <td><?= htmlspecialchars($row['customer']) ?></td>
                    <td><?= htmlspecialchars($row['product_type']) ?></td>


                    <td><?= htmlspecialchars($row['vet_name']) ?></td> 
                   
                    <td>
                        <a href="<?php echo $baseURL;?>/wccms/recordTimeAddv4.php?id=<?= htmlspecialchars($row['id']) ?>" target="_blank" title="Record Time & Costs"><i class='far fa-watch'></i></a> | 
                        <a href="<?php echo $baseURL;?>/wccms/recordViewv4.php?frm=13&po=<?= htmlspecialchars($row['po']) ?>" target="_blank" title="Review Time & Costs"><i class='fad fa-file-chart-line'></i></a>                        
                    </td>

                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>


            </div>
            </div>

        </section>
    </section>

    <?php include("include/footer-code.php"); ?>

<script>
$(document).ready(function() {
    let table = $('#outstandingReportTable').DataTable({
        dom: '<"row mb-3"<"col-md-6"B><"col-md-6"f>>rtip<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
        buttons: [
            {
                extend: 'copyHtml5',
                text: 'Copy',
                className: 'btn btn-outline-secondary btn-sm'
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                className: 'btn btn-outline-secondary btn-sm'
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                className: 'btn btn-outline-success btn-sm'
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: 'btn btn-outline-danger btn-sm'
            },
            {
                extend: 'print',
                text: 'Print',
                className: 'btn btn-outline-primary btn-sm'
            }
        ],
        order: [[0, 'desc']],
        responsive: true,
        pageLength: 50,
        lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ]
    });

    // Column Search (Text Inputs)
    $('#outstandingReportTable thead tr:eq(1) th').each(function(index) {
        let column = table.column(index);
        let input = $('input', this);

        if (input.length) {
            input.on('keyup change', function() {
                if (column.search() !== this.value) {
                    column.search(this.value).draw();
                }
            });
        }
    });

    // Column Search (Dropdown Selects)
    let dropdownColumns = {
        3: 'Account',
        6: 'Status',
        7: 'Destination Country',
        8: 'Customer'
    };

    Object.keys(dropdownColumns).forEach(function(index) {
        let column = table.column(index);
        let select = $('#outstandingReportTable thead tr:eq(1) th').eq(index).find('select');

        // Populate dropdown with unique values
        column.data().unique().sort().each(function(value) {
            if (value) {
                select.append(`<option value="${value}">${value}</option>`);
            }
        });

        // Add event listener for dropdown
        select.on('change', function() {
            let val = $.fn.dataTable.util.escapeRegex($(this).val());
            column.search(val ? '^' + val + '$' : '', true, false).draw();
        });
    });
});
</script>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
