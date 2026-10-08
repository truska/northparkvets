<!-- START import-data-sheetsv4 -->
<!DOCTYPE html>
<head>
    <?php
        // Start output buffering
        ob_start();

        // Turn off error reporting
        error_reporting(0);
        // Uncomment to enable error reporting for debugging
        // error_reporting(E_ALL);

        include('setting/main-top-files.php'); // Includes session, database connections, and functions
        include("include/header-code.php");

        // Ensure `importfiles` directory exists
        $uploadDir = "importfiles/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        global $user; // Ensure $user from `header-code.php` is accessible
        $userID = $user["id"]; // Access user ID once
    ?>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php
        include("include/header.php");
        include("include/sidebar.php");
    ?>

    <section id="main-content">
        <section class="wrapper site-min-height">

            <!-- Page Title -->
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h1 class="text-primary">North Park Exports - Import Data Sheets</h1>
                </div>
            </div>

            <!-- File Upload Form -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Upload File and Set Parameters</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            // Handle Form Submission
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $importname = $_POST['importname'] ?? 'Default Import';
                                $importtype = $_POST['importtype'] ?? 'Default Type';
                                /*
                                    if (empty($importtype) || !in_array($importtype, ['HF', 'NT'])) {
                                        echo "<div class='alert alert-danger'>Error (Form handling) : Invalid or missing import type.</div>";
                                        exit;
                                    }
                                    else{
                                        echo "<div class='alert alert-danger'>CHECK (Form handling) : importtype= ".$importtype."</div>";
                                        exit;
                                    }
                                */
                                $fileName = $_FILES['sourcefile']['name'] ?? null;

                                // Validate file upload
                                if ($fileName) {
                                    $targetFilePath = $uploadDir . basename($fileName);
                                    if (move_uploaded_file($_FILES['sourcefile']['tmp_name'], $targetFilePath)) {
                                        // Create initial log entry
                                        $logID = logProcessStart($importname, $importtype, $fileName, $user["id"]);

                                        if ($logID) {
                                            // Redirect to import routine
                                            header("Location: importMasterv4.php?frm=1&id=$logID");
                                            exit;
                                        } else {
                                            echo "<div class='alert alert-danger'>Error: Failed to create log entry.</div>";
                                        }
                                    } else {
                                        echo "<div class='alert alert-danger'>Error: Failed to upload the file.</div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-danger'>Error: Please upload a valid file.</div>";
                                }
                            }

                           echo "<p class='float-end'>Import run by: ".$user["firstname"]." ".$user["surname"]."</p>";
                            ?>

                            <!-- Upload Form -->
                            <form action="import-data-sheetsv4.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="form-group">
                                    <label for="importname" class="form-label">Import Name:</label>
                                    <input type="text" id="importname" name="importname" class="form-control" placeholder="e.g., Product Import" required>
                                    <div class="invalid-feedback">Please provide an import name.</div>
                                </div>
                                <div class="form-group">
                                    <label for="importtype" class="form-label">Import Type:</label>
                                    <select id="importtype" name="importtype" class="form-control" required>
                                        <option value="" disabled selected>Select Import Type</option>
                                        <option value="HF">HF (Heathfield)</option>
                                        <option value="NT">NT (North Tawton)</option>
                                        <option value="NPV">North Park Manual CSV</option>
                                        <option value="TANK">Tanker Import [under development]</option>
                                    </select>
                                    <div class="invalid-feedback">Please select an import type.</div>
                                </div>
                                <div class="form-group">
                                    <label for="sourcefile" class="form-label">Source File:</label>
                                    <input type="file" id="sourcefile" name="sourcefile" class="form-control" accept=".csv" required>
                                    <div class="invalid-feedback">Please upload a valid CSV file.</div>
                                </div>
                                <div class="form-group text-center mt-4">
                                    <button type="submit" class="btn btn-primary">Upload and Start Import</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </section>

    <?php include("include/footer-code.php"); ?>
    <?php ob_end_flush(); // End output buffering ?>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
<!-- END import-data-sheetsv4 -->
