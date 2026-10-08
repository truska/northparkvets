<!DOCTYPE html>
<head>
<?php
// Turn off error reporting
error_reporting(1);
// Turn on error reporting
// error_reporting(1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bring forward variables
$baseURL = $BASE_URL;

include('setting/main-top-files.php'); 
include("include/header-code.php");

?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#timeModalx">
    Open Modal
</button>

<div id="timeModalx" class="modal fade" tabindex="-1" aria-labelledby="timeModalxLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeModalxLabel">Modal Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Content goes here.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Modal Debugging Initialized');

    // Check if modal element exists
    const modalElement = document.getElementById('timeModalx');
    if (modalElement) {
        console.log('Modal element found:', modalElement);

        // Create Bootstrap modal instance
        const myModal = new bootstrap.Modal(modalElement);

        // Debug manual modal opening
        document.getElementById('openModalDebug').addEventListener('click', function () {
            console.log('Manual modal trigger clicked.');
            myModal.show();
        });
    } else {
        console.error('Modal element not found.');
    }
});
</script>
<?php
include("include/footer-code.php");
?>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>