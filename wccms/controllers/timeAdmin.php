<?php

//error_log("Raw data passed to saveTimeData: " . print_r($data, true));
//error_log("Raw POST Data: " . print_r($_POST, true));

if (defined('TIMEADMIN_EXECUTED')) {
    exit; // Prevent duplicate execution
}
define('TIMEADMIN_EXECUTED', true);

//error_log("Script started. Received action: " . (isset($_GET['action']) ? $_GET['action'] : 'No action'));

$action = isset($_GET['action']) ? $_GET['action'] : null;


if (strpos($_SERVER['REQUEST_URI'], 'timeAdmin.php') === false) {
  //  error_log("timeAdmin.php accessed in an unintended way.");
    return; // Prevent execution if accessed via include
}
// Ensure global $conn is accessible via main-top-files.php
require_once (dirname(__FILE__) . '/../../../private/dbcon.php');
require_once (dirname(__FILE__) . '/../../../private/db.php');
global $conn;

require_once __DIR__ . '/../include/session.php';
if (!isset($_SESSION['useremail'])) {
    http_response_code(401);
    echo json_encode(['success'=>false, 'message'=>'Please log in.']);
    exit;
}

//error_log("Full URL: " . $_SERVER['REQUEST_URI']);
//error_log("GET Parameters: " . print_r($_GET, true));
//error_log("POST Parameters: " . print_r($_POST, true));

//error_log("Global conn status: " . (isset($conn) ? "Set" : "Not Set"));


// Check the database connection
if ($conn && $conn->ping()) {
   // error_log("Database connection is valid.");
} else {
  //  error_log("Database connection is unavailable in timeAdmin.php.");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection is unavailable.']);
    exit;
}


function getProductDetails($conn, $productId) {
    $sql = "SELECT id, name, vet FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
    //   error_log("Failed to prepare statement in getProductDetails: " . $conn->error);
        return null;
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return null; // No product found with that ID
    }

    return $result->fetch_assoc();
}

/**
 * Fetch Vet Options from Database
 * @param mysqli $conn Database connection.
 * @return array Vet options array with 'value' and 'label'.
 */
function getVetOptionsTime($conn) {
    
    global $conn; // Declare $conn as global within the function
    $sql = "SELECT id, name, code FROM npe_vet ORDER BY sort, name";
  //  error_log("Executing SQL: $sql");

    $result = $conn->query($sql);
    if (!$result) {
     //   error_log("SQL Error: " . $conn->error);
        throw new Exception("SQL Error: " . $conn->error);
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'value' => $row['id'],
            'label' => "{$row['name']} - ({$row['code']} ({$row['id']}))"
        ];
    }

    return $options;
}

if ($action === 'saveTimeData') {
 //   error_log("Raw POST Data: " . print_r($_POST, true));
  //  error_log("Action: saveTimeData triggered.");

    try {
        $result = saveTimeData($_POST);
        echo json_encode(['success' => $result]);
    } catch (Exception $e) {
  //      error_log("Error in saveTimeData: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

/**
 * Save Time Data to Database
 * @param mysqli $conn Database connection.
 * @param array $data POST data.
 */
 function saveTimeData($data) {
    global $conn;
    require_once __DIR__ . '/timesheetRates.php';
    $actor = $conn->prepare('SELECT id FROM cms_adminlogin WHERE username=?');
    $actor->bind_param('s', $_SESSION['useremail']);
    $actor->execute();
    $loggedInUser = $actor->get_result()->fetch_assoc();
    $actor->close();
    if (!$loggedInUser) throw new RuntimeException('Please log in again.');
    $data['user'] = $loggedInUser['id'];
    $result = insertTimesheetWithRates($conn, $data);
    return $result['status'] === 'success';
}


    // Handle incoming action
    $action = isset($_GET['action']) ? $_GET['action'] : null;

if ($action === 'getVetOptions') {
    //error_log("Action: getVetOptions triggered.");
    try {
        $data = getVetOptionsTime($conn);
       // error_log("Vet options data: " . print_r($data, true));
        echo json_encode(['success' => true, 'data' => $data]);

      //  error_log("timeAdmin.php handling request: " . $_SERVER['REQUEST_URI']);
        //error_log("Final Response: " . json_encode(['success' => true, 'data' => $data]));


    } catch (Exception $e) {
      //  error_log("Error in getVetOptions: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);

      //  error_log("timeAdmin.php handling request: " . $_SERVER['REQUEST_URI']);
        //error_log("Final Response: " . json_encode(['success' => true, 'data' => $data]));

    }
    exit;
}

if ($action === 'saveTimeData') {
    //   error_log("Action: saveTimeData triggered.");
    //   error_log("Raw POST Data: " . print_r($_POST, true)); // Check if $_POST is correct here
    try {
        // Log the incoming POST data
 //       error_log("Saving Time Data: " . print_r($_POST, true));

        $result = saveTimeData($_POST);
        echo json_encode(['success' => $result]);
    } catch (Exception $e) {
    //        error_log("Error in saveTimeData: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Default case: Log and respond
/*
error_log("Unknown action: $action");
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);

*/

//<!-- TIME REPORTING -->

// At the very start
$action = isset($_GET['action']) ? $_GET['action'] : null;

if ($action === 'getTimereport') {
    $productId = isset($_GET['productid']) ? intval($_GET['productid']) : 0;

    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid Product ID.']);
        exit;
    }

    $sql = "SELECT 
                t.id, t.productid, t.time_ov, t.time_cso, t.travel_units, 
                t.travel_miles, t.certs, t.tanker_cert, t.sha_sa, t.courier, t.notes, t.showonweb, t.archived, 
                v.name AS vet, t.date
            FROM npe_timesheets t
            LEFT JOIN npe_vet v ON t.vet = v.id
            WHERE t.productid = ? 
            AND t.showonweb = 'Yes' 
            AND t.archived = 0 " ;
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'SQL preparation failed.']);
        exit;
    }

    $stmt->bind_param('i', $productId);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'SQL execution failed.']);
        exit;
    }

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);
    exit; // Terminate script after successful response
}