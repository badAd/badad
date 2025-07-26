<?php

// Only include this if (isset($_SESSION['tfa_mode'])) because everything here requires it

// Check for an existing login
if ((isset($_SESSION['user_id'])) && (isset($_SESSION['username']))) {
	echo '<p class="note_red">Error! Really shouldn\'t be here!</p>';
	return; // Quit the script
} elseif (!isset($_SESSION['tfa_mode'])) {
	echo '<p class="note_red">Error! Wrong mode!</p>';
	return; // Quit the script
}

// Create an empty error array if it doesn't already exist
if (!isset($tfa_errors)) $tfa_errors = array();

// Need the form functions script, which defines create_form_input()
// The file may already have been included (e.g., if this is register.php or forgot_password.php)
require_once ('./includes/form_functions.inc.php');

// TFA forms
// Email link
if (($_SESSION['tfa_mode'] == 'email_link') && (isset($_SESSION['tfa_email_link']))) {
	echo '<h3 class="note_blue">Email Link</h3><p class="note_blue">A login link has been sent to your email.</p>';
	if ((isset($_SESSION['rememberme_checked'])) && ($_SESSION['rememberme_checked'] == true)) { // Stay logged in
		echo '<p>You must open that link in this same browser for your 30-day login to take effect.</p>';
	}
	// All done
	return;

// Email code
} elseif (($_SESSION['tfa_mode'] == 'email_code') && (isset($_SESSION['tfa_email_code']))) {
	echo '<h3 class="note_blue">Email Code</h3><p class="note_blue">Enter the code you received via email:</p>';
	echo "
	<form id=\"loginform\" class=\"userform\" action=\"$lformaction\" method=\"post\" accept-charset=\"utf-8\">
	<input type=\"hidden\" name=\"tfa_mode\" value=\"email_code\" />
	<p>";
	if (array_key_exists('email_code', $tfa_errors)) {
		echo '<span class="error">' . $tfa_errors['email_code'] . '</span><br />';
		}
	echo "<label class =\"sans\" for=\"tfa_code\"><strong>Enter your login code</strong></label><br />";
	create_form_input('email_code', 'number', $tfa_errors);

	echo "<br /><br /><input type=\"submit\" value=\"Verify &rarr;\" class=\"formbutton\"></p>
	</form>";
	return;

// SMS code
} elseif (($_SESSION['tfa_mode'] == 'sms_code') && (isset($_SESSION['tfa_sms_code']))) {
	echo '<h3 class="note_blue">SMS Code</h3><p class="note_blue">Enter the code you received via SMS:</p>';

	// FORM SHOULD GO HERE
		//_POST submission needs this: <input type=\"hidden\" name=\"tfa_mode\" value=\"sms_code\" />

	unset($_SESSION['tfa_mode']);
	unset($_SESSION['tfa_email_link']);
	return;

// Google Authenticator
} elseif (($_SESSION['tfa_mode'] == 'google_auth') && (isset($_SESSION['tfa_google_auth']))) {
	echo '<h3 class="note_blue">Google Authenticator</h3><p class=\"note_blue\">Enter the code from your Google Authenticator:</p>';

	// FORM SHOULD GO HERE
		//_POST submission needs this: <input type=\"hidden\" name=\"tfa_mode\" value=\"google_auth\" />

	unset($_SESSION['tfa_mode']);
	unset($_SESSION['tfa_google_auth']);
	return;

// Tap app
} elseif (($_SESSION['tfa_mode'] == 'app_tap') && (isset($_SESSION['tfa_app_tap']))) {
	echo '<h3 class="note_blue"></h3><p class="note_blue">Check our app on your mobile device to approve this login.</p>';

	// Some javascript here to wait until the app_tap is approved
	//$app_tap_success = true;

	if ((isset($app_tap_success)) && ($app_tap_success == true)) {
		// All done
		unset($_SESSION['tfa_mode']);
		unset($_SESSION['tfa_app_tap']);
		unset($_SESSION['tfa_app_tap_userid']);
		return;
	}
// Shouldn't be here, just in case (should be impossible)
} else {
	echo '<p class="note_red">Error! Shouldn\'t be here!</p>';
	return; // Quit the script
}
