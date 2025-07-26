<?php

// Check for an existing login
if ((isset($_SESSION['user_id'])) && (isset($_SESSION['username']))) {
	return;
}

// Login count message
if ((isset($_SESSION['login_attempt'])) && ($_SESSION['login_attempt'] > 3)) {
	echo '<h3 class="note_red">Log In Failure</h3><p class="note_red">You tried to login too many times. Try again later.</p>';
	return;
}

// Create an empty error array if it doesn't already exist
if (!isset($login_errors)) $login_errors = array();

// Need the form functions script, which defines create_form_input()
// The file may already have been included (e.g., if this is register.php or forgot_password.php)
require_once ('./includes/form_functions.inc.php');

// TFA forms
if (isset($_SESSION['tfa_mode'])) {
	include ('./includes/tfa_form.inc.php');
	return;
} else {

	// Login Form
	echo "<h3>Log In</h3>
	<form id=\"loginform\" class=\"userform\" action=\"$lformaction\" method=\"post\" accept-charset=\"utf-8\">
	<input type=\"hidden\" name=\"loginform\" value=\"submitted\" />
	<p>";
	if (array_key_exists('login', $login_errors)) {
		echo '<span class="error">' . $login_errors['login'] . '</span><br />';
		}
	echo "<label class =\"sans\" for=\"username\"><strong>Username</strong></label><br />";
	create_form_input('username', 'text', $login_errors);
	echo "<br /><br /><label class =\"sans\" for=\"pass\"><strong>Password</strong></label><br />";
	create_form_input('pass', 'password', $login_errors);
	echo " <a href=\"password_reset.php\" align=\"right\">Forgot?</a>";
	echo '<br /><br /><input type="checkbox" name="rememberme" /> Use <a title="Read about persistent cookies in our Terms" href="Terms.htm#cookies" target="_blank">cookies</a> to stay logged in 30 days.';
	echo "<br /><br /><input type=\"submit\" value=\"Login &rarr;\" class=\"formbutton\"></p>
	</form>";
}
