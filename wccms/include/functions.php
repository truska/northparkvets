<?php
require_once __DIR__ . "/site-url.php";
/**
 * Get icon code
 * 
 * This function is used to get the icon code
 * 
 * @param string $name1 The name of the icon
 * @return string The icon code
 */
function getIcon($name1)
{
	// include("dbcon.php");
	$sqlgeticons = "SELECT * FROM `icons` 
	WHERE `name` = '$name1'";
	$icon = mysqli_fetch_array(DB::query($sqlgeticons), MYSQLI_ASSOC);

	$code = $icon["code"];

	return  $code;
}

/**
 * Get icon title
 * 
 * This function is used to get the icon title
 * 
 * @param string $name2 The name of the icon
 * @return string The icon title
 */
function getIconTitle($name2)
{
	// include("dbcon.php");
	$sqlgettitle = "SELECT * FROM `icons` 
	WHERE `name` = '$name2'";
	$icon = mysqli_fetch_array(DB::query($sqlgettitle), MYSQLI_ASSOC);

	$title = $icon["title"];

	return  $title;
}

/**
 * Get icon background colour
 * 
 * This function is used to get the icon background colour
 * 
 * @param string $name3 The name of the icon
 * @return string The icon background colour
 */
function getIconBgColour($name3)
{
	// include("dbcon.php");
	$sqlgetcolor = "SELECT * FROM `icons` 
	WHERE `name` = '$name3'";
	$icon = mysqli_fetch_array(DB::query($sqlgetcolor), MYSQLI_ASSOC);

	$color = $icon["colour"];

	return  $color;
}

/**
 * Get icon text colour
 * 
 * This function is used to get the icon text colour
 * 
 * @param string $name4 The name of the icon
 * @return string The icon text colour
 */
function getIconTextColour($name4)
{
	// include("dbcon.php");
	$sqlgettextcolor = "SELECT * FROM `icons` 
	WHERE `name` = '$name4'";
	$icon = mysqli_fetch_array(DB::query($sqlgettextcolor), MYSQLI_ASSOC);

	$textcolor = $icon["textcolour"];

	return  $textcolor;
}

//pref function 

/**
 * Load preferences
 * 
 * This function is used to load the preferences from the database
 * 
 * @return array The preferences
 */
function loadPrefs()
{
	$selectprefs = "SELECT `name`,`value` FROM `preferences` 
	ORDER BY `prefCat`";
	$icon = mysqli_fetch_all(DB::query($selectprefs), MYSQLI_ASSOC);

	foreach ($icon as $rowprefs) {
		$prefs[$rowprefs["name"]] = $rowprefs["value"];
	}

	return $prefs;
}

/**
 * Load Shop preferences
 * 
 * This function is used to load the shop preferences from the database
 * 
 * @return array The shop preferences
 */
function loadShopPrefs()
{
	$selectshopprefs = "SELECT `name`, `value` FROM `preferences_shop` 
	ORDER BY `prefCat` ";
	$queryshopprefs = mysqli_fetch_all(DB::query($selectshopprefs), MYSQLI_ASSOC);

	foreach ($queryshopprefs as $rowshopprefs) {
		$prefshop[$rowshopprefs["name"]] = $rowshopprefs["value"];
	}

	return $prefshop;
}

function getCompanyName($prefs)
{
	return $prefs['prefCompanyName'];
}
function getSiteName($prefs)
{
	return $prefs['prefSiteName'];
}
function getEmail($prefs)
{
	return $prefs['prefEmail'];
}
function getTel1($prefs)
{
	return $prefs['prefTel1'];
}
function getTel1Int($prefs)
{
	return $prefs['prefTel1Int'];
}
function getTel2($prefs)
{
	return $prefs['prefTel2'];
}
function getTel2Int($prefs)
{
	return $prefs['prefTel2Int'];
}
function getFax($prefs)
{
	return $prefs['prefFax'];
}
function getLogo($prefs)
{
	return  $prefs['prefLogo'];
}
function getTagline($prefs)
{
	return $prefs['prefTagline'];
}

// End loadprefs specifics

/**
 * Security check function
 * 
 * This function is used to check the security of the parameters passed to the function
 * 
 * @param string $param The parameter to be checked
 * @param string $type The type of the parameter
 * @return string|boolean The parameter if it is safe, false otherwise
 */
function securityCheck($param, $type = null)
{
	switch ($type) {
		case 'number':
			if (is_numeric($param)) {
				return trim(mysqli_real_escape_string(DB::connection(), $param));
			} else {
				return false;
			}
			break;
		default:
			return trim(mysqli_real_escape_string(DB::connection(), $param));
			break;
	}
}


/**
 * Function to sanitize a filename
 * 
 * This function is used to sanitize a filename
 * 
 * @param string $filename The filename to be sanitized
 * @return string The sanitized filename
 */
function sanitizeName($filename)
{
	$name = strtolower($filename);
	$name = preg_replace("/[^a-zA-Z0-9_\.]/", "", $name); // remove special characters
	$name = preg_replace("/[\s_]/", "-", $name); // replace spaces and underscores with dashes
	$name = preg_replace("/-+/", "-", $name); // remove multiple dashes
	$name = preg_replace("/^-/", "", $name); // remove leading dash
	$name = preg_replace("/-$/", "", $name); // remove trailing dash
	return $name;
}

/**
 * Function to upload a file
 * 
 * This function is used to upload a file
 * 
 * @param string $file The file to be uploaded
 * @param string $folder The folder where the file will be uploaded
 * @param string $name The name of the file
 * @return array The response of the upload
 */
function uploadFile($file, $folder, $name)
{
	$response = [];
	$destination_path = $folder . "/" . $name;

	if (move_uploaded_file($file, $destination_path)) {
		$response['status'] = 'success';
		$response['message'] = "File uploaded successfully";
	} else {
		$errors['status'] = 'error';
		$errors['message'] = "Error uploading file";
		$errors['filename'] = $name;
		$errors['folder'] = $folder;
	}

	return $response;
}

function replaceURL($url, $frm, $id, $show = null)
{
	$url = str_replace("[frm]", $frm, $url);
	$url = str_replace("[id]", $id, $url);
	if ($show != null) {
		$url = str_replace("[show]", $show, $url);
	}

	return $url;
}

function replaceURLv2($url, $params)
{
	foreach ($params as $key => $value) {
		$url = str_replace("[$key]", $value, $url);
	}

	return $url;
}