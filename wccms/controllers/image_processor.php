<?php
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/imageResizer.php');
require_once (dirname(__FILE__) . '/../include/functions.php');
require_once (dirname(__FILE__) . '/../logrecord.php');

if (isset($_FILES['images'])) {
   // echo "<pre>";
   // print_r($_FILES['images']);
   // echo "</pre>";

   $frm = securityCheck($_GET['frm'], 'number');
   $id = securityCheck($_GET['id'], 'number');

   $images_size_filter = 5000000; // 5MB
   $errors = array();

   // echo "<pre>";
   // print_r($_FILES['images']);
   // echo "</pre>";
   // die;

   // Loop through each image
   foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
      $file_name = $_FILES['images']['name'][$key];
      $file_size = $_FILES['images']['size'][$key];
      $file_tmp = $_FILES['images']['tmp_name'][$key];
      $file_type = $_FILES['images']['type'][$key];

      // Check if the file is a image
      if (!getimagesize($file_tmp)) {
         $errors[] = "The file " . $file_name . " is not an image";
         continue;
      }

      $image = new ImageResizer($file_tmp);
      $table = $image->getTable($frm);
      $form = $image->getForm($frm);
      $formName = $form['name'];
      $data = $image->getContent($table['name'], $id);
      $formField = $image->getFormField($frm);

      // Depend of the form, the folder where the images will be saved will be different
      $destination_folder = $image->getDestinationFolderByForm($frm);
      $folder = dirname(__FILE__) . "/../../filestore/$destination_folder";

      // get the file extension
      $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
      $file_ext = strtolower($file_ext);

      // If the file is a jpeg, we change the extension to jpg
      if ($file_ext == "jpeg") {
         $file_ext = "jpg";
      }

      // Check if the extension is valid
      $avaialble_ext = $formField['file_ext'];

      if (strpos($avaialble_ext, $file_ext) === false) {
         $errors[] = "The file " . $file_name . " has an invalid extension, the allowed extensions are " . $avaialble_ext;
         continue;
      }

      // Set the name of the file
      if ($formField['override_filename'] == 'Yes') {
         $newName = "{$frm}-{$id}_";
         if (!empty($data['title'])) {
            $newName .= $data['title'];
         } elseif (!empty($data['name'])) {
            $newName .= $data['name'];
         } else {
            $newName .= pathinfo($file_name, PATHINFO_FILENAME);
         }
      } else {
         $newName = pathinfo($file_name, PATHINFO_FILENAME);
      }

      $checkName = $image->cleanName($newName); // Clean the name to avoid problems with special characters

      // Check if the file exists, if it does, we add a number to the name to avoid overwriting
      if (file_exists($folder . $checkName . "." . $file_ext)) {
         $i = 1;
         while (file_exists($folder . $checkName . "_" . $i . "." . $file_ext)) {
            $i++;
         }
         $newName = $newName . "_" . $i;
      }

      // File size check
      if ($file_size > $images_size_filter) {
         $errors[] = "The file " . $file_name . " is too large, the maximum allowed size is " . $images_size_filter / 1000000 . "MB";
         continue;
      }

      if ($formField['resize_status'] == 'Yes') { // If the image must be resized
         $scaled_sizes = array(
            'xs' => array('width' => $formField['xs_max_width']),
            'sm' => array('width' => $formField['sm_max_width']),
            'md' => array('width' => $formField['md_max_width']),
            'lg' => array('width' => $formField['lg_max_width'])
         );
         $image->setScaledSizes($scaled_sizes);
      }

      if ($formField['default-resize'] > 0) {
         $image->setDefaultResize($formField['default-resize']);
      }

      echo "folder: " . $folder . "<br>";
      echo "name: " . $newName . "." . $file_ext . "<br>";

      // Upload the image
      if ($formField['resize_status'] == 'Yes') {
         $image->resize($folder, $newName);
      } else {
         $image->uploadImage($folder, $newName);
      }

      // Get the errors
      $image_errors = $image->getErrors();

      if (!empty($image_errors)) {
         $errors = array_merge($errors, $image_errors);
      } else {
         $saveName = $image->cleanName($newName);
         // --- New system (save in gallery table) ---
         $response = $image->saveInDatabase($saveName . "." . $file_ext, $id, $frm, $formName);
         // --- New system (save in gallery table) ---

         // --- Old system (column in the table) ---
         // $formField = $image->getFormField($frm);
         // $content = $image->getContent($table['name'], $id);
         // $gallery = $content[$formField['name']] . $saveName . "." . $file_ext . ",";
         // $updateData[$formField['name']] = $gallery;
         // $updateResponse = $image->updateTableContent($updateData, $table['name'], $id);
         // --- Old system (column in the table) ---

         $logtable = $table['name'];
         $action = "Update Images to Form " . $frm;
         $sqlproductlog = $response['query'];
         $notes = $response['message'];

         if ($response['status'] == 'error') {
            $errors = array_merge($errors, $response);
            savelog('', $action, $sqlproductlog, $table['name'], 'FAIL', $notes, $id);
         } else {
            savelog('', $action, $sqlproductlog, $table['name'], 'SUCCESS', $notes, $id);
         }
      }
   }
} else {
   $errors[] = "No images were uploaded";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Check image processor</title>
   <style>
      .container {
         width: 90%;
         margin: 3rem auto;
      }

      .gallery {
         display: flex;
         flex-wrap: wrap;
         justify-content: center;
         flex-direction: row;
         gap: 1rem;
      }

      .image-container {
         flex: 1 1 auto;
         width: 300px;
         height: auto;
      }

      .image-container img {
         width: 100%;
         height: auto;
         object-fit: cover;
      }

      .result {
         width: 400px;
         margin: 2rem auto auto;
         border: 1px solid black;
         border-radius: 8px;
         text-align: center;
      }
   </style>
<?php require_once __DIR__ . '/../include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/../include/dev-banner.php'; ?>
   <div class="result">
      <?php
      if (empty($errors)) {
         echo '<h3>Images uploaded successfully!</h3>
         <button onclick="location.reload()">Refresh images</button>';

         // header("Location: success.php");
         // exit();
      } else {
         echo "<pre>";
         print_r($errors);
         echo "</pre>";
      }
      ?>
   </div>

   <!-- <div class="container">
      ?php
      // folder where are the images
      if ($frm == 11) {
         $folder = 'images/products/md/';
      }else {
         $folder = 'images/content/';
      }
      $url_folder = "/wccms/controllers/". $folder;

      // Get a list of files in the folder
      $files = scandir($folder);

      // Filter to only get image files
      $images = array_filter($files, function ($file) {
         return preg_match('/\.(jpg|jpeg|png)$/i', $file);
      });

      // Create the gallery
      echo '<div class="gallery">';
      foreach ($images as $image) {
         echo '<div class="image-container">';
         echo '<img src="' . $url_folder . $image . '" alt="' . $image . '">';
         echo '</div>';
      }
      echo '</div>';
      ?>
   </div> -->
<?php require_once __DIR__ . '/../include/bootstrap-js.php'; ?>
</body>

</html>
