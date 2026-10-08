<?php
include('setting/main-top-files.php');

$id = intval($_POST['id']); // Ensure $id is an integer

// Archive records
$query = "UPDATE products SET showonweb = 'No', archived = 1 WHERE importbatch = $id";
if (!$conn->query($query)) {
    http_response_code(500);
    echo "Error archiving records: " . $conn->error;
    exit;
}

$affectedRows = $conn->affected_rows;

// Fetch import batch name for the success message
$query = "SELECT name FROM product_import_log WHERE id = $id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    http_response_code(500);
    echo "Error fetching import batch name";
    exit;
}

$name = $result->fetch_assoc()['name'];

// Output success message
echo "Import Batch $id - $name Archived OK<br>$affectedRows records archived.";
?>
