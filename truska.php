<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Hello Truska Text</title>
	
<link rel="apple-touch-icon" sizes="180x180" href="filestore/images/icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="filestore/images/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/filestore/images/icons/favicon-16x16.png">
<link rel="manifest" href="filestore/images/icons/site.webmanifest">
</head>

<body>
	<H1>Hello</H1>
	
	<?php
	
	$pageURL = $_SERVER['REQUEST_URI'] ;
	$pageURL = rtrim($pageURL,'/') ;
	$actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$pagenumber = basename( $pageURL );

	
	echo "<h3>Server: " . $_SERVER['SERVER_NAME'] . "</h3>" ;
	echo "<h3>Server IP: " . $_SERVER['SERVER_ADDR'] . "</h3>" ;

	echo "<h3>Agent: " . $_SERVER['HTTP_USER_AGENT'] . "</h3>" ;
	echo "<h3>Agent IP: " . $_SERVER['REMOTE_ADDR'] . "</h3>" ;
	echo "<h3>Time: " . $_SERVER['REQUEST_TIME'] . "</h3>" ;
	echo "<h3>Page URL: " . $pageURL . "</h3>" ;
	
	echo "<h3>Full Link: ".$actual_link."</h3>" ;
	echo "<h3>URL Level No.: ".$pagenumber."</h3>" ;
	
	echo "<hr>";
	
$debug = 'Yes';
//Get Page URL

/* 2nd level urls and variables */

//echo "Page Number = " . $pagenumber . "<br>" ;
$segs = explode('/', $pageURL);

if ($debug == 'Yes' ) {
	echo "<p>Debug = Yes</p>" ;
	echo "segs[0] = " .$segs[0] . "<br>"; 
	echo "segs[1] = " .$segs[1] . "<br>"; 
	echo "segs[2] = " .$segs[2] . "<br>"; 
	echo "segs[3] = " .$segs[3] . "<br>"; 
	echo "segs[4] = " .$segs[4] . "<br>"; 
	echo "segs[5] = " .$segs[5] . "<br>";  
	echo "new url = " .$segs[0] . "/" . $segs[1] . "<br>" ;
	echo "Page URL = " . $pageURL . "<br>" ;
}
?>
	<h5>END...</h5>
</body>
</html>