<?php

// For storing errors
$pass_errors = array();

// If it's a POST request, handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Check existing password query normally goes here

	// Check for a password and match against the confirmed password
	if (preg_match ('/^(\w*(?=\w*\d)(?=\w*[a-z])(?=\w*[A-Z])\w*){6,20}$/', $_POST['pass1']) ) {
		if ($_POST['pass1'] == $_POST['pass2']) {
			$p = mysqli_real_escape_string ($dbc, $_POST['pass1']);
		} else {
			$pass_errors['pass2'] = 'Your password did not match the confirmed password!';
		}
	} else {
		$pass_errors['pass1'] = 'Please enter a valid password!';
	}

	if (empty($pass_errors)) { // If everything's OK.

		// Current password would normally go here, but not for the reset link

			// Update the password
			$q = "UPDATE users SET pass='"  .  password_hash($p, PASSWORD_BCRYPT) .  "' WHERE id='$userid'";
			// Disable all temporary confirmation passwords
			$qt = "UPDATE confirmpartnerchange SET useable='dead' WHERE userid='$userid'";
			if (($r = mysqli_query ($dbc, $q)) && ($rt = mysqli_query ($srv_dbc, $qt))) { // If it ran OK.

				// Set the user's _SESSION
				$_SESSION['user_id'] = $userid;

				// Send changed info email
				include ('includes/confirm_change.inc.php');

				// Redirect to Account Information
				$_SESSION['repair_info'] = '<h3 class="note_red">Review Your Account!</h3><p class="note_red">You are here because you indicated that you did not make a recent change to your account. For security, we urge you to change your <b>username</b> immediately!</p><p class="note_red">And, double-check your <b>email address</b>, that it is correct and you alone can access its inbox, change it if necessary! Then, <b>click the email confirmation link</b> sent to your email address ASAP!</p>';
				$_SESSION['user_must_verify'] = true;
				$_SESSION['partner_must_verify'] = true;
				header("Location: account_info.php");
				exit(); // Quit the script

			} else { // If it did not run OK
				sql_error("$q &&& $qt", 'dbc &&& srv_dbc', "sqle_76");

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
	echo "<br /><small>Must be between 6 and 20 characters long, with at least one lowercase letter, one uppercase letter, and one number.</small></p>
	<p><label for=\"pass2\"><strong>Confirm New Password</strong></label><br />";
	create_form_input('pass2', 'password', $pass_errors, '');
	echo "</p>
	<input type=\"submit\" name=\"submit_button\" value=\"Change &rarr;\" id=\"submit_button\" class=\"formbutton\" />
</form>";
