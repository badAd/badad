<!DOCTYPE html>
<html>
<head>
  <title>Connect :: badAd</title>
  <link href="demo.css" rel="stylesheet" type="text/css" />
  <!-- <meta badad_api_dev_key="live_pub_XXXXXXXXX_my_developer_pub_key" /> -->
  <meta name="badad.api.dev.key" content="test_pub_HbJqAqi3CZO44txJdERTWXeUDb5WF6xvbsVBDRiCZUKBhjG7dM9f4NCkfLKMyXTC" />

</head>

<body>

<?php

// Dev key
$my_developer_sec_key = 'test_sec_2MmlhBtjdq0fSuqY3N0Oq5S0aRXHnYboloKXDRvUyyEBsHiFV3maGN288CAIjrTJ'; // This PHP variable is only used below in this example and can be changed, the value must be your dev key

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

  echo "<div class=\"connected\">
<p><b>Connected!</b><br /><br />
key1: $partner_app_key<br />
key2: $partner_call_key<br />
refcredURL: $partner_refcred</p></div>";
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
<input type="text" name="partner_app_key" id="partner_app_key" size="32" required />

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
