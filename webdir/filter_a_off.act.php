<?php

// Use this line just before including this:
//$_POST['filterback'] = $_SERVER['REQUEST_URI'];

// Security
if (!isset($_POST['filterback'])) {
  require ('./includes/config.inc.php');
  $IP = get_ip_addr();
  echo "No script kiddies, $IP.";
  exit(); // Quit the script
}

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Clear the REQUEST_URI
$filterback = $_POST['filterback'];

// Set the filter
unset ($_SESSION['filter_a']);

// Go back to where we were
header("Location: $filterback");
exit(); // Quit the script
