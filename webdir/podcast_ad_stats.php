<?php

// Configs
require ('includes/config.inc.php');
require (MYSQL);

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// A settings page requires form functions
require_once ('includes/form_functions.inc.php');

// Require the database connection


// Check for form submission and set the $adID variable
if ((isset($_POST['a'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['a']))) {$IP = get_ip_addr(); script_kiddy('sk_25', '_POST a', $_POST['a'], $IP);}
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['a']);
} else {
	header("Location: order_history.php");
	exit(); // Quit the script
}

// Check to see if user's email is verified
$qe = "SELECT email, confirmed_email FROM users WHERE id='$userid'";
$re = mysqli_query ($dbc, $qe);
$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
$email = "$rowe[0]";
$email_confirmed = "$rowe[1]";
if ($email != $email_confirmed) {
	include ('includes/confirm_email.inc.php');
	// Get back to order history
  header("Location: order_history.php");
  exit(); // Quit the script
}

// Check if the ad is a podcast ad
$q = "SELECT ad_id FROM pod_ads WHERE ad_id='$adID'";
$r = mysqli_query ($dbc, $q);
if (mysqli_num_rows($r) == 0) { // Not activated
	header("Location: order_history.php");
  exit(); // Quit the script
}

// Include the header file
$page_title = "Podcast Ad Stats :: $siteTitle";
include ('includes/header.html');

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Order History\" href=\"order_history.php\">Order History</a></p>";

// Include the chart
include ('inserts/podcast_ad_stats_chart.ins.php');

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Order History\" href=\"order_history.php\">Order History</a></p>";

// Include the HTML footer
include ('includes/footer.html');
?>
