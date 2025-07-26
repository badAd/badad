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
$page_title = "Dev App Restored :: $siteTitle";
include ('./includes/header.html');

// Check temp password in URL
if ((isset($_GET['c'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['c']))) {
    $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['c']);
    // Login attempts counter (no _POST required)
    if (!isset($_SESSION['login_attempt'])) {
      $_SESSION['login_attempt'] = 1;
    } else {
      $_SESSION['login_attempt'] = $_SESSION['login_attempt'] + 1;
    }
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Get the necessary info
$timeNow = date("Y-m-d H:i:s");

$q = "SELECT date_dead, useable, userid, appid FROM confirmdeldevapp WHERE binary confirmkey='$tempURLpass' AND useable='live'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$datedead = $row[0];
$usable = $row[1];
$userid = $row[2];
$appid = $row[3];

if (($timeNow < $datedead) && ($usable == 'live')) {

  // Kill the keys
  $qp = "UPDATE confirmdeldevapp SET useable='dead' WHERE userid='$userid' AND binary confirmkey='$tempURLpass'";
  $rp = mysqli_query ($srv_dbc, $qp);
  $qt = "UPDATE confirmdevappchange SET useable='dead' WHERE userid='$userid' AND binary confirmkey='$tempURLpass'";
  $rt = mysqli_query ($srv_dbc, $qt);
  if (($rp) && ($rt)) {
		// Kill any loginonce and logincode keys
	  include_once ('./includes/clear_keys.inc.php');
		// Partnersites secure
    $q = "UPDATE devkeys SET status='test' WHERE user_id='$userid' AND id='$appid'";
    if ($r == mysqli_query ($srv_dbc, $q)) {
      echo '<h3 class="note_green">Delete cancelled!</h3>';
      echo '<p class="note_green">Dev App restored, but not live.</p>';
      if (isset($_SESSION['user_id'])) {
				set_switch("&larr; Back to the Developer Center", "Go to the Developer Center", "partner_dev.php", "partner_dev", $userid, "set_gray");
				echo "<br />";
      }
    } else {
      sql_error($q, 'srv_dbc', "sqle_111");
    }
  } else {
    sql_error("$qp &&& $qt", 'srv_dbc', "sqle_112");
  }
} else {
  echo "Sorry, that link has expired.";
}

// Include the HTML footer
include ('./includes/footer.html');
?>
