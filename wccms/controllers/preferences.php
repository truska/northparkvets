<?php

/**
 * Preferences
 * 
 * @version 1.0.0
 * @author salva TDR
 * 
 * @var int $record_id  Record id
 * @var array $form  Form data
 * @var array $table  Table data
 * @var array $form_fields  Form fields data
 * @var int $maximg  Max images
 * @var int $showgallery  Show gallery
 * @var string $tablename  Table name
 */
class Preferences
{
   public $preferences;
   private $preferencesQuery;

   function __construct()
   {
      $query = "SELECT * FROM `preferences` 
      WHERE `showoncms` = 'Yes' 
      AND `archived` = '0'
      ORDER BY `prefCat` ASC, `sort` ASC";
      $this->preferencesQuery = $query;
      $preferences = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

      if (count($preferences) > 0) {
         $this->preferences = $preferences;
      } else {
         // Return a error can be catch by the users
         throw new Exception("No preferences found");
      }
   }

   public function getPreferences($refresh = false)
   {
      if ($refresh) {
         $query = "SELECT * FROM `preferences` 
         WHERE `showoncms` = 'Yes' 
         AND `archived` = '0'
         ORDER BY `prefCat` ASC, `sort` ASC";
         $this->preferencesQuery = $query;

         $preferences = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

         if (count($preferences) > 0) {
            $this->preferences = $preferences;
         } else {
            // Return a error can be catch by the users
            throw new Exception("No preferences found");
         }

      }
      
      return $this->preferences;
   }

   public function getPreferencesQuery()
   {
      return $this->preferencesQuery;
   }

   public function getPreferencesTabs()
   {
      $query = "SELECT DISTINCT `preferences`.`prefCat` as `prefId`, `prefCat`.`name` as `prefName`, `prefCat`.`notes` as `prefNotes` FROM `preferences` 
      LEFT JOIN `prefCat` ON `preferences`.`prefCat` = `prefCat`.`id`
      WHERE `showoncms` = 'Yes' 
      GROUP BY `preferences`.`prefCat`
      ORDER BY `prefCat` ASC";
      $preferencesTabs = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

      if (count($preferencesTabs) > 0) {
         return $preferencesTabs;
      } else {
         throw new Exception("Error processing the preferences tabs");
      }
   }

   /**
    * Get Field Type
    *
    * @param int $field_id  Field ID
    * @return array|null  Field Type data or null
    */
   function getFieldType($field_id)
   {
      $query = "SELECT * FROM `cms_field` 
       WHERE `id` = '" . $field_id . "'";
      $field = mysqli_fetch_array(DB::query($query), MYSQLI_ASSOC);

      if ($field) {
         return $field;
      } else {
         return null;
      }
   }

   public function updatePreferences($data)
   {
      $response = [];
      $queries = [];

      foreach ($data as $key => $value) {
         if ($key != 'formnumber' && $key != 'submit') {
            $query = "UPDATE `preferences` 
            SET `value` = '$value' 
            WHERE `name` = '$key'";
            $result = DB::query($query);

            // Store the query in the response
            $queries[$key] = $query;

            if ($result) {
               $response['log'][$key] = [
                  'status' => 'success',
                  'message' => "Preference [$key] updated successfully",
                  'query' => $query
               ];
            } else {
               $response['log'][$key] = [
                  'status' => 'error',
                  'message' => "Preference [$key] update failed",
                  'query' => $query
               ];
               $response['errors'][$key] = [
                  'status' => 'error',
                  'message' => "Preference [$key] update failed",
                  'query' => $query
               ];
            }
         }
      }

      // Include the queries in the response
      $response['queries'] = $queries;

      return $response;
   }

   

}



function getLogmaskData($updateData) {
   $conn = DB::connection();
   if (!$conn) {
       error_log("Database connection failed.");
       return [];
   }

   $logmaskData = [];
   foreach (array_keys($updateData) as $key) {
       $sql = "SELECT logmask FROM preferences WHERE name = '$key'";
       $result = DB::query($sql);

       if ($result && mysqli_num_rows($result) > 0) {
           $row = mysqli_fetch_assoc($result);
           $logmaskData[$key] = $row['logmask'];
       } else {
           $logmaskData[$key] = 0;
       }
   }

   return $logmaskData;
}

// Masking functions for Key data
function createMaskedSqlQuery($updateData, $logmaskData) {
   $maskedData = [];
   foreach ($updateData as $key => $value) {
       $logmask = isset($logmaskData[$key]) ? (int)$logmaskData[$key] : 0;
       if ($logmask > 0) {
           $maskedData[$key] = maskValue($value, $logmask);
       } else {
           $maskedData[$key] = $value;
       }
   }

   $sqlQueryParts = [];
   foreach ($maskedData as $key => $value) {
       $sqlQueryParts[] = "$key='$value'";
   }

   return "UPDATE preferences SET " . implode(', ', $sqlQueryParts);
}

function maskValue($value, $logmask) {
   $keep_start = $logmask;
   $keep_end = $logmask;
   if (strlen($value) <= $keep_start + $keep_end) {
       return $value; // Do not mask if the value is too short
   }
   return substr($value, 0, $keep_start) . str_repeat('*', strlen($value) - $keep_start - $keep_end) . substr($value, -$keep_end);
}