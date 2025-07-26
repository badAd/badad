<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['p']))) {
    $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['p']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Login check
include_once ('includes/login_check.inc.php');

// Redirect Logged-in users
if (!isset($_SESSION['user_id'])) {
  // Include the header file
  $page_title = "Email Confirmation :: $siteTitle";
  include ('includes/header.html');

  echo "<h3>Email Confirmation</h3>";

  echo "<p>Please login to confirm your email address.</p>";

    // Non-logged in users can login
    $lformaction = 'email_confirmed.php?p='.$tempURLpass; // This must be set for the include to work
    require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
    // Include the HTML footer
    include ('includes/footer.html');
    exit();
} else {
  $userid = $_SESSION['user_id'];
}

// Get user's email & join status
$q = "SELECT email, confirmed_email, join_rank FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$email = $row[0];
$confirmed_email = "$row[1]";
$join_rank = "$row[2]";

// Quit if not joined, can't confirm email until joined
if ($join_rank == NULL) {
  header("Location: index.php");
  exit(); // Quit the script
}

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
  // Include the header file
  $page_title = "Wrong Page :: $siteTitle";
  include ('includes/header.html');
  echo "That page doesn't exist.";
  // Include the HTML footer
  include ('includes/footer.html');
  exit();
}

if (($timeNow < $datedead) && ($usable == 'live')) {

  if ($email != $email2confirm) { // Do emails agree
    echo "<p class=\"note_red\">This email confirmation link is not the same as the one set in your account. They must be the same. You can send another confirmation email again by <a title=\"Confirm email\" href=\"confirm_email.php\">clicking here now</a>.</p>";
    // Include the HTML footer
    include ('includes/footer.html');
    exit();
  }

  // Unsubscribe any email subscription from the old email
  $q = "DELETE FROM emailsubscriptions WHERE email='$confirmed_email'";
  $r = mysqli_query ($dbc, $q);

	// Update the user's profile
	$q = "UPDATE users SET confirmed_email='$email', status='ok' WHERE id='$userid'";
	// Disable the temporary link
	$qt = "UPDATE confirmemail SET useable='dead', email='DONE' WHERE temppass='$tempURLpass' AND email='$email2confirm'";
	if (($r = mysqli_query ($dbc, $q)) && ($rt = mysqli_query ($dbc, $qt))) { // If it ran OK.

    // Unset any email problem
    if (isset($_SESSION['no_status'])) {unset($_SESSION['no_status']);}

    // New emailwrongunsubscribe key
    $confirming_email = true;
    include ('includes/emailwrong_create.inc.php');
    unset($confirming_email);

    // Remove any $_SESSION['email_unconfirmed']
    if (isset($_SESSION['email_unconfirmed'])) {unset($_SESSION['email_unconfirmed']);}

    // Include the header file
    $page_title = "Email Confirmed :: $siteTitle";
    include ('includes/header.html');

		// Let the user know the email has been confirmed
		echo "<h3 class=\"note_green\">Email confirmed. Thanks!</h3><p>Want to view the <a title=\"$siteTitle home\" href=\"index.php\">homepage</a>?</p>";
		include ('includes/footer.html'); // Include the HTML footer
		exit();

	} else {
		sql_error("$q &&& $qt", 'dbc', "sqle_65");
	}

} else {
  // Include the header file
  $page_title = "Expired Link :: $siteTitle";
  include ('includes/header.html');

  echo "Sorry, that link has expired.";

  // Include the HTML footer
  include ('includes/footer.html');
}
?>
