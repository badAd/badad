<!DOCTYPE html>
<html>
<head>
  <title>Connect :: badAd</title>
  <link href="demo.css" rel="stylesheet" type="text/css" />
  <!-- <meta name="badad.api.dev.key" content="live_pub_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0" /> NOT NECESSARY if custom callback differes from native callback because it is not checked -->

</head>

<body>

<?php

// Dev key
//$my_developer_sec_key = 'live_sec_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0'; // Not used since it is not sent by this [custom] callback-only page

// Capture response
if ((isset($_POST['badad_connect_response']))
&& (isset($_POST['partner_app_key']))
&& (isset($_POST['partner_call_key']))
&& (isset($_POST['partner_refcred']))
&& (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['partner_app_key']))
&& (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['partner_call_key']))
&& (preg_match ('/^call_key_(.*)/i', $_POST['partner_call_key']))
&& (preg_match ('/[a-zA-Z0-9]$/i', $_POST['partner_refcred']))) { // _POST all present and mild regex check

  $partner_app_key = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['partner_app_key']); // This is the key you just sent, the last time it will ever be used, deleted from our servers
  $partner_call_key = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['partner_call_key']); // Starts with: "call_key_" Keep this in your database for future API calls with this connected partner, it starts with: "call_key_"
  $partner_refcred = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['partner_refcred']); // Rhe "resite.html" URL, acting as BOTH a badAd click for Partner shares AND as a referral link for ad credits uppon purchase of a new customer

// Below is only for demo purposes
  echo "<div class=\"connected\">
<p><b>Connected!</b><br /><br />
key1: $partner_app_key<br />
key2: $partner_call_key<br />
refcredURL: $partner_refcred</p></div>";

/* Probably SQL INSERT $partner_call_key query here to save this call_key_ ...the whole reason we did this*/

/* Possibly include(dev_app_fetchmeta_sample.php); here so your user can see the successful connection listed */

exit();

}

// Forms to connect

// User app_key
echo '
<p>Not connected yet, nothing to see.</p>';

?>

</body>

</html>
