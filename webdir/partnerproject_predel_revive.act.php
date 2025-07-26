<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the site ID
if ((isset($_POST['s'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['s']))) {$IP = get_ip_addr(); script_kiddy('sk_58', '_POST s', $_POST['s'], $IP);}
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['s']))
&& (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $site_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['s']);
} else {
  header("Location: partner_del_project.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Make sure the user is an Activated Partner
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) { // Not a partner
  header("Location: partner.php");
  exit(); // Quit the script
}
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$confirmed = "$row[0]";
// Redirect if not activated
if ($confirmed != 'Confirmed') {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Turn off the site
$q = "UPDATE partnersites SET useable='off' WHERE id='$site_id' AND useable='deleted'";
if ($r = mysqli_query ($srv_dbc, $q)) {
  // Cancel the delete key
  $qp = "UPDATE confirmdelsite SET useable='dead' WHERE userid='$userid' AND siteid='$site_id'";
  $rp = mysqli_query ($srv_dbc, $qp);
  if (!mysqli_affected_rows($srv_dbc) > 0) {sql_error($qp, 'srv_dbc', "sqle_23");}
  // Get the site info
  $qr = "SELECT domain FROM partnersites WHERE id='$site_id'";
  $rr = mysqli_query ($srv_dbc, $qr);
  $row = mysqli_fetch_array($rr);
  $site_domain = "$row[0]";
  $_SESSION['rev_domain'] = "$site_domain (ID #$site_id)";

  // Redirect via Javascript wtih _POST set for security
  // Thanks https://stackoverflow.com/a/5576700/10343144
  echo "
  <form id=\"jsGoForm\" action=\"partner_del_project.php\" method=\"post\">
    <input type=\"hidden\" name=\"del_partner_site_page\" value=\"$userid\">
  </form>
  <script type=\"text/javascript\">
      document.getElementById('jsGoForm').submit();
  </script>";

  exit(); // Quit the script

} else {
  sql_error($q, 'srv_dbc', "sqle_22");
}
