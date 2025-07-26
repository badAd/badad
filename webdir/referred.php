<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Validate the serial number: a-zA-Z0-9
if ((isset($_GET['l'])) && (preg_match ('/[a-zA-Z0-9]$/i', $_GET['l']))) {
  $rSlug = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Check the slug against the database
$q = "SELECT userid FROM referrallinks WHERE binary reflink='$rSlug'";
$r = mysqli_query ($dbc, $q);
// Get the number of rows returned
$rows = mysqli_num_rows($r);
if ($rows == 1) { // Link exists
	$row = mysqli_fetch_array($r, MYSQLI_NUM);
	$refUserID = "$row[0]";
	$_SESSION['refUserID'] = $refUserID;
	$_SESSION['rSlug'] = $rSlug;
} else { // Link doesn't exist
	header("Location: index.php");
	exit(); // Quit the script
}

// Start the new ad
header("Location: new_ad.php");
exit(); // Quit the script

?>
