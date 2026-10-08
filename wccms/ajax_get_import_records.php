<?php
include('setting/main-top-files.php');

$id = intval($_GET['id']); // Ensure $id is an integer

// Get count of records that are NOT archived
$queryTotal = "SELECT COUNT(*) AS total FROM products WHERE importbatch = $id AND archived = 0";
$resultTotal = $conn->query($queryTotal);
$totalRecords = $resultTotal->fetch_assoc()['total'];

// Get count of records that ARE already archived
$queryArchived = "SELECT COUNT(*) AS total FROM products WHERE importbatch = $id AND archived = 1";
$resultArchived = $conn->query($queryArchived);
$archivedRecords = $resultArchived->fetch_assoc()['total'];

// Fetch records that are NOT archived
$query = "SELECT id, name, created FROM products WHERE importbatch = $id AND archived = 0 ORDER BY id ASC";
$result = $conn->query($query);

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Determine which records to return
if ($totalRecords > 15) {
    $recordsToShow = array_merge(array_slice($records, 0, 5), array_slice($records, -5));
} else {
    $recordsToShow = $records; // Show all if 15 or fewer
}

// Return JSON response
echo json_encode([
    "total_records" => $totalRecords,
    "archived_records" => $archivedRecords,
    "records" => $recordsToShow
]);
?>
