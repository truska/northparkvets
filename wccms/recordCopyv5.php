<?php
require_once __DIR__ . '/setting/main-top-files.php';
require_once __DIR__ . '/controllers/timesheetRates.php';
$formId = filter_input(INPUT_GET, 'frm', FILTER_VALIDATE_INT);
$recordId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!in_array($formId, [13,21], true) || !$recordId) {
    http_response_code(400);exit('Invalid timesheet form or record.');
}
try {
    $response = copyTimesheetWithCurrentRates($conn, $recordId);
    saveLog($_SESSION['useremail'], 'Copy timesheet '.$recordId, $response['query'], 'npe_timesheets', 'SUCCESS', 'New copy with current rates', $response['recordID']);
    header('Location: recordEditv5.php?frm='.$formId.'&id='.$response['recordID'].'&copy=success');
    exit;
} catch (Throwable $exception) {
    error_log('Timesheet copy failed: '.$exception->getMessage());
    http_response_code(500);exit('Unable to copy the timesheet. Check the current rates and server log.');
}
