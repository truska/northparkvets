<?php
/**
 * Importing Functions
 * 
 *
 **/


 function logProcessStart($importname, $importtype, $sourcefilename, $user) {
    // Log the received parameters for debugging
    $notes = 'Import Started';
    error_log("Debug: Import Name = $importname, Import Type = $importtype, Source File = $sourcefilename, Notes = $notes, User = $user");

    // Database connection
    $db = DB::connection();

    // SQL Query to insert the log record
    $sql = "INSERT INTO product_import_log (name, type, status, source, notes, user, started)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    // Prepare the statement
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log("Statement preparation failed: " . $db->error);
        return false; // Exit on error
    }

    // Set status to 1 for 'started'
    $status = 1;

    // Log the values being bound to the query
    error_log("Debug: Binding Parameters -> Name: $importname, Type: $importtype, Status: $status, Source: $sourcefilename, Notes: $notes, User: $user");

    // Bind parameters
    $stmt->bind_param("ssissi", $importname, $importtype, $status, $sourcefilename, $notes, $user);

    // Execute the query
    if ($stmt->execute()) {
        // Log success and the inserted ID
        $logID = $db->insert_id;
        error_log("Debug: Query executed successfully. Inserted Log ID: $logID");

        $stmt->close();
        $db->close();
        return $logID; // Return the log ID for further processing
    } else {
        // Log execution failure and the error
        error_log("Execution failed: " . $stmt->error);

        $stmt->close();
        $db->close();
        return false; // Exit on error
    }
}



/*
 function logProcessStart($importname, $importtype, $sourcefilename) {
    error_log("Import Type value at strat of 1st og write: ".$importtype) ;
    // Database connection using your custom class
    $db = DB::connection();

    // SQL Query to insert the log record
    $sql = "INSERT INTO product_import_log (name, type, status, source, started)
            VALUES (?, ?, ?, ?, NOW())";

    // Prepare and execute the query
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        echo "Statement preparation failed: " . $db->error;
        return false;
    }

    $status = 1; // Set status to 1 for 'started'
    $stmt->bind_param("ssis", $importname, $importtype, $status, $sourcefilename);

    if ($stmt->execute()) {
        // Return the ID of the newly inserted log record
        $logID = $db->insert_id;
        $stmt->close();
        $db->close();
        return $logID;
    } else {
        // Handle execution error
        echo "Execution failed: " . $stmt->error;
        $stmt->close();
        $db->close();
        return false;
    }
}
*/

/**
 * Normalize a date to 'Y-m-d' format
 */
function normalizeDate($dateValue) {
    // Handle Excel numeric date
    if (is_numeric($dateValue)) {
        return excelDateToPHPDate($dateValue); // Convert numeric date
    }

    // Handle various date formats
    $dateObject = DateTime::createFromFormat('D d/m/Y', $dateValue) // Format with day abbreviation
        ?: DateTime::createFromFormat('d/m/Y', $dateValue)         // Day/Month/Year
        ?: DateTime::createFromFormat('Y-m-d', $dateValue)         // Already normalized format
        ?: DateTime::createFromFormat('d-M-y', $dateValue);        // Format like 25-Dec-24

    if ($dateObject) {
        return $dateObject->format('Y-m-d'); // Normalize to Y-m-d
    }

    // Return null for invalid dates
    return null;
}




function excelDateToPHPDate($excelDate) {
    // Excel's base date is 1900-01-01; adjust for the 1900 leap year bug
    $baseDate = new DateTime('1899-12-30'); 
    return $baseDate->add(new DateInterval("P{$excelDate}D"))->format('Y-m-d');
}


function processDate($dateValue) {
    if (is_numeric($dateValue)) {
        return excelDateToPHPDate($dateValue); // Convert numeric date
    }
    return $dateValue; // Assume already formatted
}


if (!function_exists('logProcessUpdate')) {
    function logProcessUpdate($logID, $status, $notes) {
        // Database connection using your custom DB class
        $db = DB::connection();

        // SQL Query to update the log record
        $sql = "UPDATE product_import_log 
                SET status = ?, modified = NOW(), notes = ? 
                WHERE id = ?";

        // Prepare and execute the query
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            echo "Statement preparation failed: " . $db->error;
            return false;
        }

        $stmt->bind_param("isi", $status, $notes, $logID);

        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return true;
        } else {
            echo "Execution failed: " . $stmt->error;
            $stmt->close();
            $db->close();
            return false;
        }
    }
}



if (!function_exists('importDataToTempTable')) {
    function importDataToTempTable($sourceFilePath, $importtype) {
        // Open the CSV file
        if (!file_exists($sourceFilePath) || !is_readable($sourceFilePath)) {
            return ['success' => false, 'message' => "Cannot read the source file."];
        }

        // Define the column mapping based on $importtype
        $mapping = [];

        switch ($importtype) {
            case 'HF':
                $mapping = [
                    'expected_date' => 1,           // Column B
                    'expected_time' => 2,           // Column C
                    'in_out' => 5,                  // Column F
                    'depot' => 6,                   // Column G
                    'account' => 7,                 // Column H
                    'pcloud' => 8,                  // Column I
                    'po' => 9,                      // Column J
                    'destination_customer' => 10,   // Column K
                    'destination_country' => 11,    // Column L
                    'region' => 12,                 // Column M
                    'product_type' => 13,           // Column N
                    'pallet_type' => 15,            // Column P
                    'pallet_count' => 16 ,          // Column Q
                    'tanker' => null,
                    'ehc_ref' => null
                ];
                break;

            case 'NT':
                $mapping = [
                    'expected_date' => 0,           // Column A
                    'expected_time' => 1,           // Column B
                    'in_out' => 2,                  // Column C
                    'depot' => 3,                   // Column D
                    'account' => 4,                 // Column E                    
                    'pcloud' => 6,                  // Column G
                    'po' => 5,                      // Column F
                    'destination_customer' => 7,    // Column H
                    'destination_country' => 8,     // Column I
                    'region' => 9,                  // Column J
                    'product_type' => 10,           // Column K
                    'pallet_type' => 11,            // Column L
                    'pallet_count' => 12,           // Column M
                    'tanker' => null,
                    'ehc_ref' => null
                ];
                break;

            case 'NPV':
                $mapping = [
                    'expected_date' => 1,           // Column B
                    'expected_time' => 2,           // Column C
                    'in_out' => 5,                  // Column F
                    'depot' => 6,                   // Column G
                    'account' => 7,                 // Column H
                    'pcloud' => 9,                  // Column J
                    'po' => 8,                      // Column I
                    'destination_customer' => 10,   // Column K
                    'destination_country' => 11,    // Column L
                    'region' => 12,                 // Column M
                    'product_type' => 13,           // Column N
                    'pallet_type' => 15,            // Column P
                    'pallet_count' => 16,           // Column Q
                    'ehc_ref' => null,
                    'tanker' => null
                ];
                break;

            case 'TANK':
                $mapping = [
                    'expected_date' => 1,           // Column B
                    'expected_time' => 2,           // Column C
                    'in_out' => 5,                  // Column F
                    'depot' => 6,                   // Column G
                    'account' => 7,                 // Column H
                    'pcloud' => 8,                  // Column I
                    'po' => 9,                      // Column J
                    'destination_customer' => 10,   // Column K
                    'destination_country' => 11,    // Column L
                    'region' => 12,                 // Column M
                    'product_type' => 13,           // Column N
                    'pallet_type' => 15,            // Column P
                    'pallet_count' => 16,           // Column Q
                    'ehc_ref' => 17 ,               // Column R
                    'tanker' => 3                   // Column D
                ];
                break;

            default:

            return ['success' => false, 'message' => "Unsupported import type: $importtype"];
        }

        // --- define a canonical column order for products_import ---
        $targetColumns = [
            'expected_date','expected_time','in_out','depot','account','po','pcloud', 'destination_customer','destination_country','region', 'product_type', 'pallet_type','pallet_count','ehc_ref','tanker'
        ];

        // Open database connection
        $db = DB::connection();

        // Prepare insert query
        // Updated for new TANK import
        //$sql = "INSERT INTO products_import (expected_date, expected_time, in_out, depot, account, po, pcloud, destination_customer, destination_country, region, product_type, pallet_type, pallet_count)
        //VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        // Build INSERT with 15 placeholders
        $sql = "INSERT INTO products_import (" . implode(',', $targetColumns) . ")
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";



        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => "Statement preparation failed: " . $db->error];
        }

        // Read the CSV file
        if (($handle = fopen($sourceFilePath, 'r')) !== false) {
            $row = 0;
            while (($data = fgetcsv($handle, null, ",", '"', "\\")) !== false) {
                $row++;
                //if ($row == 1) {
                    // Skip header row
                 //   continue;
                //}

                $values = []; //clear / reset array 


                foreach ($targetColumns as $col) {
                    if (isset($mapping[$col]) && is_numeric($mapping[$col])) {
                        $idx = $mapping[$col];
                        if ($col === 'expected_date' && isset($data[$idx])) {
                            $values[] = normalizeDate($data[$idx]); // Y-m-d
                        } else {
                            $values[] = isset($data[$idx]) ? $data[$idx] : null;
                        }
                    } else {
                        // constant or unmapped (e.g., tanker/ehc_ref for non-TANK)
                        $values[] = $mapping[$col] ?? null;
                    }
                }
                // above replaces below after adding extra fields
                /*
                foreach ($mapping as $column => $csvIndex) {
                    if (is_numeric($csvIndex)) {
                        // Check if it's the `expected_date` column and normalize the date
                        if ($column === 'expected_date' && isset($data[$csvIndex])) {
                            $values[] = normalizeDate($data[$csvIndex]); // Normalize to 'Y-m-d'
                        } else {
                            $values[] = isset($data[$csvIndex]) ? $data[$csvIndex] : null;
                        }
                    } else {
                        // Handle constant values like 'NT'
                        $values[] = $csvIndex;
                    }
                    error_log("Row $row: Values -> " . json_encode($values));
                }
                */
                
                /*
                    foreach ($mapping as $column => $csvIndex) {
                        if (is_numeric($csvIndex)) {
                            // Convert `expected_date` for NT type if needed
                            if ($column === 'expected_date' && $importtype === 'NT' && isset($data[$csvIndex]) && is_numeric($data[$csvIndex])) {
                                $values[] = excelDateToPHPDate($data[$csvIndex]); // Convert Excel numeric date
                            } else {
                                $values[] = isset($data[$csvIndex]) ? $data[$csvIndex] : null;
                            }
                        } else {
                            // Handle constant values like 'NT'
                            $values[] = $csvIndex;
                        }
                    }
                /*
                    // Original date handling for HF
                    // Map columns to values
                    $values = [];
                    foreach ($mapping as $column => $csvIndex) {
                        if (is_numeric($csvIndex)) {
                            // Value from CSV
                            $values[] = isset($data[$csvIndex]) ? $data[$csvIndex] : null;
                        } else {
                            // Constant value
                            $values[] = $csvIndex;
                        }
                    }
                */
                // Debug: Check values being inserted
                error_log("Row $row: Values being inserted -> " . json_encode($values));

                // Validate bind variable count
                if (count($values) !== 15) {
                    error_log("Row $row: Mismatch in number of bind variables. Values: " . json_encode($values));
                    return ['success' => false, 'message' => "Mismatch in number of bind variables on row $row"];
                }

                // Bind and execute the query
                $stmt->bind_param("sssssssssssssss", ...$values);

                if (!$stmt->execute()) {
                    fclose($handle);
                    return ['success' => false, 'message' => "Failed to insert data on row $row: " . $stmt->error];
                }
            }
            fclose($handle);
        }

        $stmt->close();
        $db->close();

        return ['success' => true, 'message' => "Data imported successfully."];
    }
}





if (!function_exists('truncateTempTable')) {
    function truncateTempTable() {
        // Use your custom DB class to execute the query
        $sql = "TRUNCATE TABLE products_import";

        // Execute the query
        $result = DB::query($sql);

        // Check for success
        if (!$result) {
            error_log("Failed to truncate temp table: " . mysqli_error(DB::connection()));
            return false;
        }
        return true;
    }
}





if (!function_exists('processImportedData')) {
    function processImportedData($logID) {
        $db = DB::connection();

        // Truncate the products_processed table
        $truncateSql = "TRUNCATE TABLE products_processed";
        if (!$db->query($truncateSql)) {
            return ['success' => false, 'message' => "Failed to truncate `products_processed`: " . $db->error];
        }

        // Fetch rows from the `products_import` table
        $sql = "SELECT * FROM products_import";
        $result = $db->query($sql);

        if (!$result) {
            return ['success' => false, 'message' => "Failed to fetch imported data: " . $db->error];
        }

        // Prepare exception table insert query
        $exceptionSql = "INSERT INTO product_import_exceptions (importid, po, fieldname, fieldvalue, notes, created)
                         VALUES (?, ?, ?, ?, ?, NOW())";
            error_log("Exception sql: ".$exceptionSql) ;
         
        $exceptionStmt = $db->prepare($exceptionSql);

            if (!$exceptionStmt) {
            error_log("Failed to prepare exception SQL: " . $db->error);
            die("Error: Could not prepare exception SQL.");
        }    


        // Prepare `products_processed` insert query
        // updated for extra fields
        $processedSql = "INSERT INTO products_processed (
            expected_date, expected_time, in_out, depot, account, po, pcloud,
            destination_customer, destination_country, product_type, pallet_type,
            pallet_count, tanker, ehc_ref
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $processedStmt = $db->prepare($processedSql);
        if (!$processedStmt) {
            return ['success' => false, 'message' => "Failed to prepare `products_processed` statement: " . $db->error];
        }
        /*
            $processedSql = "INSERT INTO products_processed (expected_date, expected_time, in_out, depot, account, po, pcloud, destination_customer, destination_country, product_type, pallet_type, pallet_count)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" ;
            $processedStmt = $db->prepare($processedSql);
            if (!$processedStmt) {
                return ['success' => false, 'message' => "Failed to prepare `products_processed` statement: " . $db->error];
            }
        */

        echo "<h3>Processed Records:</h3><ul>";

        while ($row = $result->fetch_assoc()) {
            if (!(
                $row['in_out'] === 'outbound' ||
                $row['region'] === 'EU' ||
                $row['region'] === 'Overseas'
            )) {
                continue;
            }

            // Ensure `expected_date` is not empty
            if (empty($row['expected_date'])) {
                echo "<p>Invalid or missing date for expected_date | PO: {$row['po']}</p>";
                $row['expected_date'] = null;
            }

            // Validate fields and handle exceptions
            $fields = ['depot', 'account', 'destination_country'];
            $validationTables = [
                'depot' => 'npe_depot',
                'account' => 'npe_customer',
                'destination_country' => 'npe_destination'
            ];
            $validatedData = [];
            $exceptions = false;

            foreach ($fields as $field) {
                if (!empty($row[$field])) {
                    $checkSql = "SELECT id FROM {$validationTables[$field]} WHERE code = ? OR name = ?";
                    $checkStmt = $db->prepare($checkSql);
                    $fieldValue = $row[$field];
                    $checkStmt->bind_param("ss", $fieldValue, $fieldValue);
                    $checkStmt->execute();
                    $checkStmt->store_result();

                    if ($checkStmt->num_rows > 0) {
                        $checkStmt->bind_result($validatedID);
                        $checkStmt->fetch();
                        $validatedData[$field] = $validatedID;
                    } else {
                        // Log to exception table
                        $exceptions = true;
                        $importID = $logID;
                        $poValue = $row['po'];
                        $fieldname = $field;
                        $fieldvalue = $row[$field];
                        $notes = "the lookup record for '".$fieldvalue."' was not found in the ".$fieldname." table" ;
                        
                        $exceptionStmt->bind_param(
                            "issss",
                            $importID,
                            $poValue,
                            $fieldname,
                            $fieldvalue ,
                            $notes                           
                        );
                        $exceptionStmt->execute();

                        echo "<p>Exception: PO {$poValue}, Field '{$fieldname}', Value '{$fieldvalue}' does not exist.</p>";

                        $validatedData[$field] = null;
                    }
                    $checkStmt->close();
                } else {
                    $validatedData[$field] = null;
                }
            }

            // Insert into `products_processed`
            $processedStmt->bind_param(
                "ssssssssssssss",
                $row['expected_date'],
                $row['expected_time'],
                $row['in_out'],
                $validatedData['depot'],
                $validatedData['account'],
                $row['po'],
                $row['pcloud'],
                $row['destination_customer'],
                $validatedData['destination_country'],
                $row['product_type'],
                $row['pallet_type'],
                $row['pallet_count'],
                $row['tanker'],     // NEW
                $row['ehc_ref']     // NEW
            );

            if ($processedStmt->execute()) {
                $processedID = $db->insert_id;
            }
        }

        echo "</ul>";

        $exceptionStmt->close();
        $processedStmt->close();
        $db->close();

        return ['success' => true, 'message' => "Processing completed successfully."];
    }
}


if (!function_exists('updateLiveTable')) {
    function updateLiveTable($logID) {
        // Database connection
        $db = DB::connection();

        global $userID;
        error_log("In Process Live User ID:".$userID);

        // Count exceptions for this import
        $exceptionCountSql = "SELECT COUNT(*) as exception_count FROM product_import_exceptions WHERE importid = ?";
        $exceptionStmt = $db->prepare($exceptionCountSql);
        $exceptionStmt->bind_param("i", $logID);
        $exceptionStmt->execute();
        $exceptionStmt->bind_result($exceptionCount);
        $exceptionStmt->fetch();
        $exceptionStmt->close();

        // Fetch rows from `products_processed`
        $fetchProcessedSql = "SELECT * FROM products_processed";
        $processedResult = $db->query($fetchProcessedSql);

        if (!$processedResult) {
            return ['success' => false, 'message' => "Failed to fetch processed data: " . $db->error];
        }


        $insertCount = 0; // Track number of records inserted

        // Prepare exception insertion
        $exceptionSql = "INSERT INTO product_import_exceptions (importid, po, notes, created)
                         VALUES (?, ?, ?, NOW())";
        $exceptionStmt = $db->prepare($exceptionSql);

        while ($row = $processedResult->fetch_assoc()) {

            $name = $row['po']; // Use `po` from processed table as `name`

            // Calculate the week number from `expected_date`
            $weeknum = null;
            if (!empty($row['expected_date'])) {
                $date = DateTime::createFromFormat('Y-m-d', $row['expected_date']);
                if ($date) {
                    $weeknum = $date->format('W'); // Week number
                }
            }

            // Check if `name` exists in `products`
            $nameCheckSql = "SELECT id FROM products WHERE name = ? ORDER BY created DESC LIMIT 1";
            $nameCheckStmt = $db->prepare($nameCheckSql);
            $nameCheckStmt->bind_param("s", $name);
            $nameCheckStmt->execute();
            $nameCheckStmt->store_result();

            if ($nameCheckStmt->num_rows > 0) {
                // Update `status` of most recent occurrences to 10
                $updateStatusSql = "UPDATE `products` SET `status` = 10 WHERE `name` = ?";
                $updateStatusStmt = $db->prepare($updateStatusSql);
                $updateStatusStmt->bind_param("s", $name);
                $updateStatusStmt->execute();
                $updateStatusStmt->close();

                // Add to exceptions table
                $notes = "Duplicate PO - Rolled forward";
               // $userid = $userID; // Replace with actual user ID when available
                $exceptionStmt->bind_param("iss", $logID, $name, $notes);
                if ($exceptionStmt->execute()) {
                    echo "<p>Exception: Duplicate PO '{$name}' rolled forward and logged.</p>";
                } else {
                    error_log("Failed to log exception for PO '{$name}': " . $exceptionStmt->error);
                }
            }
            $nameCheckStmt->close();

            // Insert the new record into `products`
            // New for extrat fields
            $insertSql = "INSERT INTO products (
                `expected_date`, `expected_time`, `in_out`, `depot`, `pcloud`, `account`,
                `name`, `destination_customer`, `destination_country`, `product_type`,
                `pallet_type`, `pallet_count`, `tanker`, `ehc_ref`, `importbatch`, `weeknum`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $db->prepare($insertSql);
            
            $insertStmt->bind_param(
                "ssssssssssssssii",
                $row['expected_date'],
                $row['expected_time'],
                $row['in_out'],
                $row['depot'],
                $row['pcloud'],
                $row['account'],
                $name,                           // (PO used as name)
                $row['destination_customer'],
                $row['destination_country'],
                $row['product_type'],
                $row['pallet_type'],
                $row['pallet_count'],
                $row['tanker'],                  // NEW
                $row['ehc_ref'],                 // NEW
                $logID,
                $weeknum
            );
            
            /*          
                $insertSql = "INSERT INTO products (`expected_date`, `expected_time`, `in_out`, `depot`,  `pcloud`, `account`, `name`, `destination_customer`, `destination_country`, `product_type`, `pallet_type`, `pallet_count`, `importbatch`, `weeknum`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $db->prepare($insertSql);
                $insertStmt->bind_param(
                    "sssssssssssiii",
                    $row['expected_date'],
                    $row['expected_time'],
                    $row['in_out'],
                    $row['depot'],
                    $row['pcloud'],
                    $row['account'],
                    $name,              // Use `name` instead of `po`
                    $row['destination_customer'],
                    $row['destination_country'],
                    $row['product_type'],
                    $row['pallet_type'],
                    $row['pallet_count'],
                    $logID, // Include the logID for importbatch
                    $weeknum // Include the week number
                );
            */
            if ($insertStmt->execute()) {
                $insertCount++;
            } else {
                error_log("Error inserting record into products: " . $insertStmt->error);
                echo "<p>Error inserting record into products: {$insertStmt->error}</p>";
            }
            $insertStmt->close();
        }

        $exceptionStmt->close();

        // Update the log
        $logUpdateSql = "UPDATE product_import_log SET status = 6, notes = CONCAT(notes, ' | Records Inserted = ', ?, ' | Exceptions = ', ?) WHERE id = ?";
        $logUpdateStmt = $db->prepare($logUpdateSql);
        $logUpdateStmt->bind_param("iii", $insertCount, $exceptionCount, $logID);
        $logUpdateStmt->execute();
        $logUpdateStmt->close();

        // Close database connection
        $db->close();

        return ['success' => true, 'message' => "Live table updated successfully. Records inserted: $insertCount"];
    }
}




if (!function_exists('archiveSourceFile')) {
    function archiveSourceFile($sourceFilePath, $logID) {
        // Define destination folder
        $destinationFolder = "importfiles/done/";
        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder, 0755, true); // Ensure destination folder exists
        }

        // Extract filename and append Batch LogID
        $fileInfo = pathinfo($sourceFilePath);
        $newFileName = $fileInfo['filename'] . "_Batch_" . $logID . "." . $fileInfo['extension'];
        $destinationPath = $destinationFolder . $newFileName;

        // Move the file
        if (rename($sourceFilePath, $destinationPath)) {
            // File moved successfully
            return ['success' => true, 'newFileName' => $newFileName];
        } else {
            // Failed to move file
            return ['success' => false, 'message' => "Failed to archive the file: $sourceFilePath"];
        }
    }
}


if (!function_exists('logProcessCompletion')) {
    function logProcessCompletion($logID, $notes) {
        $db = DB::connection();
        $sql = "UPDATE product_import_log 
                SET status = 9, 
                    finished = NOW(), 
                    notes = CONCAT(notes, ' | ', ?) 
                WHERE id = ?";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            error_log("Failed to prepare log update statement: " . $db->error);
            return false;
        }

        $stmt->bind_param("si", $notes, $logID);
        $result = $stmt->execute();
        $stmt->close();
        $db->close();

        return $result;
    }
}