<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// We need our freekey
if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9]$/i', $_GET['p']))) {
	$p_key = $_GET['p'];
	$p = mysqli_real_escape_string ($dbc, $p_key);
	$q = "SELECT id FROM freekeys WHERE BINARY reg_key='$p' AND user_id IS NULL AND purchase_useable='live' AND date_reg_expires>NOW()";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) == 1) {
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		// Store the key ID in a session
		$_SESSION['reg_key_id'] = $row[0];

	} else {
		header("Location: index.php");
		exit(); // Quit the script

	}

} else {
	header("Location: index.php");
	exit(); // Quit the script

}

// Login dropdown action
$login_form_action = "index.php";

// Logged-in redirect

	// Redirect Logged-in users
	if (isset($_SESSION['user_id'])) {
		header("Location: index.php");
		exit(); // Quit the script
	}

// Include the header file
$page_title = "Register :: $siteTitle";
include ('./includes/header.html');


// Freekey?
if (isset($_SESSION['reg_key_id'])) {
	echo '<p class="note_blue">Free key mode</p>';
}

// Insert the page content
$rformaction = 'register.php?p='.$p_key; // This must be set for the include to work
include ('includes/register.inc.php');

// Expire our key
if ((isset($_SESSION['just_registered_freekey'])) && ($_SESSION['just_registered_freekey'] == true)) {
	$userid = $_SESSION['user_id'];
	$key_id = $_SESSION['reg_key_id'];
	// Expire our key
	$q = "UPDATE freekeys SET user_id='$userid', date_reg_expires=NOW() WHERE id='$key_id'";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_affected_rows($dbc) != 1) {
		sql_error($q, 'dbc', "sqle_120");
		header("Location: index.php");
		exit(); // Quit the script
	}
	// Get the purchase key
	$q = "SELECT purchase_key FROM freekeys WHERE id='$key_id'";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) == 1) {
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$purchase_key = $row[0];

		// Redirect to the purchase page
		header("Location: https://badad.one/new_ad.php?p=$purchase_key");
		exit(); // Quit the script

	} else {
		header("Location: index.php");
		exit(); // Quit the script

	}

	// Done with our reg_key
	unset($_SESSION['reg_key_id']);
}

// Include the HTML footer
include ('./includes/footer.html');
?>
