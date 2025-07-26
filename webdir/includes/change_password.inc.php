<?php

// For storing errors
$pass_errors = array();

// If it's a POST request, handle the form submission
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['pass1'])) && (isset($_POST['pass2']))) {

	// Check for the existing password
	if (!empty($_POST['current'])) {
		$current = mysqli_real_escape_string ($dbc, $_POST['current']);
	} else {
		$pass_errors['current'] = 'Please enter your current password!';
	}

	// Check for a password and match against the confirmed password
	// Old preg_match: '/^(\w*(?=\w*\d)(?=\w*[a-z])(?=\w*[A-Z])\w*){6,32}$/'
	if (preg_match ('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z!@&#$%]{6,32}$/', $_POST['pass1']) ) {
		if ($_POST['pass1'] == $_POST['pass2']) {
			$p = $_POST['pass1'];
		} else {
			$pass_errors['pass2'] = 'Your passwords did not match!';
		}
	} else {
		$pass_errors['pass1'] = 'Please enter a valid password!';
	}

	if (empty($pass_errors)) { // If everything is OK

			// Current password
			$q = "SELECT pass FROM users WHERE id={$_SESSION['user_id']}";
			$r = mysqli_query ($dbc, $q);
			$row = mysqli_fetch_array ($r, MYSQLI_NUM);
			$hp = $row[0];
			if (password_verify($current, $hp)) { // Correct

				$allwell_dochange = true;

			} else {

				$pass_errors['current'] = 'Your current password is incorrect!';

			} // End of current password ELSE

		if ((isset($allwell_dochange)) && ($allwell_dochange == true)) {
			// Update the password
			$q = "UPDATE users SET pass='"  .  password_hash($p, PASSWORD_BCRYPT) .  "' WHERE id={$_SESSION['user_id']} LIMIT 1";
			if ($r = mysqli_query ($dbc, $q)) { // If it ran OK.

				// Process the email confirmation
				include ('./includes/confirm_change.inc.php');

				// Let the user know the password has been changed
				echo '<h3 class="note_green">Your password has been changed.</h3>';

				// Include the HTML footer
				include ('./includes/footer.html');
				exit();

			} else { // If it did not run OK
				sql_error($q, 'dbc', "sqle_72");

			} // End of the SQL new password update

		} // End of $allwell_dochange

	} // End of error checks

} // End of the form submission conditional

// Need the form functions script, which defines create_form_input()
require_once ('./includes/form_functions.inc.php');
echo "<h3>Change Your Password</h3>
<p>Use the form below to change your password.</p>
<form action=\"$rformaction\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"pid\" value=\"$userid\" />";
	// Current password
		echo "<p><label for=\"current\"><strong>Current Password</strong></label><br />";
		create_form_input('current', 'password', $pass_errors, '');
		echo "</p>";

	echo "<p><label for=\"pass1\"><strong>New Password</strong></label><br />";
	create_form_input('pass1', 'password', $pass_errors, '');
	echo "<br /><small>6-32 characters, one lowercase letter, one uppercase letter, one number, also allowed: ! @ & # $ %</small></p>
	<p><label for=\"pass2\"><strong>Confirm New Password</strong></label><br />";
	create_form_input('pass2', 'password', $pass_errors, '');
	echo "</p>
	<input type=\"submit\" name=\"submit_button\" value=\"Change &rarr;\" id=\"submit_button\" class=\"formbutton\" />
</form>";
