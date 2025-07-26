<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Include the header file
$page_title = 'Test Mail';
include ('./includes/header.html');



// Redirect if this is not a referral link
if (!isset($_GET['l'])) {
	echo "No link set!";
} else { // There is indeed a referal link
	$rSlug = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
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
		echo "No link in database!";
	}
}

// Referral values?
if (isset($_SESSION['user_id'])) { $userID = $_SESSION['user_id']; }
if (isset($_SESSION['refUserID'])) { $refUserID = $_SESSION['refUserID']; }
if (isset($_SESSION['rSlug'])) { $rSlug = $_SESSION['rSlug']; }
// REFERRED

// Test Ref grant
include ('./includes/referral_cred_grant.inc.php');

// Include the HTML footer
include ('./includes/footer.html');
?>
