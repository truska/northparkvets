<?php
require_once (__DIR__ . '/setting/main-top-files.php');

//error_log("Base URL: ".$BASE_URL) ;

if (isset($_POST['submitReset'])) { // Reset password
   $toast = [];
   $email = securityCheck($_POST['resetEmail']);

   $sql = "SELECT * FROM `cms_adminlogin` 
   WHERE `username` = '$email'";
   $adminUser = mysqli_fetch_array(DB::query($sql), MYSQLI_ASSOC);

   if (!empty($adminUser)) { // User exists
      $sqlgetusercode = "SELECT * FROM `recoverpassword` 
      WHERE `email` = '$email'";
      $resrow = mysqli_fetch_array(DB::query($sqlgetusercode), MYSQLI_ASSOC);

      if (!empty($resrow)) {
         $resetCode = $resrow["emailcode"];
      } else {
         // Create a token with email+time
         $resetCode = md5($email . time());
         // Insert new reset code
         $sql = "INSERT INTO `recoverpassword` (`email`, `emailcode`) VALUES ('$email', '$resetCode')";
         $resultInsert = DB::query($sql);
      }

      // Send recovery email
      $to = $email;
      $subject = "{$prefs['prefCompanyName']} - Password Reset Email";
      $time = time();
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      $headers .= "From: " . $prefs['prefManagerEmail'];

      $message = "<h3>Click link below Reset your account password for your CMS system </h3><h4><a href='$BASE_URL/wccms/reset.php?$resetCode'>Click Here </a></h4><h6>Thank you<br>wITeCanvas Admin Team</h6>";

      if (mail($to, $subject, $message, $headers)) {
         $toast[] = array(
            "message" => "Kindly Check your email",
            "type" => "success",
            "redirect" => "$BASE_URL/wccms"
         );
      }
   } else { // User not exists
      $toast[] = array(
         "message" => "Email not found",
         "type" => "error"
      );
   }
}

if (isset($_POST["sbt"])) {
   $username = trim(mysqli_real_escape_string(DB::connection(), $_POST["username"]));
   $password = trim(mysqli_real_escape_string(DB::connection(), $_POST["password"]));
   $passwordhash = md5($password);

   // Start new code | salva TDR
   $user = new CMSUser($username);

   // 1. send 2FA code
   $from = $prefs['prefManagerEmail'];
   $to = $username;
   $isOk = $user->signIn($username, $passwordhash);
      
      $username1 = $_SESSION["useremail"];
   
      if ($isOk) { // Check if the user and password are correct

      $logtable = "N/A";
      $action = "LogIn" ;
      $sqlquery = "N/A";
      $notes = "User LOGGED IN" ;


      if($prefs['pref2fa'] === 'Yes') { // check if 2FA enabled
         $code = rand(100000, 999999);
         $notes = "User LOGGED IN with 2FA" ;
      
         // Proceed with the login with 2FA
         $save2fa = $user->save2fa($to, $code); // Save 2FA code to the database

         
         $response = $user->send2fa($from, $to, $code); // Send 2FA code to the user

         if ($response['status'] == 200) {
            $toast[] = array(
               "message" => "2FA code sent to your email",
               "type" => "success",
               "redirect" => "$BASE_URL/wccms/2fa.php?u=$username"
            );
         } 
         else 
         {
            $toast[] = array(
               "message" => "Error sending 2FA code",
               "type" => "error"
            );
         }
      }
      else
      {
         // Proceed with the login without 2FA
         $notes = "User LOGGED IN without 2FA" ;

         $resuser = $user->getUser();
         $fname = $resuser["firstname"];
         $lname = $resuser["surname"];
         $full = "$fname $lname";

         $_SESSION["useremail"] = $username;
         $_SESSION["user"] = $full;

         saveLog($username1, $action, $sqlquery, $logtable, 'SUCCESS', $notes, $recordnumber);

         $toast[] = array(
               "message" => "Login successful [No 2FA], you will be redirected to the dashboard in a few seconds",
               "type" => "success",
               "redirect" => "$BASE_URL/wccms/dashboard.php" // Redirect to the dashboard or desired page
         );
      }
   }
   else 
   {     
      saveLog($username, $action, $sqlquery, $logtable, 'FAILED', $notes, $recordnumber);
      echo "<script>alert('Invalid Username or Password!')</script>";
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   
   <?php
include("header-code.php") ;
?>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description" content="">
   <meta name="author" content="wITeCanvas">
   <meta name="keyword" content="">
   <link rel="shortcut icon" href="img/favicon.html">

   <title>WiteCanvas CMS</title>

   <!-- Bootstrap core CSS -->
 

   <link href="css/style.css" rel="stylesheet">
   <link href="css/style-responsive.css" rel="stylesheet" />
 

   <?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
   <link href="css/bootstrap-reset.css" rel="stylesheet">
<!-- 


       <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
-->
   
   <style>
      #forgotPassword {
         border-radius: 10px;
         padding: 2rem;

      }
   </style>

   <script src="/wccms/js/Toast.js"></script> 

<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body class="login-body">
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>

   <div class="container">

      <form class="form-signin" method="post">
         <h2 class="form-signin-heading">sign in now</h2>
         <div class="login-wrap">
            <input type="text" class="form-control" name="username" placeholder="User ID" autofocus>
            <input type="password" class="form-control" name="password" placeholder="Password">

            <button class="btn btn-lg btn-login w-100" type="submit" name="sbt">Sign in</button>

            <p style="font-size:12px; color:#555555;">Forgotten Password? <span
                  style="cursor: pointer; font-weight: bold;"
                  onclick="document.getElementById('forgotPassword').showModal()">CLICK HERE</span><br>or contact your
               manager or site admin</p>

          <!-- <p>baseurl: <?php echo $BASE_URL;?></p>
                 <p>url: <?php echo $baseURL;?></p>  -->
         </div>

         <!-- Modal -->
         <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal"
            class="modal fade">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <h4 class="modal-title">Forgot Password ?</h4>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                     <p>Enter your e-mail address below to reset your password.</p>
                     <input type="text" name="email" placeholder="Email" autocomplete="off"
                        class="form-control placeholder-no-fix">

                  </div>
                  <div class="modal-footer">
                     <button data-bs-dismiss="modal" class="btn btn-secondary" type="button">Cancel</button>
                     <button class="btn btn-success" type="button">Submit</button>
                  </div>
               </div>
            </div>
         </div>
      </form>

      <dialog id="forgotPassword">
         <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <h4>Insert your email to get a recovery email</h4>
            <div class="form-group">
               <label>Email:</label>
               <input type="text" class="form-control" name="resetEmail" placeholder="yoremail@domain.com" autofocus="">
            </div>
            <button class="btn btn-primary" type="submit" name="submitReset">Submit</button>
            <button class="btn btn-secondary" type="button"
               onclick="document.getElementById('forgotPassword').close()">Cancel</button>
         </form>
      </dialog>
   </div>


   <?php
   include("include/footer-code.php") ;

   ?>
   <script>
      $(document).ready(function () {
         <?php
         if (count($toast) > 0) {
            echo "const toast = new Toast('" . $toast[0]['message'] . "', '" . $toast[0]['type'] . "');";
            echo "toast.show();";
            if ($toast[0]['redirect']) {
               echo "setTimeout(() => { 
                  window.location.href = '{$toast[0]['redirect']}'; 
               }, 2000);"; // Redirect delay
            }
         }
         ?>
      });
   </script>



<script>
    console.log("jQuery version:", $.fn.jquery); // Should print version if jQuery loads
    console.log("Toast.js loaded successfully.");
</script>


<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>


</html>
<!-- END index -->