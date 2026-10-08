<?php
function saveLog($username = 'not@set.net', $action = 'Action Not Set', $sqlquery = 'SQL Not Set', $form = 'Form Not Set', $result = 'Result Not Set', $notes = 'Notes Not Set', $contentid = 0)
{
    function getBrowserName($userAgent)
    {
        $browsers = [
            'Opera' => 'opr',
            'Chrome' => 'chrome',
            'Internet Explorer' => 'msie',
            'Firefox' => 'firefox',
            'Safari' => 'safari'
        ];

        foreach ($browsers as $browser => $browserKey) {
            if (strpos(strtolower($userAgent), $browserKey) !== false) {
                return $browser;
            }
        }

        return "OUT OF DATA";
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browser = getBrowserName($userAgent);
    $ip = $_SERVER['REMOTE_ADDR'];

    // Establish the connection
    $conn = DB::connection();
    if (!$conn) {
        error_log("Database connection failed.");
        return;
    }

    if (empty($username)) {
        $username = $_SESSION["useremail"];
    }

    // Escape all variables properly
    $username = mysqli_real_escape_string($conn, $username);
    $result = mysqli_real_escape_string($conn, $result);
    $contentid = (int)$contentid; // Ensure contentid is an integer
    $action = mysqli_real_escape_string($conn, $action);
    $sqlquery = mysqli_real_escape_string($conn, $sqlquery);
    $notes = mysqli_real_escape_string($conn, $notes);
    $ip = mysqli_real_escape_string($conn, $ip);
    $browser = mysqli_real_escape_string($conn, $browser);
    $userAgent = mysqli_real_escape_string($conn, $userAgent);
    $form = mysqli_real_escape_string($conn, $form);

    // Construct the SQL query
    $sqlinsertlog = "
        INSERT INTO `cms_log` 
        (`username`, `datetime`, `result`, `contentid`, `action`, `query`, `notes`, `ip`, `browser`, `agent`, `form`) 
        VALUES (
            '$username', 
            NOW(), 
            '$result', 
            '$contentid', 
            '$action', 
            '$sqlquery', 
            '$notes', 
            '$ip', 
            '$browser', 
            '$userAgent', 
            '$form'
        )";

    // Execute the query using the DB class
    $queryinsertlog = DB::query($sqlinsertlog);

    // Error handling
    if (!$queryinsertlog) {
        // Retrieve the last error
        $error = mysqli_error($conn);
        $errorMessage = "ERROR writing to log! SQL: " . $sqlinsertlog . " | ERROR: " . $error;
       // error_log($errorMessage);
    } else {
      //  error_log("SUCCESS writing to log! SQL: " . $sqlinsertlog);
    }
}
//End Function Live

function saveLogDeDug($username = 'User Not Set', $action = 'Action Not Set', $sqlquery = 'SQL Not Set', $form = 'Form Not Set', $result = 'Result Not Set', $notes = 'Notes Not Set', $contentid = 0)
{
    function getBrowserName1($userAgent)
    {
        $browsers = [
            'Opera' => 'opr',
            'Chrome' => 'chrome',
            'Internet Explorer' => 'msie',
            'Firefox' => 'firefox',
            'Safari' => 'safari'
        ];

        foreach ($browsers as $browser => $browserKey) {
            if (strpos(strtolower($userAgent), $browserKey) !== false) {
                return $browser;
            }
        }

        return "OUT OF DATA";
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browser = getBrowserName1($userAgent);
    $ip = $_SERVER['REMOTE_ADDR'];

    // Establish the connection
    $conn = DB::connection();
    if (!$conn) {
    //    error_log("Database connection failed.");
        return;
    }

    // Debugging information before escaping
    $debugInfoBefore = [
        'username' => $username,
        'result' => $result,
        'contentid' => $contentid,
        'action' => $action,
        'query' => $sqlquery,
        'notes' => $notes,
        'ip' => $ip,
        'browser' => $browser,
        'agent' => $userAgent,
        'form' => $form
    ];
  // error_log("Before escaping: " . json_encode($debugInfoBefore));

    if (empty($username)) {
        $username = $_SESSION["useremail"];
    }

    // Escape all variables properly
    $username = mysqli_real_escape_string($conn, $username);
    $result = mysqli_real_escape_string($conn, $result);
    $contentid = (int)$contentid; // Ensure contentid is an integer
    $action = mysqli_real_escape_string($conn, $action);
    $sqlquery = mysqli_real_escape_string($conn, $sqlquery);
    $notes = mysqli_real_escape_string($conn, $notes);
    $ip = mysqli_real_escape_string($conn, $ip);
    $browser = mysqli_real_escape_string($conn, $browser);
    $userAgent = mysqli_real_escape_string($conn, $userAgent);
    $form = mysqli_real_escape_string($conn, $form);

    // Debugging information after escaping
    $debugInfo = [
        'username' => $username,
        'result' => $result,
        'contentid' => $contentid,
        'action' => $action,
        'query' => $sqlquery,
        'notes' => $notes,
        'ip' => $ip,
        'browser' => $browser,
        'agent' => $userAgent,
        'form' => $form
    ];
   // error_log("After escaping: " . json_encode($debugInfo));

    // Construct the SQL query
    $sqlinsertlog = "
        INSERT INTO `cms_log` 
        (`username`, `datetime`, `result`, `contentid`, `action`, `query`, `notes`, `ip`, `browser`, `agent`, `form`) 
        VALUES (
            '$username', 
            NOW(), 
            '$result', 
            '$contentid', 
            '$action', 
            '$sqlquery', 
            '$notes', 
            '$ip', 
            '$browser', 
            '$userAgent', 
            '$form'
        )";

    // Log the constructed SQL query for debugging
    //error_log("Executing SQL: " . $sqlinsertlog);

    // Execute the query using the DB class
    $queryinsertlog = DB::query($sqlinsertlog);

    // Error handling
    if (!$queryinsertlog) {
        // Retrieve the last error
        $error = mysqli_error($conn);
        $errorMessage = "ERROR writing to log! SQL: " . $sqlinsertlog . " | ERROR: " . $error . " | Debug Info: " . json_encode($debugInfo);
       // error_log($errorMessage);
    } else {
       // error_log("SUCCESS writing to log! SQL: " . $sqlinsertlog . " Debug Info: " . json_encode($debugInfo));
    }
}
//END Function Debug
?>