<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

if ((isset($_SESSION['stop_clicking'])) && ($_SESSION['stop_clicking'] == true)) {
  unset($_SESSION['stop_clicking']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Include the header file
$page_title = "Too much";
include ('./includes/header.html');

// Login cluster

  // Message
  echo "<h3>A watched pot never boils.</h3>
  <p>You're checking those stats an aweful lot. Don't worry so much. The world will keep spinning.</p>
  <p>&larr; Back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></p>";

// Include the HTML footer
include ('./includes/footer.html');
?>
