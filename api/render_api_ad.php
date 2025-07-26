<?php
// This creates the .js file in an embedded ad

header("Content-Type: application/javascript");
header("Cache-Control: max-age=604800, public");

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Validate the serial number: a-zA-Z0-9
if ((isset($_POST['dev_key']))
  && (preg_match ('/[a-zA-Z0-9]$/i', $_POST['dev_key']))
  && (isset($_POST['call_key']))
  && (preg_match ('/[a-zA-Z0-9]$/i', $_POST['call_key']))) {
  $dev_key = $_POST['dev_key'];
  $call_key = $_POST['call_key'];
} else {
  header("Location: https://badad.one");
  exit(); // Quit the script
}

// Number of ads
if ((isset($_POST['num_ads'])) && (filter_var($_POST['num_ads'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max_range' => 10)))) {
  $num_ads = $_POST['num_ads'];
} else {
  $num_ads = 1;
}

// Show badAd link?
if ((isset($_POST['show_badad_link'])) && ($_POST['show_badad_link'] == true)) {
  $show_badad_link = true;
} else {
  $show_badad_link = false;
}

// Inline?
if ((isset($_POST['inline_div'])) && ($_POST['inline_div'] == true)) {
  $inline_div = true;
} else {
  $inline_div = false;
}


// Retrieve the ad content
include ('./api_ad_get.inc.php');

?>
