<!DOCTYPE html>
<html>
<head>
  <title>Connect :: badAd</title>
  <link href="demo.css" rel="stylesheet" type="text/css" />
  <meta name="badad.api.dev.key" content="live_pub_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0" />

</head>

<body>

<?php

// Dev key
$my_developer_sec_key = 'live_sec_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0'; // This PHP variable is only used below in this example and can be changed, the value must be your dev key

// Optional Dev custom callback, uncomment to use
// IMPORTANT: If you use a custom callback URL, it will be retained in our Partner database tables, but NOT in our tables for your Dev App, so you must retain this URL in your own records if you want to keep it
//$my_custom_callback_url = 'https://example.com/some/path/dev_app_connect_custcall_sample.php?=maybe_get_args';

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
<form id="connect_partner_app_id" class="connect_partner" action="https://badad.one/connect_app.php" method="post" accept-charset="utf-8">
<p><b>Connect with a Partner App Key</b></p>

<!-- DEV NEEDS THIS -->
<input type="hidden" name="dev_key" value="'.$my_developer_sec_key.'" />

<label for="partner_app_key">Your Partner App Key:</label>
<br /><br />

<!-- DEV NEEDS THIS: name="partner_app_key" -->
<input type="text" name="partner_app_key" id="partner_app_key" size="32" required />';

// Custom callback?
if (isset($my_custom_callback_url)) {
  echo '
  <!-- DEV OPTIONAL CUSTOM CALLBACK -->
  <input type="hidden" name="custom_callback" value="'.$my_custom_callback_url.'" />';
}

echo  '
<input type="submit" value="Connect" class="formbutton" />
<br />
</form>';

// Be pretty
echo "<br /><hr /><br />";

// User login
echo '
<form id="connect_partner_app_id" class="connect_partner" action="https://badad.one/connect_app.php" method="post" accept-charset="utf-8">
<p><b>Connect by login</b></p>

<!-- DEV NEEDS THIS -->
<input type="hidden" name="dev_key" value="'.$my_developer_sec_key.'" />

<input type="submit" value="Login to Connect..." class="formbutton" />
<br />
</form>';

?>

</body>

</html>
