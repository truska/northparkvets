<!-- START recordTimeAddv5-->
<?php
// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

include ('setting/main-top-files.php'); // loads Session, Database Connections, functions, and more
include("include/header-code.php");





// Validate ID from the URL
if (!isset($_GET['id']) || !preg_match('/^\d+$/', $_GET['id'])) {
    die('Error: Invalid product ID format');
}

$productId = intval($_GET['id']);

$productDetails = getProductDetails($conn, $productId);

if (!$productDetails) {
    die('Error: Product not found');
}
else
{
  //  error_log("Product ID: ".$productId) ;
  //  error_log("Product Name: ".$productDetails['name']) ;
  //  error_log("VET id: ".$productDetails['vet']) ;
}


// Extract name and vet from product details
$name = htmlspecialchars($productDetails['name']);
$vet = intval($productDetails['vet']);


// Handle form submission


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $name = htmlspecialchars($_POST['name']); // 
    $date = $_POST['date'] ?? date('Y-m-d');
    $timesheetUserId = $user["id"]; // Replace with $userID if available
    $vet = intval($_POST['vet']);
    $time_ov = number_format(floatval($_POST['time_ov']), 2, '.', '');
    $travel_units = number_format(floatval($_POST['travel_units']), 2, '.', '');
    $time_cso = number_format(floatval($_POST['time_cso']), 2, '.', '');
    $travel_miles = number_format(floatval($_POST['travel_miles']), 2, '.', '');
    $certs = number_format(floatval($_POST['certs']), 2, '.', '');
    $sha_sa = number_format(floatval($_POST['sha_sa']), 2, '.', '');
    $courier = number_format(floatval($_POST['courier']), 2, '.', '');
    $notes = htmlspecialchars($_POST['notes']);

    require_once __DIR__ . '/controllers/timesheetRates.php';
    try {
        insertTimesheetWithRates($conn, [
            'productid'=>$productId, 'name'=>$name, 'date'=>$date, 'user'=>$timesheetUserId,
            'vet'=>$vet, 'time_ov'=>$time_ov, 'travel_units'=>$travel_units,
            'time_cso'=>$time_cso, 'travel_miles'=>$travel_miles, 'certs'=>$certs,
            'sha_sa'=>$sha_sa, 'courier'=>$courier, 'notes'=>$notes, 'tanker_cert'=>0
        ]);
        $successMessage = 'Record added successfully!';
        $formSubmitted = true;
    } catch (Throwable $exception) {
        error_log('Timesheet creation failed: '.$exception->getMessage());
        $errorMessage = 'Unable to save the timesheet. Check the current rates and entered values.';
        $formSubmitted = false;
    }

}

?>

<!DOCTYPE html>

<head>
    <?php
        // Turn off error reporting
        error_reporting(0);
        // Turn on error reporting
        // error_reporting(1);



        // Requires `frm` and `id` parameters on URL - if not needed comment out next lines
        if (!$formnumber = securityCheck($_GET['id'], 'number')) {
            die('Error in the form'); // If the user tries to insert something different from a number, we kill the script
        }
/*
        if (!isset($_GET['id']) || !preg_match('/^[a-zA-Z0-9 _-]+$/', $_GET['id'])) {
            die('Error: Invalid ID format'); // If the user tries to insert invalid characters, we kill the script
        }
    */
        $recordnumber = $_GET['id'];

        // Fetch VET parameter from URL
      //  $vet = isset($_GET['vet']) ? intval($_GET['vet']) : '';

    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>

    <!--/ Custom styles if needed -->
    <style>
        #successMessage,
        #errorMessage {
            transition: opacity 0.5s ease-in-out;
        }
        .floating-alert {
            position: fixed;        /* Float on the screen */
            top: 10%;               /* Position 10% from the top of the screen */
            left: 50%;              /* Center horizontally */
            transform: translateX(-50%); /* Ensure it's perfectly centered */
            width: 50%;             /* Limit the width to 50% of the screen */
            max-width: 600px;       /* Prevent excessive width on large screens */
            z-index: 1050;          /* Ensure it's above other elements */
            text-align: center;     /* Center the text inside the alert */
            padding: 15px;          /* Add padding for spacing */
            border-radius: 8px;     /* Rounded corners for a smooth look */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for better visibility */
            opacity: 1;             /* Ensure it's visible initially */
            transition: opacity 0.5s ease-in-out; /* Smooth fade-out transition */
        }

        .alert-success {
            background-color: #d4edda; /* Light green for success */
            color: #155724; /* Dark green text */
            border: 1px solid #c3e6cb;
            font-size:large;
        }

        .alert-danger {
            background-color: #f8d7da; /* Light red for error */
            color: #721c24; /* Dark red text */
            border: 1px solid #f5c6cb;
            font-size:large;
        }


    </style>

<?php if (isset($successMessage)): ?>
    <div id="successMessage" class="alert alert-success floating-alert">
        <?= $successMessage ?>
    </div>
<?php endif; ?>

<?php if (isset($errorMessage)): ?>
    <div id="errorMessage" class="alert alert-danger floating-alert">
        <?= $errorMessage ?>
    </div>
<?php endif; ?>



    <?php
    include("include/header.php"); // Added by salva TDR | 2.12.2022
    include("include/sidebar.php");
    ?>



    <section id="main-content">
        <section class="wrapper site-min-height">
            <div class="row">
                <div class="col-8 mainbody">
                    <h1>North Park Exports</h1>
                    <?php echo "<p> Debug on/off: ".$prefs["prefCMSDebugOn"]."</p>"; ?>
                </div>
                <div class="col-4">
                    <h3>Last Write Debug</h3>


                    <?php

                    echo "SQL: $sql <br>" ;
                    echo "Name: $name <br>";
echo "Date: $date <br>";
echo "User: $user <br>";
echo "Vet: $vet <br>";
echo "Time OV: $time_ov <br>";
?>
                </div>
            </div>

            <!-- Main Data Area -->
            <div class="row">
                <div class="col-12">
                    <h2>Add Timesheet Record</h2>


                    <form method="POST">
    <div class="row">
        <!-- First Column -->
        <div class="col-md-6">
            <!-- Name -->
            <div class="form-group">
                <label>PO / Ref:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control" readonly>
            </div>

            <!-- Date Picker -->
            <div class="form-group">
                <label>Date (work done)</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control">
            </div>

            <!-- Vet Dropdown -->

            <?php
            if (!isset($vet)) {
               // $vet = isset($productDetails['vet']) ? intval($productDetails['vet']) : 0;
            }
           // echo "Debug Vet Value: ".$vet."<br>";
            ?>

            <div class="form-group">
    <label>Vet/CSO</label>
    <select name="vet" class="form-control">
        <?php
        $vetOptions = getVetOptionsTime($conn);

        if (!$vetOptions) {
            echo "<option value=''>No Vet options available</option>";
        } else {
            foreach ($vetOptions as $option) {
                $selected = (isset($vet) && $option['value'] == $vet) ? 'selected' : '';
                echo "<option value='{$option['value']}' $selected>{$option['label']}</option>";
            }
        }
        ?>
    </select>
</div>

           

            <!-- Courier -->
            <div class="form-group">
                <label>Courier (Net Cost £.p)</label>
                <input type="number" step="0.01" name="courier" class="form-control" value="0.00">
            </div>

            <!-- Notes with TinyMCE -->
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" id='tinymcetextarea' class="form-control"></textarea>
            </div>
        </div>

        <!-- Second Column -->
        <div class="col-md-6">
            <?php
            $fields = [
                'time_ov' => 'Time OV (Minutes)',
                'travel_units' => 'Travel (Units)',
                'time_cso' => 'Time CSO (Minutes)',
                'travel_miles' => 'Travel (Miles)',
                'certs' => 'Certs (Number of)',
                'sha_sa' => 'SHA/SA (Number of)'
            ];
            foreach ($fields as $field => $label): ?>
                <div class="form-group">
                    <label><?= $label ?></label>
                    <input type="number" step="0.01" name="<?= $field ?>" class="form-control" value="0.00">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="row">
        <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>



                </div>
            </div>



        </section>
    </section>

    <?php
    include("include/footer-code.php");
    include("include-tinymce.php");
    ?>

<script>
    // Automatically hide success and error messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');

        [successMessage, errorMessage].forEach(message => {
            if (message) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 500); // Remove from DOM after fade-out
                }, 5000); // 5 seconds
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($formSubmitted) && $formSubmitted === true): ?>
            // Wait for 2 seconds to let the success message be visible
            setTimeout(() => {
                // Close the current window
                window.close();

                // Attempt to focus on the parent window (where the user came from)
                if (window.opener) {
                    window.opener.focus();
                }
            }, 2000); // 2-second delay before closing
        <?php endif; ?>
    });
</script>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
<!-- END recordTimeAddv5 -->
