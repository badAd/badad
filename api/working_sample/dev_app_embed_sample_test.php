<!DOCTYPE html>
<html>
<head>
  <title>Embed :: badAd</title>
  <link href="demo.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h1>badAd Embed API Sample</h1>
<?php

// Dev key
$my_developer_sec_key = 'test_sec_2MmlhBtjdq0fSuqY3N0Oq5S0aRXHnYboloKXDRvUyyEBsHiFV3maGN288CAIjrTJ';
$partner_call_key = 'call_key_qUWXob8ZtxQpGi7cpkrrQFakCGv64N7nbpwkM4V6LGYiRw9wb90nQRbwG4DKlN1B'; // You retrieved this from our _POST response at the handshake (above) and probably set this as a variable value queried from your own database

  $post = http_build_query(
    array(
      'num_ads' => 4, // Optional, 1-10, default 1
      'show_badad_link' => true, // Optional, default false
      'inline_div' => true, // Optional, default false

      'dev_key' => $my_developer_sec_key,
      'call_key' => $partner_call_key
    )
  );

  $optns = array('http' =>
    array(
      'method' => 'POST',
      'header' => 'Content-Type: application/x-www-form-urlencoded',
      'content' => $post
    )
  );

  $context = stream_context_create($optns);
  $response = file_get_contents('https://api.badad.one/render.php', false, $context);
  echo "<div class=\"connected\"><p>$response</p></div>"; // This $response is the HTML payload fetched from our Dev API
  exit();

?>

</body>

</html>
