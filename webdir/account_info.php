<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Login check
include_once ('includes/login_check.inc.php');

// If the user isn't logged in, redirect them
if (!isset($_SESSION['user_id'])) {
	header("Location: index.php");
	exit(); // Quit the script
} else {
	$userid = $_SESSION['user_id'];
}

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Get the user's info to populate the form
$q = "SELECT username, name, email, project, status, tfa FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$old_username = "$row[0]";
$old_name = "$row[1]";
$old_email = "$row[2]";
$old_project = "$row[3]";
$user_status = "$row[4]";
$old_tfa = "$row[5]";
if ($user_status == "emailwrong") {$_SESSION['no_status'] = true;}

// For storing registration errors
$reg_errors = array();

// Check for a form submission
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['accountform']))) {

	// Check for a name
	if (preg_match ('/^[A-Z \'.-]{1,60}$/i', $_POST['name'])) {
		$na = $_POST['name'];
	} else {
		$reg_errors['name'] = 'Please enter your name, only letters and hyphens!';
	}

	// Check for a Project
	if (preg_match ('/^[A-Z0-9 \'.-]{0,80}$/i', $_POST['project'])) {
		$pr = $_POST['project'];
	} else {
		$reg_errors['project'] = 'Project may use letters, numbers, periods, and hyphens only!';
	}

	// Check for a username
	if (preg_match ('/^[A-Z0-9]{6,60}$/i', $_POST['username'])) {
		$u = $_POST['username'];
	} else {
		$reg_errors['username'] = 'Please enter a valid username!';
	}

	// Check for an email and match against the confirmed email
	if ((filter_var($_POST['email1'], FILTER_VALIDATE_EMAIL)) && (filter_var($_POST['email2'], FILTER_VALIDATE_EMAIL))) {
		if ($_POST['email1'] == $_POST['email2']) {
			$e = strtolower($_POST['email1']);
		} else {
			$reg_errors['email2'] = 'Your email addresses did not match!';
		}
	} else {
		$reg_errors['email1'] = 'Please enter a valid email address!';
	}

	// TFA
	if (isset($_POST['tfa_set'])) {
		    if ($_POST['tfa_set'] == 'none') {$tfa = 'none';}
	  elseif ($_POST['tfa_set'] == 'email_code') {$tfa = 'email_code';}
		elseif ($_POST['tfa_set'] == 'email_link') {$tfa = 'email_link';}
		elseif (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['tfa_set'])) {$IP = get_ip_addr(); script_kiddy('sk_87', '_POST tfa_set', $_POST['tfa_set'], $IP);}
	}

	if (empty($reg_errors)) { // If everything's OK...

		// Check if there are any changes

		if (( $old_username == $u ) && ( $old_name == $na ) && ( $old_email == $e ) && ( $old_project == $pr ) && ( $old_tfa == $tfa )) {
			$updateMessage = "<p class=\"note_yellow\">No changes.</p>";
		} else {

			// Make sure the email address and username are available
			$q = "SELECT email, username FROM users WHERE email='$e' OR username='$u'";
			$r = mysqli_query ($dbc, $q);

			// Get the number of rows returned
			$rows = mysqli_num_rows($r);

			if ($rows == 1) { // No duplicates

				// Update the info in the database...
				$new_na = mysqli_real_escape_string ($dbc, $_POST['name']);
				$new_pr = mysqli_real_escape_string ($dbc, $_POST['project']);
				$new_u = mysqli_real_escape_string ($dbc, $_POST['username']);
				$new_e = mysqli_real_escape_string ($dbc, $e);
				if (isset($tfa)) { // In the event email is not confirmed, TFA settings are hidden
					$q = "UPDATE users SET username='$new_u', email='$new_e', name='$new_na', project='$new_pr', status='ok', tfa='$tfa' WHERE id='$userid'";
				} else {
					$q = "UPDATE users SET username='$new_u', email='$new_e', name='$new_na', project='$new_pr', status='ok' WHERE id='$userid'";
				}
				$r = mysqli_query ($dbc, $q);

				if (mysqli_affected_rows($dbc) == 1) { // If it ran OK

					// Process the email confirmation if changed & unsubscribe key for wrong email
					if ($old_email != $e) {
						include ('includes/emailwrong_create.inc.php');
						include ('includes/confirm_email.inc.php');
						$confirm_change = true;
						$_SESSION['user_email'] = $e;
					}

					// Reset $old_ variables now that they have been updated
					$old_username = $u;
					$old_name = $na;
					$old_email = $e;
					$old_project = $pr;
					$old_tfa = $tfa;

					// Can only save if not with a emailwrong, so make sure the session is not set
					if (isset($_SESSION['no_status'])) {unset($_SESSION['no_status']);}

					// Send changed info email
					include ('includes/confirm_change.inc.php');

					// Display a verification message
					$updateMessage = "<p class=\"note_green\"><b>Info updated!</b> You should receive an email informing you that there was a change.</p>";

				} else { // If it did not run OK
					sql_error($q, 'dbc', "sqle_70");
				}

			} else { // The email address or username is not available

				if ($rows == 2) { // Both are taken

					$reg_errors['email1'] = 'This email address has already been registered. If you have forgotten your password, use the link at right to have your password sent to you.';
					$reg_errors['username'] = 'This username has already been registered. Please try another.';

				} else { // One or both may be taken

					// Get row
					$row = mysqli_fetch_array($r, MYSQLI_NUM);

					if( ($row[0] == $_POST['email1']) && ($row[1] == $_POST['username'])) { // Both match
						$reg_errors['email1'] = 'This email address has already been registered. If you have forgotten your password, use the link at right to have your password sent to you.';
						$reg_errors['username'] = 'This username has already been registered with this email address. If you have forgotten your password, use the link at right to have your password sent to you.';
					} elseif ($row[0] == $_POST['email1']) { // Email match
						$reg_errors['email1'] = 'This email address has already been registered. <a href=\"forgot_password.php\" align=\"right\">Forgot your password?</a>';
					} elseif ($row[1] == $_POST['username']) { // Username match
						$reg_errors['username'] = 'This username has already been registered. Please try another.';
					}
				} // End of $rows == 2 ELSE
			} // End of $rows == 0 IF
		} // End of duplicate check
	} // End of empty($reg_errors) IF
} // End of the main form submission conditional

// Get email confirmation status from the dateabse
$q = "SELECT email, confirmed_email, join_rank FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$email = "$row[0]";
$confirmed_email = "$row[1]";
$join_rank = "$row[2]";
if ($email == $confirmed_email) {
	$confirmedYN = "Confirmed";
	if (isset($_SESSION['email_unconfirmed'])) {unset($_SESSION['email_unconfirmed']);}
} else {
	$confirmedYN = "Unconfirmed";
	$_SESSION['email_unconfirmed'] = true;
}

// Remove any repair info messages
if (isset($_SESSION['info_repaired'])) {
	unset($_SESSION['repair_info']);
	unset($reg_errors['username']);
	unset($reg_errors['email1']);
	unset($_SESSION['info_repaired']);
}

// Include the header file
$page_title = "Account Information :: $siteTitle";
include ('./includes/header.html');

// Any confirmation message
if (isset($confirmationEmailSent)) {echo $confirmationEmailSent;}

// Any repair info message
if (isset($_SESSION['repair_info'])) {
	echo $_SESSION['repair_info'];
	$reg_errors['username'] = 'For security, change your username!';
	$reg_errors['email1'] = 'For security, make sure no one else can access this email inbox! Changing your email address here is safest bet.';
	$_SESSION['info_repaired'] = true;
} elseif (isset($_SESSION['user_must_verify'])) { // Use the switch to clear out all verification emails
	echo '<div><p class="note_blue">There have been some changes on your account. Please double check that your information here is correct.</p>';
	set_switch("Yes, my info is correct!", "Confirm this information", "verify_info.act.php", "uid", $userid, "set_blue");
	echo '</div><br /><br />';
} elseif (isset($_SESSION['all_confirmed'])) {
	echo '<p class="note_blue">Your account information has been confirmed. Thank you!</p>';
	unset($_SESSION['all_confirmed']);
}

// Any account lookover notice
if (isset($_SESSION['need_lookover_account_info'])) { // Use the switch to clear out all verification emails
	echo '<div><p class="note_blue">We want you to double-check that everything here is as it should be...</p>';
	echo "<p><b class=\"note_blue\">Username:</b> <b>$old_username</b><br /><b class=\"note_blue\">Email:</b> <b>$old_email</b></p>";
	echo '<p class="note_blue">... and everything else...</p>';
	set_switch("Okay, all my account information looks good.", "Looks great!", "user_loacct_ok.act.php", "uid", $userid, "set_blue");
	echo '</div><br /><br />';
}

// Start the page
echo "<h3>Account Info</h3>";

// Any update message
if (isset($updateMessage)) {echo $updateMessage;}

// Referrals
// Check if qualified
// Check to see if the user has made a purchase
$q = "SELECT COUNT(user_id) FROM ads WHERE user_id='$userid' AND price_total > 0";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$countAds = $row[0];

//if (($confirmedYN != "Confirmed") || ($countAds < 1)) { // User doesn't qualify to earn referral credits
if ($confirmedYN != "Confirmed") { // User doesn't qualify to earn referral credits
  	echo "<p class=\"note_yellow\">You must be registered, have your email address confirmed, and have purchased at least one ad before you may earn referral credits to buy ads.";
	// email check
	if (($confirmedYN != "Confirmed") && ($join_rank != NULL)) {
		echo " <b><a class=\"note_gray\" title=\"Click to send a confirmation link to your email address\" href=\"confirm_email.php\">Send confirmation link</a></b>";
	}
	echo "</p>";
} else { // User qualifies, so include the referral_gen form

	// Insert the the referral content
	$rformaction = 'account_info.php'; // This must be set for the include to work
	include ('includes/referral_gen.inc.php');
}

// Credits
echo "<h4>Credits</h4>";
// Check if confirmed & joined
if (($confirmedYN == "Confirmed") && ($join_rank != NULL)) {
	// See how many credits are available
	$q = "SELECT creditcount FROM credits WHERE userid='$userid'";
	$r = mysqli_query ($dbc, $q);
	$rows = mysqli_num_rows($r);
	if ($rows == 0) {
		$creditsAvailable = 0;
	} else {
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$creditsAvailable = "$row[0]";
}

	echo "<p class=\"note\">Your credits: <b>$creditsAvailable</b></p>";
	echo "<p class=\"note_gray\">One credit buys one week for one ad.</p>";

} else {
	echo "<p class=\"note_gray\">Once you qualify to earn credits, your total credits will appear hear.</p>";
}


// Account Details
echo "<h4>Account Details</h4>
<form id=\"accountinfoform\" class=\"userform\" action=\"account_info.php\" method=\"post\" accept-charset=\"utf-8\">
		<input type=\"hidden\" name=\"accountform\" value=\"submitted\" />

		<input type=\"submit\" name=\"submit_button\" value=\"Save\" id=\"submit_button\" class=\"formbutton_green\" />
		<br />
		<br />

		<p><label for=\"name\"><strong>Name</strong></label><br /><small>For your reference and correspondence, legal name not required</small><br />";
		update_form_input('name', 'text', $reg_errors, $old_name);
		echo "</p>

		<p><label for=\"project\"><strong>Project</strong></label><br /><small>Optional, for your reference only, eg: company, project, campaign, team, department, etc</small><br />";
		update_form_input('project', 'text', $reg_errors, $old_project);
		echo "</p>

		<p><label for=\"username\"><strong>Username</strong></label><br /><small>Only letters and numbers, case doesn't matter</small><br />";
		update_form_input('username', 'text', $reg_errors, $old_username);
		echo "</p>";

		// Two-Factor Authentication setting
		if (($confirmedYN == 'Confirmed') && ($join_rank != NULL)) {
			echo '<p><label for="tfa"><strong>Two-Factor Authentication</strong></label></p>';
			echo '
			<select class="formselect" name="tfa_set">';
		} else { // Same form, but hidden
			echo '
			<select hidden class="formselect" name="tfa_set">';
		}
		echo '
			<option value="none" '; if ($old_tfa == 'none') {echo "selected";} echo ' >None - Just username and password</option>
			<option value="email_code" '; if ($old_tfa == 'email_code') {echo "selected";} echo ' >Email me an 8-digit code</option>
			<option value="email_link" '; if ($old_tfa == 'email_link') {echo "selected";} echo ' >Email me a link to click</option>
		</select><br /><br />';

		// Show message about email confirmation status
			// Decide which message to display
			if (($confirmedYN == 'Confirmed') && ($join_rank != NULL)) {
				echo "<p><span class=\"note_green\">Email confirmed</span></p>";
			}	elseif (($confirmedYN == 'Unconfirmed') && ($join_rank != NULL)) {
					if (isset($_SESSION['no_status'])) {
						echo "<p class=\"note_yellow\"><b>You must update your email address because your previous address was unsubscribed!</b> <i>Is this email address is correct? <a class=\"note_gray\" title=\"Click to send a confirmation link to your email address\" href=\"confirm_email.php\">Send a confirmation link</a></i></p>";
				} else {
						echo "<p class=\"note_yellow\"><b>Email unconfirmed!</b><br /><i>Confirming your email address helps protect your account and allows you to refer others for credit, become an advertising partner, and use the checkbox to stay logged in.</i><br /><b><a class=\"note_gray\" title=\"Click to send a confirmation link to your email address\" href=\"confirm_email.php\">Send confirmation link</a></b></p>";
				}
			} elseif ($join_rank == NULL) {
				echo "<p class=\"note_red\">You may change your email, but it may not be confirmed until you have made your first purchase.</p>";
			} else {
				echo "<p><span class=\"note_red\">Critical error 48762, this shouldn't be possible!</span><br />";
			}

		echo "<p><label for=\"email1\"><strong>Email</strong></label><br />";
		update_form_input('email1', 'email', $reg_errors, $old_email);
		echo "</p>
		<p><label for=\"email2\"><strong>Double-Check Email (if changing)</strong></label><br />";
		update_form_input('email2', 'email', $reg_errors, $old_email);
		echo "</p>

		<input type=\"submit\" name=\"submit_button\" value=\"Save\" id=\"submit_button\" class=\"formbutton_green\" />

</form>";

// Email subscription
if ($confirmedYN == 'Confirmed') {
	// See if the user is subscribed
	$q = "SELECT email FROM emailsubscriptions WHERE email='$old_email'";
	$r = mysqli_query ($dbc, $q);
	$rows = mysqli_num_rows($r);
	if ($rows == 0) {
		echo "<h4>NOT Subscribed</h4><p>Would you like to subscribe to our bulletin newsletters with improvements, new features, and our growth as a company? You can easily unsubscribe anytime.</p>";
		set_switch("Subscribe me!", "Subscribe to the newsletters", "subscribeme_on.act.php", "a", $old_email, "set_violet");
	}	else {
		echo "<h4>Subscribed</h4><p>You have subscribed to our bulletin newsletters with improvements, new features, and our growth as a company! You can easily unsubscribe anytime.</p>";
		set_switch("Unsubscribe me!", "Unsubscribe to the newsletters", "subscribeme_off.act.php", "e", $old_email, "set_yellow");
	}
}

// Logins
echo '<br /><hr /><br /><h4>Login sessions</h4><p class="note_gray"><i>If you checked "...stay logged in 30 days" when you logged in, that session will be listed below in Eastern time. This current session (where you are viewing our website from now) will be indicated below and can only be logged out by choosing: "My Stuff" > "Logout" from the top menu. From here, you can disable other 30-day login sessions if you like.</i></p>';
$q = "SELECT id, key_a, key_b, date_created FROM rememberme WHERE userid='$userid' AND useable='live' ORDER BY date_created DESC";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) {
	echo "<p>No current 30-login sessions.</p>";
} else {
	// Start the table
	echo "<br /><div class=\"livelogin_sessions\">\n
	<table class=\"sitestable\">\n<tbody>\n
	<tr><th>Date logged-in</th><th>Disable session?</th></tr>";
	//REMOVED
	//<th>Subdomains</th>
	while ($row = mysqli_fetch_array($r)) {
		$login_id = "$row[0]";
		$login_key_a = "$row[1]";
		$login_key_b = "$row[2]";
		$login_date = "$row[3]";
		echo "<tr><td>$login_date</td><td>";
		if ((isset($_COOKIE['rememberme_a'])) && (isset($_COOKIE["rememberme_b"])) && ($_COOKIE['rememberme_a'] == $login_key_a) && ($_COOKIE['rememberme_b'] == $login_key_b)) {
			echo '<b class="note_blue">This session</b>';
		} else {
			set_switch("Disable login", "Disable this other login session", "unset_rememberme.act.php", "i", $login_id, "set_gray");
		}
		echo "</td></tr>";
	}
	echo "</tbody></table>\n</div>";
}

// Change password
echo "<br /><hr /><br />";
set_switch("Change Password...", "Change your password", "change_password.php", "pid", $userid, "set_blue");

// Delete account
echo "<br /><hr /><br /><h3 class=\"note_red\">Danger Zone</h3><br />";
set_switch("Delete my account...", "Go to the page to delete this account", "user_del_account.php", "del_user_account_page", $userid, "set_red");

// Include the HTML footer
include ('./includes/footer.html');
?>
