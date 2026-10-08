<!-- START admin-database-fields -->
<!DOCTYPE html>

<?php
// Check for and if missing added several fields to all tables

// use $action# = Yes / No to select what to run

$action1 = "No" ;
$action2 = "No" ;
$action3 = "No" ;

$action1desc = 'Action 1: check and if missing add fields showonweb, created, modified and archived to ALL tables' ;
$action2desc = 'Action 2: cms_form_field adding new fields if missing and renaming some fields';
$action3desc = 'Action 3: cms_form - checking and adding new fields if missing';

// Turn off error reporting
error_reporting(0);
// Turn on error reporting
// error_reporting(1);

include('setting/main-top-files.php'); // Added by salva TDR | 9.12.2022

//Bring Fwd variables - Edited by salva TDR | 12.12.2022
$baseURL = $BASE_URL;


?>
<html lang="en">

<head>
    <?php
        include("include/header-code.php");
    ?>

<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <section id="container" class="">
        <?php
        include("include/header.php");
        include("include/sidebar.php");
        ?>


        <section id="main-content">
            <section class="wrapper site-min-height">

                <!-- page start-->
                <section class="card" style="width:100%;margin-left: -10px">
                    <div class="row">
                        <div class="card-body">
                            <!-- <div class="col-md-1 hidden-sm hidden-xs"></div>  -->
                            <div class="col-sm-12 col-md-10 col-lg-10" style="margin-top:20px;">
                                <h2>Check and adjust Database tables </h2>
                                <?php
                                echo "<h4>Settings</h4>" ;
                                echo "<p>".date('m/d/Y h:i:s a', time())."<br>" ;
                                echo "".$action1." - ".$action1desc."<br>" ;
                                echo "".$action2." - ".$action2desc."<br>" ;
                                echo "".$action3." - ".$action3desc."</p>" ;
                                ?>
                            </div>


                            <?php
                            // Update all tables
                            $result = DatabaseUpdater::updateDatabaseSchema($action1,$action2,$action3);
                            print_r($result);

                            // Specifically update 'cms_form_field' table
                            DatabaseUpdater::updateCmsFormFieldTable($action1,$action2,$action3);

                            DatabaseUpdater::updateCmsFormTable($action1,$action2,$action3);


                            class DatabaseUpdater
                            {
                                // This function updates all tables with certain fields
                                public static function updateDatabaseSchema($action1,$action2,$action3)
                                {
                                    $action = $action1 ; // Set to 'Yes' to apply changes, 'No' to only simulate and log - needs set in 2nd function as well

                                    // Get all table names from the database
                                    $tablesResult = DB::query("SHOW TABLES");
                                    if (!$tablesResult) {
                                        return "Error fetching tables.";
                                    }

                                    $tables = mysqli_fetch_all($tablesResult, MYSQLI_NUM);
                                    $output = [];
                                    foreach ($tables as $tableRow) {
                                        $table = $tableRow[0];
                                        $tableChanges = ["<br>"];

                                        // Define the fields and SQL to add them if missing
                                        $fieldsToAdd = [
                                            'showonweb' => "ALTER TABLE `$table` ADD `showonweb` ENUM('Yes', 'No') NOT NULL DEFAULT 'Yes'",
                                            'created' => "ALTER TABLE `$table` ADD `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                                            'modified' => "ALTER TABLE `$table` ADD `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
                                            'archived' => "ALTER TABLE `$table` ADD `archived` TINYINT(1) NOT NULL DEFAULT 0"
                                        ];

                                        foreach ($fieldsToAdd as $fieldName => $sql) {
                                            $checkFieldExists = DB::query("SHOW COLUMNS FROM `$table` LIKE '$fieldName'");
                                            if (!$checkFieldExists || mysqli_num_rows($checkFieldExists) == 0) {
                                                if ($action == 'Yes') {
                                                    $result = DB::query($sql);
                                                    if ($result) {
                                                        $tableChanges[] = "Added `$fieldName` to $table <br>";
                                                    } else {
                                                        $tableChanges[] = "Failed to add `$fieldName` to $table <br>";
                                                    }
                                                } else {
                                                    $tableChanges[] = "Needs to add `$fieldName` to $table  [Turn Action On to apply change]<br>";
                                                }
                                            } else {
                                                $tableChanges[] = "`$fieldName` already exists in table $table <br>";
                                            }
                                        }

                                        if (!empty($tableChanges)) {
                                            $output[$table] = $tableChanges;
                                        }
                                    }

                                    if (empty($output)) {
                                        return "No changes needed or all fields already exist.";
                                    }

                                    return $output;
                                }

                                // This function specifically updates the 'cms_form_field' table
                                public static function updateCmsFormFieldTable($action1,$action2,$action3)
                                {
                                    $action = $action2; // Set at top of file
                                    $table = 'cms_form_field';

                                    // Check and rename `order` to `sort` if it exists
                                    $orderExists = DB::query("SHOW COLUMNS FROM `$table` LIKE 'order'");
                                    if ($orderExists && mysqli_num_rows($orderExists) > 0) {
                                        if ($action == 'Yes') {
                                            DB::query("ALTER TABLE `$table` CHANGE `order` `sort` INT(11)");
                                        }
                                        else
                                        {
                                            echo "Needs to rename `order` to 'sort' in $table  [Turn Action On to apply change]<br>";
                                        }
                                        echo "`order` renamed to `sort` in $table <br>";
                                    } else {
                                        echo "`sort` [not order] already exists in table $table <br>";
                                    }


                                    // Define the fields and SQL to add them if missing
                                    $fieldsToAdd = [
                                        'tab' => [
                                            'sql' => "ALTER TABLE `$table` ADD `tab` VARCHAR(255) AFTER ",
                                            'position' => ['order', 'sort']
                                        ],
                                        'default_size' => [
                                            'sql' => "ALTER TABLE `$table` ADD `default_size` VARCHAR(255) AFTER `file_ext`"
                                        ],
                                        'resize_status' => [
                                            'sql' => "ALTER TABLE `$table` ADD `resize_status` TINYINT(1) AFTER `file_ext`"
                                        ]
                                    ];

                                    // Add fields if they do not exist
                                    foreach ($fieldsToAdd as $fieldName => $info) {
                                        $checkFieldExists = DB::query("SHOW COLUMNS FROM `$table` LIKE '$fieldName'");
                                        if (!$checkFieldExists || mysqli_num_rows($checkFieldExists) == 0) {
                                            // Special handling for `tab` because it has conditional positioning
                                            if ($fieldName == 'tab') {
                                                foreach ($info['position'] as $positionField) {
                                                    $positionExists = DB::query("SHOW COLUMNS FROM `$table` LIKE '$positionField'");
                                                    if ($positionExists && mysqli_num_rows($positionExists) > 0) {
                                                        $info['sql'] .= "`$positionField`";
                                                        break;
                                                    }
                                                }
                                            }

                                            if ($action == 'Yes') {
                                                $result = DB::query($info['sql']);
                                                if ($result) {
                                                    echo "Added `$fieldName` to $table <br>";
                                                } else {
                                                    echo "Failed to add `$fieldName` to $table <br>";
                                                }
                                            } else {
                                                echo "Needs to add `$fieldName` to $table [Turn Action On to apply change]<br>";
                                            }
                                        } else {
                                            echo "`$fieldName` already exixts in table $table <br>";
                                        }
                                    }

                                    echo "Update completed for table $table <br>";
                                }

                                // This function specifically updates the 'cms_form' table
                                public static function updateCmsFormTable($action1,$action2,$action3)
                                {
                                    $action = $action3 ; // Set at top of file
                                    $table = 'cms_form';

                                    // Check and rename `order` to `sort` if it exists
                                    /*
                                    $orderExists = DB::query("SHOW COLUMNS FROM `$table` LIKE 'order'");
                                    if ($orderExists && mysqli_num_rows($orderExists) > 0) {
                                        if ($action == 'Yes') {
                                            DB::query("ALTER TABLE `$table` CHANGE `order` `sort` INT(11)");
                                        }
                                        else
                                        {
                                            echo "Needs to rename `order` to 'sort' in $table  [Turn Action On to apply change]<br>";
                                        }
                                        echo "`order` renamed to `sort` in $table <br>";
                                    } else {
                                        echo "`sort` [not order] already exists in table $table <br>";
                                    }
                                    */

                                    // Define the fields and SQL to add them if missing
                                    $fieldsToAdd = [
                                        //COL
                                        'col4' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col4` VARCHAR(32) AFTER 
                                            `col3table` " 
                                        ],
                                        'col5' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col5` VARCHAR(32) AFTER 
                                            `col4` " 
                                        ],
                                        'col6' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col6` VARCHAR(32) AFTER 
                                            `col5` " 
                                        ],   

                                        //ColNAME
                                        'col1name' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col1name` VARCHAR(32) AFTER 
                                            `table` " 
                                        ],
                                        'col2name' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col2name` VARCHAR(32) AFTER 
                                            `col1` " 
                                        ],
                                        'col3name' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col3name` VARCHAR(32) AFTER 
                                            `col2` " 
                                        ],
                                        'col4name' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col4name` VARCHAR(32) AFTER 
                                            `col3` " 
                                        ],
                                        'col5name' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col5name` VARCHAR(32) AFTER 
                                            `col4` " 
                                        ],
                                        'col6name' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col6name` VARCHAR(32) AFTER 
                                            `col5` " 
                                        ],


                                        //ColTABLE
                                        'col1table' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col1table` VARCHAR(32) AFTER 
                                            `col1name` " 
                                        ],
                                        'col2table' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col2table` VARCHAR(32) AFTER 
                                            `col2name` " 
                                        ],
                                        'col3table' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col3table` VARCHAR(32) AFTER 
                                            `col3name` " 
                                        ],
                                        'col4table' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col4table` VARCHAR(32) AFTER 
                                            `col4name` " 
                                        ],
                                        'col5table' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col5table` VARCHAR(32) AFTER 
                                            `col5name` " 
                                        ],
                                        'col6table' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col6table` VARCHAR(32) AFTER 
                                            `col6name` " 
                                        ],

                                        //ColTABLE
                                        'col1type' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col1type` ENUM('None', 'Search', 'Select') NOT NULL DEFAULT 'Search' AFTER 
                                            `col1table` " 
                                        ],
                                        'col2type' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col2type` ENUM('None', 'Search', 'Select') NOT NULL DEFAULT 'Search' AFTER 
                                            `col2table` " 
                                        ],
                                        'col3type' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col3type` ENUM('None', 'Search', 'Select') NOT NULL DEFAULT 'Search' AFTER 
                                            `col3table` " 
                                        ],
                                        'col4type' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col4type` ENUM('None', 'Search', 'Select') NOT NULL DEFAULT 'Search' AFTER 
                                            `col4table` " 
                                        ],
                                        'col5type' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col5type` ENUM('None', 'Search', 'Select') NOT NULL DEFAULT 'Search' AFTER
                                            `col5table` " 
                                        ],
                                        'col6type' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col6type` ENUM('None', 'Search', 'Select') NOT NULL DEFAULT 'Search' AFTER 
                                            `col6table` " 
                                        ],

                                        //ColDATA
                                        'col1data' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col1data` VARCHAR(64) AFTER 
                                            `col1type` " 
                                        ],
                                        'col2data' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col2data` VARCHAR(64) AFTER 
                                            `col2type` " 
                                        ],
                                        'col3data' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col3data` VARCHAR(64) AFTER 
                                            `col3type` " 
                                        ],
                                        'col4data' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col4data` VARCHAR(64) AFTER 
                                            `col4type` " 
                                        ],
                                        'col5data' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col5data` VARCHAR(64) AFTER 
                                            `col5type` " 
                                        ],
                                        'col6data' => [
                                            'sql' => "ALTER TABLE `$table` ADD `col6data` VARCHAR(64) AFTER 
                                            `col6type` " 
                                        ],

                                    ];

                                    // Add fields if they do not exist
                                    foreach ($fieldsToAdd as $fieldName => $info) {
                                        $checkFieldExists = DB::query("SHOW COLUMNS FROM `$table` LIKE '$fieldName'");
                                        if (!$checkFieldExists || mysqli_num_rows($checkFieldExists) == 0) {
                                            
                                            // Special handling for `tab` because it has conditional positioning
                                            /*if ($fieldName == 'tab') {
                                                foreach ($info['position'] as $positionField) {
                                                    $positionExists = DB::query("SHOW COLUMNS FROM `$table` LIKE '$positionField'");
                                                    if ($positionExists && mysqli_num_rows($positionExists) > 0) {
                                                        $info['sql'] .= "`$positionField`";
                                                        break;
                                                    }
                                                }
                                            }
                                            */

                                            if ($action == 'Yes') {
                                                $result = DB::query($info['sql']);
                                                if ($result) {
                                                    echo "Added `$fieldName` to $table <br>";
                                                } else {
                                                    echo "Failed to add `$fieldName` to $table <br>";
                                                }
                                            } else {
                                                echo "Needs to add `$fieldName` to $table [Turn Action On to apply change]<br>";
                                            }
                                        } else {
                                            echo "`$fieldName` already exists in table $table <br>";
                                        }
                                    }

                                    echo "Update completed for table $table <br>";
                                }


                            }



                            // START FOOTER FIXED STUFF

                            
                            include("include/footer-code.php");
                            include("include-tinymce.php");
                            ?>
                        </div>
                    </div>
                </section>
            </section>
        </section>
    </section>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>

<!-- END admin-database-fields -->