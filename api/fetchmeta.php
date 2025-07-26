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
if ((isset($_POST['dev_key'])) && (preg_match ('/[^a-zA-Z0-9_]$/i', $_POST['dev_key']))) {$IP = get_ip_addr(); script_kiddy('sk_a4', '_POST dev_key', $_POST['dev_key'], $IP);}
if ((isset($_POST['call_key'])) && (preg_match ('/[^a-zA-Z0-9_]$/i', $_POST['call_key']))) {$IP = get_ip_addr(); script_kiddy('sk_a5', '_POST call_key', $_POST['call_key'], $IP);}
if ((isset($_POST['dev_key']))
  && (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['dev_key']))
  && (isset($_POST['call_key']))
  && (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['call_key']))) {
    $dev_key = preg_replace( '/[^a-zA-Z0-9_]/', '', $_POST['dev_key'] );
    $call_key = preg_replace( '/[^a-zA-Z0-9_]/', '', $_POST['call_key'] );
} else {
  exit(); // Quit the script
}

// Credit link?
if ((isset($_POST['refcred'])) && ($_POST['refcred'] == true)) {
  $refcred = true;
} else {
  $refcred = false;
}

// Listing the ads needs the _SRV config
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Escape globally
$esc_dev_key = mysqli_real_escape_string($srv_dbc, $dev_key);
$esc_call_key = mysqli_real_escape_string($srv_dbc, $call_key);

// Retrieve the ad content
include ('./api_meta_get.inc.php');

?>
