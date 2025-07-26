<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Include the header file
$page_title = 'Test Mail';
include ('./includes/header.html');

// Test Mail

// Create a fake _SESSION login if no user because system emails are only sent to logged-in users
if (!isset($_SESSION['user_id'])) {$_SESSION['user_id'] = 1; $fakesession = true;}

// Send canned email
$canned_email = "sysemailtest"; // Slug from the "pantry" table to select the canned email
$subject_suffix = " - Subject Suffix"; // Appends the canned email Subject
$payload_content = "<p>I am the payload. This might be an important link: <a href=\"http://pacificdailyads.com\">Click here to visit PDA</a>.</p>"; // Middle of the Body, after the canned email and before the salutation
$footer_link_content = "<p>I am the footer link. If the link above doesn't work, copy and paste this into your browser: <a href=\"http://pacificdailyads.com\">pacificdailyads.com</a></p>"; // After the salutation and before the unsubscribe footer
include ('./includes/sendusrmail.inc.php');

echo "Test email sent.";

// Unset any fake session
if (isset($fakesession)) {unset($fakesession); unset($_SESSION['user_id']);}

// Include the HTML footer
include ('./includes/footer.html');
?>
