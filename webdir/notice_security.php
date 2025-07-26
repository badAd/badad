<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in or this is not relevant, redirect them
redirect_invalid_user();
if ((isset($_SESSION['sent_need_see_security_notice'])) && ($_SESSION['sent_need_see_security_notice'] == true)) {
	$userid = $_SESSION['user_id'];
} else {
	header("Location: index.php");
	exit(); // Quit the script
}

// Require the database connection
require_once (MYSQL);

// _POST
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['accept']))) {
	// Update the database
	$q = "UPDATE users SET need_see_security_notice=false WHERE id='$userid'";
	$r = mysqli_query ($dbc, $q);
	if ($r) {
		unset($_SESSION['sent_need_see_security_notice']);
		header("Location: index.php");
		exit(); // Quit the script
	} else {
		sql_error($q, 'dbc', "sqle_83");
	}
}

// Include the header file
$page_title = "Security Notice :: $siteTitle";
include ('./includes/header.html');

// Start the form
echo '<form action="notice_security.php" method="post">';
echo '<p>This is an important message to accept!</p>';
echo '<input type="submit" value="Accept" name="accept" class="formbutton" /><br /><br />';
// End the form
echo '</form>';

// Include the HTML footer
include ('./includes/footer.html');
?>
