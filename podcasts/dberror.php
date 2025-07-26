<?php

// Verify that a real SQL error brought us here
if ((isset($_SESSION['sql_error'])) && ($_SESSION['sql_error'] == true) && (isset($_SESSION['sql_error_time']))) {
  unset($_SESSION['sql_error']);
  // Get the time
  $errorTimeNow = $_SESSION['sql_error_time'];
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

/*
// Dev for testing (replace sql_error test above)
$errorTimeNow = date("Y-m-d H:i:s");
$_SESSION['sql_error_time'] = $errorTimeNow;
*/


// Include the header file
$page_title = "Database Error";
include ('./includes/header.html');

// Message
echo "<h3>Database Error</h3>
<p>Very sorry! We had a database error. If this is a very big deal for you, note this time for your reference: $errorTimeNow</p>
<p>We have been informed about the error and will work on preventing similar problems in the future. Feel free to try again.</p>";

// Logged in users can report
if (isset($_SESSION['user_id'])) {
  echo '<p><i>If this is a really big deal for you,</i> tell us why...</p>';
	set_switch("Tell us...", "Go to the feedback form", "feedback.php", "type", "SQL_ERROR", "set_black");
}

?>
