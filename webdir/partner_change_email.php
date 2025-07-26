 <?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Make sure we're not here on accident
if (!isset($_POST['change_partner_payout_email'])) {
  header("Location: partner.php");
  exit(); // Quit the script
} elseif ($_POST['change_partner_payout_email'] != $userid) {
  header("Location: partner.php");
  exit(); // Quit the script
}

// We need database connection
require (MYSQL);

// Check for form submission
if (isset($_POST['clicked_change_email'])) {

  // Check the Partner's account
  // Get Partner's email & name
  $q = "SELECT email FROM partners WHERE user_id='$userid'";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($r) == 1) {
    $row = mysqli_fetch_array ($r, MYSQLI_NUM);
    $email = $row[0];
    $name = $_SESSION['user_name'];
  } else { // User doesn't have an entry after all
    sql_error($q, 'dbc', "sqle_11");
  }
  // Partner's Unsubscribe key
  // Get the user's info to populate the form
  $q = "SELECT sec_key FROM users WHERE id='$userid'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $eml_sec_key = "$row[0]";
  include ('includes/emailwrong_create.inc.php');
  $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$userid' AND useable='live' ORDER BY id DESC LIMIT 1";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $eml_email_delkey = "$row[0]";
  $unsubscribe_url = "https://$siteDomain/$eml_email_delkey/$eml_sec_key/unsubscribe.html";
  $unsubscribe_footer = '<br /><br /><br /><table width="100%" bgcolor="#000" border="0" cellspacing="0" cellpadding="3"><tr align="center"><td style="color: #fff"><a style="color: #fff; text-decoration: none" title="'.$siteDomain.'" href="https://'.$siteDomain.'">'.$siteTitle.'</a> | <a style="color: #fff; text-decoration: none" title="Unsubscribe immediately" href="'.$unsubscribe_url.'">Unsubscribe</a></td></tr></table>';
  // Delete the Partner entry
  $q = "DELETE FROM partners WHERE user_id='$userid'";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) == 1) {
    // Send the Partner account change system email notice
    $canned_email = "partner_email_change"; // Slug from the "pantry" table to select the canned email
    $payload_content = "<p>You requested to change your partner email from the old address: <b>$email</b></p>";
    include ('./includes/confirm_partner_change.inc.php');

    // Notify the confirmed Partner email
      // Confirmation keys
      // Generate the ridiculously long random string
      require_once ('./includes/string_functions.inc.php');
      // Create the password link
      $pstring = longDashScoreString(255);
      $cstring = longDashScoreString(255);
      // Dup check
      $q = "SELECT confirmkey FROM confirmpartnerchange WHERE binary confirmkey='$cstring'"; // "binary" makes sure case and characters are exact
      $row = mysqli_query ($srv_dbc, $q);
      // while ($dup = mysqli_fetch_array($row)) {
      //   $cstring = longDashScoreString(255);
      // }
      while (mysqli_num_rows($row) != 0) {
        $cstring = longDashScoreString(255);
        // Check again
        $q = "SELECT confirmkey FROM confirmpartnerchange WHERE binary confirmkey='$cstring'"; // "binary" makes sure case and characters are exact
        $row = mysqli_query ($srv_dbc, $q);
        if (mysqli_num_rows($row) == 0) {
          break;
        }
      }
      // Dup check
      $q = "SELECT temppass FROM confirmpartnerchange WHERE binary temppass='$pstring'"; // "binary" makes sure case and characters are exact
      $row = mysqli_query ($srv_dbc, $q);
      // while ($dup = mysqli_fetch_array($row)) {
      //   $pstring = longDashScoreString(255);
      // }
      while (mysqli_num_rows($row) != 0) {
        $pstring = longDashScoreString(255);
        // Check again
        $q = "SELECT temppass FROM confirmpartnerchange WHERE binary temppass='$pstring'"; // "binary" makes sure case and characters are exact
        $row = mysqli_query ($srv_dbc, $q);
        if (mysqli_num_rows($row) == 0) {
          break;
        }
      }
      // Add the link to the database
      $q = "INSERT INTO confirmpartnerchange (userid, confirmkey, temppass, date_dead) VALUES ('$userid', '$cstring', '$pstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -45 DAY))";
      $r = mysqli_query ($srv_dbc, $q);
      if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran OK
      // Send the email
      $payloadlinkyes = "https://$siteDomain/partner_info_confirmed.php?c=$cstring";
      $payloadlinkno = "https://$siteDomain/partner_info_repair.php?p=$pstring";
      $payload_content = "<p><a href=\"$payloadlinkyes\">Yes, I made this request.</a><br /><br /><a href=\"$payloadlinkno\">No, I didn't!</a> (Click to change password NOW.)</p><p>Answering helps keep your account secure.</p>"; // Middle of the Body, after the canned email and before the salutation
      // HTML email requirements
      $headers  = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
      $to = '"'.$name.'" <'.$email.'>';
      $from = '"'.$site_from_email_name.'" <'.$site_from_email.'>';
      $subject = 'No longer paying from '.$siteTitle;
      $headers .= "From: " . $from . "\r\n";
      $headers .= "Bcc: " . $site_bcc_email;
      $sending_body = "<p>This email address has been removed from Partner payouts at $siteTitle.</p>";
      $message = "$sending_body\n$payload_content\n$unsubscribe_footer"; // New lines (\n) & "quotes" prevent DKIM failures
      mail($to,$subject,$message,$headers);

      header("Location: partner.php");
      exit(); // Quit the script
  } else { // Add the link to the database
    sql_error($qr, 'srv_dbc', "sqle_13");
  }
} else { // Delete partner entry
  sql_error($q, 'dbc', "sqle_12");
}
} // End _POST form process

// Include the header file
$page_title = "Change Partner Email :: $siteTitle";
include ('./includes/header.html');

// Breadcrumb
echo "<p class=\"note_gray\">&larr; Return to the <a title=\"Partner Center\" href=\"partner.php\">Partner center</a>?</p>";

// Print a customized message
echo '<h3>Change your Partner payout email address?</h3><br /><p>To change your partner email address, you will temporarily lose access to the Partner Center until you agree to the terms again and confirm a new email address. This will not remove any of your sites you may be monetizing, but once you click on "Proceed and Change your Partner email address" below, you will not be able to make any changes to your monetized domains until a new PayPal payout email address has been confirmed.</p><p>Are you sure you are ready to proceed?</p>';

echo "<form id=\"partnerchangeemail\" class=\"userform\" action=\"partner_change_email.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"clicked_change_email\" value=\"submitted\" />
<input type=\"hidden\" name=\"change_partner_payout_email\" value=\"$userid\" />";
		// Disclaimers
		echo"
		<p><input type=\"checkbox\" name=\"agree_to_change_partner_email\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must indicate that you are sure you are ready to proceed.')\" onchange=\"this.setCustomValidity('')\"/> <strong>I am ready to temporarily lose access to my monetized domain settings until I confirm a new email address.</strong></p>
		<input type=\"submit\" name=\"submit_button\" value=\"Proceed and Change your Partner email address\" id=\"submit_button\" class=\"set_red\" />

</form>";


// Include the HTML footer
include ('./includes/footer.html');
?>
