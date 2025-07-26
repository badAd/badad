<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require(MYSQL);

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if ((isset($_POST['d'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['d']))) {$IP = get_ip_addr(); script_kiddy('sk_82', '_POST d', $_POST['d'], $IP);}
if ((isset($_POST['d'])) && (filter_var($_POST['d'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['d']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

$q = "DELETE FROM ads WHERE id='$adID'"; // Sites gone, now the User
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 1) {
  header("Location: order_history.php");
  exit(); // Quit the script
} else {

  // Include the header
  $page_title = "Error deleting a pending ad";
  include ('./includes/header.html');
  echo "There was a database error deleting this pending ad. Try again later.";
  // Include the HTML footer
  include ('./includes/footer.html');
}

?>
