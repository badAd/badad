<?php

// This edits a scheduled ad before it runs

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Make sure we're not here by accident
if ((!isset($_SESSION['editing_id'])) || (!isset($_SESSION['editing_status']))) {
	// Clear any old SESSION form values
  include ('./includes/ad_values_unset.inc.php');
	header("Location: index.php");
	exit(); // Quit the script
}

// Need an ad to edit
if (isset($_SESSION['editing_id'])) {
  $adID = $_SESSION['editing_id'];
  // Allow this one dup (based on the rerun_id)
  $q = "SELECT rerun_id FROM ads WHERE id='$adID'";
  $r = mysqli_query($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  if ($row[0] != NULL) {
    $allowed_dup = "$row[0]";
  } else {
    $allowed_dup = $adID;
  }
} else {
	// Clear any old SESSION form values
  include ('./includes/ad_values_unset.inc.php');
  // Get out of Dodge
  header("Location: index.php");
  exit(); // Quit the script
}

// User ID
if (isset($_SESSION['user_id'])) {
  $userid = $_SESSION['user_id'];
} else {
	// Clear any old SESSION form values
  include ('./includes/ad_values_unset.inc.php');
  // Get out of Dodge
  header("Location: index.php");
  exit(); // Quit the script
}

// Insert the ad form check
include ('inserts/new_ad.check.ins.php');

// Save the ad if set

if ((isset($_SESSION['validAd'])) && ($_SESSION['validAd'] == true)) {
	include ('inserts/edit_ad.save.ins.php');
}

// Include the header
$page_title = "Edit Your Ad :: $siteTitle";
include ('includes/header.html');

// Include any message from the save action
if (isset($save_message)) { echo $save_message; }

// In-page title
echo "<h3>Edit Your Ad:</h3>";

// If it's a POST request, handle the login attempt
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['username'])) && (isset($_POST['pass']))) {
}

// Insert the page content
include ('inserts/edit_ad.ins.php');

 // Include the HTML footer
include ('includes/footer.html');
?>
