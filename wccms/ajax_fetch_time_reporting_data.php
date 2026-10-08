<?php
include('setting/main-top-files.php'); // Include DB setup

header('Content-Type: application/json');

// Fetch DataTables parameters
$request = $_GET;
$limit = intval($request['length'] ?? 50); // Number of records per page
$offset = intval($request['start'] ?? 0); // Offset for pagination
$searchValue = $request['search']['value'] ?? ''; // Global search value
$columns = $request['columns'] ?? []; // Column-specific search values

// Determine ordering
$orderColumnIndex = $request['order'][0]['column'] ?? 0; // Default to first column
$orderDirection = $request['order'][0]['dir'] ?? 'asc'; // Default to ascending
$orderColumnName = $columns[$orderColumnIndex]['data'] ?? 'id'; // Match column name with data key

// Ensure the order column is safe and valid
$validColumns = ['id', 'vet', 'name', 'date', 'time_ov', 'time_cso', 'travel_units', 'travel_miles', 'certs', 'sha_sa', 'courier'];
if (!in_array($orderColumnName, $validColumns)) {
    $orderColumnName = 'id'; // Default fallback
}

// Define base query
// Define base query with new joins
$query = "
SELECT 
    npe_timesheets.id,
    npe_vet.name AS vet,
    npe_timesheets.name,
    npe_timesheets.date,
    COALESCE(npe_customer.name, 'Unknown') AS customer_name,
    npe_timesheets.time_ov,
    npe_timesheets.time_cso,
    npe_timesheets.travel_units,
    npe_timesheets.travel_miles,
    npe_timesheets.certs,
    npe_timesheets.sha_sa,
    npe_timesheets.courier 
    FROM npe_timesheets
LEFT JOIN npe_vet ON npe_timesheets.vet = npe_vet.id
LEFT JOIN products ON TRIM(LOWER(npe_timesheets.name)) = TRIM(LOWER(products.name))
LEFT JOIN npe_customer ON products.account = npe_customer.id 
WHERE npe_timesheets.showonweb = 'Yes' AND npe_timesheets.archived = 0 ";

// Add global search filter
$searchQuery = '';
if (!empty($searchValue)) {
    $escapedSearch = $conn->real_escape_string($searchValue);
    $searchQuery = " WHERE (
        npe_timesheets.name LIKE '%$escapedSearch%' OR 
        npe_vet.name LIKE '%$escapedSearch%' OR
        npe_timesheets.date LIKE '%$escapedSearch%' OR
        npe_timesheetscustomer.name LIKE '%$escapedSearch%'
    )";
}

console.log("AJAX Response:", xhr.responseJSON);

// Add column-specific filters
$columnSearchFilters = [];
foreach ($columns as $index => $column) {
    $columnSearch = $column['search']['value'] ?? '';
    if (!empty($columnSearch)) {
        $escapedColumnSearch = $conn->real_escape_string($columnSearch);
        switch ($index) {
            case 1: // Vet
                $columnSearchFilters[] = "npe_timesheets.vet = '$escapedColumnSearch'";
                break;
            case 2: // Name
                $columnSearchFilters[] = "npe_timesheets.name LIKE '%$escapedColumnSearch%'";
                break;
            case 3: // Date
                $columnSearchFilters[] = "npe_timesheets.date LIKE '%$escapedColumnSearch%'";
                break;
            case 4: // Customer
                $columnSearchFilters[] = "npe_customer.name LIKE '%$escapedColumnSearch%'";
                break;
        }
    }
}

// Combine column-specific filters with global search
if (!empty($columnSearchFilters)) {
    $searchQuery .= ($searchQuery ? ' AND ' : ' WHERE ') . implode(' AND ', $columnSearchFilters);
}

// Combine query with filters
$query .= $searchQuery;

// Add ordering, limit, and offset
$query .= " ORDER BY $orderColumnName $orderDirection";
$query .= " LIMIT $limit OFFSET $offset";

// Get total records without filtering
$totalQuery = "SELECT COUNT(*) AS total FROM npe_timesheets WHERE showonweb = 'Yes' AND archived = 0 ";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'] ?? 0;

// Get filtered records count
$filteredQuery = "SELECT COUNT(*) AS total FROM npe_timesheets 
    LEFT JOIN products ON npe_timesheets.name = products.name
    LEFT JOIN npe_customer ON products.account = npe_customer.id
    $searchQuery";
$filteredResult = $conn->query($filteredQuery);
$filteredRecords = $filteredResult->fetch_assoc()['total'] ?? $totalRecords;


if ($_POST['action'] === 'getVetOptionsWithRecords') {
    $query = "
        SELECT DISTINCT npe_vet.name 
        FROM npe_timesheets 
        LEFT JOIN npe_vet ON npe_timesheets.vet = npe_vet.id
        WHERE npe_timesheets.vet IS NOT NULL
        ORDER BY npe_vet.name ASC";

    $result = $conn->query($query);

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'name' => $row['name']
        ];
    }

    echo json_encode($options);
    exit;
}


// Execute main query
$dataResult = $conn->query($query);
$data = [];
while ($row = $dataResult->fetch_assoc()) {
    $data[] = $row;
}

// Send JSON response

error_log("Full request: " . print_r($_GET, true));
$request = $_GET; // or $_POST depending on your configuration

$draw = intval($GET['draw'] ?? 1); // Safely handle draw
error_log("Incoming draw value: " . $draw);


$response = [
    "draw" => $draw, // Ensure the draw is echoed back
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => $data
];

echo json_encode($response);


