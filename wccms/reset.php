<!-- START reset -->
<?php
include('setting/main-top-files.php'); // Added by salva TDR | 7.12.2022

$link = $BASE_URL . ($_SERVER['REQUEST_URI'] ?? '');

$proarray = explode("?", $link);
$resetCode = end($proarray);
$email = null;

$query = "SELECT * FROM `recoverpassword` 
WHERE `emailcode` LIKE '$resetCode'";
$result = mysqli_fetch_array(DB::query($query), MYSQLI_ASSOC);

if (!empty($result)) {
   $email = $result["email"];
} else {
   $toast[] = array(
      "message" => "Invalid reset code",
      "type" => "error",
      "redirect" => "$BASE_URL/wccms",
   );
}

if (isset($_POST['repwdSubmit']) && $email !== null) {
   $toast = [];
   $password = securityCheck($_POST['password']);
   $rePassword = securityCheck($_POST['repassword']);

   if ($password != $rePassword) {
      $toast[] = array(
         "message" => "Password and Re-Password are not the same",
         "type" => "error"
      );
   } else {
      $user = new CMSUser($email);

      try {
         $responseUpdate = $user->updatePassword($password);

         if ($responseUpdate['status'] == 200) {
            $deleteResetCode = $user->deleteResetCode($resetCode);

            if ($deleteResetCode['status'] == 200) {
               $toast[] = array(
                  "message" => $responseUpdate['msg'],
                  "type" => "success",
                  "redirect" => "$BASE_URL/wccms"
               );
            } else {
               $toast[] = array(
                  "message" => $deleteResetCode['msg'],
                  "type" => "error"
               );
            }
         } else {
            $toast[] = array(
               "message" => $responseUpdate['msg'],
               "type" => "error"
            );
         }
      } catch (\Exception $e) {
         $toast[] = array(
            "message" => $e->getMessage(),
            "type" => "error"
         );
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description" content="">
   <meta name="author" content="Mosaddek">
   <meta name="keyword" content="FlatLab, Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
   <link rel="shortcut icon" href="img/favicon.html">
   <title>WiteCanvas CMS</title>
   <!-- Bootstrap core CSS -->
   <?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
   <link href="css/bootstrap-reset.css" rel="stylesheet">
   <!--external css-->
   <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
   <!-- Custom styles for this template -->
   <link href="css/style.css" rel="stylesheet">
   <link href="css/style-responsive.css" rel="stylesheet" />
   <script></script>
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

      <form autocomplete="off" class="form-signin validate-form" method="post" onsubmit="return validateForm()">
         <h2 class="form-signin-heading">New password</h2>
         <div class="login-wrap">
            <p><?php echo $email; ?></p>
            <label for="password1">Write the new password:</label>
            <input type="text" class="form-control" name="password" placeholder="Secure password" id="password1" autofocus>
            <label for="repassword">Repeat the password:</label>
            <input type="password" class="form-control" name="repassword" placeholder="Repeat the password" id="repassword">

            <button class="btn btn-lg btn-login w-100" type="submit" name="repwdSubmit">Reset password</button>
         </div>
      </form>
   </div>

   <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
   <?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>

   <script>
      function validateForm() {
         const password = document.querySelector('#password1').value;
         const confirm_password = document.querySelector('#repassword').value;

         if (password == "" || confirm_password == "") {
            const toast = new Toast("Password and Retype-Password are required", "error");
            toast.show();
            return false;
         } else {
            if (password != confirm_password) {
               const toast = new Toast("Passwords do not match", "error");
               toast.show();
               return true;
            }
         }
      }

      $(document).ready(function() {
         <?php
         if (count($toast) > 0) {
            echo "const toast = new Toast('" . $toast[0]['message'] . "', '" . $toast[0]['type'] . "');";
            echo "toast.show();";
            if ($toast[0]['redirect']) {
               echo "setTimeout(() => { 
                  window.location.href = '{$toast[0]['redirect']}'; 
               }, 4000);";
            }
         }
         ?>
      });
   </script>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>
<!-- END reset -->