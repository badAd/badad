<?php

//In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Configs
require ('./includes/config.inc.php');
require (MYSQL);
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('./includes/config_agg.inc.php');
require_once (MYSQL_AGG);

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// _POST the site ID
if ((isset($_POST['s'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['s']))) {$IP = get_ip_addr(); script_kiddy('sk_33', '_POST s', $_POST['s'], $IP);}
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['s'])) && (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $siteID = preg_replace("/[^A-Za-z0-9]/","", $_POST['s']);
} else {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Include the header file
$page_title = "Partner Site Stats :: $siteTitle";
include ('./includes/header.html');

// Check if partner account has been activated
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows == 1) { // partner account exists in database
	$activation = $row[0];
	if ($activation != "Confirmed") { // Not activated
		header("Location: partner.php");
    exit(); // Quit the script
	}
} else { // No partner application entry
		// Check to see if user's email is verified
		$qe = "SELECT email, email_confirmed FROM users WHERE id='$userid'";
		$re = mysqli_query ($dbc, $qe);
		$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
		$email = "$rowe[0]";
		$email_confirmed = "$rowe[1]";
		if ($email != $email_confirmed) {
			header("Location: partner.php");
	    exit(); // Quit the script
	} else { // email verified
		header("Location: partner.php");
    exit(); // Quit the script
	}
} // activated check complete, user is a fully-fledged partner

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></p>";

// Include the chart
include ('./inserts/partner_site_stats_chart.ins.php');

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></p>";

// Include the HTML footer
include ('./includes/footer.html');
?>
