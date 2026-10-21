<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');
include('setting/main-top-files.php'); // Added by salva TDR | 16.1.2023


$TypeDebug = $prefs["prefCMSDebugOn"]; // Yes or No
$PageType = 'Add'; // Add, Edit, Copy, Delete

$baseURL = $BASE_URL;

// include('wideimage/lib/WideImage.php');

$fileuploadname = "";

if ($TypeDebug == 'Yes' or $userlevel > '20') {
	echo "<script>alert('Open ID = [" . $_GET['id'] . "]');</script>";
	echo "<script>alert('Post name = [" . $_POST['name'] . "]');</script>";
	echo "<script>alert('Post Submit = [" . $_POST['submit'] . "]');</script>";
}

if (!$formnumber = securityCheck($_GET['frm'], 'number')) {
	die('Error in the form');
}

// --- START added by salva TDR | 16.1.2023 ---
$FORM = new RecordAdd($formnumber);
$form = $FORM->getForm();
$table = $FORM->getTable();
$form_fields = $FORM->getFormFields(true);
// echo "<pre>";
// print_r($form);
// print_r($table);
// print_r($form_fields);
// echo "</pre>";
// --- END added by salva TDR | 16.1.2023 ---

$saveError = '';
require_once __DIR__ . '/controllers/recordInsert.php';

if (isset($_POST['submit'])) {
	$updateData = [];

	// Check if `slug` exists
	if (isset($_POST['slug'])) {
		// Check if `slug` has a value
		if (empty($_POST['slug'])) {
			// Generate a value for slug
			if (!empty($_POST['title'])) {
				$slugValue = $_POST['title'];
			} elseif (!empty($_POST['heading'])) {
				$slugValue = $_POST['heading'];
			} elseif (!empty($_POST['name'])) {
				$slugValue = $_POST['name'];
			} else {
				$slugValue = 'No-Value-For-Slug'; // Fallback value in case none of the fields are available
			}

			// Apply sanitization (example: lowercase, remove special characters, replace spaces with hyphens)
			$slugValue = strtolower($slugValue); // Convert to lowercase
			$slugValue = str_replace(['&', '+'], '-', $slugValue);
			$slugValue = preg_replace('/[^a-z0-9\s\-]/', '', $slugValue); // Remove special characters except spaces and hyphens
			$slugValue = str_replace(' ', '-', $slugValue); // Replace spaces with hyphens
			$slugValue = preg_replace('/-+/', '-', $slugValue); // Remove consecutive hyphens
			$slugValue = trim($slugValue, '-'); // Remove hyphens from the start and end

			// Add the sanitized slug value to the $_POST array
			$_POST['slug'] = $slugValue;
		}
	}

	// Check if Internal `Name` exists
	if (isset($_POST['name']) ) {
		// Check if internal name is empty
		if (empty($_POST['name'])) {
			// Generate a value for internal name
			if (!empty($_POST['title'])) {
				$nameValue = $_POST['title'];
			} elseif (!empty($_POST['heading'])) {
				$nameValue = $_POST['heading'];
			} else {
				$nameValue = 'No Name Value For Internal Name'; // Fallback value in case none of the fields are available
			}
			$_POST['name'] = $nameValue;
		}
	}

    try {
        foreach ($_POST as $key => $value) {
            if (!is_scalar($value)) throw new InvalidArgumentException('Invalid form value.');
            $updateData[$key] = trim((string)$value);
        }
        $insertResponse = $FORM->insertTableContent($updateData);
        if ($insertResponse['status'] !== 'success') {
            $saveError = $insertResponse['message'];
        } else {
            // A logging failure must not hide an already successful insert.
            try {
                saveLog($_SESSION['useremail'], 'Insert New Record into Form ' . $formnumber,
                    $insertResponse['query'], $table['name'], 'SUCCESS',
                    $insertResponse['message'], $insertResponse['recordID']);
            } catch (Throwable $logError) {
                error_log('Record saved, but audit logging failed: ' . $logError->getMessage());
            }
            header('Location: recordEditv' . $prefs['prefCMSVer'] . '.php?' . http_build_query([
                'frm' => $formnumber, 'id' => $insertResponse['recordID'],
                'insert' => 'success', 'msg' => $insertResponse['message']
            ]), true, 303);
            exit;
        }
    } catch (Throwable $exception) {
        error_log('V4 record creation failed: ' . $exception->getMessage());
        $saveError = cmsRecordSaveError($exception);
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<?php
	echo "<title>" . $form['title'] . " | Add Record</title>";
	include("include/header-code.php");
	?>
	<style>
		.gallery_product img {
			width: 75px;
			height: 75px;
			margin-bottom: 20px;
		}

		.current img {
			border: #CE559A 2px solid;
		}

		.gallery_product {
			position: relative;
			display: inline-block;
		}

		.select_button a {
			padding: 10px;
			background-color: #9C4755;
			color: #c9d4f6;
		}

		.select_button {
			margin-bottom: 25px;
		}

		.form-group.Gallery {
			display: none;
		}
	</style>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
	<!-- Fixed navbar -->
	<?php
	include("include/header.php");
	include("include/sidebar.php");
	echo "<div id='cl-wrapper' class='fixed-menu'>";
	?>

	<section id="main-content">
		<section class="wrapper site-min-height">
			<!-- page start-->
			<div class="row">
				<?php
					echo "<h3><span style='fontweight:200px;'>Add New </span>" . $form["title"] . "</h3>";
				?>
			</div>
			<div class="content">
				<?php
				$infomark = "<i class='fas fa-info-circle' style='color:#28998B; padding-left:10px;'></i>";
				?>

				<?php if ($saveError !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($saveError, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <!-- FORM HERE -->
				<form role="form" class="form-horizontal" method="post" enctype="multipart/form-data">
					<?php
					echo "<p><span style='color:red;'>*</span> Read Only</p><hr>" ;
					echo "<input type='hidden' name='formnumber' value='" . $formnumber . "' />";

					foreach ($form_fields as $rowformfield) {
						// echo "<pre>";
						// print_r($rowformfield);
						// echo "</pre>";
						// set Required 
						if ($rowformfield["required"] == 'Yes') {
							$required = 'required';
						} else {
							$required = '';
						}

						//Get the Field Type	
						$rowfield = $FORM->getFieldType($rowformfield["field"]);

						// SET WIDTH STYLE
						$itemclass = $rowformfield["class"];
						// We change the all if statements to switch case
						switch ($itemclass) {
							case 'small':
								$itemclass = 'width:25%';
								break;
							case 'm-25':
								$itemclass = 'width:25%';
								break;
							case 'medium':
								$itemclass = 'width:50%';
								break;
							case 'm-50':
								$itemclass = 'width:50%';
								break;
							default:
								$itemclass = 'width:50%';
								break;
						}

						$submittedValue = isset($_POST[$rowformfield['name']]) && is_scalar($_POST[$rowformfield['name']])
                            ? (string)$_POST[$rowformfield['name']] : '';
                        // Text fields escape themselves; other legacy renderers expect escaped content.
                        $submittedValue = (int)$rowfield['id'] === 1 ? addslashes($submittedValue)
                            : htmlspecialchars($submittedValue, ENT_QUOTES, 'UTF-8');
                        $FORMFIELD = new FormField($rowformfield, $rowfield, $submittedValue, $TypeDebug, $PageType, $infomark);
						echo $FORMFIELD->render(0,$formnumber);

						if ($rowfield["id"] == 118) // New Slug Creation Select Statement From Table
						{
							//Get the Select Data from D/B	
							// - $recordnumber doesn't exist because this is the add page - salva TDR | 17.1.2023 
							$selectfieldoptions =  $rowformfield["sourcesql"];
							$selectfieldoptions = str_replace("{{mainID}}", $recordnumber, $selectfieldoptions);
							$queryfieldoptions = mysqli_query($conn, $selectfieldoptions);
							echo "<div class='form-group'>";
							echo "<label for='exampleInputEmail1'>" . $rowformfield["label"] . "";
							if ($rowformfield["tooltip"]) {
								echo "<a href='#' data-bs-toggle='tooltip' title='" . $rowformfield["tooltip"] . "'>" . $infomark . "</a>";
								if ($TypeDebug == 'Yes') {
									echo "<span style='color:#dddddd;'>&nbsp;&nbsp;(" . $rowfield["id"] . ")</span>";
								}
							}
							echo "</label>";
							echo "<select name='" . $rowformfield["name"] . "'  class='form-control " . $rowformfield["class"] . "'>";
							echo "<option value='0' selected >Select Option</option>";
							while ($rowfieldoptions = mysqli_fetch_assoc($queryfieldoptions)) {
								// Selected ???	
								if ($formnumber == '23') {
									echo "<option value='" . $rowfieldoptions["name"] . "'>" . $rowfieldoptions["name"] . "</option>";
								} else {
									echo "<option value='" . $rowfieldoptions["id"] . "'>" . $rowfieldoptions["name"] . " (" . $rowfieldoptions["id"] . ")</option>";
								}
							}
							echo "</select>";
							echo "</div>";
						}
					}
					?>
					<div class='form-group'>
						<button data-bs-toggle="popover" data-bs-content="Save" data-bs-placement="top" data-bs-trigger="hover" type="submit" name="submit" class="btn btn-success" id="sbt"><i class="fa fa-check"></i>Save New Record</button>
					</div>
				</form>

				<!-- Form Ends Here -->

				<?php
				if ($form["text"]) {
					echo "<h3>Notes:</h3>";
					echo $form["text"];
				}
				?>



				<div class="col-sm-2 col-md-2"></div>
			</div>
		</section>
	</section>
	<?php
	// include("include/footer.php"); // - footer.php doesn't exist inside wccms/include folder - Salva TDR | 17.1.2023
	include("include/footer-code.php");
	echo "</div>";
	// include('autoload.php'); // Removed by salva TDR | 17.1.2023
	?>

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>

</html>

<!-- END recordAddv4 -->