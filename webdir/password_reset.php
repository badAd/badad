<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Login dropdown action
$login_form_action = "index.php";

// Include the header file
$page_title = "Forgot Your Password? :: $siteTitle";
include ('./includes/header.html');

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// For storing errors
$pass_errors = array();

// If it's a POST request, handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Validate the email address
	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

    // Check reCaptcha
    if ((isset($_POST['g-recaptcha-response'])) && (!empty($_POST['g-recaptcha-response']))) {
      $secret = '6Lfbe9AUAAAAAOTEuStYKboN__tYN9QPpMk5Age0'; // v2 secret
      $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
      $responseData = json_decode($verifyResponse);
      if ($responseData->success) {
        $recaptcha_success = 'reCaptcha passed'; // This doesn't go anywhere as of implementation time
				$email=$_POST['email'];
      } else {
        $pass_errors['recaptcha'] = 'Robot verification failed, please try again.';
      }
    } elseif ((isset($_POST['g-recaptcha-response'])) && (empty($_POST['g-recaptcha-response']))) {
      $pass_errors['recaptcha'] = 'If you are not a robot, you must check the box below:';
    }


		// Check for the existence of that email address...
		$q = 'SELECT id FROM users WHERE email="'.  mysqli_real_escape_string ($dbc, $_POST['email']) . '" OR confirmed_email="'.  mysqli_real_escape_string ($dbc, $_POST['email']) . '"';
		$r = mysqli_query ($dbc, $q);

		if (mysqli_num_rows($r) == 1) { // Retrieve the user ID
      $row = mysqli_fetch_array($r, MYSQLI_NUM);
			$userid = "$row[0]";
		} else { // No database match made.
			$pass_errors['email'] = 'The submitted email address does not match those on file!';
		}

	} else { // No valid address submitted
		$pass_errors['email'] = 'Please enter a valid email address!';
	} // End of $_POST['email'] IF



	if (empty($pass_errors)) { // If everything's OK

		// Create the password link
		$pstring = longDashScoreString(255);

    // Check the string to be unique
    $q = "SELECT temppass FROM temppasswords WHERE binary temppass='$pstring'";
    $row = mysqli_query ($dbc, $q);
    // while ($dup = mysqli_fetch_array($row)) {
    //   $pstring = longDashScoreString(255);
    // }
		while (mysqli_num_rows($row) != 0) {
			$pstring = longDashScoreString(255);
			// Check again
			$q = "SELECT temppass FROM temppasswords WHERE binary temppass='$pstring'";
	    $row = mysqli_query ($dbc, $q);
			if (mysqli_num_rows($row) == 0) {
				break;
			}
		}

		// Add the link to the database:
    $q = "INSERT INTO temppasswords (userid, temppass, date_dead) VALUES ('$userid', '$pstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
		$r = mysqli_query ($dbc, $q);

		if (mysqli_affected_rows($dbc) == 1) { // If it ran OK

			// Get the new user's ID from the database for the email
			$q = "SELECT id FROM users WHERE email='$email'";
			$r = mysqli_query ($dbc, $q);
			$row = mysqli_fetch_array($r, MYSQLI_NUM);
			$userid = "$row[0]";

			// Send an email
      $payloadlink = "https://$siteDomain/login_recovery.php?p=$pstring";

      $emailUserID = $userid;
      $canned_email = "forgot_password"; // Slug from the "pantry" table to select the canned email
      $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
      $payload_content = "<p><a href=\"$payloadlink\">This is the link to reset your password</a> and it will expire after 40 minutes.</p>"; // Middle of the Body, after the canned email and before the salutation
      $footer_link_content = ""; // After the salutation and before the unsubscribe footer
      include ('./includes/sendusrmail.inc.php');

			// Print a message and wrap up
			echo '<h3>Email sent</h3><p>An email has been sent to that address with a link to create a new password. Hurry up! That link will expire in about 42 minutes.</p>';
      unset($_SESSION['user_id']);
      include ('./includes/footer.html');
			exit(); // Stop the script

		} else { // If it did not run OK
			sql_error($q, 'dbc', "sqle_61");
		}

	} // End of error check

} // End of the main Submit conditional

// Need the form functions script, which defines create_form_input()
require_once ('./includes/form_functions.inc.php');

echo '
<h3>Reset Your Password</h3>
<p>Enter your email address below to receive a link to reset your password.</p>
<form action="password_reset.php" method="post" accept-charset="utf-8">
	<p><label for="email"><strong>Email Address</strong></label><br />';
  create_form_input('email', 'text', $pass_errors, '');
  echo '</p>';

  // reCaptcha
  if (isset($pass_errors['recaptcha'])) {echo '<p class="error">'.$pass_errors['recaptcha'].'</p>';} // Echo an error message
  echo '<div id="rcpt_check"></div><script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script><br />'; // The reCaptcha v2 box

  echo '
	<input type="submit" name="submit_button" value="Reset &rarr;" id="submit_button" class="formbutton" />
</form>';

 // Include the HTML footer
include ('./includes/footer.html');
?>
