<?php

// For storing errors
$pass_errors = array();

// If it's a POST request, handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Check existing password query normally goes here

	// Check for a password and match against the confirmed password
	if (preg_match ('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z!@&#$%]{6,32}$/', $_POST['pass1']) ) {
		if ($_POST['pass1'] == $_POST['pass2']) {
			$p = mysqli_real_escape_string ($dbc, $_POST['pass1']);
		} else {
			$pass_errors['pass2'] = 'Your password did not match the confirmed password!';
		}
	} else {
		$pass_errors['pass1'] = 'Please enter a valid password!';
	}

	// Check reCaptcha
	if ((isset($_POST['g-recaptcha-response'])) && (!empty($_POST['g-recaptcha-response']))) {
		$secret = '6Lfbe9AUAAAAAOTEuStYKboN__tYN9QPpMk5Age0'; // v2 secret
		$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
		$responseData = json_decode($verifyResponse);
		if ($responseData->success) {
			$recaptcha_success = 'reCaptcha passed'; // This doesn't go anywhere as of implementation time
		} else {
			$pass_errors['recaptcha'] = 'Robot verification failed, please try again.';
		}
	} elseif ((isset($_POST['g-recaptcha-response'])) && (empty($_POST['g-recaptcha-response']))) {
		$pass_errors['recaptcha'] = 'If you are not a robot, you must check the box below:';
	}

	if (empty($pass_errors)) { // If everything's OK.

		// Current password would normally go here, but not for the reset link

			// Update the password
			$q = "UPDATE users SET pass='"  .  password_hash($p, PASSWORD_BCRYPT) .  "' WHERE id='$userid'";
			// Disable the temporary password
			$qt = "UPDATE temppasswords SET useable='dead' WHERE useable='live' AND userid='$userid'";
			if (($r = mysqli_query ($dbc, $q)) && ($rt = mysqli_query ($dbc, $qt))) { // If it ran OK.

				// Let the user know the password has been changed
				echo '<h3>Your password has been changed.</h3><p>You may now <a title="Login" href="login.php">login</a>.</p>';
				// Process the email confirmation
				include ('includes/confirm_change.inc.php');
				// Include the HTML footer
				include ('./includes/footer.html');
				exit();

			} else { // If it did not run OK
				sql_error("$q &&& $qt", 'dbc', "sqle_77");

			} // End of the SQL new password update

	} // End of error checks

} // End of the form submission conditional

// Need the form functions script, which defines create_form_input()
echo "<h3>Change Your Password</h3>
<p>Use the form below to change your password.</p>
<form action=\"$rformaction\" method=\"post\" accept-charset=\"utf-8\">";
	// Current password would normally go here, but not for the reset link
	echo "<p><label for=\"pass1\"><strong>New Password</strong></label><br />";
	create_form_input('pass1', 'password', $pass_errors, '');
	echo "<br /><small>6-32 characters, one lowercase letter, one uppercase letter, one number, also allowed: ! @ & # $ %</small></p>
	<p><label for=\"pass2\"><strong>Confirm New Password</strong></label><br />";
	create_form_input('pass2', 'password', $pass_errors, '');
	echo "</p>";

	// reCaptcha
  if (isset($pass_errors['recaptcha'])) {echo '<p class="error">'.$pass_errors['recaptcha'].'</p>';} // Echo an error message
  echo '<div id="rcpt_check"></div><script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script><br />'; // The reCaptcha v2 box


	echo "
	<input type=\"submit\" name=\"submit_button\" value=\"Change &rarr;\" id=\"submit_button\" class=\"formbutton\" />
</form>";
