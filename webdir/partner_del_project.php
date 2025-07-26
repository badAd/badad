<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Make sure we're not here on accident
if (!isset($_POST['del_partner_site_page'])) {
  header("Location: partner.php");
  exit(); // Quit the script
} elseif ($_POST['del_partner_site_page'] != $userid) {
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
$page_title = "Delete a Partner Project :: $siteTitle";
include ('./includes/header.html');

// Action messages?
if (isset($_SESSION['del_domain'])) {
  $action_message = $_SESSION['del_domain'];
  echo "<p class=\"note_yellow\"><b>Deleting</b> $action_message... You should receive an email for final confirmation.</p>";
  unset($_SESSION['del_domain']);
} elseif (isset($_SESSION['rev_domain'])) {
  $action_message = $_SESSION['rev_domain'];
  echo "<p class=\"note_green\">$action_message is revived and the <b>delete request has been cancelled</b>!</p>";
  unset($_SESSION['rev_domain']);
} elseif (isset($_SESSION['off_domain'])) {
  $action_message = $_SESSION['off_domain'];
  echo "<p class=\"note_yellow\">$action_message is off and <b>ready to delete</b>!</p>";
  unset($_SESSION['off_domain']);
} elseif (isset($_SESSION['on_domain'])) {
  $action_message = $_SESSION['on_domain'];
  echo "<p class=\"note_green\">$action_message is back on and <b>live</b>!</p>";
  unset($_SESSION['on_domain']);
}

// Page for activated partners
echo "<h3 class=\"note_red\">Delete a Partner Project!</h3>";
echo "<p><a title=\"Go back to the Partner Center\" href=\"partner.php\">&larr; Partner Center</a></p>";
echo "<p>If your project has had any statistics activity since the last payout, all moneies, statistics, and shares earned will be forfeited and lost if you delete it. If you want to delete a project without losing money or statistics, and start a new one in its place, turn it off by clicking \"live\", then wait to delete it until after the next payout when its stats are reset to zero. Then it will be preserved in your history, but will be deleted and removed from the list in the Partner Center.</p>";
echo "<p>You cannot delete a project while it is live. Clicking \"live\" will reveal the \"delete\" button, which sends a confirmation email with a link to permanently delete a project and its current statistics hit count. This only deletes a projecet and its current statistics history; site statistics that have been paid out cannot be deleted.</p>";
echo "<p class=\"note_red\">All the private information we have pertaining to your partner account, including site projects listed here, is still available to you free of charge. If you desire to obtain this information, do so before deleting a site project from your partner account. Requests for any such information we may retain after an account or site project is deleted incurs a minimum fee of $1,000 USD, plus tech support time spent obtaining the data, and any such requests can only be made in person.</p>";

// Get the Partner's sites' info to populate the profile
$q = "SELECT id, domain, nickname, useable, date_tallied, type, serial_no FROM partnersites WHERE user_id='$userid' ORDER BY domain, nickname, id";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) {
	echo "<p>No sites to delete!</p>";
} else {
	// Start the table
	echo "<br /><div class\"partnersitestable\">\n<p><b>Procede with caution:</b></p><table class=\"sitestable\">\n";
	echo "<th>Domain</th><th>ID</th><th>Counting since</th><th>Delete Forever?</th>";
	while ($row = mysqli_fetch_array($r)) {
		$site_id = "$row[0]";
		$site_domain = "$row[1]";
    $site_nickname = "$row[2]";
		$site_useable = "$row[3]";
		$site_date_tallied = "$row[4]";
		$site_type = "$row[5]";
		$site_serial = "$row[6]";

    // Proper site check for podcast or website
    $site_check = ($site_domain == 'podcast') ? $site_serial : $site_domain;

		// Iterate each project site into the table
    // Website project or podcast?
    $site_listing = ($site_domain == 'podcast') ? "<b>$site_serial</b> (podcast slug)" : "<b>$site_domain</b>";
    // Listing
		echo "<tr><td align=\"left\">$site_listing<br /><br />";
		if ($site_nickname != NULL) {echo "$site_nickname";}
		echo "</td>";

		// Domain ID
		echo "<td align=\"left\" class=\"note_gray\">#$site_id</td>";

		// Date created
		echo "<td align=\"center\">";
		echo "<span class=\"note_gray\">$site_date_tallied</span>";
		echo "</td>";

		// Delete
		echo "<td align=\"center\">";
		if ($site_useable == 'live') {
			set_switch("live", "Switch off first, then delete", "partnerproject_predel_off.act.php", "s", $site_id, "set_green");
  }  elseif ($site_useable == 'failed') {
			set_switch("failed", "Switch off first, then delete", "partnerproject_predel_off.act.php", "s", $site_id, "set_yellow");
	} elseif ($site_useable == 'off') {
    $placeholder = ($site_type == 'podcast') ? 'type the podcast slug to delete' : 'type the domain to delete';
    echo "<form align=\"center\" action=\"partner_del_project.act.php\" method=\"post\">
    <input type=\"hidden\" name=\"del_site\" value=\"$site_id\" />
    <input type=\"hidden\" name=\"del_site_domain_check\" value=\"$site_check\" />
    <input type=\"text\" name=\"del_site_domain\" size=\"32\" placeholder=\"$placeholder\" required />
    <input type=\"submit\" title=\"Delete this site and its records for its current cycle forever\" value=\"email link to delete forever\" class=\"set_red\" />
  	</form>";
    echo '<br />';
    set_switch("Switch on (and don't delete)", "Make this site go live, which also takes it out of this 'pre-delete' stage", "partnerproject_predel_on.act.php", "s", $site_id, "set_blue");
  } elseif ($site_useable == 'deleted') {
    echo '<b class="note_gray">pending delete confirmation from you, check your email inbox</b>';
    echo '<br /><br />';
    set_switch("cancel delete", "I changed my mind", "partnerproject_predel_revive.act.php", "s", $site_id, "set_yellow");
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
