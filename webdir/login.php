<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Redirect Logged-in users
include_once ('includes/login_check.inc.php');
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit(); // Quit the script
} else {

// Login attempts counter
require_once ('./includes/login_count.inc.php');

// Login dropdown action
$login_form_action = "index.php";

// Include the header file
$page_title = "Log In :: $siteTitle";
include ('./includes/header.html');

// Login cluster

  // Non-logged in users can login
  $lformaction = 'login.php'; // This must be set for the include to work
  require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
  }

// Include the HTML footer
include ('./includes/footer.html');
?>
