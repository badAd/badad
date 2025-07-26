<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Login check
include_once ('includes/login_check.inc.php');

// If the user isn't logged in, redirect them
if (!isset($_SESSION['user_id'])) {
	header("Location: index.php");
	exit(); // Quit the script
} else {
	$userid = $_SESSION['user_id'];
}

// If the user doens't have editing privileges, redirect
if ((!$_SESSION['user_is_admin']) && (!$_SESSION['user_is_supervisor'])) {
	header("Location: index.php");
	exit(); // Quit the script
}

// Process any POST


// Include the header file
$page_title = "Supervision :: $siteTitle";
include ('./includes/header.html');

// Start the page
echo "<h3>Supervision</h3>";





// Include the HTML footer
include ('./includes/footer.html');
?>
