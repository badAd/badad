<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Include the header file
$page_title = "Confirm email :: $siteTitle";
include ('./includes/header.html');

// Redirect non-logged-in users
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Process the email confirmation
include ('includes/confirm_email.inc.php');

// Any confirmation message
if (isset($confirmationEmailSent)) {echo $confirmationEmailSent;}

// Include the HTML footer
include ('./includes/footer.html');
?>
