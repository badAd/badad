<?php

// _POST the $cat slug
if ((isset($_POST['c'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['c']))) {$IP = get_ip_addr(); script_kiddy('sk_75', '_POST c', $_POST['c'], $IP);}
if (isset($_POST['c'])) {
  $cat = preg_replace("/[^A-Za-z0-9]/","", $_POST['c']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Do what we need to do here
session_start(); // We need our _SESSION to be working
unset($_SESSION['validAd']);
$_SESSION['edit_back'] = true;

// Redirect to the proper place
header("Location: new_ad.php?c=$cat");
exit(); // Quit the script

?>
