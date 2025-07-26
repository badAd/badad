<?php

// Partner information needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Check for an existing login
if ((isset($_SESSION['user_id'])) && (isset($_SESSION['username'])) && ($_SERVER['REQUEST_METHOD'] != 'POST')) {
	return;
} elseif ((isset($_COOKIE['rememberme_a'])) && (isset($_COOKIE['rememberme_b'])) && ($_SERVER['REQUEST_METHOD'] != 'POST')) { // Check for persistent cookies
	// Get the user ID
	$rememberme_key_a = $_COOKIE['rememberme_a'];
	$rememberme_key_b = $_COOKIE['rememberme_b'];
	$q = "SELECT userid FROM rememberme WHERE binary key_a='$rememberme_key_a' AND binary key_b='$rememberme_key_b' AND useable='live' AND date_expires > CURRENT_TIMESTAMP"; // "binary" makes sure case and characters are exact
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) == 1) { // Login success
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
		$userid = $row[0];
	} else {
		header("Location: logout.php"); // logout
		exit();
	}

	// Retrieve the user info
	$q = "SELECT username, status, type, email, confirmed_email, name, need_accept_new_tc, need_see_security_notice, need_lookover_account_info, join_rank FROM users WHERE id='$userid'";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) == 1) { // Login success
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
		$username = $row[0];
		$user_status = $row[1];
		$username_set = true; // For final error check

	// Store the data in a session
	$_SESSION['username'] = $username;
	$_SESSION['user_id'] = $userid;
	$_SESSION['type'] = $row[2];
	$set_email = $row[3];
	$confirmed_email = $row[4];
	$_SESSION['user_name'] = $row[5];
	$_SESSION['user_email'] = $set_email;
	$_SESSION['need_accept_new_tc'] = $row[6];
	$_SESSION['need_see_security_notice'] = $row[7];
	$_SESSION['need_lookover_account_info'] = $row[8];
	$_SESSION['joined'] = ($row[9] == 0) ? 'unranked' : 'joined';
	if ($_SESSION['need_accept_new_tc'] == false) {unset($_SESSION['need_accept_new_tc']);}
	if ($_SESSION['need_see_security_notice'] == false) {unset($_SESSION['need_see_security_notice']);}
	if ($_SESSION['need_lookover_account_info'] == false) {unset($_SESSION['need_lookover_account_info']);}
	if (isset($_SESSION['tfa_mode'])) {unset($_SESSION['tfa_mode']);}
	if (isset($_SESSION['tfa_email_code'])) {unset($_SESSION['tfa_email_code']);}
	if (isset($_SESSION['tfa_sms_code'])) {unset($_SESSION['tfa_sms_code']);}
	if (isset($_SESSION['tfa_google_auth'])) {unset($_SESSION['tfa_google_auth']);}
	if (isset($_SESSION['login_attempt'])) {unset($_SESSION['login_attempt']);}
} else {
	sql_error($q, 'dbc', "sqle_51");
}

	// Email status
	if ($user_status != 'ok') { // "unsubscribed" from site
		$_SESSION['no_status'] = true;
		header("Location: account_info.php");
		exit(); // Quit the script
	} elseif (($set_email != $confirmed_email) || ($confirmed_email == 'Unconfirmed')) { // Unconfirmed email
		$_SESSION['email_unconfirmed'] = true;
	}

	// Indicate if the user's account is admin
	if ($_SESSION['type'] == 'editor') $_SESSION['user_is_editor'] = true;
	if ($_SESSION['type'] == 'voice') $_SESSION['user_is_voice'] = true;
	if ($_SESSION['type'] == 'editorvoice') $_SESSION['user_is_editorvoice'] = true;
	if ($_SESSION['type'] == 'publisher') $_SESSION['user_is_publisher'] = true;
	if ($_SESSION['type'] == 'supervisor') $_SESSION['user_is_supervisor'] = true;
	if ($_SESSION['type'] == 'admin') $_SESSION['user_is_admin'] = true;

	// Kill any loginonce and logincode keys
	include_once ('./includes/clear_keys.inc.php');
	// Check for old confirm_change links
	$q = "SELECT id FROM confirmchange WHERE userid='$userid' AND useable='live'";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		$_SESSION['user_must_verify'] = true;
	}
	// Check for old Partner confirm_change links
	$q = "SELECT id FROM confirmdevappchange WHERE userid='$userid' AND useable='live'";
	$r = mysqli_query ($srv_dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		$_SESSION['partner_dev_must_verify'] = true;
	}
	// Check for old Partner confirm_change links
	$q = "SELECT id FROM confirmpartnerchange WHERE userid='$userid' AND useable='live'";
	$r = mysqli_query ($srv_dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		$_SESSION['partner_must_verify'] = true;
	}
	// Check for Partner notices
	$q = "SELECT need_accept_new_tc, need_see_new_categories FROM partners WHERE user_id='$userid'";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) == 1) { // user is Partner
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
		$_SESSION['partner_need_accept_new_tc'] = $row[0];
		$_SESSION['partner_need_see_new_categories'] = $row[1];
		if ($_SESSION['partner_need_accept_new_tc'] == false) {unset($_SESSION['partner_need_accept_new_tc']);}
		if ($_SESSION['partner_need_see_new_categories'] == false) {unset($_SESSION['partner_need_see_new_categories']);}
	}

	return;

} elseif (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['rememberme']))) {
	$_SESSION['rememberme_checked'] = true;
} elseif ($_SERVER['REQUEST_METHOD'] != 'POST') {
	return;
} // End persistent cookie login


// Login attempts counter
require_once ('./includes/login_count.inc.php');

// TFA form _POST submission check
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['tfa_mode'])) && (isset($_SESSION['tfa_mode'])) && ($_POST['tfa_mode'] == $_SESSION['tfa_mode']) && (isset($_SESSION['tfa_email_code_userid']))) {

	// Array for recording errors
	$tfa_errors = array();

	// Check the TFA mode
	// email_code
	if ($_POST['tfa_mode'] == 'email_code') {
		if (preg_match ('/[0-9]$/i', $_POST['email_code'])) {
			$tempCode = preg_replace("/[^0-9]/","", $_POST['email_code']);

			// User ID
			$user_try_ID = $_SESSION['tfa_email_code_userid'];

			// Check the key
			$q = "SELECT id FROM logincode WHERE date_dead > CURDATE() AND useable='live' AND userid='$user_try_ID' AND binary temppass='$tempCode'";
			$r = mysqli_query ($dbc, $q);
			if (mysqli_num_rows($r) == 1) {
			  $q = "UPDATE logincode SET useable='used' WHERE binary temppass='$tempCode'";
			  $r = mysqli_query ($dbc, $q);
			  if (mysqli_affected_rows($dbc) == 1) { // All success
					$tfa_type = 'email_code';
					unset($_SESSION['tfa_email_code_userid']);
				} else {
			      sql_error($q, 'dbc', "sqle_52");
			  }
			} else { // Wrong code
				$tfa_errors['email_code'] = 'That code is incorrect, try again.';
				return; // Not ready
			}
		} else { // Not numbers
			$tfa_errors['email_code'] = 'Only numbers! Use the code just emailed to you.';
			return; // Not ready
		}

		// sms_code
	} elseif ($_POST['tfa_mode'] == 'sms_code') {

			// User ID
			$user_try_ID = $_SESSION['tfa_sms_code_userid'];

			$tfa_errors['sms_code'] = 'That code is incorrect, try again.';

			$tfa_type = 'sms_code';
			unset($_SESSION['tfa_sms_code_userid']);
			return; // Not ready

		// google_auth
	} elseif ($_POST['tfa_mode'] == 'google_auth') {

			// User ID
			$user_try_ID = $_SESSION['tfa_google_auth_userid'];

			$tfa_errors['google_auth'] = 'That code is incorrect, try again.';

			$tfa_type = 'google_auth';
			unset($_SESSION['tfa_google_auth_userid']);
			return; // Not ready
	}

	// TFA success, login the user
	if ((empty($tfa_errors)) && (isset($tfa_type))) {
		// Retrieve the user info
		$q = "SELECT username, status, type, email, confirmed_email, name, need_accept_new_tc, need_see_security_notice, need_lookover_account_info, join_rank FROM users WHERE id='$user_try_ID' AND tfa='$tfa_type'";
		$r = mysqli_query ($dbc, $q);
		if (mysqli_num_rows($r) == 1) { // Login success
			$row = mysqli_fetch_array ($r, MYSQLI_NUM);
			$username = $row[0];
			$user_status = $row[1];
			$userid = $user_try_ID;
			$username_set = true; // For final error check

		// Store the data in a session
		$_SESSION['username'] = $username;
		$_SESSION['user_id'] = $userid;
		$_SESSION['type'] = $row[2];
		$set_email = $row[3];
		$confirmed_email = $row[4];
		$_SESSION['user_name'] = $row[5];
		$_SESSION['user_email'] = $set_email;
		$_SESSION['need_accept_new_tc'] = $row[6];
		$_SESSION['need_see_security_notice'] = $row[7];
		$_SESSION['need_lookover_account_info'] = $row[8];
		$_SESSION['joined'] = ($row[9] == 0) ? 'unranked' : 'joined';
		if ($_SESSION['need_accept_new_tc'] == false) {unset($_SESSION['need_accept_new_tc']);}
		if ($_SESSION['need_see_security_notice'] == false) {unset($_SESSION['need_see_security_notice']);}
		if ($_SESSION['need_lookover_account_info'] == false) {unset($_SESSION['need_lookover_account_info']);}
		unset($_SESSION['tfa_mode']);
		if (isset($_SESSION['tfa_email_code'])) {unset($_SESSION['tfa_email_code']);}
		if (isset($_SESSION['tfa_sms_code'])) {unset($_SESSION['tfa_sms_code']);}
		if (isset($_SESSION['tfa_google_auth'])) {unset($_SESSION['tfa_google_auth']);}
		if (isset($_SESSION['login_attempt'])) {unset($_SESSION['login_attempt']);}
	} else {
		sql_error($q, 'dbc', "sqle_53");
	}

		// Email status
		if ($user_status != 'ok') { // "unsubscribed" from site
			$_SESSION['no_status'] = true;
			header("Location: account_info.php");
			exit(); // Quit the script
		} elseif (($set_email != $confirmed_email) || ($confirmed_email == 'Unconfirmed')) { // Unconfirmed email
			$_SESSION['email_unconfirmed'] = true;
		} elseif ((isset($_SESSION['rememberme_checked'])) && ($_SESSION['rememberme_checked'] == true)) { // Stay logged in
			include_once ('./includes/set_rememberme.inc.php');
		}

		// Indicate if the user's account is admin
		if ($_SESSION['type'] == 'admin') $_SESSION['user_is_admin'] = true;
		if ($_SESSION['type'] == 'editor') $_SESSION['user_is_editor'] = true;

		// Kill any loginonce and logincode keys
	  include_once ('./includes/clear_keys.inc.php');
		// Check for old confirm_change links
		$q = "SELECT id FROM confirmchange WHERE userid='$userid' AND useable='live'";
		$r = mysqli_query ($dbc, $q);
		if (mysqli_num_rows($r) > 0) {
			$_SESSION['user_must_verify'] = true;
		}
		// Check for old Partner confirm_change links
		$q = "SELECT id FROM confirmdevappchange WHERE userid='$userid' AND useable='live'";
		$r = mysqli_query ($srv_dbc, $q);
		if (mysqli_num_rows($r) > 0) {
			$_SESSION['partner_dev_must_verify'] = true;
		}
		// Check for old Partner confirm_change links
		$q = "SELECT id FROM confirmpartnerchange WHERE userid='$userid' AND useable='live'";
		$r = mysqli_query ($srv_dbc, $q);
		if (mysqli_num_rows($r) > 0) {
			$_SESSION['partner_must_verify'] = true;
		}
		// Check for Partner notices
		$q = "SELECT need_accept_new_tc, need_see_new_categories FROM partners WHERE user_id='$userid'";
		$r = mysqli_query ($dbc, $q);
		if (mysqli_num_rows($r) == 1) { // user is Partner
			$row = mysqli_fetch_array ($r, MYSQLI_NUM);
			$_SESSION['partner_need_accept_new_tc'] = $row[0];
			$_SESSION['partner_need_see_new_categories'] = $row[1];
			if ($_SESSION['partner_need_accept_new_tc'] == false) {unset($_SESSION['partner_need_accept_new_tc']);}
			if ($_SESSION['partner_need_see_new_categories'] == false) {unset($_SESSION['partner_need_see_new_categories']);}
		}

	} else {
		echo '<p class="note_red">This is strange! User two-factor authentication method has changed since this login attempt began.</p>';
		exit();
	}

// Pass the errors to the Header
if ((!isset($tfa_errors['email_code'])) && (!isset($tfa_errors['sms_code'])) && (!isset($tfa_errors['google_auth'])) && (isset($username_set))) {
	$login_success = true;
	}
} // End TFA form submission check


// Normal login form _POST submission check
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['registerform'])) || ((isset($_POST['username'])) && (isset($_POST['pass'])))) {

	// Array for recording errors
	$login_errors = array();

		// Check for a registration
	if ((!empty($_POST['registerform'])) && ($_POST['registerform'] == "submitted")) {
		require_once ('./includes/register_check.inc.php');

		// Validate the username:
	} elseif ((!empty($_POST['username'])) && (preg_match ('/^[A-Z0-9]{6,32}$/i', $_POST['username']))) {
			$username = mysqli_real_escape_string ($dbc, $_POST['username']);
			$username_set = true;
	} else  {
		$login_errors['username'] = 'Please enter a valid username!';
	}

		// Validate the password
		if (!empty($_POST['pass'])) {
			$password = mysqli_real_escape_string ($dbc, $_POST['pass']);
			$password_set = true;
		} else {
			$login_errors['pass'] = 'Please enter your password!';
		}

		if (empty($login_errors)) { // OK to proceed!

			// Verify the password, continue other checks if TRUE
			$q = "SELECT pass, status, id, type, email, confirmed_email, name, tfa, need_accept_new_tc, need_see_security_notice, need_lookover_account_info, join_rank FROM users WHERE username='$username'";
			$r = mysqli_query ($dbc, $q);
			$row = mysqli_fetch_array ($r, MYSQLI_NUM);
			$hp = $row[0];
			$tfa = $row[7];
			if (password_verify($password, $hp)) {
				if (mysqli_num_rows($r) == 1) { // A match was made

					// Two-Factor Authentication?
					if ($tfa == 'none') {
						// Store the data in a session
						$user_status = $row[1];
						$_SESSION['username'] = $username;
						$userid = $row[2];
						$_SESSION['type'] = $row[3];
						$set_email = $row[4];
						$confirmed_email = $row[5];
						$_SESSION['user_name'] = $row[6];
						$_SESSION['user_email'] = $set_email;
						$_SESSION['user_id'] = $userid;
						$_SESSION['need_accept_new_tc'] = $row[8];
						$_SESSION['need_see_security_notice'] = $row[9];
						$_SESSION['need_lookover_account_info'] = $row[10];
						$_SESSION['joined'] = ($row[11] == 0) ? 'unranked' : 'joined';
						if ($_SESSION['need_accept_new_tc'] == false) {unset($_SESSION['need_accept_new_tc']);}
						if ($_SESSION['need_see_security_notice'] == false) {unset($_SESSION['need_see_security_notice']);}
						if ($_SESSION['need_lookover_account_info'] == false) {unset($_SESSION['need_lookover_account_info']);}

						if (isset($_SESSION['login_attempt'])) {unset($_SESSION['login_attempt']);}
					} else {
						$_SESSION['tfa_mode'] = $tfa;
						$userid = $row[2]; // tfa.inc needs this
						include ('./includes/tfa.inc.php');
						return; // with TFA, we're done for now
					}
					// Email status
					if ($user_status != 'ok') { // "unsubscribed" from site
						$_SESSION['no_status'] = true;
						header("Location: account_info.php");
						exit();
					} elseif (($set_email != $confirmed_email) || ($confirmed_email == 'Unconfirmed')) { // Unconfirmed email
						$_SESSION['email_unconfirmed'] = true;
					} elseif ((isset($_SESSION['rememberme_checked'])) && ($_SESSION['rememberme_checked'] == true)) { // Stay logged in
						include_once ('./includes/set_rememberme.inc.php');
					}

					// Indicate if the user's account is admin
					if ($_SESSION['type'] == 'admin') $_SESSION['user_is_admin'] = true;
					if ($_SESSION['type'] == 'editor') $_SESSION['user_is_editor'] = true;

					// Kill any loginonce, logincode, and rememberme keys
				  include_once ('./includes/clear_keys.inc.php');

					// Check for old confirm_change links
					$q = "SELECT id FROM confirmchange WHERE userid='$userid' AND useable='live'";
					$r = mysqli_query ($dbc, $q);
					if (mysqli_num_rows($r) > 0) {
						$_SESSION['user_must_verify'] = true;
					}
					// Check for old Partner confirm_change links
					$q = "SELECT id FROM confirmdevappchange WHERE userid='$userid' AND useable='live'";
					$r = mysqli_query ($srv_dbc, $q);
					if (mysqli_num_rows($r) > 0) {
						$_SESSION['partner_dev_must_verify'] = true;
					}
					// Check for old Partner confirm_change links
					$q = "SELECT id FROM confirmpartnerchange WHERE userid='$userid' AND useable='live'";
					$r = mysqli_query ($srv_dbc, $q);
					if (mysqli_num_rows($r) > 0) {
						$_SESSION['partner_must_verify'] = true;
					}
					// Check for Partner notices
					$q = "SELECT need_accept_new_tc, need_see_new_categories FROM partners WHERE user_id='$userid'";
					$r = mysqli_query ($dbc, $q);
					if (mysqli_num_rows($r) == 1) { // user is Partner
						$row = mysqli_fetch_array ($r, MYSQLI_NUM);
						$_SESSION['partner_need_accept_new_tc'] = $row[0];
						$_SESSION['partner_need_see_new_categories'] = $row[1];
						if ($_SESSION['partner_need_accept_new_tc'] == false) {unset($_SESSION['partner_need_accept_new_tc']);}
						if ($_SESSION['partner_need_see_new_categories'] == false) {unset($_SESSION['partner_need_see_new_categories']);}
					}
				} else { // No match was made
					$login_errors['login'] = 'The username and password do not match those on file.';
				}
			} else { // No match was made
				$login_errors['login'] = 'The username and password do not match those on file.';
			}
		} // End of $login_errors IF

	// Pass the errors to the Header
	if ((!isset($login_errors['username'])) && (!isset($login_errors['pass'])) && (!isset($login_errors['login'])) && (isset($username_set)) && (isset($password_set))) {
		$_SESSION['login_success'] = true;
		}
}
