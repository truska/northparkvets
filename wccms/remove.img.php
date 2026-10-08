<?php
// include('include/session.php');
// include("logrecord.php");
// include("include/dbcon.php");

include("setting/main-top-files.php"); // Added by salva TDR | 5.4.2023

if (!$id = securityCheck($_GET["id"], 'number')) {
  $URL = "recordEditv4.php?frm=$frm&id=$id&del=error&msg=Wrong record id";
  echo "<script>window.location= '$URL'</script>";
}

if (!$frm = securityCheck($_GET["frm"], 'number')) {
  $URL = "recordEditv4.php?frm=$frm&id=$id&del=error&msg=Wrong form id";
  echo "<script>window.location= '$URL'</script>";
}

if (!$img = securityCheck($_GET["img"], 'number')) {
  $URL = "recordEditv4.php?frm=$frm&id=$id&del=error&msg=Wrong image id";
  echo "<script>window.location= '$URL'</script>";
}

if ($frm == 11) {
  $sqldelimg = "DELETE FROM `pro_product_images` 
  WHERE id = $img";
}else {
  $sqldelimg = "DELETE FROM `gallery` 
  WHERE `id` = $img
  AND `form_id` = $frm";
}

$querydelimg = mysqli_query($conn, $sqldelimg);

$action = "Remove Image";
$form = ($frm === 11) ? 'pro_product_images' : 'gallery';
$notes = "Image Deleted";

if ($querydelimg) {
  $URL = "recordEditv4.php?frm=$frm&id=$id&del=success&msg=Image deleted successfully";
  savelog('', $action, $sqldelimg, $form, 'SUCCESS', $notes, $img);
  echo "<script>window.location= '$URL'</script>";
} else {
  $URL = "recordEditv4.php?frm=$frm&id=$id&del=error&msg=Error deleting image";
  savelog('', $action, $sqldelimg, $form, 'FAIL', $notes, $img);
  echo "<script>window.location= '$URL'</script>";
}
