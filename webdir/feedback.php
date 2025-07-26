<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// A form page requires form functions
require_once ('./includes/form_functions.inc.php');

// We need database connection
require (MYSQL);

// Include the check
require_once ('./inserts/feedback.check.ins.php');

// Include the header file
$page_title = "Contact Us | $siteTitle";
include ('./includes/header.html');

// Get the time
if (isset($_SESSION['sql_error_time'])) {
  $eventTimeNow = $_SESSION['sql_error_time'];
} else {
  $eventTimeNow = date("Y-m-d H:i:s");
}

// Title
echo "<h3>Contact Us</h3>";

// Should the user be here?
if ((isset($_POST['type'])) && (preg_match ('/[^a-zA-Z0-9_]$/i', $_POST['type']))) {$IP = get_ip_addr(); script_kiddy('sk_16', '_POST type', $_POST['type'], $IP);}
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_SESSION['user_id'])) && (isset($_POST['type']))) { // Logged in user selected a type OR form had errors
  $userid = $_SESSION['user_id'];
  $form_type = preg_replace("/[^A-Za-z0-9_]/","", $_POST['type']);

  // SQL error logged-in (skip the guided welcome)
  if (($form_type == "SQL_ERROR") && (!isset($reg_errors['feedback_content'])) && (!isset($_POST['feedback_type']))) {
    echo '
  <form id="feedback_form" class="userform" action="feedback.php" method="post" accept-charset="utf-8">
    <input hidden name="user_id" value="'.$userid.'" />
    <input hidden name="event_time_now" value="'.$eventTimeNow.'" />
    <input hidden name="feedback_type" value="'.$form_type.'" />
    <input hidden name="type" value="'.$form_type.'" />';

    echo '<p><b>Report your database dilemma:</b></p>';
    // Explanation box
    echo '<p>We already have your user information and we know which error this was. Tell us why this is important to you in 1,000 characters or less and we\'ll have a look...</p>';
    create_form_input('feedback_content', 'textarea', $reg_errors);
    echo '<br /><br />';
    // Note: <form> ends at close of feedback.php
  } // end SQL error report

} elseif ((isset($_POST['type'])) && ($_POST['type'] == 'LEGAL')) { // Non-logged in user selected "legal"
  $userid = "LGL_GUEST";
  $form_type = preg_replace("/[^A-Za-z0-9_]/","", $_POST['type']);
} elseif (isset($_SESSION['user_id'])) { // Non-SQL error logged-in
  echo '<p>What would you like to report?</p>';

  // What-to-report form
  echo '
  <form id="report_selection" class="userform" action="feedback.php" method="post" accept-charset="utf-8">
    <input hidden name="user_id" value="'.$userid.'" />
    <select class="formselect" name="type">
      <option selected disabled hidden>Choose...</option>
      <option value="LEGAL">Legal report</option>
      <option value="DUPLICATE">Duplicate ads or Contact links</option>
      <option value="INAPPROPRIATE">Inappropriate content, abuse, or strange use of language</option>
      <option value="OTHER">Something else</option>
    </select>
    <input type="submit" name="submit_button" value="File the report &rarr;" id="submit_button" class="formbutton" />
  </form>
  ';

  // Include the HTML footer
  include ('./includes/footer.html');
  exit();
} else {
  $userid = "LGL_GUEST";
  echo '<p>You may report a legal, defamatory, or other intellectual property rights violation. To contact us for any other reason, you must be a registered user and logged in.</p>';
  set_switch("Make a legal report &rarr;", "Report something illegal on this site...", "feedback.php", "type", "LEGAL", "set_black");
  // Include the HTML footer
  include ('./includes/footer.html');
  exit();
}

// Several Kiddy Checks
if ((isset($_POST['feedback_type'])) && (preg_match ('/[^a-zA-Z0-9_]$/i', $_POST['feedback_type']))) {$IP = get_ip_addr(); script_kiddy('sk_17', '_POST feedback_type', $_POST['feedback_type'], $IP);}
if ((isset($_POST['user_id'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['user_id']))) {$IP = get_ip_addr(); script_kiddy('sk_18', '_POST user_id', $_POST['user_id'], $IP);}
if ((isset($_POST['dupl_type'])) && (preg_match ('/[^a-zA-Z0-9-]$/i', $_POST['dupl_type']))) {$IP = get_ip_addr(); script_kiddy('sk_19', '_POST dupl_type', $_POST['dupl_type'], $IP);}
if ((isset($_POST['legl_type'])) && (preg_match ('/[^a-zA-Z0-9-]$/i', $_POST['legl_type']))) {$IP = get_ip_addr(); script_kiddy('sk_20', '_POST legl_type', $_POST['legl_type'], $IP);}
if ((isset($_POST['agree_i_have_standing'])) && (preg_match ('/[^a-zA-Z0-9. ]$/i', $_POST['agree_i_have_standing']))) {$IP = get_ip_addr(); script_kiddy('sk_21', '_POST agree_i_have_standing', $_POST['agree_i_have_standing'], $IP);}
if ((isset($_POST['agree_to_honesty'])) && (preg_match ('/[^a-zA-Z0-9., ]$/i', $_POST['agree_to_honesty']))) {$IP = get_ip_addr(); script_kiddy('sk_22', '_POST agree_to_honesty', $_POST['agree_to_honesty'], $IP);}
if ((isset($_POST['tc_agree'])) && (preg_match ('/[^a-zA-Z0-9. ]$/i', $_POST['tc_agree']))) {$IP = get_ip_addr(); script_kiddy('sk_23', '_POST tc_agree', $_POST['tc_agree'], $IP);}
if ((isset($_POST['agree_to_not_solicit_business'])) && (preg_match ('/[^a-zA-Z0-9. ]$/i', $_POST['agree_to_not_solicit_business']))) {$IP = get_ip_addr(); script_kiddy('sk_24', '_POST agree_to_not_solicit_business', $_POST['agree_to_not_solicit_business'], $IP);}

// Process a form
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['feedback_type'])) && (isset($_SESSION['validFeedbackForm'])) && ($_SESSION['validFeedbackForm'] == true)) {

  // Set our types & meta
  $feedback_type = preg_replace("/[^A-Za-z0-9_]/","", $_POST['feedback_type']);
  $user_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['user_id']);

  // Head content
  $event_time_now = preg_replace("/[^A-Za-z0-9_:-]/","_", $_POST['event_time_now']);
  $sending_body_internal_head = "<p>Form type: $feedback_type</p><p>Event timestamp: $event_time_now</p><p>User ID: $user_id</p>";

  // Logged-in user?
  if ((isset($_SESSION['user_id'])) && ($_SESSION['user_id'] == $user_id)) {
    // Retrieve the user's info
    $q = "SELECT name, email, confirmed_email FROM users WHERE id='$user_id'";
    $r = mysqli_query ($dbc, $q);
    $row = mysqli_fetch_array($r, MYSQLI_NUM);
    $user_name = "$row[0]";
    $current_email = "$row[1]";
    $confirmed_email = "$row[2]";

    // Confirmed email?
    if (($current_email == $confirmed_email) || ($confirmed_email == "Unconfirmed")) {
      $user_email_string = $current_email . " (current-confirmed)";
    } else {
      $user_email_string = $current_email . " (current-new) & " . $confirmed_email . " (confirmed-old)";
    }
    // Define the User's info into the head
    $sending_body_internal_head .= "<p>User Name: $user_name</p><p>User Email: $user_email_string</p><hr />";
  } else { // If not a logged-in user, simply close the head
    $sending_body_internal_head .= "<hr />";
  }

  // This email created is not going to the user, therefore it is created here, not the user email system
  // From / To
  $from = '"badAd Feedback Form" <'.$feedback_from_email.'>';
  $to = '"badAd Feedback" <'.$feedback_email.'>';

  // What type of feedback?
  if ($feedback_type == 'SQL_ERROR') {
    $subject_payload = 'SQL-Error';
    $from .= ', "badAd-SQL Error Handler" <'.$sql_error_from_email.'>';
    $to .= ', "badAd-SQL Errors" <'.$sql_error_email.'>';
    $sending_payload = "<p>Feedback Content:<br />$feedbackContent</p>";
  } elseif ($feedback_type == 'LEGAL') {
    $leglType = preg_replace("/[^A-Za-z0-9-]/","", $_POST['legl_type']);
    $leglStanding = preg_replace("/[^A-Za-z0-9. ]/","", $_POST['agree_i_have_standing']);
    $agreeToHonesty = preg_replace("/[^A-Za-z0-9., ]/","", $_POST['agree_to_honesty']);
    $TCagree = preg_replace("/[^A-Za-z0-9. ]/","", $_POST['tc_agree']);
    $subject_payload = 'Legal Report';
    //$sending_payload = "<p>Legal report type (from dropdown-select): $leglType</p><p>'Your Full Name': $fullName</p><p>'Firm': $Firm</p><p>'Plaintiff or Crime': $Plaintiff</p><p>Standing?: '$leglStanding'</p><p>'Your homepage or social media URL': $formURL</p><p>'Relevant URL': $optionalURL</p><p>'Email': $emailAddress</p><p>'Content':<br />$feedbackContent</p><p>'Summary':<br />$feedbackRequiredText</p><p>Perjury and Honesty: $agreeToHonesty</p><p>Agree to terms?: $TCagree</p>";

    $sending_payload = "<p>Legal report type (from dropdown-select): $leglType</p>
    <p>'Your Full Name': $fullName</p>
    <p>'Firm': $Firm</p>
    <p>'Plaintiff or Crime': $Plaintiff</p>
    <p>Standing?: $leglStanding</p>
    <p>'Your homepage or social media URL': $formURL</p>
    <p>'Relevant URL': $optionalURL</p>
    <p>'Email': $emailAddress</p>
    <p>'Content':<br />$feedbackContent</p>
    <p>'Summary':<br />$feedbackRequiredText</p>
    <p>Perjury and Honesty?: $agreeToHonesty</p>
    <p>Agree to terms?: $TCagree</p>";

  } elseif ($feedback_type == 'DUPLICATE') {
    $dupType = preg_replace("/[^A-Za-z0-9-]/","", $_POST['dupl_type']);
    $subject_payload = 'Duplicate';
    $sending_payload = "<p>Dup type (from dropdown-select): $dupType</p><p>Feedback Content:<br />$feedbackContent</p>";
  } elseif ($feedback_type == 'INAPPROPRIATE') {
    $subject_payload = 'Inappropriate';
    $sending_payload = "<p>Reported Content:<br />$feedbackContent</p>";
  } elseif ($feedback_type == 'OTHER') {
    $agreeToNotSolicitBusiness = preg_replace("/[^A-Za-z0-9. ]/","", $_POST['agree_to_not_solicit_business']);
    $subject_payload = 'Other';
    $sending_payload = "<p>'Your homepage or social media URL': $formURL</p><p>'URL about your inquiry': $optionalURL</p><p>'Subject': $subjectOther</p><p>'Your message':<br />$feedbackText</p><p>'Agree to not solicit business': $agreeToNotSolicitBusiness</p>";
  }

  // HTML email requirements
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

  // Send the email
  $subject = $subject_payload . ' - badAd FF';
  $message = $sending_body_internal_head . '<p><b>Content submitted by user:</b></p>' . $sending_payload;
  $headers .= "From: " . $from . "\r\n";
  $headers .= "Bcc: " . $site_bcc_email;
  mail($to,$subject,$message,$headers);

  // Email to logged in users
  if (isset($_SESSION['user_id'])) {
    $canned_email = "feedback"; // Slug from the "pantry" table to select the canned email
    $subject_suffix =  " - $subject_payload"; // Appends the canned email Subject
    $payload_content = "<hr /><p>Time: $event_time_now</p>$sending_payload<hr />"; // Middle of the Body, after the canned email and before the salutation
    $footer_link_content = "<p>Do not reply to this message. If you need to, reach us some other way.</p>"; // After the salutation and before the unsubscribe footer
    include ('./includes/sendusrmail.inc.php');
  }

  // Message to user
  echo "<p>Your message has been sent! Thanks for the feedback. We'll have a look.</p>";

  // Remove any SQL error meta from the _SESSION
  if ((isset($_SESSION['sql_error'])) && ($_SESSION['sql_error'] == true) && (isset($_SESSION['sql_error_time']))) {
    unset($_SESSION['sql_error']);
  }
  // Remove any other _SESSION values from the form
  if (isser($_SESSION['feedbackText'])) {unset($_SESSION['feedbackText']);}
  if (isser($_SESSION['feedbackContent'])) {unset($_SESSION['feedbackContent']);}
  if (isser($_SESSION['feedbackRequiredText'])) {unset($_SESSION['feedbackRequiredText']);}
  if (isser($_SESSION['fullName'])) {unset($_SESSION['fullName']);}
  if (isser($_SESSION['subjectOther'])) {unset($_SESSION['subjectOther']);}
  if (isser($_SESSION['Plaintiff'])) {unset($_SESSION['Plaintiff']);}
  if (isser($_SESSION['Firm'])) {unset($_SESSION['Firm']);}
  if (isser($_SESSION['emailAddress'])) {unset($_SESSION['emailAddress']);}
  if (isser($_SESSION['validFeedbackForm'])) {unset($_SESSION['validFeedbackForm']);}

} else {
  echo '
<form id="feedback_form" class="userform" action="feedback.php" method="post" accept-charset="utf-8">
  <input hidden name="user_id" value="'.$userid.'" />
  <input hidden name="event_time_now" value="'.$eventTimeNow.'" />
  <input hidden name="feedback_type" value="'.$form_type.'" />
  <input hidden name="type" value="'.$form_type.'" />';

  // What type of form?
  if ($form_type == 'LEGAL') {
    echo '<p><b>Report your legal concern:</b></p>
    <p>Should we choose to take action, all of the information below must be legally verified.</p>
    <select class="formselect" name="legl_type" required>
      <option selected disabled hidden>Choose...</option>
      <option value="DMCA-Report">DMCA/intellectual property report</option>
      <option value="Defamation">Defamatory content</option>
      <option value="Other-Illegal-Crime">Other illegal crime</option>
    </select>
    <br /><br />
     <p><input type="checkbox" name="agree_i_have_standing" value="I have standing." required> <strong>"I have standing."</strong> I legally claim that this content involves me: Either it is my intellectual property, it is defamatory and about me, it otherwise affects me directly, I am attorney representing someone so affected, or else I am a police officer or other legal authority. (No \'concerned citizen\' reports about someone else\'s intellectual property or defamation; of course anyone can and should report content that puts people in harms way, such as \'doxing\', stalking, putting minors at risk, etc.)</p>';
    // Fields
    echo "
    <p><label for=\"full_name\"><strong>Your Full Name</strong></label><br /><small>Legal name required, we may require ID to match this name in future communication</small><br />";
    create_form_input('full_name', 'text', $reg_errors);
    echo "</p>

    <p><label for=\"project\"><strong>Firm</strong></label><br /><small>Optional, eg: company, firm, etc; if entered this must be a legal, verifiable name</small><br />";
    create_form_input('project', 'text', $reg_errors);
    echo "</p>

    <p><label for=\"concerning\"><strong>Plaintiff or Crime</strong></label><br /><small>Required, the alleged plaintiff or injured party with standing or else the nature of the crime you are reporting as a witness; this must be a legal, verifiable name of the alleged injured party or else it must describe a real crime</small><br />";
    create_form_input('concerning', 'text', $reg_errors);
    echo "</p>

    <p><label for=\"form_url\"><strong>Your homepage or social media URL</strong></label><br /><small>To prevent abuse, we do not accept \"anonymous tips\"</small><br />";
    create_form_input('form_url', 'url', $reg_errors);
    echo "</p>

    <p><label for=\"url_option\"><strong>Relevant URL</strong><br /><small>Optional, explain in your message</small></label><br />";
    create_form_input('url_option', 'url', $reg_errors);
    echo "</p>

    <p><label for=\"email1\"><strong>Email</strong></label><br />";
    create_form_input('email1', 'email', $reg_errors);
    echo "</p>

    <p><label for=\"email2\"><strong>Email again</strong></label><br />";
    create_form_input('email2', 'email', $reg_errors);
    echo "</p>";

    // Content box
    echo '<p><label for="feedback_content"><strong>Content</strong></label><br /><small>Required: Copy and paste or otherwise include the content in question to help us better understand...</small><br />';
    create_form_input('feedback_content', 'textarea', $reg_errors);
    echo '</p>
    ';

    // Explanation box
    echo '<p><label for="feedback_required_text"><strong>Summary</strong></label><br /><small>Required: Sumarize important information in 1,000 characters or less...</small><br />';
    create_form_input('feedback_required_text', 'textarea', $reg_errors);
    echo '</p>';

    // Terms
    echo '
    <p><input type="checkbox" name="agree_to_honesty" value="To my best knowledge, I am telling the whole truth only, including anything exculpatory." required> <strong>"To my best knowledge, I am telling the whole truth only, including anything exculpatory."</strong> I legally agree, on pentalty of perjury, that the information provided herein is truthful to the best of my knowledge; it is the whole truth and only the truth. I know what "exculpatory" means and I am including or at least mentioning any exculpatory information I have that could be beneficial to the defense.</p>';
    echo "
    <p><input type=\"checkbox\" name=\"tc_agree\" value=\"I agree to the Terms and Conditions of $siteTitle.\" required oninvalid=\"this.setCustomValidity('You must understand and agree to the terms and conditions.')\" onchange=\"this.setCustomValidity('')\"/> <strong>\"I agree to the Terms and Conditions of $siteTitle.\"</strong> I understand and agree to the <a href=\"Terms.html\">Terms &amp; Conditions</a> and <a href=\"Privacy.html\">Privacy</a>
		statement, which may be subject to changes, which contain full details of these agreements and shall supercede should there be any discrepancy or ambiguity here or elsewhere.</p>";

  } elseif ($form_type == 'DUPLICATE') {
    echo '<p><b>Report a duplicate:</b> <i>Because we don\'t want to repeat ourselves.</i></p><p>Duplicates can take many forms, either identical content or the Content link pointing to the same destination or something else.</p>
    <select class="formselect" name="dupl_type" required>
      <option selected disabled hidden>Help us by choosing...</option>
      <option value="Dup-URL">Two URL/website Contact destinations are the same (please include both if you can)</option>
      <option value="Dup-Text">Duplicate text content</option>
      <option value="Dup-Other">Other kind of duplicate</option>
    </select>';
    echo '<br /><br />';

    // Explanation box
    echo '<p>Please just copy and paste the content you wish to report as duplicate and we will take it from there. It\'s just that simple, really...</p>';
    echo '<p>If you are reporting two URL/website Contact destinations, please get their long forward links this way:</p>
    <ol>
      <li>1. Right click on "Contact"</li>
      <li>2. select "Copy web address..." from the context menu.</li>
    </ol>';
    create_form_input('feedback_content', 'textarea', $reg_errors);
    echo '<br /><br />';
    echo '<p>Thanks in advance for your help!</p>';
  } elseif ($form_type == 'INAPPROPRIATE') {
    echo '<p><b>Report inappropriate content:</b></p>';
    // Explanation box
    echo '<p>Copy and paste the content you find inappropriate. You may also provide a very short explanation, if you want. Then, we\'ll check it out.</p>
    <p>If you are reporting an app that embeds our ads in push notifications, please include the app\'s website, name in an app store, the app store in which it is listed, or any other information that can help us identify it.</p>
    <p>Thanks in advance.</p>';
    create_form_input('feedback_content', 'textarea', $reg_errors);
    echo '<br /><br />
    ';
  } elseif ($form_type == 'OTHER') {
    echo '<p><b>Contact us about something else:</b></p>';
    echo '<p>If there\'s something else you need to contact us about, we\'ll need some extra information. We don\'t take business solicitations here and need to know you are serious. Fill out everything as best as you can so we can get the picture of what you want to communicate.</p>';

    // Fields
    echo "
    <p><label for=\"form_url\"><strong>Your homepage or social media URL</strong></label><br />";
    create_form_input('form_url', 'url', $reg_errors);
    echo "</p>

    <p><label for=\"url_option\"><strong>URL about your inquiry</strong><br /><small>Optional, explain in your message</small></label><br />";
    create_form_input('url_option', 'url', $reg_errors);
    echo "</p>

    <p><label for=\"subject_other\"><strong>Subject</strong></label><br /><small>What's this about?</small><br />";
    create_form_input('subject_other', 'text', $reg_errors);
    echo "</p>";

    // Not solicitations
    echo '<p>You may not use this "Contact Us" form to request a business relationship. For that purpose, seek to contact us by another means.</p>
    <p><input type="checkbox" name="agree_to_not_solicit_business" value="I am not soliciting business." required> <strong>"I am not soliciting business."</strong> I legally affirm, on pentalty of perjury, that in the message herein I am not making a request for business: whether to sell, to buy something other than what may normally be purchased at this website, or to cooperate.</p>';


    // Explanation box
    echo"
    <p><label for=\"feedback_text\"><strong>Your message</strong></label><br />";
    create_form_input('feedback_text', 'textarea', $reg_errors);
    echo '</p>';
  }

  // reCaptcha
  if (isset($reg_errors['recaptcha'])) {echo '<p class="error">'.$reg_errors['recaptcha'].'</p>';} // Echo an error message
  echo '<div id="rcpt_check"></div><script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script><br />'; // The reCaptcha v2 box

  // End the form for all submission types: LEGAL, DUPLICATE, INAPPROPRIATE, OTHER, and SQL_ERROR
  echo'
  <input type="submit" name="submit_button" value="Send" id="submit_button" class="formbutton" />
</form>
    ';
}


// Include the HTML footer
include ('./includes/footer.html');
?>
