<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Login count page
if ((isset($_SESSION['login_attempt'])) && ($_SESSION['login_attempt'] > 3)) {
	$page_title = "Log In Failure";
	include ('./includes/header.html');
	echo '<h3 class="note_red">Log In Failure</h3><p class="note_red">You tried to login too many times. Try again later.</p>';
	include ('./includes/footer.html');
	exit();
}

require_once ('./includes/form_functions.inc.php');

// Include the header file
$page_title = "Reset Password :: $siteTitle";
include ('./includes/header.html');

// Check temp password in URL
if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['p']))) {
    $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['p']);
} elseif ((isset($_GET['r'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['r']))) {
    $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['r']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Login attempts counter (no _POST required)
if (!isset($_SESSION['login_attempt'])) {
  $_SESSION['login_attempt'] = 1;
} else {
  $_SESSION['login_attempt'] = $_SESSION['login_attempt'] + 1;
}

// Get the necessary info
$timeNow = date("Y-m-d H:i:s");

$q = "SELECT date_dead, useable, userid FROM confirmpartnerchange WHERE binary temppass='$tempURLpass' AND useable='live'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$datedead = $row[0];
$usable = $row[1];
$userid = $row[2];

if (($timeNow < $datedead) && ($usable == 'live')) {

	// Kill any loginonce and logincode keys
  include_once ('./includes/clear_keys.inc.php');

  // Info recovery form here
  if (isset($_GET['p'])) {
    echo '<h3 class="note_red">Urgent change needed!</h3><p class="note_red">You reached this page after clicking on the email link indicating that you <b>did not</b> make the recent changes to your account.</p><p>If this is true, please change your password, then confirm your account information.</p>';
  }
  $rformaction = 'partner_info_repair.php?r='.$tempURLpass; // This must be set for the include to work
  include ('includes/partner_info_repair_form.inc.php');

} else {
  echo "Sorry, that link has expired.";
}


// Include the HTML footer
include ('./includes/footer.html');
?>
