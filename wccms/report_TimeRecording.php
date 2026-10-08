<!-- START report_TimeRecording -->

<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(0);
        // Turn on error reporting
        // error_reporting(1);

        include ('setting/main-top-files.php'); // loads Session, Datebase Connectons, functions and more
        include("include/header-code.php");

        // Fetch vet options
        $vetOptions = getVetOptionsTime($conn);

        //Requires `frm` and `id` parameteres on url - if not needed comment out next lines
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
    include("include/header.php"); // Added by salva TDR | 2.12.2022
    include("include/sidebar.php");
    ?>
    <!--/ Any Custom styles for alignment and indentation -->
    <style>
        /* Styles here if needed */
    </style>

    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="row">
                <div class="col-8">
                    <?php
                        echo "<h1>".$prefs["prefSiteName"]."</h1>" ;
                        echo "<p> Debug on/off: ".$prefs["prefCMSDebugOn"]."</p>" ;
                    ?>
                    </div>
                <div class="col-4">

                    <h3>Area for more info</h3>

                </div>

                <?php
                ?>

<div class="table-responsive">
<table id="reportingTable" class="display table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Vet</th>
            <th>Name</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Time OV</th>
            <th>Time CSO</th>
            <th>Travel Units</th>
            <th>Travel Miles</th>
            <th>Certs</th>
            <th>SHA SA</th>
            <th>Courier</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th colspan="5" style="text-align:right">Totals:</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </tfoot>
</table>
</div>
<?php                
?>

            </div>

        <?php
        // include("include/footer.php"); // - footer.php doesn't exist inside wccms/include folder - Salva TDR | 17.1.2023
        // echo "</div>"; // Removed by salva TDR | 17.1.2023
        include ("include/footer-code.php");
        include ("include-tinymce.php");
        ?>

        </section>
<!--
    <script>


$(document).ready(function () {
    let table = $('#reportingTable').DataTable({
        "processing": true,
        "serverSide": false, // Disable server-side processing for static data

        "data": [ // Static test data
            {
                "id": "1",
                "vet": "Roger",
                "name": "Test Name",
                "date": "2024-01-01",
                "customer_name": "Test Customer",
                "time_ov": "10",
                "time_cso": "5",
                "travel_units": "3",
                "travel_miles": "15",
                "certs": "2",
                "sha_sa": "1",
                "courier": "20.00"
            },
            {
                "id": "2",
                "vet": "Hayden",
                "name": "Sample Name",
                "date": "2024-01-02",
                "customer_name": "Sample Customer",
                "time_ov": "15",
                "time_cso": "10",
                "travel_units": "5",
                "travel_miles": "25",
                "certs": "3",
                "sha_sa": "2",
                "courier": "30.00"
            }
        ],
        "columns": [
            { "data": "id", "title": "ID" },
            { "data": "vet", "title": "Vet" },
            { "data": "name", "title": "Name" },
            { "data": "date", "title": "Date" },
            { "data": "customer_name", "title": "Customer" },
            { "data": "time_ov", "title": "Time<br>OV", "className": "text-right" },
            { "data": "time_cso", "title": "Time<br>CSO", "className": "text-right" },
            { "data": "travel_units", "title": "Travel<br>Units", "className": "text-right" },
            { "data": "travel_miles", "title": "Travel<br>Miles", "className": "text-right" },
            { "data": "certs", "title": "Certs", "className": "text-right" },
            { "data": "sha_sa", "title": "SHA<br>SA", "className": "text-right" },
            { "data": "courier", "title": "Courier", "className": "text-right" }
        ],
        "paging": true,
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "ordering": true,
        "searching": true,
        "dom": '<"top"fB>rt<"bottom"lip><"clear">',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print',
        ]
    });
});


    </script>

-->
    <script>


$(document).ready(function () {
    let table = $('#reportingTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax_fetch_time_reporting_data.php",
            "type": "GET",
            "dataSrc": function (json) {
            error_log("AJAX Response:", json); // Log the entire response
            return json.data; // Return the data to DataTables for rendering
        }
        },
"columns": [
    { "data": "id", "title": "ID" },
    { "data": "vet", "title": "Vet" },
    { "data": "name", "title": "Name" },
    { "data": "date", "title": "Date" },
    { "data": "customer_name", "title": "Customer" }, // New column
    { "data": "time_ov", "title": "Time<br>OV", "className": "text-right" },
    { "data": "time_cso", "title": "Time<br>CSO", "className": "text-right" },
    { "data": "travel_units", "title": "Travel<br>Units", "className": "text-right" },
    { "data": "travel_miles", "title": "Travel<br>Miles", "className": "text-right" },
    { "data": "certs", "title": "Certs", "className": "text-right" },
    { "data": "sha_sa", "title": "SHA<br>SA", "className": "text-right" },
    { "data": "courier", "title": "Courier", "className": "text-right" },
],
        "paging": true,
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "ordering": true,
        "searching": true,
        "initComplete": function () {
        console.log("DataTable Initialization Complete"),
        "footerCallback": function (row, data, start, end, display) {
            let api = this.api();

            // Update totals for numerical columns
            [5, 6, 7, 8, 9, 10, 11].forEach(function (colIdx) {
                let total = api
                    .column(colIdx)
                    .data()
                    .reduce((a, b) => parseFloat(a) + parseFloat(b), 0);

                let pageTotal = api
                    .column(colIdx, { page: 'current' })
                    .data()
                    .reduce((a, b) => parseFloat(a) + parseFloat(b), 0);

                $(api.column(colIdx).footer()).html(`${pageTotal.toFixed(2)} (${total.toFixed(2)})`);
            });
        },
        "dom": '<"top"fB>rt<"bottom"lip><"clear">',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print',
        ],

        "error": function (xhr, error, thrown) { // Error handling added here
            console.error("DataTables Error:", error, thrown);
            console.log("XHR Response:", xhr.responseText);

        },



"initComplete": function () {
    let api = this.api();

    // Add a new row below the header for search inputs
    $('#reportingTable thead').append('<tr></tr>');
    $('#reportingTable thead tr:eq(1)').html(
        $('#reportingTable thead tr:eq(0) th').map(function () {
            return '<th></th>';
        }).get().join('')
    );

    // Add simple text inputs for testing search functionality
    api.columns().every(function (index) {
        if (index !== 1) { // Skip vet column temporarily
            $('<input type="text" class="form-control" placeholder="Search" />')
                .appendTo($('#reportingTable thead tr:eq(1) th').eq(index))
                .on('keyup change', function () {
                    api.column(index).search(this.value).draw();
                });
        }
    });
},





</script>

    
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>

<!-- END report_TmeRecording -->