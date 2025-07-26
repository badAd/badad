<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// Check temp password in URL
if ((isset($_GET['c'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['c']))) {
  $URLconfirmkey = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['c']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

// If the user isn't logged in, redirect them
if (!isset($_SESSION['user_id'])) {
  if (!isset($_SESSION['user_id'])) {
    // Include the header file
    $page_title = "Password Required :: $siteTitle";
    include ('includes/header.html');
    echo '<p class="note_red">You must be logged-in to access this page.</p>';
    $lformaction = "partner_del_account_confirmed.php?c=$URLconfirmkey"; // This must be set for login_form.inc.php to work
    require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
    // Include the HTML footer
    include ('includes/footer.html');
    exit();
  }
}
$userid = $_SESSION['user_id'];

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Get the temp URL password time & date and other info
$timeNow = date("Y-m-d H:i:s");

$q = "SELECT appid, useable, date_dead FROM confirmdeldevapp WHERE userid='$userid' AND useable='live' AND binary confirmkey='$URLconfirmkey'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$dev_app_id = $row[0];
$usable = $row[1];
$datedead = $row[2];

// Check if the temp URL password even exists
$rows = mysqli_num_rows($r);
if ($rows == 0) {
  // Include the header file
  $page_title = "Expired :: $siteTitle";
  include ('includes/header.html');
  echo "Sorry, that link has expired.";
  // Include the HTML footer
  include ('includes/footer.html');
  exit();
}

if (($timeNow < $datedead) && ($usable == 'live')) {
  // Disable the temporary link
	$qp = "UPDATE confirmdeldevapp SET useable='dead' WHERE userid='$userid' AND binary confirmkey='$URLconfirmkey'";
  $rp = mysqli_query ($srv_dbc, $qp);
  $qt = "UPDATE confirmdevappchange SET useable='dead' WHERE userid='$userid' AND binary confirmkey='$URLconfirmkey'";
  $rt = mysqli_query ($srv_dbc, $qt);
  if (($rp) && ($rt)) { // If it ran OK
    // Get the name of the site
    $q = "SELECT name, domain FROM devkeys WHERE id='$dev_app_id'";
    $r = mysqli_query ($srv_dbc, $q);
    $row = mysqli_fetch_array ($r, MYSQLI_NUM);
    $dev_app_name = "$row[0]";
    $dev_app_domain = "$row[1]";

    // Delete the site
    $qd = "INSERT INTO deleteddevaps (appid, userid) VALUES ('$dev_app_id', '$userid')";
    $rd = mysqli_query ($srv_dbc, $qd);
    if (mysqli_affected_rows($srv_dbc) != 1) {
      sql_error($qd, 'srv_dbc', "sqle_108");
    }
    $qs = "DELETE FROM devkeys WHERE id='$dev_app_id' AND user_id='$userid'";
    $rs = mysqli_query ($srv_dbc, $qs);
    if (mysqli_affected_rows($srv_dbc) != 1) {
      sql_error($qs, 'srv_dbc', "sqle_109");
    }

    // Send the Partner account change email
    $canned_email = "confirm_partner_dev_del_app"; // Slug from the "pantry" table to select the canned email
    $payload_content = "<p>You permanently deleted the dev app: <b>$dev_app_name</b> ($dev_app_domain ID #$dev_app_id)</p>";
    include ('./includes/confirm_partner_dev_change.inc.php');

    // Include the header file
    $page_title = "Delete Dev App :: $siteTitle";
    include ('includes/header.html');

    // Print a customized message
    echo '<h3 class="note_red">Dev App Deleted!</h3>';
    echo "<p class=\"note_red\">The Dev App has been deleted: <b>$dev_app_domain</b> (ID #$dev_app_id)</p>";
    set_switch("&larr; Back to the Developer Center", "Go to the Developer Center", "partner_dev.php", "partner_dev", $userid, "set_gray");
    echo "<br />";

    // Include the HTML footer
		include ('includes/footer.html'); // Include the HTML footer
		exit();

	} else {
		sql_error("$qp &&& $qt", 'srv_dbc', "sqle_110");
	}

} else {
  // Include the header file
  $page_title = "Expired :: $siteTitle";
  include ('includes/header.html');
  echo "Sorry, that link has expired.";
  // Include the HTML footer
  include ('includes/footer.html');
  exit();
}

?>
