<?php
ob_start();

include('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');
include('functions.php');

// ------------------------------------------------------------
// Input / filters - mirrors the existing Weekly PDF report
// ------------------------------------------------------------
$formnumber   = $_POST['frm'] ?? null;
$recordnumber = $_POST['id'] ?? null;
$currweeknum  = date('W');

$whereClause = "p.showonweb = 'Yes' AND p.archived = 0 ";
$weeknums = [];
$subtitle = '';

if ($formnumber == 1) {
    switch ($recordnumber) {
        case 1:
            $whereClause = "p.weeknum = $currweeknum AND p.showonweb = 'Yes' AND p.archived = 0 ";
            $subtitle = "This Week [Week: " . $currweeknum . "]";
            $weeknums[] = ltrim($currweeknum, '0');
            break;

        case 2:
            $whereClause = "p.weeknum = " . ($currweeknum + 1) . " AND p.showonweb = 'Yes' AND p.archived = 0 ";
            $subtitle = "Next Week [Week: " . ($currweeknum + 1) . "]";
            $weeknums[] = ltrim($currweeknum + 1, '0');
            break;

        case 3:
            $whereClause = "p.weeknum = " . ($currweeknum - 1) . " AND p.showonweb = 'Yes' AND p.archived = 0 ";
            $subtitle = "Last Week [Week: " . ($currweeknum - 1) . "]";
            $weeknums[] = ltrim($currweeknum - 1, '0');
            break;

        case 4:
            $whereClause = "p.status < 4 AND p.showonweb = 'Yes' AND p.archived = 0 ";
            $subtitle = 'Status < 4';
            break;

        case 5:
            $whereClause = "p.depot = 11 AND p.showonweb = 'Yes' AND p.archived = 0 ";
            $subtitle = 'Heathfield Depot';
            break;

        case 6:
            $whereClause = "p.depot = 13 AND p.showonweb = 'Yes' AND p.archived = 0 ";
            $subtitle = 'North Tawton Depot';
            break;

        case 7:
            $whereClause = "p.expected_date = CURDATE() AND p.showonweb = 'Yes' AND p.archived = 0";
            $subtitle = "Today's Report [" . date('d M Y') . "]";
            break;

        case 8:
            $whereClause = "p.expected_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
            $subtitle = "Yesterday's Report [" . date('d M Y', strtotime('yesterday')) . "]";
            break;

        case 9:
            $whereClause = "p.expected_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND p.showonweb = 'Yes' AND p.archived = 0";
            $subtitle = "Tomorrow's Report [" . date('d M Y', strtotime('tomorrow')) . "]";
            break;

        case 10:
            $whereClause = "MONTH(p.expected_date) = MONTH(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))
                            AND YEAR(p.expected_date) = YEAR(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";
            $subtitle = 'Next Month [' . date('F Y', strtotime('+1 month')) . ']';
            break;

        case 11: // TANKER
            $whereClause = "p.weeknum = $currweeknum
                            AND p.customer = '17'
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0 ";
            $subtitle = "TANKER This Week [Week: " . $currweeknum . "]";
            break;

        default:
            $whereClause = "p.expected_date >= CURDATE()
                            AND p.expected_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                            AND p.showonweb = 'Yes'
                            AND p.archived = 0";
            $subtitle = 'Default';
    }
} elseif ($formnumber == 2) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $filters = [];

        if (!empty($_POST['fromDate']) && !empty($_POST['toDate'])) {
            $filters[] = "p.expected_date BETWEEN '" .
                $conn->real_escape_string($_POST['fromDate']) .
                "' AND '" .
                $conn->real_escape_string($_POST['toDate']) .
                "'";
        }

        if (!empty($_POST['includeDepots'])) {
            $includeDepots = implode(
                ',',
                array_map('intval', $_POST['includeDepots'])
            );

            $filters[] = "p.depot IN ($includeDepots)";
        }

        if (!empty($_POST['excludeDepots'])) {
            $excludeDepots = implode(
                ',',
                array_map('intval', $_POST['excludeDepots'])
            );

            $filters[] = "p.depot NOT IN ($excludeDepots)";
        }

        if (!empty($_POST['ehcLocations'])) {
            $ehcLocations = implode(
                ',',
                array_map('intval', $_POST['ehcLocations'])
            );

            $filters[] = "p.ehc_location IN ($ehcLocations)";
        }

        if (!empty($_POST['customer'])) {
            $customer = array_map(
                'intval',
                $_POST['customer']
            );

            $filters[] = 'p.account IN (' .
                implode(',', $customer) .
                ')';
        }

        if (!empty($_POST['status'])) {

            $statuses = implode(
                ',',
                array_map(function ($status) use ($conn) {
                    return "'" .
                        $conn->real_escape_string($status) .
                        "'";
                }, $_POST['status'])
            );

            $filters[] = "p.status IN ($statuses)";
        }

        if (!empty($filters)) {
            $whereClause = implode(
                ' AND ',
                $filters
            );
        }

        $whereClause .=
            " AND p.showonweb = 'Yes'
              AND p.archived = 0 ";
    }
}

// ------------------------------------------------------------
// Fetch data and organise it in same structure as Weekly PDF
// ------------------------------------------------------------
$data = fetchProductData(
    $conn,
    $whereClause
);

$processedData = preprocessTotals(
    $data
);


// ------------------------------------------------------------
// PDF helpers
// ------------------------------------------------------------

/**
 * Draw one standard information row.
 *
 * Labels:
 *      regular weight
 *      8.5pt
 *
 * Values:
 *      11pt
 *      bold where requested
 *      left aligned
 *      3mm left padding
 */
function preloadFieldRow(
    $pdf,
    $x,
    $y,
    $labelWidth,
    $valueWidth,
    $height,
    $label,
    $value = '',
    $valueBold = false
) {

    // --------------------------------------------------------
    // Label column
    // --------------------------------------------------------
    $pdf->SetXY(
        $x,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        '',
        8.5
    );

    $pdf->Cell(
        $labelWidth,
        $height,
        $label,
        1,
        0,
        'L',
        false
    );


    // --------------------------------------------------------
    // Draw blank bordered value cell
    // --------------------------------------------------------
    $valueX = $x + $labelWidth;

    $pdf->SetXY(
        $valueX,
        $y
    );

    $pdf->Cell(
        $valueWidth,
        $height,
        '',
        1,
        0,
        'L',
        false
    );


    // --------------------------------------------------------
    // Place actual value with left padding
    // --------------------------------------------------------
    $pdf->SetXY(
        $valueX + 3,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        $valueBold ? 'B' : '',
        11
    );

    $pdf->Cell(
        $valueWidth - 4,
        $height,
        (string)$value,
        0,
        0,
        'L',
        false
    );
}


/**
 * Convert time to 24-hour HH:mm format.
 */
function preloadFormatTime($value)
{
    $value = trim(
        (string)$value
    );

    if ($value === '') {
        return '';
    }

    $timestamp = strtotime(
        $value
    );

    if ($timestamp !== false) {
        return date(
            'H:i',
            $timestamp
        );
    }

    return $value;
}


/**
 * Draw one tanker preload sheet.
 */
function drawPreloadSheet(
    $pdf,
    $date,
    $row,
    $serialNumber
) {

    // --------------------------------------------------------
    // A4 landscape = 297 x 210 mm
    //
    // Form occupies only LEFT half of page.
    // Right half remains blank for cutting.
    // --------------------------------------------------------
    $outerX = 5;
    $outerY = 5;
    $outerW = 138;
    $outerH = 194;

    $tableX = 9;
    $tableW = 130;

    $labelW = 50;
    $valueW = 80;


    // --------------------------------------------------------
    // Data formatting
    // --------------------------------------------------------
    $dateStamp = strtotime(
        $date
    );

    $loadingDate = $dateStamp
        ? date('d M', $dateStamp)
        : '';

    $loadingDay = $dateStamp
        ? date('D', $dateStamp)
        : '';

    $loadingTime = preloadFormatTime(
        $row['expected_time']
        ?? ($row['time'] ?? '')
    );

    $exportVet = trim(
        (string)(
            $row['vet_name']
            ?? ''
        )
    );

    // Current booking report stores commercial
    // document / Batch Ref in "name".
    $commercialDoc = trim(
        (string)(
            $row['name']
            ?? (
                $row['PO']
                ?? (
                    $row['po']
                    ?? ''
                )
            )
        )
    );

    // Existing tanker data exposes DSV number
    // through "tanker".
    $dsvNumber = trim(
        (string)(
            $row['tanker']
            ?? ''
        )
    );


    // --------------------------------------------------------
    // Outer border
    // --------------------------------------------------------
    $pdf->SetLineWidth(
        0.25
    );

    $pdf->Rect(
        $outerX,
        $outerY,
        $outerW,
        $outerH
    );


    // --------------------------------------------------------
    // Title
    // --------------------------------------------------------
    $pdf->SetXY(
        15,
        11
    );

    $pdf->SetFont(
        'helvetica',
        'B',
        11
    );

    $pdf->Cell(
        118,
        8,
        'DSV TANKER INFORMATION SHEET',
        1,
        0,
        'C'
    );


    // --------------------------------------------------------
    // Main information rows
    //
    // Office Check / Before Sealing removed.
    // ARAL removed.
    // --------------------------------------------------------
    $y = 27;


    // Export Vet
    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'EXPORT VET',
        $exportVet,
        true
    );


    // Batch Ref
    $y += 10;

    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'Batch Ref',
        $commercialDoc,
        true
    );


    // Date of Loading
    $y += 10;

    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'Date of Loading',
        $loadingDate,
        true
    );


    // Day of Loading
    $y += 10;

    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'Day of Loading',
        $loadingDay,
        true
    );


    // Time of Loading
    $y += 10;

    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'Time of Loading',
        $loadingTime,
        true
    );


    // Number of Loads
    // Intentionally blank
    $y += 10;

    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'Number of Loads',
        '',
        false
    );


    // DSV Number
    $y += 10;

    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'DSV Number',
        $dsvNumber,
        true
    );


    // --------------------------------------------------------
    // SEAL NUMBER row
    //
    // This is deliberately larger/bold unlike other
    // labels in the left-hand column.
    // --------------------------------------------------------
    $y += 10;

    $sealLabelW   = 50;
    $sealValueW   = 52;
    $sealPictureW = 28;
    $sealHeight   = 15;


    // Seal Number label
    $pdf->SetXY(
        $tableX,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        'B',
        10
    );

    $pdf->Cell(
        $sealLabelW,
        $sealHeight,
        'SEAL NUMBER',
        1,
        0,
        'L'
    );


    // --------------------------------------------------------
    // Serial number bordered area
    // --------------------------------------------------------
    $serialX =
        $tableX +
        $sealLabelW;

    $pdf->SetXY(
        $serialX,
        $y
    );

    $pdf->Cell(
        $sealValueW,
        $sealHeight,
        '',
        1,
        0,
        'L'
    );


    // Serial number text
    $pdf->SetXY(
        $serialX + 3,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        'B',
        13
    );

    $pdf->Cell(
        $sealValueW - 4,
        $sealHeight,
        $serialNumber,
        0,
        0,
        'L'
    );


    // --------------------------------------------------------
    // Seal picture reminder
    // --------------------------------------------------------
    $pdf->SetXY(
        $serialX + $sealValueW,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        'B',
        8.5
    );

    $pdf->MultiCell(
        $sealPictureW,
        $sealHeight,
        "Seal Picture\nin situ taken",
        1,
        'C',
        false,
        0,
        '',
        '',
        true,
        0,
        false,
        true,
        $sealHeight,
        'M'
    );


    // --------------------------------------------------------
    // Top 1
    // --------------------------------------------------------
    $y += $sealHeight;

    preloadFieldRow(
        $pdf,
        $tableX,
        $y,
        $labelW,
        $valueW,
        10,
        'Top 1',
        ''
    );


    // --------------------------------------------------------
    // Top 2
    // --------------------------------------------------------
    $y += 10;

    $pdf->SetXY(
        $tableX,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        '',
        8.5
    );

    $pdf->Cell(
        $labelW,
        10,
        'Top 2',
        1,
        0,
        'L'
    );

    $pdf->Cell(
        $valueW,
        10,
        '',
        1,
        0,
        'L'
    );


    // --------------------------------------------------------
    // Top 3
    // --------------------------------------------------------
    $y += 10;

    $pdf->SetXY(
        $tableX,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        '',
        8.5
    );

    $pdf->Cell(
        $labelW,
        10,
        'Top 3',
        1,
        0,
        'L'
    );

    $pdf->Cell(
        $valueW,
        10,
        '',
        1,
        0,
        'L'
    );


    // --------------------------------------------------------
    // Rear Seal
    // --------------------------------------------------------
    $y += 10;

    $pdf->SetXY(
        $tableX,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        '',
        8.5
    );

    $pdf->Cell(
        $labelW,
        12,
        'Rear - Vet to apply',
        1,
        0,
        'L'
    );

    $pdf->Cell(
        $valueW,
        12,
        '',
        1,
        0,
        'L'
    );


    // --------------------------------------------------------
    // Net Weights
    // --------------------------------------------------------
    $y += 12;

    $pdf->SetXY(
        $tableX,
        $y
    );

    $pdf->SetFont(
        'helvetica',
        'BI',
        12
    );

    $pdf->Cell(
        50,
        20,
        'NET WEIGHTS',
        1,
        0,
        'C'
    );

    $pdf->Cell(
        55,
        20,
        '',
        1,
        0,
        'C'
    );

    $pdf->SetFont(
        'helvetica',
        'B',
        12
    );

    $pdf->Cell(
        25,
        20,
        'KG',
        1,
        0,
        'C'
    );


    // --------------------------------------------------------
    // Footer instruction
    // --------------------------------------------------------
    $y += 20;

    $pdf->SetXY(
        $tableX,
        $y + 2
    );

    $pdf->SetFont(
        'helvetica',
        'I',
        7.5
    );

    $footer =
        'VET: Keep Seals linked together / ensure in numerical order / ' .
        'sellotape Vet (rear) seal so top seal(s) are easily detachable';

    $pdf->MultiCell(
        $tableW,
        12,
        $footer,
        0,
        'C',
        false,
        0,
        '',
        '',
        true,
        0,
        false,
        true,
        12,
        'M'
    );
}


// ------------------------------------------------------------
// Create PDF
//
// A4 landscape.
// Form occupies LEFT HALF.
// Right half intentionally blank.
// ------------------------------------------------------------
$pdf = new TCPDF(
    'L',
    'mm',
    'A4',
    true,
    'UTF-8',
    false
);

$pdf->SetCreator(
    PDF_CREATOR
);

$pdf->SetAuthor(
    'North Park Vets TANKER Export'
);

$pdf->SetTitle(
    'DSV Tanker PreLoad Information Sheet'
);

$pdf->SetSubject(
    'DSV Tanker PreLoad Information Sheet'
);

$pdf->SetKeywords(
    'TCPDF, tanker, preload, DSV'
);

$pdf->setPrintHeader(
    false
);

$pdf->setPrintFooter(
    false
);

$pdf->SetMargins(
    0,
    0,
    0
);

$pdf->SetAutoPageBreak(
    false,
    0
);


// ------------------------------------------------------------
// Serial Number
//
// Hardcoded for now.
// Later this can become a parameter / database setting.
// ------------------------------------------------------------
$serialNumber = '00589_';

$sheetCount = 0;


// ------------------------------------------------------------
// Create one sheet for each returned tanker record
// ------------------------------------------------------------
if (!empty($processedData)) {

    foreach ($processedData as $date => $dateData) {

        if ($date === 'totals') {
            continue;
        }

        foreach ($dateData as $depot => $depotData) {

            if (
                $depot === 'totals'
                ||
                empty($depotData['rows'])
            ) {
                continue;
            }

            foreach ($depotData['rows'] as $row) {

                $pdf->AddPage(
                    'L',
                    'A4'
                );

                drawPreloadSheet(
                    $pdf,
                    $date,
                    $row,
                    $serialNumber
                );

                $sheetCount++;
            }
        }
    }
}


// ------------------------------------------------------------
// Nothing returned
// ------------------------------------------------------------
if ($sheetCount === 0) {

    $pdf->AddPage(
        'L',
        'A4'
    );

    $pdf->SetXY(
        10,
        20
    );

    $pdf->SetFont(
        'helvetica',
        'B',
        12
    );

    $pdf->Cell(
        130,
        10,
        'No data available for the selected filters.',
        1,
        0,
        'C'
    );
}


// ------------------------------------------------------------
// Output PDF
// ------------------------------------------------------------
ob_end_clean();

$pdf->Output(
    'tanker_preload_' .
    date('Ymd') .
    '.pdf',
    'I'
);
?>