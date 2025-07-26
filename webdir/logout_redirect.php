<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Set the message
$_SESSION['logged_out'] = true;

if ((isset($_POST['lc'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['lc']))) {$IP = get_ip_addr(); script_kiddy('sk_79', '_POST lc', $_POST['lc'], $IP);}
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['lc']))
&& (filter_var($_POST['lc'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $_SESSION['login_attempt'] = preg_replace("/[^A-Za-z0-9]/","", $_POST['lc']);
}

// Go home
header("Location: index.php");
exit(); // Quit the script

?>
