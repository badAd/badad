<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Make sure we're not here on accident
if (!isset($_POST['del_develeoper_app_page'])) {
  header("Location: partner.php");
  exit(); // Quit the script
} elseif ($_POST['del_develeoper_app_page'] != $userid) {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Require the database connection
require_once (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Check if partner account has been activated
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows == 1) { // partner account exists in database
	$activation = $row[0];
	if ($activation != "Confirmed") { // Not activated
		// Resent activation email
    // Include the header file
    $page_title = "Confirmation Needed First :: $siteTitle";
    include ('./includes/header.html');
    echo '<h3 class="note_yellow">Confirmation Needed First</h3>';
		echo "<p class=\"note_yellow\"><b>Good news, partner application already processed!</b> Now, confirm your payout email.</p>";
		// Process the email confirmation if changed
		include ('includes/confirm_partner_email.inc.php');
		// Include the HTML footer
		include ('./includes/footer.html');
		exit();
	}
} else { // No partner application entry
		// Check to see if user's email is verified
		$qe = "SELECT email, email_confirmed FROM users WHERE id='$userid'";
		$re = mysqli_query ($dbc, $qe);
		$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
		$email = "$rowe[0]";
		$email_confirmed = "$rowe[1]";
		if ($email != $email_confirmed) {
			// Process the email confirmation
      // Include the header file
      $page_title = "Confirmation Needed First :: $siteTitle";
      include ('./includes/header.html');
      echo '<h3 class="note_yellow">Confirmation Needed First</h3>';
			echo "<p class=\"note_yellow\">Confirm your email ($email).</p>";
			include ('includes/confirm_email.inc.php');
			// Include the HTML footer
			include ('./includes/footer.html');
			exit();
	} else { // email verified

		// Partner signup
    // Include the header file
    $page_title = "Partner Center :: $siteTitle";
    include ('./includes/header.html');
		$rformaction = "partner.php";
		include ('includes/register_partner.inc.php');
		include ('./includes/footer.html');
		exit();
	}
} // activated check complete, user is a fully-fledged partner

// Start building the page

// Include the header file
$page_title = "Delete a Dev App :: $siteTitle";
include ('./includes/header.html');

// Action messages?
if (isset($_SESSION['del_dev_app'])) {
  $action_message = $_SESSION['del_dev_app'];
  echo "<p class=\"note_yellow\"><b>Deleting</b> $action_message... You should receive an email for final confirmation.</p>";
  unset($_SESSION['del_dev_app']);
}

// Page for activated developers
echo "<h3 class=\"note_red\">Delete a Dev App!</h3>";
echo "<br />";
set_switch("&larr; Back to the Developer Center", "Go to the Developer Center", "partner_dev.php", "partner_dev", $userid, "set_gray");
echo "<br />";
echo "<p>You cannot delete a dev app while it is live. Clicking \"live\" will reveal the \"delete\" button, which sends a confirmation email with a link to permanently delete a dev app</p>";

// Get the Partner's sites' info to populate the profile
$q = "SELECT id, name, domain, description, status FROM devkeys WHERE user_id='$userid' ORDER BY name, domain, description, id";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) {
	echo "<p>No dev apps to delete!</p>";
} else {
	// Start the table
	echo "<br /><div class\"partnerdevappstable\">\n<p><b>Procede with caution:</b></p><table class=\"devappstable\">\n";
	echo "<th>Dev App</th><th>ID</th><th>Delete Forever?</th>";
	while ($row = mysqli_fetch_array($r)) {
		$dev_app_id = "$row[0]";
		$dev_app_name = "$row[1]";
    $dev_app_domain = "$row[2]";
		$dev_app_description = "$row[3]";
    $dev_app_status = "$row[4]";

		// Iterate each project site into the table
    // Dev App
		echo "<tr><td align=\"left\"><b>$dev_app_name</b><br /><br /><b>$dev_app_domain</b><br /><br />";
		if (($dev_app_description != NULL) && ($dev_app_description != "")) {echo "$dev_app_description";}
		echo "</td>";

		// Dev App ID
		echo "<td align=\"left\" class=\"note_gray\">#$dev_app_id</td>";


		// Delete
		echo "<td align=\"center\">";
		if ($dev_app_status == 'live') {
      set_switch("live", "Switch to test mode first, then delete", "partner_dev_app_status_test.act.php", "s", $dev_app_id, "set_green");
	} elseif ($dev_app_status == 'test') {
    echo "<form align=\"center\" action=\"partner_dev_del_app.act.php\" method=\"post\">
    <input type=\"hidden\" name=\"del_dev_app\" value=\"$dev_app_id\" />
    <input type=\"hidden\" name=\"del_dev_app_name\" value=\"$dev_app_name\" />
    <input type=\"hidden\" name=\"del_dev_app_domain_check\" value=\"$dev_app_domain\" />
    <input type=\"text\" name=\"del_dev_app_domain\" size=\"32\" placeholder=\"type domain of the Dev App to delete\" required />
    <input type=\"submit\" title=\"Delete this Dev App and disconnect all clients forever\" value=\"email link to delete forever\" class=\"set_red\" />
  	</form>";
    echo '<br />';
  } elseif ($dev_app_status == 'deleted') {
    echo '<b class="note_gray">pending delete confirmation from you, check your email inbox</b>';
    echo '<br /><br />';
    set_switch("cancel delete", "I changed my mind", "partner_dev_app_predel_revive.act.php", "s", $dev_app_id, "set_yellow");
	}
		echo "</td>";

		// Finish the row
		echo "</tr>";

	}
	// Finish the table
	echo "</table></div><br />";

	// Close the table section
	echo "<br /><hr />";

// Bottom breadcrumb
echo "<p><a title=\"Go back to the Partner Center\" href=\"partner.php\">&larr; Partner Center</a></p>";

} // Have sites check


// Include the HTML footer
include ('./includes/footer.html');
?>
