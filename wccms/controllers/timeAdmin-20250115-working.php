<?php
//error_log("Raw data passed to saveTimeData: " . print_r($data, true));
//error_log("Raw POST Data: " . print_r($_POST, true));


if (strpos($_SERVER['REQUEST_URI'], 'timeAdmin.php') === false) {
    error_log("timeAdmin.php accessed in an unintended way.");
    return; // Prevent execution if accessed via include
}
// Ensure global $conn is accessible via main-top-files.php
require_once (dirname(__FILE__) . '/../../../private/dbcon.php');
require_once (dirname(__FILE__) . '/../../../private/db.php');
global $conn;

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
        error_log("Failed to prepare statement in getProductDetails: " . $conn->error);
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

 //   error_log("Starting saveTimeData...");
 //  error_log("Raw data passed to saveTimeData: " . print_r($data, true));
 //   error_log("log entry NOTES: ".$data['notes']) ;


    // Log binding values
 //   error_log("Binding values: productid={$data['productid']}, name={$data['name']}, user={$data['user']}, date={$data['date']}, vet={$data['vet']}, courier={$data['courier']}, notes={$data['notes']}, time_ov={$data['time_ov']}, time_cso={$data['time_cso']}, travel_units={$data['travel_units']}, travel_miles={$data['travel_miles']}, certs={$data['certs']}, sha_sa={$data['sha_sa']}");

    //  error_log("SQL statement prepared successfully."); 

    $sql = "INSERT INTO npe_timesheets (productid, name, user, date, vet, courier, notes, time_ov, time_cso, travel_units, travel_miles, certs, sha_sa)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
 //       error_log("SQL Prepare Error: " . $conn->error);
        throw new Exception("Database error during prepare: " . $conn->error);
    }

    $bindResult = $stmt->bind_param(
        'isisidsiiiiii',
        $data['productid'], 
        $data['name'], 
        $data['user'], 
        $data['date'], 
        $data['vet'], 
        $data['courier'], 
        $data['notes'],
        $data['time_ov'], 
        $data['time_cso'], 
        $data['travel_units'], 
        $data['travel_miles'], 
        $data['certs'], 
        $data['sha_sa']
    );

    if (!$bindResult) {
  //      error_log("SQL Bind Error: " . $stmt->error);
        throw new Exception("Database error during bind: " . $stmt->error);
    }

    // Log constructed query
    $queryString = sprintf(
        "INSERT INTO npe_timesheets (productid, name, user, date, vet, courier, notes, time_ov, time_cso, travel_units, travel_miles, certs, sha_sa)
        VALUES (%d, '%s', %d, '%s', %d, %f, '%s', %d, %d, %d, %d, %d, %d)",
        $data['productid'], 
        $conn->real_escape_string($data['name']), 
        $data['user'], 
        $conn->real_escape_string($data['date']), 
        $data['vet'], 
        $data['courier'], 
        $conn->real_escape_string($data['notes']),
        $data['time_ov'], 
        $data['time_cso'], 
        $data['travel_units'], 
        $data['travel_miles'], 
        $data['certs'], 
        $data['sha_sa']
    );
 //   error_log("Constructed SQL Query: " . $queryString);

    // Execute statement
    if (!$stmt->execute()) {
 //       error_log("SQL Execution Error: " . $stmt->error);
        throw new Exception("Database error during execute: " . $stmt->error);
    }

 //   error_log("SQL Execution Successful.");
    return true;
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
error_log("Unknown action: $action");
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
?>
