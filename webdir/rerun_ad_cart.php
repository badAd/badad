<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Set this as a rerun
if ((!isset($_SESSION['rerun_ad'])) || ($_SESSION['rerun_ad'] != true)) {
	header("Location: index.php");
	exit(); // Quit the script
} else {
$rerunAd = true;
}

// Include the header
$page_title = "Rerun Checkout :: $siteTitle";
include ('./includes/header.html');

// In-page title
echo "<h3>Rerun Checkout</h3>";

// Declare the rerun valid
$_SESSION['validAd'] = true;

// Set the New Ad variables
require_once ('./includes/ad_values_set.inc.php');

// Create the ad content entry
require_once ('./includes/ad_content_set.inc.php');

// Insert the page content
include ('./inserts/rerun_ad_cart.ins.php');
include ('./inserts/new_ad_cart.ins.php');

 // Include the HTML footer
include ('./includes/footer.html');
?>
