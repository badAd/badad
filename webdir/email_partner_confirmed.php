<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Login check
include_once ('includes/login_check.inc.php');

// Include the header file
$page_title = "Email Confirmed :: $siteTitle";
include ('includes/header.html');

// Check temp password in URL
if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['p']))) {
    $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['p']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Redirect Logged-in users
if (!isset($_SESSION['user_id'])) {
  // Login cluster

    // Non-logged in users can login
    $lformaction = 'email_confirmed.php?p='.$tempURLpass; // This must be set for the include to work
    require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
    // Include the HTML footer
    include ('includes/footer.html');
    exit();
} else {
  $userid = $_SESSION['user_id'];
}

// Get user's email
$q = "SELECT email FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$email = $row[0];

// Get the temp URL password time & date and other info
$timeNow = date("Y-m-d H:i:s");

$q = "SELECT date_dead, useable, email FROM confirmemail WHERE binary temppass='$tempURLpass' AND userid='$userid' AND email='$email'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$datedead = $row[0];
$usable = $row[1];
$email2confirm = $row[2];

// Check if the temp URL password even exists
$rows = mysqli_num_rows($r);
if ($rows == 0) {
  echo "That page doesn't exist.";
  // Include the HTML footer
  include ('includes/footer.html');
  exit();
}

if (($timeNow < $datedead) && ($usable == 'live')) {

  if ($email != $email2confirm) { // Do emails agree
    echo "This activation link is not the same as the one set in your account. They must be the same. You can send another confirmation email again by <a title=\"Confirm email\" href=\"confirm_email.php\">clicking here now</a>.";

  }

	// Update the partner's profile
	$q = "UPDATE partners SET email_confirmed='Confirmed' WHERE user_id='$userid'";
	// Disable the temporary link
	$qt = "UPDATE confirmemail SET useable='dead', email='DONE' WHERE temppass='$tempURLpass' AND email='$email2confirm'";
	if (($r = mysqli_query ($dbc, $q)) && ($rt = mysqli_query ($dbc, $qt))) { // If it ran OK.

		// Let the user know the email has been confirmed
		echo "<h3 class=\"note_green\">Partner account activated!</h3><p>Return to the <a title=\"Go to the Partner Center\" href=\"partner.php\">Partner Center</a>.</p>";
		include ('includes/footer.html'); // Include the HTML footer
		exit();

	} else {
		sql_error("$q &&& $qt", 'dbc', "sqle_64");
	}

} else {
  echo "Sorry, that link has expired.";
}

// Include the HTML footer
include ('includes/footer.html');
?>
