<?php


if (!isset($_SESSION['tfa_mode'])) {
	return;
} else {
	include_once ('includes/string_functions.inc.php');
}


if ($_SESSION['tfa_mode'] == 'email_link') {

		$newString = longDashScoreString(255);
			// Dup check
			$q = "SELECT temppass FROM loginonce WHERE binary temppass='$newString'"; // "binary" makes sure case and characters are exact
			$row = mysqli_query ($dbc, $q);
			// while ($dup = mysqli_fetch_array($row)) {
			// 	$newString = longDashScoreString(255);
			// }
			while (mysqli_num_rows($row) != 0) {
				$newString = longDashScoreString(255);
				// Check again
				$q = "SELECT temppass FROM loginonce WHERE binary temppass='$newString'"; // "binary" makes sure case and characters are exact
				$row = mysqli_query ($dbc, $q);
				if (mysqli_num_rows($row) == 0) {
					break;
				}
			}
		// Add the link to the database:
		$q = "INSERT INTO loginonce (userid, temppass, date_dead) VALUES ('$userid', '$newString', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
		$r = mysqli_query ($dbc, $q);
		if (mysqli_affected_rows($dbc) == 1) { // If it ran OK
			$payloadlink = "https://$siteDomain/login_once.php?p=$newString";
			$emailUserID = $userid; // For non-logged-in users, viz password_reset
			$canned_email = "email_link"; // Slug from the "pantry" table to select the canned email
			$subject_suffix = " - $siteTitle"; // Appends the canned email Subject
			$payload_content = "<p><a href=\"$payloadlink\">LOGIN LINK</a></p>"; // Middle of the Body, after the canned email and before the salutation
			$footer_link_content = ""; // After the salutation and before the unsubscribe footer
			include ('./includes/sendusrmail.inc.php');
			$_SESSION['tfa_email_link'] = true;
			$_SESSION['login_attempt'] = 0; // Fresh start on success

			return;
		}

} elseif ($_SESSION['tfa_mode'] == 'email_code') {

		$newString = digitString(8);
			// No Dup check for small codes
		// Add the link to the database:
		$q = "INSERT INTO logincode (userid, temppass, date_dead) VALUES ('$userid', '$newString', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
		$r = mysqli_query ($dbc, $q);
		if (mysqli_affected_rows($dbc) == 1) { // If it ran OK
			$canned_email = "email_code"; // Slug from the "pantry" table to select the canned email
			$emailUserID = $userid; // For non-logged-in users, viz password_reset
			$subject_suffix = ": $newString"; // Appends the canned email Subject
			$payload_content = "<br /><b><pre>$newString</pre></b><br />"; // Middle of the Body, after the canned email and before the salutation
			$footer_link_content = ""; // After the salutation and before the unsubscribe footer
			include ('./includes/sendusrmail.inc.php');
			$_SESSION['tfa_email_code'] = true;
			$_SESSION['tfa_email_code_userid'] = $userid;
			$_SESSION['login_attempt'] = 0; // Fresh start on success

			return;
		}

} elseif ($_SESSION['tfa_mode'] == 'sms_code') {

		// Do something here

		$_SESSION['tfa_sms_code'] = true;
		$_SESSION['tfa_sms_code_userid'] = $userid;
		$_SESSION['login_attempt'] = 0; // Fresh start on success

		return;

} elseif ($_SESSION['tfa_mode'] == 'google_auth') {

		// Do something here

		$_SESSION['tfa_google_auth'] = true;
		$_SESSION['tfa_google_auth_userid'] = $userid;
		$_SESSION['login_attempt'] = 0; // Fresh start on success

		return;

} elseif ($_SESSION['tfa_mode'] == 'app_tap') {

		// Do something here

		$_SESSION['tfa_app_tap'] = true;
		$_SESSION['tfa_app_tap_userid'] = $userid;
		$_SESSION['login_attempt'] = 0; // Fresh start on success

		return;

}
