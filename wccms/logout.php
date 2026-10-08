<?php
include ('setting/main-top-files.php'); 

    $logtable = $table['name'];
    $action = "LogOut" ;
    $sqlquery = "N/A";
    $notes = "User Logged Out using Logout Button";
    $username = $_SESSION["useremail"];

    saveLog($username, $action, $sqlquery, $logtable, 'SUCCESS', $notes, $recordnumber);

    session_destroy();
     
    echo"<script>window.location='index.php'</script>";
?>