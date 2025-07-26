<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Check temp password in URL
if ((isset($_GET['c'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['c']))) {
  $URLconfirmkey = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['c']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Login check
include_once ('includes/login_check.inc.php');

// Get the temp URL password time & date and other info
$timeNow = date("Y-m-d H:i:s");

$q = "SELECT date_dead, useable FROM confirmchange WHERE binary confirmkey='$URLconfirmkey'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$datedead = $row[0];
$usable = $row[1];

// Check if the temp URL password even exists
$rows = mysqli_num_rows($r);
if ($rows == 0) {
  // Include the header file
  $page_title = "Wrong Page :: $siteTitle";
  include ('includes/header.html');
  echo "That page doesn't exist.";
  // Include the HTML footer
  include ('includes/footer.html');
  exit();
}

if (($timeNow < $datedead) && ($usable == 'live')) {
  // Disable the temporary link
	$qt = "UPDATE confirmchange SET useable='dead' WHERE binary confirmkey='$URLconfirmkey'";
	if ($rt = mysqli_query ($dbc, $qt)) { // If it ran OK.

    // Cleanup any verify message
    if (isset($_SESSION['user_must_verify'])) {unset($_SESSION['user_must_verify']);}

    // Include the header file
    $page_title = "Change Confirmed :: $siteTitle";
    include ('includes/header.html');

		// Let the user know the email has been confirmed
		echo "<h3 class=\"note_green\">Change confirmed. Thanks for letting us know!</h3>";
		include ('includes/footer.html'); // Include the HTML footer
		exit();

	} else {
		sql_error($qt, 'dbc', "sqle_67");
	}

} else {
  // Include the header file
  $page_title = "Wrong Page :: $siteTitle";
  include ('includes/header.html');
  echo "Sorry, that link has expired.";
  // Include the HTML footer
  include ('includes/footer.html');
}

?>
