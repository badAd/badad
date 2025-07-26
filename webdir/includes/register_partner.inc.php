<?php

// For storing registration errors
$reg_errors = array();

// Check for a form submission
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['activation_form'])) && ($_POST['activation_form'] == 'submitted')) {

	// Check agreement requirements
	if ((!isset($_POST['tc_partner_agreement'])) || (!isset($_POST['tc_income_no_guarantee'])) || (!isset($_POST['tc_usa_ein'])) || (!isset($_POST['tc_paypal_ein'])) || (!isset($_POST['tc_tax_truth']))) {
		echo "<p class=\"note_red\">You must agree to all terms!</p>";
		return;
	} else {
		$tc_partner_agreement = true;
		$tc_income_no_guarantee = true;
		$tc_usa_ein = true;
		$tc_paypal_ein = true;
		$tc_tax_truth = true;
	}

	// Check for an email
	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$e = mysqli_real_escape_string ($dbc, $_POST['email']);
	} else {
		$reg_errors['email'] = 'Please enter a valid email address!';
	}

	if (empty($reg_errors)) { // If everything's OK...

			// Make sure the email address is available
			$q = "SELECT email, email_confirmed FROM partners WHERE email='$e'";
			$r = mysqli_query ($dbc, $q);
			// Get the number of rows returned
			$rows = mysqli_num_rows($r);
			if ($rows == 0) { // No duplicates

				// Set the all-clear
				$registerable = 1;

			} else { // Duplicate email
				$row = mysqli_fetch_array($r);
				$dup_email = $row[0];
				$dup_confirmed = $row[1];
				if ( $dup_confirmed != "Confirmed" ) { // The duplicate email is not confirmed and shall be removed
					$qd = "DELETE FROM partners WHERE email='$dup_email'";
					$rd = mysqli_query ($dbc, $qd);
					if (mysqli_affected_rows($dbc) != 1) { // Database problem
						sql_error($q, 'dbc', "sqle_55");
					}
			} else { // The email address is not available
					$reg_errors['email'] = 'This email address has already been registered with another partner account.';
				}
			} // End duplicate email check
		} // End of error checks
} // End main form submission conditional

if ((isset($registerable)) && ($registerable == 1)) {
	// Add the info in the database...
	$q = "INSERT INTO partners (user_id, email, tc_partner_agreement, tc_income_no_guarantee, tc_usa_ein, tc_paypal_ein, tc_tax_truth) VALUES ('$userid', '$e', $tc_partner_agreement, $tc_income_no_guarantee, $tc_usa_ein, $tc_paypal_ein, $tc_tax_truth)";
	$r = mysqli_query ($dbc, $q);

	if (mysqli_affected_rows($dbc) == 1) { // If it ran OK
		// Process the email confirmation if changed
		include ('includes/confirm_partner_email.inc.php');
		// Include the HTML footer
		include ('./includes/footer.html');
		exit();
	} else { // If it did not run OK
		sql_error($q, 'dbc', "sqle_79");
	}
}

// Get the registering user's email
$q = "SELECT email FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r);
$user_email = $row[0];

// define create_form_input()
require_once ('./includes/form_functions.inc.php');
echo "<h3>Partner Registration</h3>
<form id=\"partneragreementform\" class=\"userform\" action=\"$rformaction\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"activation_form\" value=\"submitted\" />
		<p><label for=\"email\"><strong>Email for PayPal payouts</strong><br />(You may optionally use a different email address here from the one registered with your account. If we need to contact you concerning payment, we may use any email address you gave us.)</label><br />";
		update_form_input('email', 'email', $reg_errors, $user_email);
		echo "</p>";
		// Disclaimers
		echo"
		<p><strong>Agree to Terms &amp; Conditions</strong><br /><input type=\"checkbox\" name=\"tc_partner_agreement\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree to the terms and conditions.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree to the <a href=\"Terms.html\">Terms &amp; Conditions</a> and <a href=\"Privacy.html\">Privacy</a>
		statement, which remain available for my reading, that they may change without further notice, that I will accept some change notices via email, and that the most current Terms & Conditions will govern my continued use of this site. I also agree that, while I may be required to agree to other specific information at this Registration, that these serve as further clarification for my benefit, but that the <a href=\"Terms.html\">Terms &amp; Conditions</a> contain full details of these agreements and shall supercede should there be any discrepancy.</p>
		<p><strong>Income and rates are not guaranteed!</strong><br /><input type=\"checkbox\" name=\"tc_income_no_guarantee\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that income and rates are not guaranteed.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that signing up as a Partner does not guarantee I will earn income. Any money I earn will depend on traffic to websites where I properly implement the code necessary to monetize using badAd.one partner tools. Even with traffic, rates I am paid per view are not guarenteed at any fixed rate, regardless of any reports of what traffic rates were in the past.</p>
		<p><strong>I am a US taxpayer or I am incorporated in the United States and have an EIN!</strong><br /><input type=\"checkbox\" name=\"tc_usa_ein\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that you are incorporated in the USA.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that I am if I so claim, I am incorporated in the United States and have an EIN (Employer Identifcation Number) provided to me by the Internal Revenue Service. If I claim to have an EIN, any income I earn as a Partner will not be reported to the IRS as \"self-employed\" and/or with a Social Security Number (SSN) or other taxpayer ID number, but only as a corporate or taxpayer entity registered with the United States, using the EIN or taxpayer information provided to me by the IRS. Furthermore, the email address I provided for PayPal payments will be used to payout earnings to my corporation with this same EIN or other taxpayer ID I will provide, not any other entity.</p>
		<p><strong>This email address is registered with PayPal and my EIN or taxpayer ID!</strong><br /><input type=\"checkbox\" name=\"tc_paypal_ein\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that you email is for a PayPal with correct taxpayer information.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that the email address I provided for PayPal payments will be used to payout earnings to my corporation with this same EIN or taxpayer ID I will use to file for taxes, not any other entity. I have properly informed PayPal, which has this EIN or taxpayer ID on file, and any bank account where I direct PayPal to send these payouts to is also an account with this same EIN or taxpayer ID on file with that bank.</p>
		<p><strong>This is accurate USA taxpayer information!</strong><br /><input type=\"checkbox\" name=\"tc_tax_truth\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that you email is for a PayPal corporate account.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that I am honestly and to the best of my ability providing tax interview information to badAd.one. I take full responsibility if it is found that anything here has not been properly understood concerning my tax information, that my email address will be reported to the IRS with any of my earnings, and I will not make any claim in any way so as to evade paying taxes.</p>

		<input type=\"submit\" name=\"submit_button\" value=\"Agree and Become a Partner\" id=\"submit_button\" class=\"formbutton\" />

</form>";
