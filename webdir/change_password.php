<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Require the database connection
require (MYSQL);

if ((!isset($_POST['pid'])) || ((isset($_POST['pid'])) && ($_POST['pid'] != $userid))) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Include the header file
$page_title = "Change Your Password :: $siteTitle";
include ('./includes/header.html');

// Breadcrumb
echo "<p>&larr; Back to your <a title=\"Account Information\" href=\"account_info.php\">Account Information</a></p>";

// Insert the page content
$rformaction = 'change_password.php'; // This must be set for the include to work
include ('includes/change_password.inc.php');

// Include the HTML footer
include ('./includes/footer.html');
?>
