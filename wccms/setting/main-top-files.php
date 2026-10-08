<?php
require_once __DIR__ . "/../include/runtime.php";
require_once __DIR__ . "/features.php";
$toast = $toast ?? [];
$bootstrapver = 5 ; 

require_once (dirname(__FILE__) . '/../../../private/dbcon.php');
require_once (dirname(__FILE__) . '/../../../private/db.php');
require_once (dirname(__FILE__) . '/../logrecord.php');
require_once (dirname(__FILE__) . '/../include/session.php');
require_once (dirname(__FILE__) . '/../include/functions.php');
require_once (dirname(__FILE__) . '/../controllers/cmsUser.php'); // User class (admin)
require_once (dirname(__FILE__) . '/../controllers/User.php'); // User class (front)
require_once (dirname(__FILE__) . '/../controllers/menu.php');
require_once (dirname(__FILE__) . '/../controllers/imageResizer.php');
require_once (dirname(__FILE__) . '/../controllers/formField.php');
require_once (dirname(__FILE__) . '/../controllers/preferences.php');
require_once (dirname(__FILE__) . '/../controllers/recordView.php');
require_once (dirname(__FILE__) . '/../controllers/recordEdit.php');
require_once (dirname(__FILE__) . '/../controllers/recordAdd.php');
require_once (dirname(__FILE__) . '/../controllers/report1.php');
require_once (dirname(__FILE__) . '/../controllers/import1.php');
require_once (dirname(__FILE__) . '/../controllers/timeAdmin.php');
require_once (dirname(__FILE__) . '/../controllers/adminBilling.php');


$prefs = loadPrefs();
// A stored preference cannot activate the unused 2FA flow by itself.
$prefs['pref2fa'] = CMS_TWO_FACTOR_ENABLED ? ($prefs['pref2fa'] ?? 'No') : 'No';
// $prefshop = loadShopPrefs();
$BASE_URL = siteBaseUrl($prefs['prefSiteUrl'] ?? '');
$baseURL = $BASE_URL;
// Override only the in-memory preference; the database value is unchanged.
$prefs['prefSiteUrl'] = $BASE_URL;

// check if user is logged in
if (!isset($_SESSION["useremail"])) {
   if (
      $_SERVER['PHP_SELF'] !== '/wccms/index.php' &&
      $_SERVER['PHP_SELF'] !== '/wccms/reset.php' &&
      $_SERVER['PHP_SELF'] !== '/wccms/2fa.php'
   ) {
      header("Location: " . $BASE_URL . "/wccms/index.php");
      exit();
   }
} else {
   $USER = new CMSUser($_SESSION['useremail']);
   $user = $USER->getUser();
}
// Timesheet forms use v5; other forms retain the configured CMS version.
if (isset($_GET['frm']) && in_array((string)$_GET['frm'], ['13', '21'], true)) {
    $prefs['prefCMSVer'] = '5';
}
