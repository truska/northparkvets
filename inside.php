<?php
// This deployment contains the CMS only; the public-site templates are absent.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Cache-Control: no-store');
$destination = isset($_SESSION['useremail']) ? 'dashboard.php' : 'index.php';
header('Location: /wccms/' . $destination);
exit;
