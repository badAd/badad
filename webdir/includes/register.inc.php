<?php

// For storing registration errors
if (!isset($registr_errors)) {$registr_errors = array();}

// Check for a form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
require_once ('./includes/register_check.inc.php');

} // End of the main form submission conditional

// Make sure the user is not logged in or just registered
if (!isset($_SESSION['user_id'])) {

// define create_form_input()
require_once ('./includes/form_functions.inc.php');
echo "<h3>Register</h3>
<form id=\"registerform\" class=\"userform\" action=\"$rformaction\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"registerform\" value=\"submitted\" />

		<p><label for=\"name\"><strong>Name</strong></label><br /><small>For your reference and correspondence, legal name not required</small><br />";
		create_form_input('name', 'text', $registr_errors);
		echo "</p>

		<p><label for=\"project\"><strong>Project</strong></label><br /><small>Optional, for your reference only, eg: company, project, campaign, team, department, etc</small><br />";
		create_form_input('project', 'text', $registr_errors);
		echo "</p>

		<p><label for=\"username\"><strong>Username</strong></label><br /><small>6-32 characters, only letters and numbers, case doesn't matter</small><br />";
		create_form_input('username', 'text', $registr_errors);
		echo "</p>

		<p><label for=\"email1\"><strong>Email</strong></label><br />";
		create_form_input('email1', 'email', $registr_errors);
		echo "</p>
		<p><label for=\"email2\"><strong>Double-Check Email</strong></label><br />";
		create_form_input('email2', 'email', $registr_errors);
		echo "</p>

		<p><label for=\"pass1\"><strong>Password</strong></label><br /><small>6-32 characters, one lowercase letter, one uppercase letter, one number, also allowed: ! @ & # $ %</small><br />";
		create_form_input('pass1', 'password', $registr_errors);
		echo "</p>
		<p><label for=\"pass2\"><strong>Confirm Password</strong></label><br />";
		create_form_input('pass2', 'password', $registr_errors);
		echo "</p>";
		// Disclaimers
		echo"
		<p><strong>Laws, Safety &amp; Honesty</strong><br /><input type=\"checkbox\" name=\"tc_honesty\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree to Truth &amp; Honesty.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that I am responsible for behing honest and for the truthfulness and for legality of any and all information, products, and/or services relating to ads I may purchase and that I bear sole responsibility for any any and all implications thereof. I will not run ads to stalk, \"dox\", defame, threaten, harass, steal intellectual property, put minors at risk, etc., and I will be prosecuted if I run advertisements that break the law.</p>
		<p><strong>Tags may change</strong><br />Tags are managed by $siteTitle for the purposes of 1. identifying overlapping relevance among ads in searches or other indexing and 2. consolidating multiple tags into a single tag so as to have no more tags than necessary, such as combining singular and plural spellings of the same word into one tag among other variances that carry little-to-no meaningful difference. Upon review, some tags you choose at purchase may be removed, replaced, have a change of spelling, and other tags may be added.<br />
		<input type=\"checkbox\" name=\"tc_tags\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that tags may be changed.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that any \"tags\" I choose may be changed.</p>
		<p><strong>Agree to Terms &amp; Conditions</strong><br /><input type=\"checkbox\" name=\"tc_signup\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree to the terms and conditions.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree to the <a href=\"Terms.html\">Terms &amp; Conditions</a> and <a href=\"Privacy.html\">Privacy</a>
		statement, which remain available for my reading, that they may change without further notice, that I will accept some change notices via email, and that the most current Terms & Conditions will govern my continued use of this site. I also agree that, while I may be required to agree to other specific information at this Registration, that these serve as further clarification for my benefit, but that the <a href=\"Terms.html\">Terms &amp; Conditions</a> contain full details of these agreements and shall supercede should there be any discrepancy.</p>

<p><strong>Quality, No spam</strong><br /><input type=\"checkbox\" name=\"tc_spam\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that you will not be spammy.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that, if I advertise the same thing twice (including different things from the same store twice, etc.,) my ads may be deleted without refund or notice. I also understand that, as clarified in the <a href=\"Terms.html\">Terms &amp; Conditions</a>, the use of language for an ad must fit within \"native speaker\" use or understandable alternatives thereof for said language, according to stated accredited institution-recognized style and use standards. Creativity with use of language is welcome, but not so unusual as to create confusion for the reader. Use of languae that, in our sole discretion, may seem foreign and thus confusing and/or distracting to readers will be deleted without refund or notice.</p>
<p><strong>Obey the law</strong><br /><input type=\"checkbox\" name=\"tc_tm\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that you will not violate trademark or other law.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that will not pretend to be another business nor will I violate trademark or any other law on this site. My ads may be used against me if I use them to violate the law.</p>

		<p><strong>All Sales are Final! No changes!</strong><br /><input type=\"checkbox\" name=\"tc_norefund\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that all sales are final.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that all sales are final and no refunds are given under any circumstances! I cannot make any changes to an ad after purchasing. I may \"pull\" (or \"kill\") my ad if it becomes irrelevant, but this will not result in a refund of any kind.</p>
		<p><strong>This is a \"beta\" program</strong><br /><input type=\"checkbox\" name=\"tc_beta\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must understand and agree that you are entering a beta program.')\" onchange=\"this.setCustomValidity('')\"/> I understand and agree that I am joining a \"beta\" program, which means that everything I do could be lost. By participating early, I may be entitled to \"early bird\" perks, including higher payout rates and lower prices, but while in this beta stage, there are no such guarentees.</p>";

		// reCaptcha
	  if (isset($registr_errors['recaptcha'])) {echo '<p class="error">'.$registr_errors['recaptcha'].'</p>';} // Echo an error message
	  echo '<div id="rcpt_check"></div><script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script><br />'; // The reCaptcha v2 box

		// Finish the form
		echo "
		<input type=\"submit\" name=\"submit_button\" value=\"Next &rarr;\" id=\"submit_button\" class=\"formbutton\" />

</form>";
}
