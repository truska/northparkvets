<?php
/*
<!-- START header-code (new 20250113) -->
*/
?>

<title>wITeCanvas CMS - <?php echo $prefs["prefSiteName"]; ?></title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="wITeCanvas CMS System">
<meta name="author" content="wITeCanvas">
<meta name="keyword" content="wITeCanvas, cms, truska, digita">
<link rel="shortcut icon" href="img/witecanvas-favicon.ico">

<!-- Noindex for search engines -->
<meta name="robots" content="noindex, nofollow" />

<!-- Bootstrap 5 Core CSS -->
<?php require_once __DIR__ . '/bootstrap-css.php'; ?>

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
<!-- DataTables Responsive CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">


<!-- Local custom styles -->
<link href="css/style.css" rel="stylesheet">
<link href="css/style-responsive.css" rel="stylesheet">

<!-- FontAwesome -->
<script src="https://kit.fontawesome.com/<?php echo $prefs["prefFontAwsomeToken"]; ?>" crossorigin="anonymous"></script>

<!-- TinyMCE --> 
<!-- Pubic cdn -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

<!-- Toast Notification -->
<script src="/wccms/js/Toast.js"></script>

<?php
// PDF library (keep as required)
require_once('tcpdf/tcpdf.php');
?>

<?php
/*
<!-- END header-code -->
*/
?>