<?php

// Process & respond to the New Ad entry

// Check agreement requirements
if ((!isset($_POST['tc_signup'])) || (!isset($_POST['tc_honesty'])) || (!isset($_POST['tc_tags'])) || (!isset($_POST['tc_spam'])) || (!isset($_POST['tc_tm'])) || (!isset($_POST['tc_norefund']))) {
	echo "You must agree to all terms!";
	return;
} else {
	$tc_signup = true;
	$tc_honesty = true;
	$tc_tags = true;
	$tc_spam = true;
	$tc_tm = true;
	$tc_norefund = true;
	$tc_beta = true;
}

// Check reCaptcha
if ((isset($_POST['g-recaptcha-response'])) && (!empty($_POST['g-recaptcha-response']))) {
	$secret = '6Lfbe9AUAAAAAOTEuStYKboN__tYN9QPpMk5Age0'; // v2 secret
	$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
	$responseData = json_decode($verifyResponse);
	if ($responseData->success) {
		$recaptcha_success = 'reCaptcha passed'; // This doesn't go anywhere as of implementation time
	} else {
		$registr_errors['recaptcha'] = 'Robot verification failed, please try again.';
	}
} elseif ((isset($_POST['g-recaptcha-response'])) && (empty($_POST['g-recaptcha-response']))) {
	$registr_errors['recaptcha'] = 'If you are not a robot, you must check the box below:';
}

// Check for a name
if (preg_match ('/^[A-Z \'.-]{1,80}$/i', $_POST['name'])) {
	$na = mysqli_real_escape_string ($dbc, $_POST['name']);
} else {
	$registr_errors['name'] = 'Please enter your name, only letters and hyphens, 80 characters max!';
}

// Check for a Project
if (preg_match ('/^[A-Z0-9 \'.-]{0,80}$/i', $_POST['project'])) {
	$pr = mysqli_real_escape_string ($dbc, $_POST['project']);
} else {
	$registr_errors['project'] = 'Project may use letters, numbers, periods, and hyphens only, 80 characters max!';
}

// Check for a username
if (preg_match ('/^[A-Z0-9]{6,32}$/i', $_POST['username'])) {
	$u = mysqli_real_escape_string ($dbc, $_POST['username']);
} else {
	$registr_errors['username'] = 'Please enter a valid username, 6-32 characters!';
}

// Check for an email and match against the confirmed email
if ((filter_var($_POST['email1'], FILTER_VALIDATE_EMAIL)) && (filter_var($_POST['email2'], FILTER_VALIDATE_EMAIL))) {
	if ($_POST['email1'] == $_POST['email2']) {
		$e = strtolower($_POST['email1']);
		$e = mysqli_real_escape_string ($dbc, $e);
	} else {
		$registr_errors['email2'] = 'Your email addresses did not match!';
	}
} else {
	$registr_errors['email1'] = 'Please enter a valid email address, 90 characters max!';
}

// Check for a password and match against the confirmed password
// Old preg_match: '/^(\w*(?=\w*\d)(?=\w*[a-z])(?=\w*[A-Z])\w*){6,32}$/'
if (preg_match ('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z!@&#$%]{6,32}$/', $_POST['pass1']) ) {
	if ($_POST['pass1'] == $_POST['pass2']) {
		$p = $_POST['pass1'];
	} else {
		$registr_errors['pass2'] = 'Your passwords did not match!';
	}
} else {
	$registr_errors['pass1'] = 'Please enter a valid password!';
}

if (empty($registr_errors)) { // If everything's OK...

	// Make sure the email address and username are available
	$q = "SELECT email, username FROM users WHERE email='$e' OR username='$u'";
	$r = mysqli_query ($dbc, $q);

	// Get the number of rows returned
	$rows = mysqli_num_rows($r);

	if ($rows == 0) { // No dups!

		// Generate the ridiculously long random string
		require_once ('./includes/string_functions.inc.php');
		$sec_key = longString(64);
		// Dup check
		$q = "SELECT sec_key FROM users WHERE binary sec_key='$sec_key'"; // "binary" makes sure case and characters are exact
		$row = mysqli_query ($dbc, $q);
		// while ($dup = mysqli_fetch_array($row)) {
		//   $sec_key = longString(64);
		// }
		while (mysqli_num_rows($row) != 0) {
			$sec_key = longString(64);
			// Check again
			$q = "SELECT sec_key FROM users WHERE binary sec_key='$sec_key'"; // "binary" makes sure case and characters are exact
			$row = mysqli_query ($dbc, $q);
			if (mysqli_num_rows($row) == 0) {
				break;
			}
		}

		// Add the user to the database...
		$q = "INSERT INTO users (username, email, pass, name, project, tc_signup, tc_honesty, tc_tags, tc_spam, tc_tm, tc_norefund, tc_beta, sec_key)
		VALUES ('$u', '$e', '"  .  password_hash($p, PASSWORD_BCRYPT) .  "', '$na', '$pr', $tc_signup, $tc_honesty, $tc_tags, $tc_spam, $tc_tm, $tc_norefund, $tc_beta, '$sec_key')";
		$r = mysqli_query ($dbc, $q);

		if (mysqli_affected_rows($dbc) == 1) { // If it ran OK

			// Get the user ID
			// Store the new user ID in the session
			//$uid = mysqli_insert_id($dbc);
			//$_SESSION['reg_user_id']  = $uid;

			// Cleanup
			unset($registr_errors);

			// Get the new user's ID from the database and set it in the session for Logged-in status
			$q = "SELECT id, username, type, name FROM users WHERE username='$u'";
			$r = mysqli_query ($dbc, $q);
			$row = mysqli_fetch_array($r, MYSQLI_NUM);
			// Store the data in a session
			$_SESSION['user_id'] = $row[0];
			$_SESSION['username'] = $row[1];
			$_SESSION['type'] = $row[2];
			$_SESSION['reguser_name'] = $row[3];
			$_SESSION['just_registered'] = true;

			// Freekey
			if (isset($_SESSION['reg_key_id'])) {
				$_SESSION['just_registered_freekey'] = true;
			}

			// emailwrongunsubscribe key
			include ('includes/emailwrong_create.inc.php');

			// Send the email
			$confirm_email = true; // For confirmation email addressess
			$canned_email = "register"; // Slug from the "pantry" table to select the canned email
			$subject_suffix = ": $siteTitle"; // Appends the canned email Subject
			$payload_content = "<p>Username: $u</p>"; // Middle of the Body, after the canned email and before the salutation
			$footer_link_content = ""; // After the salutation and before the unsubscribe footer
			include ('./includes/sendusrmail.inc.php');

			// Process the email confirmation
			//used to be here: include ('includes/confirm_email.inc.php'); // BUT NO email confirmation until first purchase with join_rank


		} else { // If it did not run OK
			sql_error($q, 'dbc', "sqle_75");
		}

	} else { // The email address or username is not available

		if ($rows == 2) { // Both are taken

			$registr_errors['email1'] = 'This email address has already been registered. If you have forgotten your password, use the link at right to have your password sent to you.';
			$registr_errors['username'] = 'This username has already been registered. Please try another.';

		} else { // One or both may be taken

			// Get row
			$row = mysqli_fetch_array($r, MYSQLI_NUM);

			if( ($row[0] == $_POST['email1']) && ($row[1] == $_POST['username'])) { // Both match
				$registr_errors['email1'] = 'This email address has already been registered. If you have forgotten your password, use the link at right to have your password sent to you.';
				$registr_errors['username'] = 'This username has already been registered with this email address. If you have forgotten your password, use the link at right to have your password sent to you.';
			} elseif ($row[0] == $_POST['email1']) { // Email match
				$registr_errors['email1'] = 'This email address has already been registered. <a href=\"forgot_password.php\" align=\"right\">Forgot your password?</a>';
			} elseif ($row[1] == $_POST['username']) { // Username match
				$registr_errors['username'] = 'This username has already been registered. Please try another.';
			}

		} // End of $rows == 2 ELSE

	} // End of $rows == 0 IF

} // End of empty($registr_errors) IF
