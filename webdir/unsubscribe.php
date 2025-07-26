<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// _GET the subscription ID for _GET e and _GET l
if ((isset($_GET['l'])) && (isset($_GET['e'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['l'])) && (preg_match ('/^[a-zA-Z0-9]{64}$/i', $_GET['e']))) {
    $unsubscribeme_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
    $unsubscribeme_get_sec_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['e']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

/* DEPRECIATED: this was the old format of the same validation above, remove if the above validation works
// _GET the subscription ID
if (isset($_GET['l'])) {
  $unsubscribeme_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
  if (preg_match('/[^a-zA-Z0-9]/', $unsubscribeme_key)) {
    header("Location: /index.php");
    exit(); // Quit the script}
  }
} else {
  header("Location: /index.php");
  exit(); // Quit the script
}
if (isset($_GET['e'])) {
  if (preg_match ('/^[a-zA-Z0-9]{64}$/i', $_GET['e'])) {
    $unsubscribeme_get_sec_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['e']);
  }
} else {
  header("Location: /index.php");
  exit(); // Quit the script
}
*/

// Verify that the key is real
$q = "SELECT userid, email FROM emailwrongunsubscribe WHERE delkey='$unsubscribeme_key' AND useable='live'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) { // No key
  header("Location: /index.php");
  exit(); // Quit the script
}

// Include the header file
$page_title = "Unsubscribe?";
include ('includes/header.html');

echo '<h3 class="note_red">Unsubscribe: Are you sure?</h3><p>This is a "brute" unsubscribe action; there is a gentler way if you only want to cancel your subscription to our newsletter or if you want to fully delete your account. See those options by logging-in and visiting <a title="Account Information" href="account_info.php">Account Information</a>.</p><p>But, on this page, you are about to unsubscribe from mandatory emails. This will suspend your account and you <b>might not</b> be able to use this email address to recover a lost password.</p><p><b>Are you sure?</b></p>
<form action="/unsubscribed.php" method="post" accept-charset="utf-8">
<input type="hidden" name="l" value="'.$unsubscribeme_key.'" />
<input type="hidden" name="e" value="'.$unsubscribeme_get_sec_key.'" />

<input type="submit" name="submit_button" value="Yes, remove this email and suspend my account!" id="submit_button" class="formbutton set_red" />
</form>';

// Include the HTML footer
include ('./includes/footer.html');
?>
