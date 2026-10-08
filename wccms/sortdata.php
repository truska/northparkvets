<?php
include(dirname(__FILE__) . '/../../private/dbcon.php');

$data = json_decode($_POST["order"]);
$formNumber = $_POST["formNumber"];
$tableName = ($formNumber == 11) ? 'pro_product_images' : 'gallery';
$response = [];
$errors = [];

foreach ($data as $item) {
   $sqlsortupdate = "UPDATE `$tableName` 
   SET sort =  {$item->position}
   WHERE `id` = {$item->id}";
   $querysortupdate = mysqli_query($conn, $sqlsortupdate);

   if (!$querysortupdate) {
      $errors[] = [
         'status' => 'error',
         'message' => "Image '{$item->id}' sort failed in '$tableName'"
      ];
   }
}

if (empty($errors)) {
   $response = [
      'status' => 'success',
      'message' => 'Images sorted successfully'
   ];
} else {
   $response = [
      'status' => 'error',
      'message' => 'Images sort failed',
      'errors' => $errors
   ];
}

echo json_encode($response);



// $data = $_POST["data"];
// $data = substr($data, 0, -1);
// $data1 = explode(" ", $data);
// $check = 0;

// foreach ($data1 as $datavalue) {
//    $datavalue = trim($datavalue);


//    $data2 = explode(":", $datavalue);
//    $sort = $data2[0];
//    $imgid1 = $data2[1];

//    $sqlsortupdate = "UPDATE `pro_product_images` 
//    SET sort = $sort 
//    WHERE `id` = $imgid1";
//    $querysortupdate = mysqli_query($conn, $sqlsortupdate);
//    if ($querysortupdate) {
//       $check = 1;
//    } else {

//       $check0;
//    }
// }

// if ($check == 1) {
//    echo "Image Sorted";
// } else {
//    echo "Error !";
// }
