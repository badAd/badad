<?php

// This creates the HTML content in an embedded ad
header("Content-Type: application/javascript");
header("Cache-Control: max-age=604800, public");

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Validate the serial number: a-zA-Z0-9
if ((isset($_POST['dev_key'])) && (preg_match ('/[^a-zA-Z0-9_]$/i', $_POST['dev_key']))) {$IP = get_ip_addr(); script_kiddy('sk_a1', '_POST dev_key', $_POST['dev_key'], $IP);}
if ((isset($_POST['call_key'])) && (preg_match ('/[^a-zA-Z0-9_]$/i', $_POST['call_key']))) {$IP = get_ip_addr(); script_kiddy('sk_a2', '_POST call_key', $_POST['call_key'], $IP);}
if ((isset($_POST['dev_key']))
  && (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['dev_key']))
  && (isset($_POST['call_key']))
  && (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['call_key']))) {
    $dev_key = preg_replace( '/[^a-zA-Z0-9_]/', '', $_POST['dev_key'] );
    $call_key = preg_replace( '/[^a-zA-Z0-9_]/', '', $_POST['call_key'] );
} else {
  header("Location: https://badad.one/noconnection.php");
  exit(); // Quit the script
}

// Number of ads
if ((isset($_POST['num_ads'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['num_ads']))) {$IP = get_ip_addr(); script_kiddy('sk_a3', '_POST num_ads', $_POST['num_ads'], $IP);}
if ((isset($_POST['num_ads'])) && (filter_var($_POST['num_ads'], FILTER_VALIDATE_INT, array("options"=>array('min_range'=>0, 'max_range'=>20))))) {
  $num_ads = preg_replace("/[^A-Za-z0-9]/","", $_POST['num_ads']);
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

// DO NOT count as a hit?
if ((isset($_POST['no_hit'])) && ($_POST['no_hit'] == true)) {
  $no_hit = true;
} else {
  $no_hit = false;
}

// Listing the ads needs the _SRV config
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Escape globally
$esc_dev_key = mysqli_real_escape_string($srv_dbc, $dev_key);
$esc_call_key = mysqli_real_escape_string($srv_dbc, $call_key);

// Retrieve the ad content
include ('./api_ad_get.inc.php');

?>
