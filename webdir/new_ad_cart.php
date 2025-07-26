<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Users
if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

if ((!isset($_SESSION['validAd'])) || ($_SESSION['validAd'] != true) || (isset($_SESSION['edit_back'])) || (!isset($_POST['ad_ready'])) || ($_POST['ad_ready'] != 'ready')) {
	header("Location: new_ad.php");
	exit(); // Quit the script
}

// No new ads for bad emails
if (isset($_SESSION['no_status'])) {
	header("Location: account_info.php");
	exit(); // Quit the script
}

// Do we have a freekey?
if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9]$/i', $_GET['p']))) {
	$p_key = $_GET['p'];
	$p = mysqli_real_escape_string ($dbc, $p_key);
	$q = "SELECT id FROM freekeys WHERE BINARY purchase_key='$p' AND user_id='$userid' AND purchase_useable='live'";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) == 1) {
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		// Store the key ID in a session
		$_SESSION['purchase_key_id'] = $row[0];
	}
}

// Login dropdown action
$login_form_action = "new_ad_cart.php";

// Set the New Ad variables
require_once ('./includes/ad_values_set.inc.php');

// Insert the ad form check
include ('./inserts/new_ad_cart.check.ins.php');

// Dup checks
include ('./inserts/new_ad_dup.check.ins.php');
// Dup Errors?
if ((isset($_SESSION['validAd'])) && ($_SESSION['validAd'] == false)) { // If reg_errors exist
  header("Location: new_ad.php?c=$cat");
  exit(); // Quit the script
}

// Create the ad content entry
require_once ('./includes/ad_content_set.inc.php');

// Include the header
$page_title = "Checkout :: $siteTitle";
include ('./includes/header.html');

// Freekey?
if (isset($_SESSION['purchase_key_id'])) {
	echo '<p class="note_blue">Free key mode, $0/wk</p>';
}

// In-page title
echo "<h3>Checkout</h3>";

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
include ('./inserts/new_ad_cart.ins.php');

 // Include the HTML footer
include ('./includes/footer.html');
?>
