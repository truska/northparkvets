<?php
ob_start();

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');
include('functions.php'); // Include shared functions

error_log("DEBUG vetName: " . print_r($_POST['vetName'] ?? 'MISSING', true));

// Check for filters (e.g., formnumber and recordnumber)
$username = $user["firstname"] . " " . $user["surname"] ;
$formnumber = $_POST['frm'] ?? null;
$recordnumber = $_POST['id'] ?? null;



// moved to a funtion call
extract(buildReportFilter($formnumber, $recordnumber, $_POST, $conn));

/*
    $currweeknum = date("W");

    // Define default WHERE clause
    $whereClause = "p.showonweb = 'Yes' AND p.archived = 0 ";

    $weeknums = [];


    if ($formnumber == 1) {
        // Hardcoded SQL based on id
        switch ($recordnumber) {
            case 1:
                $whereClause = "p.weeknum = $currweeknum AND p.showonweb = 'Yes' AND p.archived = 0 " ;             // This week
                $subtitle  = "This Week [Week: ".$currweeknum."]" ;
                $weeknums[] = ltrim($currweeknum, '0'); // Remove leading zero
                break;
            case 2:
                $whereClause = "p.weeknum = " . ($currweeknum + 1 )." AND p.showonweb = 'Yes' AND p.archived = 0 " ;     // Next week
                $subtitle  = "Next Week [Week: ".($currweeknum + 1 )."]" ;
                $weeknums[] = ltrim($currweeknum + 1, '0');
                break;
            case 3:
                $whereClause = "p.weeknum = " . ($currweeknum - 1)." AND p.showonweb = 'Yes' AND p.archived = 0  " ;    // Last Week
                $subtitle  = "Last Week [Week: ".($currweeknum - 1)."]" ;
                $weeknums[] = ltrim($currweeknum - 1, '0');
                break;
            case 4:
                $whereClause = "p.status < 4 AND p.showonweb = 'Yes' AND p.archived = 0  " ;    // less than approved
                $subtitle  = "Status < 4 " ;
                $startWeek = date("W", strtotime("first day of this month"));
                $endWeek = date("W", strtotime("last day of this month"));
                for ($w = $startWeek; $w <= $endWeek; $w++) {
                    $weeknums[] = ltrim($w, '0');                                               // Ensure no leading zeros
                }
                break;
            case 5:
                $whereClause = "p.depot = 11 AND p.showonweb = 'Yes' AND p.archived = 0  " ;    // Heathfield
                $subtitle  = "Heathfield Depot" ;
                break;
            case 6:
                $whereClause = "p.depot = 13 AND p.showonweb = 'Yes' AND p.archived = 0 " ;     // North Tawton
                $subtitle  = "North Tawton Depot" ;
                break;

                case 7: // Today's report
                    $whereClause = "p.expected_date = CURDATE() AND p.showonweb = 'Yes' AND p.archived = 0";
                    $subtitle = "Today's Report [" . date("d M Y") . "]";
                    $weeknums[] = $currweeknum;
                    break;
            
                case 8: // Yesterday's report
                    $whereClause = "p.expected_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
                    $subtitle = "Yesterday's Report [" . date("d M Y", strtotime("yesterday")) . "]";
                    $yesterday = date("Y-m-d", strtotime("-1 day"));
                    $weeknums[] = date("W", strtotime($yesterday));
                    break;

                case 9: // Tomorrow's report
                    $whereClause = "p.expected_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
                    $subtitle = "Tomorrow's Report [" . date("d M Y", strtotime("tomorrow")) . "]";
                    $tomorrow = date("Y-m-d", strtotime("+1 day"));
                    $weeknums[] = date("W", strtotime($tomorrow));
                    break;    

            case 10: // Next Month
                $whereClause = "MONTH(p.expected_date) = MONTH(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(p.expected_date) = YEAR(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) AND p.showonweb = 'Yes' AND p.archived = 0";
                $subtitle  = "Next Month [" . date("F Y", strtotime("+1 month")) . "]" ;
            
                $startWeek = date("W", strtotime("first day of next month"));
                $endWeek = date("W", strtotime("last day of next month"));
                for ($w = $startWeek; $w <= $endWeek; $w++) {
                    $weeknums[] = $w;
                }
                break;

            default:
                $whereClause = "p.expected_date >= CURDATE() AND p.expected_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)  AND p.showonweb = 'Yes'  AND p.archived = 0  "; 
                $subtitle  = "Default show a lot...." ;
        }
    } elseif ($formnumber == 2) {
        // Construct SQL dynamically based on POST values
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filters = [];

            $subtitleParts = []; // Array to hold subtitle components

            if (!empty($_POST['fromDate']) && !empty($_POST['toDate'])) {
                $filters[] = "p.expected_date BETWEEN '" . $conn->real_escape_string($_POST['fromDate']) . "' AND '" . $conn->real_escape_string($_POST['toDate']) . "'";
                $subtitleParts[] = "From " . htmlspecialchars($_POST['fromDate']) . " to " . htmlspecialchars($_POST['toDate']);
            }
            if (!empty($_POST['includeDepots'])) {
                $includeDepots = implode(",", array_map('intval', $_POST['includeDepots']));
                $filters[] = "p.depot IN ($includeDepots)";
                $subtitleParts[] = "Including depots: " . implode(", ", $_POST['includeDepots']);
            }
            if (!empty($_POST['excludeDepots'])) {
                $excludeDepots = implode(",", array_map('intval', $_POST['excludeDepots']));
                $filters[] = "p.depot NOT IN ($excludeDepots)";
                $subtitleParts[] = "Excluding depots: " . implode(", ", $_POST['excludeDepots']);
            }
            if (!empty($_POST['ehcLocations'])) {
                $ehcLocations = implode(",", array_map('intval', $_POST['ehcLocations']));
                $filters[] = "p.ehc_location IN ($ehcLocations)";
                $subtitleParts[] = "EHC Locations: " . implode(", ", $_POST['ehcLocations']);
            }
            if (!empty($_POST['customer'])) {
                $customer = array_map('intval', $_POST['customer']);
                $filters[] = "p.account IN (" . implode(",", $customer) . ")";
                $customerNames = getNamesFromIds($conn, 'npe_customer', 'id', 'name', $customer);
                $subtitleParts[] = "Customer: " . implode(", ", $customerNames);
            }
            if (!empty($_POST['status'])) {
                $statuses = implode(",", array_map(function ($status) use ($conn) {
                    return "'" . $conn->real_escape_string($status) . "'";
                }, $_POST['status']));
                $filters[] = "p.status IN ($statuses)";
                $subtitleParts[] = "Statuses: " . implode(", ", $_POST['status']);
            }
        
            // Combine filters into the WHERE clause
            if (!empty($filters)) {
                $whereClause = implode(" AND ", $filters);
            }
            
            $whereClause = $whereClause. " AND `p`.`showonweb` = 'Yes' AND `p`.`archived` = 0 " ;

            // Construct the subtitle
            if (!empty($subtitleParts)) {
                $subtitle = "" . implode("; ", $subtitleParts);
            } else {
                $subtitle = "No specific filters applied.";
            }
        }
    }
*/

// Fetch and process data
$data = fetchProductData($conn, $whereClause);
$processedData = preprocessTotals($data);
$comments = fetchWeekComments($conn, $weeknums);

// Create a new PDF instance in Portrait Mode
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set PDF metadata
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('North Park Vets Export');
$pdf->SetTitle('Booking Report');
$pdf->SetSubject('Booking Report by Depot');
$pdf->SetKeywords('TCPDF, PDF, report, weekly, booking');

// Add a page
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'North Park Exports Booking Sheet', 0, 1, 'C');

// Add a Subtitle
$pdf->SetFont('helvetica', '', 12); // Slightly smaller, non-bold font
$pdf->Cell(0, 8, $subtitle, 0, 1, 'C'); // Subtitle centered
$pdf->Ln(5); // Add spacing below the subtitle


// Display Comments (If Available)
if (!empty($comments)) {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(240, 240, 240); // Light Gray Background
    $pdf->SetTextColor(50, 50, 50); // Darker text for readability
    $pdf->MultiCell(0, 6, "Comments:", 0, 'L', false);
    
    foreach ($comments as $weeknum => $comment) {
       // $pdf->MultiCell(0, 6, "Week " . htmlspecialchars($weeknums[$weeknum]) . " - " . htmlspecialchars($comment), 0, 'L', false);
        $pdf->MultiCell(0, 6, "Week " . htmlspecialchars($weeknum) . " - " . htmlspecialchars($comment), 0, 'L', false);

    }

    $pdf->Ln(5); // Space below comments
}

// Check if there is any data
if (empty($processedData)) {
    $pdf->SetTextColor(0, 0, 0); // Reset text color
    $pdf->Cell(0, 10, 'No data available for the selected filters.', 0, 1, 'C');
} else {
    // Table Column Headers
    $columnWidths = [
        'Details' => 135, // Combined column for details
        'PCloud' => 20,   // Smaller column
        'Vet' => 20,      // Smaller column
        'Total' => 15     // Smaller column
    ];

    // Add the table headers
    $pdf->SetFillColor(220, 220, 220); // Light gray for headers
    $pdf->SetTextColor(0, 0, 0);       // Black text
    $pdf->SetFont('', 'B');            // Bold font

    // Header row
    $pdf->Cell($columnWidths['Details'], 7, 'Details', 1, 0, 'C', 1);
    $pdf->Cell($columnWidths['PCloud'], 7, 'PCloud', 1, 0, 'C', 1);
    $pdf->Cell($columnWidths['Vet'], 7, 'Vet', 1, 0, 'C', 1);
    $pdf->Cell($columnWidths['Total'], 7, 'Total', 1, 1, 'C', 1);

    // Reset font for data rows
    $pdf->SetFont('', '');

    // Add Data Rows
    $fill = false; // Alternating row colors
    foreach ($processedData as $date => $dateData) {
        if ($date === 'totals') continue;

        $formatted_date = date("d M Y", strtotime($date));
        $day_only = date("D", strtotime($date));
        $dateTotals = $dateData['totals'];

        // Date Header (Level 1)
        $pdf->SetFont('helvetica', 'B', 12); // Bigger, bolder font for date
        $pdf->SetFillColor(200, 200, 255);   // Light blue for Level 1
        $pdf->Cell(array_sum($columnWidths) - $columnWidths['Total'], 10, "$formatted_date ($day_only)", 1, 0, 'L', 1); // Text span
        $pdf->Cell($columnWidths['Total'], 10, $dateTotals['Total'], 1, 1, 'C', 1); // Totals in last column
        $pdf->SetFont('helvetica', '', 10);  // Reset font for the next rows

        foreach ($dateData as $depot => $depotData) {
            if ($depot === 'totals') continue;

            $depot_name = $depotData['depot_name'] ?? 'Unknown Depot';
            $depotTotals = $depotData['totals'];

            // Depot Header (Level 2)
            $pdf->SetFillColor(220, 240, 255); // Lighter blue for Level 2
            $pdf->Cell(array_sum($columnWidths) - $columnWidths['Total'], 7, "Depot: $depot_name", 1, 0, 'L', 1); // Text span
            $pdf->Cell($columnWidths['Total'], 7, $depotTotals['Total'], 1, 1, 'C', 1); // Totals in last column

            foreach ($depotData['rows'] as $row) {
                $expected_time = $row['expected_time'] ?? '00:00';
                $time_object = new DateTime($expected_time);

                if($row['destination_country_code']) {$country_code = $row['destination_country_code'];} else {$country_code = $row['destination_country_name'];}

                // Concatenate details into one cell
                $details = implode(
                    ' | ',
                    [
                        $time_object->format('H:i'),
                        $row['customer_name'],
                        $row['name'],
                        $country_code,
                        $row['destination_customer'] ,
                        "{$row['pallet_count']} x {$row['pallet_type']}",
                        $row['product_type']
                    ]
                );

                // Data Row
                $RowTotalNull = '' ;
                $pdf->SetFillColor($fill ? 240 : 255, 240, 240); // Alternating row colors
                $pdf->Cell($columnWidths['Details'], 6, $details, 1, 0, 'L', $fill);
                $pdf->Cell($columnWidths['PCloud'], 6, $row['pcloud'], 1, 0, 'L', $fill);
                $pdf->Cell($columnWidths['Vet'], 6, $row['vet_name'], 1, 0, 'C', $fill);
             //  $pdf->Cell($columnWidths['Total'], 6, $row['RowTotal'], 1, 1, 'C', $fill);
                $pdf->Cell($columnWidths['Total'], 6, $RowTotalNull, 1, 1, 'C', $fill);
                // Toggle fill for alternating colors
                $fill = !$fill;
            }
        }
    }
}

// Debug Print of array
/*
$pdf->SetFont('helvetica', 'I', 8);
$pdf->MultiCell(0, 10, "Debug Row: " . print_r($row, true), 0, 'L', false);
*/

// Add Footer Text Below Table
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Report generated on: ' . date('d/m/Y H:i:s').' By: '.$username, 0, 1, 'L');

// Output the PDF
ob_end_clean();
$pdf->Output("booking_report_" . date('Ymd') . ".pdf", 'I');
?>
