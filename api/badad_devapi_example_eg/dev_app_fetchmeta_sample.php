<!DOCTYPE html>
<html>
<head>
  <title>Meta :: badAd</title>
  <link href="demo.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h1>badAd Fetch Partner Meta API Sample</h1>
<?php

// Dev key
$my_developer_sec_key = 'live_sec_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0';
/* Probably SQL query here
$partner_call_key = $row['call_key_blablabla']; (you received for this user at the handshake)
the dummy key below will only work if you use your 'test_sec_blablabla' key */
$partner_call_key = 'call_key_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0'; // You retrieved this from our _POST response at the handshake and probably set this as a variable value queried from your own database

  $post = http_build_query(
    array(
      'dev_key' => $my_developer_sec_key,
      'call_key' => $partner_call_key,
      //'refcred' => true, // Optional, default false, retrieves ONLY the "resite.html" URL, acting as BOTH a badAd click for Partner shares AND as a referral link for ad credits uppon purchase of a new customer; capture and store this to use with any img/test link to avoid the loadtime slowdown of an extra badAd API request
    )
  );

  $optns = array('http' =>
    array(
      'method' => 'POST',
      'headder' => 'Content-type: application/x-www-form-urlencoded',
      'content' => $post
    )
  );

  $context = stream_context_create($optns);
  $response = file_get_contents('https://api.badad.one/fetchmeta.php', false, $context);
  echo "<div class=\"connected\"><p>$response</p></div>"; // This $response is the HTML payload fetched from our Dev API
  exit();

?>

</body>

</html>
