<!-- START MasterPagev4 -->
<!DOCTYPE html>
<head>
    <?php
        // Turn off error reporting
        error_reporting(0);
        // Turn on error reporting
        // error_reporting(1);

        include ('setting/main-top-files.php'); // loads Session, Datebase Connectons, functions and more
        include("include/header-code.php");

        //Requires `frm` and `id` parameteres on url - if not needed comment out next lines
        if (!$formnumber = securityCheck($_GET['frm'], 'number')) {
            die('Error in the form'); // If the user try to insert something different from a number, we kill the script
        }
        if (!$recordnumber = securityCheck($_GET['id'], 'number')) {
            die('Error in the id'); // If the user try to insert something different from a number, we kill the script
        }
    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

    <body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php
    include("include/header.php"); // Added by salva TDR | 2.12.2022
    include("include/sidebar.php");
    ?>
    <!--/ Any Custom styles for alignment and indentation -->
    <style>
        /* Styles here if needed */
    </style>

    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="row">
                <div class="col-8 mainbody">
                    <?php
                        echo "<h1>".$prefs["prefSiteName"]."</h1>" ;
                        echo "<p> Debug on/off: ".$prefs["prefCMSDebugOn"]."</p>" ;
                    ?>
                    </div>
                <div class="col-4">

                    <h3>Area for more info</h3>

                </div>

                <?php

                    echo "<div class='table-responsive' id='main-data-area'>";

                        echo "<h2>Main Data Area</h2>" ;
                        // This area will generally be the area that the required functionality will be neded
                        // It maybe an inpout form
                        // It mayeb a repor using datatables
                        // That report will probably need an:
                        // sql statement
                        // Output options (psf, csv, copy, print etc....) 
                        // Search AND Select functions by column and global
                        // Sort on columns
                        // Total functions on some columns
                        // Records to view control and pagination

                    echo '</div>';
                ?>

            </div>

        <?php
        // include("include/footer.php"); // - footer.php doesn't exist inside wccms/include folder - Salva TDR | 17.1.2023
        // echo "</div>"; // Removed by salva TDR | 17.1.2023
        include ("include/footer-code.php");
        include ("include-tinymce.php");
        ?>

        </section>
    </section>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>

<!-- END MasterPagev4 -->