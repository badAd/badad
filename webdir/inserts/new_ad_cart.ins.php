<?php

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Starting
if (isset($userid)) {
	if ($after_ad_date_to_start == 'right_now') {
		$new_ad_date_starts = $timeNow;
		$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;
		$pretty_after_ad_date_to_start = 'Starts: <b>Immediately</b>';
	} elseif ($after_ad_date_to_start == 'cloned') {
		$new_ad_date_starts = $timeNow;
		$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;
		if ($_SESSION['cloned_status'] == 'expired') {
			$pretty_after_ad_date_to_start = 'Starts: <b>Immediately</b>';
		} elseif ($_SESSION['cloned_status'] == 'running') {
			$pretty_after_ad_date_to_start = 'Rerun starts: <i><b>'.$new_ad_date_starts.'</b></i>';
		}
	} else {
		$new_chase_ad_id = $after_ad_date_to_start;
		$_SESSION['new_chase_ad_id'] = $new_chase_ad_id;
		$qs = "SELECT ad_nickname, date_expires FROM ads WHERE user_id='$userid' AND id='$after_ad_date_to_start'";
		$rs = mysqli_query ($dbc, $qs);
		$rows = mysqli_fetch_array($rs, MYSQLI_NUM);
		$chase_ad_nickname = "$rows[0]";
		$new_ad_date_starts = "$rows[1]";
		$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;
		$pretty_after_ad_date_to_start = 'Starts on the heels of: <b>"'.$chase_ad_nickname.'" at <i>'.$new_ad_date_starts.'</i></b>';
	}
} else {
	$pretty_after_ad_date_to_start = 'Starts: <b>Immediately</b>';
	$new_ad_date_starts = $timeNow;
	$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;
}

// Pretty weeks
if ($new_ad_weekslong == 1) {
	$pretty_new_ad_weekslong = "$new_ad_weekslong week";
} elseif ($new_ad_weekslong > 1) {
	$pretty_new_ad_weekslong = "$new_ad_weekslong weeks";
}

// Beta boost?
if ($totalAds <= 1000000) {
	$pretty_new_ad_weekslong .='*';
}

// Preview the New Ad content
echo "
<div class\"order_preview_label full center\">";
	if ($new_ad_pod_listing == 'pod') {
		echo "<div class=\"info center\">Category: <b>$categoryName</b> - <i class=\"bizn_yn\">podcast & business listing</i></div>";
	} elseif ($new_ad_biz_listing == 'biz') {
		echo "<div class=\"info center\">Category: <b>$categoryName</b> - <i class=\"bizn_yn\">business listing</i></div>";
	} else {
		echo "<div class=\"info center\">Category: <b>$categoryName</b> - <i class=\"bizn_yn\">normal listing</i></div>";
	}
echo "
<div class=\"info center\">Subcategory: <b>$subcatName</b></div>
<div class=\"info center\">Tags: <b>$new_ad_tagList</b></div>
<div class=\"info center\">Advertising as: <b>$roleName</b></div>
<div class=\"info center\">For: <b>$pretty_new_ad_weekslong</b></div>
<div class=\"info center\">$pretty_after_ad_date_to_start</div>
<div class=\"info center\">Nicknamed: <b>\"$new_ad_nickname\"</b></div>
<div class=\"info center\">Contact URL: <b>$new_ad_contactURL</b></div>
</div>
<br /><br />
<div class=\"info center\">Preview of your ad:</div>
<br />
<div class=\"ad_preview\" style=\"text-align: center;\"><hr /><br />$adContent<br /><hr /></div>";


// Podcast manuscript
if ((isset($new_ad_content_pdcst)) && ($new_ad_pod_listing == 'pod')) {
	echo "<div class=\"info center\">
	<br />
	<p>Podcast ad manuscript:</p>
	<p><i>$new_ad_content_pdcst</i></p>
	<p><i>###</i></p>
	</div>";
}

// Approval
	// We need the form functions so "back" will clear the valid ad
	require_once ('./includes/form_functions.inc.php');
if (isset($rerunAd)) {
	echo "<br />
	<div class=\"validadapproval\">";
	// Weeks long
	echo '
	<div class="center">
	<form action="rerun_ad_cart.php" method="post" accept-charset="utf-8" class="newadform">
		<b class="weekslong">This ad is set to rerun<br /><br />for:</b>
		<select class="formselect" name="weekslong" required> ';
		// Start creating the "Weeks long" select option
			// Add the value to the input if it is there
			// First, get the SESSIONed $value, if it exists
			if (isset($_SESSION['new_ad_weekslong'])) {
				$value = $_SESSION['new_ad_weekslong'];
			} else {
				$value = '';
			}
			// "Weeks long" select option
			// Count to 8
			if ($new_ad_biz_listing == 'biz') {
				$countTo = 53;
			} else {
				$countTo = 8;
			}
			$count = 1;
			while ($count <= $countTo) {
				// Iterate each value as a select option
				if ($value == $count) {
					echo '<option value="'.$count.'" selected>'.$count.'</option>';
				} else {
					echo '<option value="'.$count.'">'.$count.'</option>';
				}

			$count++;
			}
		// Include the category slug so we don't trip the empty _POST check on new ads
		echo '<input type="hidden" name="ctgr" value="'.$cat.'" />';
		// End the select options
		echo '</select><b> weeks</b><br /><br />
		<input type="submit" name="submit_button" value="Update weeks" id="submit_button" class="formbutton" />
		<br /><br />
		</form></div>';

		// Convert to new?
		echo "
		<div class=\"center\">";
		set_switch("Convert this to a new ad to make changes", "Make this a new ad so you can make changes", "new_ad_cart_back.act.php", "c", $cat, "set_gray");
		echo "
		<br />
		</div>";

		// Disclaimer
		echo "<p class =\"note_gray\">Verify your ad carefully because <em>it cannot be changed after purchase!</em> As agreed to in the <a href=\"Terms.html\">Terms &amp; Conditions</a>, all sales are final and no refunds are given under any circumstances! Tags are subject to change.</p>";
		echo "
	</div>";
} else {
		echo "<br />
		<div class=\"validadapproval\">";

			// Go back?
			echo "
			<div class=\"center\">";
			set_switch("&larr; Go back & edit", "Go back to make changes to this ad", "new_ad_cart_back.act.php", "c", $cat, "set_gray");
			echo "
			<br />
			</div>";

			// Disclaimer
			echo "<p class =\"note_gray\">Verify your ad carefully because <em>it cannot be changed after purchase!</em> As agreed to in the <a href=\"Terms.html\">Terms &amp; Conditions</a>, all sales are final and no refunds are given under any circumstances! Tags are subject to change.</p>";
		  echo "
		</div>";
}

// Price totals displayed at checkout
echo "
<div class=\"inline full money\">";
	// Price Total
	echo "<div class=\"inline left pricing\">";
	require ('./includes/calculate_total.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
	echo "</div>";
	// Use Credits
	echo "<div class=\"inline right info\">";
	// This must be set for credit.inc.php to work, but new_ad_cart and rerun_ad_cart both use this
	if (isset($rerunAd)) {
		$sformaction = 'rerun_ad_cart.php';
	} else {
		$sformaction = 'new_ad_cart.php';
	}
	require ('./includes/credit.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
	echo "</div>";
echo "
</div>"; // End money box

// New and non-logged-in users see login and registration forms
if (!isset($_SESSION['user_id'])) {

	 // REFERRAL VERSION
	 // Log in form (Referrals excluded)
	 if ((!isset($_POST['registerform'])) && (!isset($_SESSION['refUserID'])) && (!isset($_SESSION['rSlug']))) { // Don't show this or run the error checks if the register form was submitted
		 echo "<h5 class=\"loginNotice\">Login (returning users)</h>";
		 $lformaction = 'new_ad_cart.php'; // This must be set for login_form.inc.php to work
		 require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
 } elseif ((isset($_SESSION['refUserID'])) && (isset($_SESSION['rSlug']))) {
	 	echo "<br />Referred user credit applies to new accounts only. Sign-up here...<br /><br />";
 } // REFERRED

	/*REFERRAL VERSION REPLACES THIS
	 // Log in form
	 if (!isset($_POST['registerform'])) { // Don't show this or run the error checks if the register form was submitted
		 echo "<h5 class=\"loginNotice\">Login (returning users)</h>";
		 $lformaction = 'new_ad_cart.php'; // This must be set for login_form.inc.php to work
		 require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
	}
	REFERRAL COMMENTS THIS*/

	 // Registration form
	 if (!isset($_POST['loginform'])) { // Don't show this or run the error checks if the login form was submitted
		 echo "<h5 class=\"loginNotice\">Signup (new users)</h5>";
		 $rformaction = 'new_ad_cart.php'; // This must be set for register.inc.php to work
		 require ('./includes/register.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
 	}
}

// This must be a separate IF test, not an ELSE or ELSEIF because of the script flow
//// The previous IF tests set the SESSION user_id, now we test to see if it is there to show the form without reloading the page

// Logged in users see the payment form
if (isset($_SESSION['user_id'])) {

// User ID
$userid = $_SESSION['user_id'];

// Pay or free?
$pformaction = 'checkout.php'; // This must be set for stripe_form.inc.php and free_form.inc.php to work
	// Free button or freekey
	if (($adPricePaying == "0.00") || (isset($_SESSION['purchase_key_id']))) {
		require ('./includes/free_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
	// Stripe
	} else {

		// Errors?
		if (isset($_SESSION['stripe_error'])) {
			$error_pending_save_message = "<p>You're welcome to try again. No worries. Your ad ($new_ad_nickname) is saved in your <a title=\"View your order history\" href=\"order_history.php\">Order History</a> as \"pending\".</p>";
			if (isset($_SESSION['error1'])) {$error1 = $_SESSION['error1']; echo "<div class=\"inline full payment_error\"><p class=\"note_red\"><b>We had trouble:</b> $error1</p>$error_pending_save_message</div>"; unset($_SESSION['error1']);}
			if (isset($_SESSION['error2'])) {$error2 = $_SESSION['error2']; echo "<div class=\"inline full payment_error\"><p class=\"note_red\"><b>We had trouble:</b> $error2</p>$error_pending_save_message</div>"; unset($_SESSION['error2']);}
			if (isset($_SESSION['error3'])) {$error3 = $_SESSION['error3']; echo "<div class=\"inline full payment_error\"><p class=\"note_red\"><b>We had trouble:</b> $error3</p>$error_pending_save_message</div>"; unset($_SESSION['error3']);}
			if (isset($_SESSION['error4'])) {$error4 = $_SESSION['error4']; echo "<div class=\"inline full payment_error\"><p class=\"note_red\"><b>We had trouble:</b> $error4</p>$error_pending_save_message</div>"; unset($_SESSION['error4']);}
			if (isset($_SESSION['error5'])) {$error5 = $_SESSION['error5']; echo "<div class=\"inline full payment_error\"><p class=\"note_red\"><b>We had trouble:</b> $error5</p>$error_pending_save_message</div>"; unset($_SESSION['error5']);}
			if (isset($_SESSION['error6'])) {$error6 = $_SESSION['error6']; echo "<div class=\"inline full payment_error\"><p class=\"note_red\"><b>We had trouble:</b> $error6</p>$error_pending_save_message</div>"; unset($_SESSION['error6']);}
			if (isset($_SESSION['error7'])) {$error7 = $_SESSION['error7']; echo "<div class=\"inline full payment_error\"><p class=\"note_red\"><b>We had trouble:</b> $error7</p>$error_pending_save_message</div>"; unset($_SESSION['error7']);}
			unset($_SESSION['stripe_error']);

			// Send the declined email
			$canned_email = "payment_declined"; // Slug from the "pantry" table to select the canned email
			$subject_suffix = " - $siteTitle"; // Appends the canned email Subject
			if (isset($rSlug)) { // Referred? Create link again
				$referred_link = "https://badad.one/referred.php?l=$rSlug";
				$payload_content = "<p>You were referred! When you are ready to finish purchasing your ad, login, then paste this URL into your browser to re-activate your one-time referral credit: <a href=\"$referred_link\">$referred_link</a></p><p>Ad Nickname: <b>\"$new_ad_nickname\"</b></p>"; // Middle of the Body, after the canned email and before the salutation
			} else {
				$payload_content = "<p>Ad Nickname: <b>\"$new_ad_nickname\"</b></p>"; // Middle of the Body, after the canned email and before the salutation
			}
			$footer_link_content = ""; // After the salutation and before the unsubscribe footer
			include ('./includes/sendusrmail.inc.php');
		}

		// Stripe form
		require ('./includes/stripe_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work

	}
}
