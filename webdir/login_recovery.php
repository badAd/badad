<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// A form page requires form functions
require_once ('./includes/form_functions.inc.php');

// We need database connection
require (MYSQL);

// Login count page
if ((isset($_SESSION['login_attempt'])) && ($_SESSION['login_attempt'] > 3)) {
	$page_title = "Log In Failure";
	include ('./includes/header.html');
	echo '<h3 class="note_red">Log In Failure</h3><p class="note_red">You tried to login too many times. Try again later.</p>';
	include ('./includes/footer.html');
	exit();
}

// Login attempts counter
require_once ('./includes/login_count.inc.php');

// Include the header file
$page_title = "Reset Password | $siteTitle";
include ('./includes/header.html');

// Check temp password in URL
if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['p']))) {
  // Login attempts counter (no _POST required)
  if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = 1;
  } else {
    $_SESSION['login_attempt'] = $_SESSION['login_attempt'] + 1;
  }
  // Set the temp pass key variable
  $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['p']);

} else {
	header("Location: index.php");
  exit(); // Quit the script
}

/* DEPRECIATED: this was the old format of the same validation above, remove if the above validation works
// Check temp password in URL
if (!isset($_GET['p'])) {
  header("Location: index.php");
  exit(); // Quit the script
} else {
  // Login attempts counter (no _POST required)
  if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = 1;
  } else {
    $_SESSION['login_attempt'] = $_SESSION['login_attempt'] + 1;
  }
  // Set the temp pass key variable
  $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['p']);
}

*/

// Redirect Logged-in users
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Get the necessary info
$timeNow = date("Y-m-d H:i:s");

$q = "SELECT date_dead, useable, userid FROM temppasswords WHERE binary temppass='$tempURLpass'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$datedead = $row[0];
$usable = $row[1];
$userid = $row[2];

if (($timeNow < $datedead) && ($usable == 'live')) {

	// Kill any loginonce and logincode keys
  include_once ('./includes/clear_keys.inc.php');

  // Login recovery form here
  $rformaction = 'login_recovery.php?p='.$tempURLpass; // This must be set for the include to work
  include ('includes/login_recovery_form.inc.php');

} else {
  echo "Sorry, that link has expired.";
}


// Include the HTML footer
include ('./includes/footer.html');
?>
