<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

include ('./includes/newsletter_unsubscribe.inc.php');

if (!isset($_SESSION['newsletter_unsubscribe'])) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Include the header file
$page_title = "Unsubscribed from the newsletter!";
include ('./includes/header.html');

echo "<h3 class=\"note_green\">$page_title</h3><p>No more pestering newsletters at that address.</p>";

unset($_SESSION['newsletter_unsubscribe']);

// Include the HTML footer
include ('./includes/footer.html');
?>
