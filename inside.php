<?php
session_start();

require_once(__DIR__ . '/wccms/setting/main-top-files.php');

if (isset($_SESSION['userid'])) {
   $userid = $_SESSION['userid'];
} else {
   $userid = 1;
}

$USER = new User($userid, $_SESSION['orderno']);
?>

<!-- START INSIDE -->
<!DOCTYPE html>
<html lang="en">

<head>
   <!-- START Head -->
   <?php
   include("includes/controller.php");
   include("includes/header-code.php");
   include("includes/google.php");
   ?>
   <script src="<?php echo $baseURL ?>/js/Toast.js"></script>
   <!-- End Head -->
</head>

<body>
   <?php
   echo "<div class='pagetopbannerbg'>";

   include("includes/header.php");
   // Code to add BG to below menu
  // echo "<div class='BgBannger'>";
 //  echo "<div class='container' style='background-color:#fff;'>";

 //  echo "<div class='pagebody'> ";

   if ($pageLayout) {
      include("includes/" . $pageLayout . "");
   }

  // echo "</div> ";

 //  echo "</div> ";
 //  echo "</div> ";

   echo "</div> ";


   include("includes/footer.php");

   if (
      $GlobalDebug == 'Yes'
      or $_SERVER['REMOTE_ADDR'] == $prefs['prefTruskaIP']
      or $_SERVER['REMOTE_ADDR'] == $prefs['prefClientIP']
      or $_SERVER['REMOTE_ADDR'] == $prefs['prefClient1IP']
      or $_SERVER['REMOTE_ADDR'] == $prefs['prefCoderIP']
      or $_SERVER['HTTP_X_FORWARDED_FOR'] == $prefs['prefClientIP']
   ) {

      include("includes/footer-debug.php");
   }

   if ($prefs["prefCookieCheck"] == 'Yes') {
      include("includes/cookiealert.php");
   }

   include("includes/footer-code.php");
   ?>
</body>

</html>