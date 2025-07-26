<?php

// This sends a message to all users, use from the terminal
// This uses a key made with keygen.php
// Access from webdir with: writetoallusersupdatetcp.php?k=LONGWRITERSKEY

// The $messageBody variable on the following line contains the main message, edit it to change the message:

$messageBody = "<p>THIS_IS_THE_MESSAGE</p>";

// Config
require ('/srv/www/badad/webdir/includes/config.inc.php');
// Require the database connection
require (MYSQL);

// If the user isn't logged in, redirect
redirect_invalid_user();

// Admin User ID or leave
if (isset($_SESSION['user_is_admin'])) {
  $userID = $_SESSION['user_id'];
} else {
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
}

// Make sure $_GET['k'] is set
if (!isset($_GET['k'])) {
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
} else {
  $writers_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['k']);
}

// Check to see if the user is admin
$q = "SELECT type, email FROM users WHERE id='$userID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$userType = "$row[0]";
$writers_email = "$row[1]";
if ($userType != "admin") {
  // Destroy the session
  $_SESSION = array(); // Destroy the variables
  session_destroy(); // Destroy the session itself
  setcookie (session_name(), '', time()-300); // Destroy the cookie
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
}

// Check the key
$q = "SELECT writekey FROM writerkey WHERE email='$writers_email'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$keyonfile = "$row[0]";
if ($keyonfile != $writers_key) {
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
} else {
  // Delete the old key
  $q = "DELETE FROM writerkey WHERE email='$writers_email' AND writekey='$writers_key'";
  $r = mysqli_query ($dbc, $q);
}

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Set the message
$mass_log_no_bcc = true; // This is a mass email
$canned_email = "updateuserterms"; // Slug from the "pantry" table to select the canned email
$subject_suffix = " - badAd"; // Appends the canned email Subject
$payload_content = $messageBody; // Middle of the Body, after the canned email and before the salutation
$footer_link_content = "";

echo "<br />Sending TCP message to all users:<br />
time: $timeNow<br />
canned_email: $canned_email<br />
subject_suffix: $subject_suffix<br />
footer_link_content: $footer_link_content<br />
payload_content: $payload_content<br />
To user_id:<br />";

// Get each user's email from the database
$q = "SELECT id FROM users";
$rows = mysqli_query ($dbc, $q);
while ($row = mysqli_fetch_array($rows)) {
  // Set the email value
  $emailUserIDea = $row[0];

  // Get user's info from the database
  $qea = "SELECT userid FROM emailwrongunsubscribe WHERE useable='live' AND userid='$emailUserIDea'";
  $rowsea = mysqli_query ($dbc, $qea);
  while ($rowea = mysqli_fetch_array($rowsea)) {
    $emailUserID = $rowea[0]; // This is important for sendusrmail.inc

    include ('./includes/sendusrmail.inc.php');

    echo "$emailUserID<br/>";
  }
}

echo "<br />Sending finished.";
