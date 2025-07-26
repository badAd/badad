<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

include ('./includes/emailwrong_unsubscribe.inc.php');

if (!isset($_SESSION['unsubscribed'])) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Include the header file
$page_title = "Unsubscribed!";
include ('./includes/header.html');

echo "<h3>$page_title</h3><p>No more pestering emails at that address.</p>";

unset($_SESSION['unsubscribed']);

// Include the HTML footer
include ('./includes/footer.html');
?>
