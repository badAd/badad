<?php

// This must be set when including this page:
// Insert the page content
//$rformaction = 'CURRENT_PAGE_INCLUDING_THIS.php'; // This must be set for the include to work
//include ('includes/referral_gen.inc.php');

// Create our functions
// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// Show nothing if user is not logged in, otherwise set $userid
if (isset($_SESSION['user_id'])) { $userid = $_SESSION['user_id']; } else { exit(); }

// Create a new link?
if (isset($_POST['gen_ref_link'])) {

  // See whether the user already has a referral link
  $q = "SELECT reflink FROM referrallinks WHERE userid='$userid'";
  $r = mysqli_query ($dbc, $q);
  $rows = mysqli_num_rows($r);
  if ($rows == 0) { // Only generate the new referral link if the user doesn't already have one

  // Generate the ridiculously long random string
  require_once ('./includes/string_functions.inc.php');

  // Generate the new slug
  $rstring = longString(42);

  // Check the string to be unique
  $q = "SELECT reflink FROM referrallinks WHERE binary reflink='$rstring'";
  $row = mysqli_query ($dbc, $q);
  while (mysqli_num_rows($row) != 0) {
    $rstring = longString(42);
    // Check again
    $q = "SELECT reflink FROM referrallinks WHERE binary reflink='$rstring'";
    $row = mysqli_query ($dbc, $q);
    if (mysqli_num_rows($row) == 0) {
      break;
    }
  }

  // Add the new link to the database
  $qi = "INSERT INTO referrallinks (userid, reflink) VALUES ('$userid', '$rstring')";
  $ri = mysqli_query ($dbc, $qi);


    if (mysqli_affected_rows($dbc) == 1) { // If it ran OK

      // Set the full URL link
      $payloadlink = "https://$siteDomain/referred.php?l=$rstring";

      // email

      // Get the new user's name from the database for the email
      $qu = "SELECT name, email FROM users WHERE id='$userid'";
      $ru = mysqli_query ($dbc, $qu);
      $rowu = mysqli_fetch_array ($ru, MYSQLI_NUM);
      $userName = $rowu[0];
      $email = $rowu[1];

      // Send the email
      $canned_email = "referral_gen"; // Slug from the "pantry" table to select the canned email
      $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
      $payload_content = "<p>This is your referral link:<br /><a href=\"$payloadlink\">$payloadlink</a></p>"; // Middle of the Body, after the canned email and before the salutation
      $footer_link_content = ""; // After the salutation and before the unsubscribe footer
      include ('./includes/sendusrmail.inc.php');

      // Print a message and wrap up
      echo '<p class="note_green">For your reference, an email has been sent to your address with your referral link. This link works right now and no futher action is required.</p>';
    } else {
      sql_error($qi, 'dbc', "sqle_74");
    }
  } // End redundant test if user already has the newly-created link

// No new link request, just display the user's existing link
} else {

  // See whether the user already has a referral link
  $q = "SELECT reflink FROM referrallinks WHERE userid='$userid'";
  $r = mysqli_query ($dbc, $q);
  $rows = mysqli_num_rows($r);
  if ($rows == 0) { // If the user has no link

    echo "<p>Refer friends and get free ad credits!</p>
    <form action=\"$rformaction\" method=\"post\" style=\"padding-left:25px\">
      <input type=\"submit\" name=\"gen_ref_link\" value=\"Get your referral link\" id=\"gen_ref_link\" class=\"formbutton\" />
    </form>";

  } else { // If the user has a link
      // Set the link variables from the table
      $row = mysqli_fetch_array ($r, MYSQLI_NUM);
      $reflink = $row[0];

      // Set the full URL link
      $payloadlink = "https://$siteDomain/referred.php?l=$reflink";

  }
}

// Display the link
if (isset($payloadlink)) {
  // Show the link
  echo "<h4>Your referral link:</h4><p>When someone signs up and buys an ad with this link, both of you get a free 1-week ad credit!</p>";
  // Put it in a table
  echo "<table><tbody><tr><td>";
  // Normal link
  echo "Sharable link: <a title='badad.one' href='#'>http://badAd.one/referred...</a><br />
  <input type=\"text\" size=\"12\" value=\"$payloadlink\" class=\"copy_link\" id=\"referral_link\">
  <button class=\"copy_button\" onclick=\"copyLink()\">Copy</button>
  <script>function copyLink() {var copyText = document.getElementById(\"referral_link\"); copyText.select(); document.execCommand(\"copy\"); }</script>
  <script>document.getElementById(\"referral_link\").readOnly = true; </script>";
  // Table column
  echo "</td><td>";
  // Pretty embed
  $prettypaloadlink = "<a title='badad.one' href='$payloadlink'>badAd.one - Claim your ad credit</a>";
  echo "HTML embed code: <a title='badad.one' href='#'>badAd.one - Claim your ad credit</a><br />
  <input type=\"text\" size=\"12\" value=\"$prettypaloadlink\" class=\"copy_link\" id=\"referral_link_pretty\" />
  <button class=\"copy_button\" onclick=\"copyLinkPretty()\">Copy</button>
  <script>function copyLinkPretty() {var copyTextPretty = document.getElementById(\"referral_link_pretty\"); copyTextPretty.select(); document.execCommand(\"copy\"); }</script>
  <script>document.getElementById(\"referral_link_pretty\").readOnly = true; </script>";
  // Close table
  echo "</td></tr></tbody></table>";
}
