<?php
ob_start();

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');
include('functions.php'); // Include shared functions


// Check for filters (e.g., formnumber and recordnumber)
$username = $user["firstname"] . " " . $user["surname"];
$formnumber = $_POST['frm'] ?? null;
$recordnumber = $_POST['id'] ?? null;
$currweeknum = date("W");

// Define default WHERE clause
$whereClause = "p.showonweb = 'Yes' AND p.archived = 0 ";


if ($formnumber == 1) {

    // Hardcoded SQL based on id
    switch ($recordnumber) {

        case 1:
            $whereClause = "p.weeknum = $currweeknum
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "This Week [Week: " . $currweeknum . "]";
        break;


        case 2:
            $whereClause = "p.weeknum = " . ($currweeknum + 1) . "
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Next Week [Week: " . ($currweeknum + 1) . "]";
        break;


        case 3:
            $whereClause = "p.weeknum = " . ($currweeknum - 1) . "
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Last Week [Week: " . ($currweeknum - 1) . "]";
        break;


        case 4:
            $whereClause = "p.status < 4
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Status < 4";
        break;


        case 5:
            $whereClause = "p.depot = 11
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Heathfield Depot";
        break;


        case 6:
            $whereClause = "p.depot = 13
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "North Tawton Depot";
        break;


        case 7:
            // Today's report
            $whereClause = "p.expected_date = CURDATE()
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Today's Report [" . date("d M Y") . "]";
        break;


        case 8:
            // Yesterday's report
            $whereClause = "p.expected_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Yesterday's Report [" . date("d M Y", strtotime("yesterday")) . "]";
        break;


        case 9:
            // Tomorrow's report
            $whereClause = "p.expected_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Tomorrow's Report [" . date("d M Y", strtotime("tomorrow")) . "]";
        break;


        case 10:
            // Next Month
            $whereClause = "MONTH(p.expected_date) = MONTH(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))
                            AND YEAR(p.expected_date) = YEAR(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Next Month [" . date("F Y", strtotime("+1 month")) . "]";
        break;


        case 11:
            // TANKER
            $whereClause = "p.weeknum = $currweeknum
                            AND p.customer = '17'
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "TANKER This Week [Week: " . $currweeknum . "]";
        break;


        default:
            $whereClause = "p.expected_date >= CURDATE()
                            AND p.expected_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";

            $subtitle = "Default show a lot....";
    }

} elseif ($formnumber == 2) {

    // Construct SQL dynamically based on POST values
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $filters = [];
        $subtitleParts = [];

        if (!empty($_POST['fromDate']) && !empty($_POST['toDate'])) {

            $filters[] =
                "p.expected_date BETWEEN '" .
                $conn->real_escape_string($_POST['fromDate']) .
                "' AND '" .
                $conn->real_escape_string($_POST['toDate']) .
                "'";

            $subtitleParts[] =
                "From " .
                htmlspecialchars($_POST['fromDate']) .
                " to " .
                htmlspecialchars($_POST['toDate']);
        }


        if (!empty($_POST['includeDepots'])) {

            $includeDepots = implode(
                ",",
                array_map('intval', $_POST['includeDepots'])
            );

            $filters[] = "p.depot IN ($includeDepots)";

            $subtitleParts[] =
                "Including depots: " .
                implode(", ", $_POST['includeDepots']);
        }


        if (!empty($_POST['excludeDepots'])) {

            $excludeDepots = implode(
                ",",
                array_map('intval', $_POST['excludeDepots'])
            );

            $filters[] = "p.depot NOT IN ($excludeDepots)";

            $subtitleParts[] =
                "Excluding depots: " .
                implode(", ", $_POST['excludeDepots']);
        }


        if (!empty($_POST['ehcLocations'])) {

            $ehcLocations = implode(
                ",",
                array_map('intval', $_POST['ehcLocations'])
            );

            $filters[] = "p.ehc_location IN ($ehcLocations)";

            $subtitleParts[] =
                "EHC Locations: " .
                implode(", ", $_POST['ehcLocations']);
        }


        if (!empty($_POST['customer'])) {

            $customer = array_map(
                'intval',
                $_POST['customer']
            );

            $filters[] =
                "p.account IN (" .
                implode(",", $customer) .
                ")";

            $customerNames = getNamesFromIds(
                $conn,
                'npe_customer',
                'id',
                'name',
                $customer
            );

            $subtitleParts[] =
                "Customer: " .
                implode(", ", $customerNames);
        }


        if (!empty($_POST['status'])) {

            $statuses = implode(
                ",",
                array_map(
                    function ($status) use ($conn) {
                        return "'" .
                            $conn->real_escape_string($status) .
                            "'";
                    },
                    $_POST['status']
                )
            );

            $filters[] = "p.status IN ($statuses)";

            $subtitleParts[] =
                "Statuses: " .
                implode(", ", $_POST['status']);
        }


        // Combine filters into the WHERE clause
        if (!empty($filters)) {
            $whereClause = implode(" AND ", $filters);
        }


        $whereClause =
            $whereClause .
            " AND `p`.`showonweb` = 'Yes'
              AND `p`.`archived` = 0 ";


        // Construct the subtitle
        if (!empty($subtitleParts)) {

            $subtitle = implode("; ", $subtitleParts);

        } else {

            $subtitle = "No specific filters applied.";
        }
    }
}


// Fetch and process data
$data = fetchProductData($conn, $whereClause);
$processedData = preprocessTotals($data);


// Create a new PDF instance in Landscape Mode
$pdf = new TCPDF(
    'L',
    PDF_UNIT,
    PDF_PAGE_FORMAT,
    true,
    'UTF-8',
    false
);


// Set PDF metadata
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('North Park Vets TANKER Export');
$pdf->SetTitle('Booking Report');
$pdf->SetSubject('Booking Report by Depot');
$pdf->SetKeywords('TCPDF, PDF, report, weekly, booking');


// Add a page
$pdf->AddPage();


// Main heading
$pdf->SetFont('helvetica', 'B', 14);

$pdf->Cell(
    0,
    10,
    'North Park TANKER Exports Booking Sheet',
    0,
    1,
    'C'
);


// Subtitle
$pdf->SetFont('helvetica', '', 12);

$pdf->Cell(
    0,
    8,
    $subtitle,
    0,
    1,
    'C'
);


// No comments / week comments section.
// No additional blank space here so the table begins immediately.


// Check if there is any data
if (empty($processedData)) {

    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(
        0,
        10,
        'No data available for the selected filters.',
        0,
        1,
        'C'
    );

} else {

    // Table Column Headers
    $columnWidths = [
        'Details' => 220,
        'PCloud'  => 20,
        'Vet'     => 20,
        'Total'   => 15
    ];


    // Add the table headers
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('', 'B');


    // Header row
    $pdf->Cell(
        $columnWidths['Details'],
        7,
        'Details',
        1,
        0,
        'C',
        1
    );

    $pdf->Cell(
        $columnWidths['PCloud'],
        7,
        'PCloud',
        1,
        0,
        'C',
        1
    );

    $pdf->Cell(
        $columnWidths['Vet'],
        7,
        'Vet',
        1,
        0,
        'C',
        1
    );

    $pdf->Cell(
        $columnWidths['Total'],
        7,
        'Total',
        1,
        1,
        'C',
        1
    );


    // Reset font for data rows
    $pdf->SetFont('', '');


    // Alternating row colours
    $fill = false;


    // Add Data Rows
    foreach ($processedData as $date => $dateData) {

        if ($date === 'totals') {
            continue;
        }


        $formatted_date = date(
            "d M Y",
            strtotime($date)
        );

        $day_only = date(
            "D",
            strtotime($date)
        );

        $dateTotals = $dateData['totals'];


        // -------------------------------------------------
        // DATE HEADER
        // -------------------------------------------------

        $pdf->SetFont(
            'helvetica',
            'B',
            12
        );

        $pdf->SetFillColor(
            200,
            200,
            255
        );


        $pdf->Cell(
            array_sum($columnWidths) - $columnWidths['Total'],
            10,
            "$formatted_date ($day_only)",
            1,
            0,
            'L',
            1
        );


        $pdf->Cell(
            $columnWidths['Total'],
            10,
            $dateTotals['Total'],
            1,
            1,
            'C',
            1
        );


        // Reset font for detail rows
        $pdf->SetFont(
            'helvetica',
            '',
            10
        );


        // -------------------------------------------------
        // DEPOT GROUP
        //
        // Depot grouping is retained internally because the
        // processed data is structured by depot.
        //
        // The visible "Depot: XXXXX" row has been removed.
        // -------------------------------------------------

        foreach ($dateData as $depot => $depotData) {

            if ($depot === 'totals') {
                continue;
            }


            // Individual booking rows
            foreach ($depotData['rows'] as $row) {

                $expected_time =
                    $row['expected_time'] ?? '00:00';

                $time_object =
                    new DateTime($expected_time);


                if ($row['destination_country_code']) {

                    $country_code =
                        $row['destination_country_code'];

                } else {

                    $country_code =
                        $row['destination_country_name'];
                }


                // Concatenate details into one cell
                $details = implode(
                    ' | ',
                    [
                        $time_object->format('H:i'),
                        $row['customer_name'],
                        $row['name'],
                        $country_code,
                        $row['product_type'],
                        $row['pallet_type'],
                        $row['tanker'],
                        $row['ehc_ref']
                    ]
                );


                // No value required in row total column
                $RowTotalNull = '';


                // Alternating row colours
                $pdf->SetFillColor(
                    $fill ? 240 : 255,
                    240,
                    240
                );


// Slightly deeper data rows - 10% increase from 6 to 6.6
$rowHeight = 10.0;

// Details
$pdf->Cell(
    $columnWidths['Details'],
    $rowHeight,
    $details,
    1,
    0,
    'L',
    $fill,
    '',
    0,
    false,
    'T',
    'M'
);

// PCloud
$pdf->Cell(
    $columnWidths['PCloud'],
    $rowHeight,
    $row['pcloud'],
    1,
    0,
    'L',
    $fill,
    '',
    0,
    false,
    'T',
    'M'
);

// Vet
$pdf->Cell(
    $columnWidths['Vet'],
    $rowHeight,
    $row['vet_name'],
    1,
    0,
    'C',
    $fill,
    '',
    0,
    false,
    'T',
    'M'
);

// Total - intentionally blank
$pdf->Cell(
    $columnWidths['Total'],
    $rowHeight,
    $RowTotalNull,
    1,
    1,
    'C',
    $fill,
    '',
    0,
    false,
    'T',
    'M'
);


                // Toggle fill for alternating colours
                $fill = !$fill;
            }
        }
    }
}


// Debug Print of array
/*
$pdf->SetFont('helvetica', 'I', 8);

$pdf->MultiCell(
    0,
    10,
    "Debug Row: " . print_r($row, true),
    0,
    'L',
    false
);
*/


// Footer Text Below Table
$pdf->Ln(5);

$pdf->SetFont(
    'helvetica',
    'I',
    8
);

$pdf->Cell(
    0,
    10,
    'Report generated on: ' .
    date('d/m/Y H:i:s') .
    ' By: ' .
    $username,
    0,
    1,
    'L'
);


// Output the PDF
ob_end_clean();

$pdf->Output(
    "booking_report_" . date('Ymd') . ".pdf",
    'I'
);
?>