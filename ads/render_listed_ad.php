<?php
// This creates the .js file in an embedded ad
// This is identical to render_listed_ad_nocount.php, but without the $no_hit option

header("Content-Type: application/javascript");
header("Cache-Control: max-age=604800, public");

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Validate the serial number: a-zA-Z0-9
if ((isset($_GET['l'])) && (preg_match ('/[a-zA-Z0-9]$/i', $_GET['l']))) {
  $site_serial = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
} else {
  header("Location: https://badad.one");
  exit(); // Quit the script
}

// This is for "counts"
$no_hit = false;

// Retrieve the ad content
include ('./list_ad_get.inc.php');

?>
