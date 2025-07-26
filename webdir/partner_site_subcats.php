<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Require the database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Valid the Site ID
if ((isset($_GET['s'])) && (filter_var($_GET['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
	$siteID = preg_replace("/[^A-Za-z0-9]/","", $_GET['s']);
} else {
	header("Location: partner.php");
	exit(); // Quit the script
}

// Get the subcategory name
$q = "SELECT domain, auto_add_new_cat FROM partnersites WHERE id='$siteID' AND user_id='$userid'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows == 0) { // If there are no sites
	header("Location: partner.php");
	exit(); // Quit the script
} else {
$sitedomain = $row[0];
$auto_add_new_cat = $row[1];

// Include the header file
$page_title = "$sitedomain - Partner Site :: $siteTitle";
include ('./includes/header.html');


// Insert the page content
include ('inserts/partner_site_subcats.ins.php');
}

// Include the HTML footer
include ('./includes/footer.html');
?>
