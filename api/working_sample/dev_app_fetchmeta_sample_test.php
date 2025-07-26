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
$my_developer_sec_key = 'test_sec_2MmlhBtjdq0fSuqY3N0Oq5S0aRXHnYboloKXDRvUyyEBsHiFV3maGN288CAIjrTJ';
$partner_call_key = 'call_key_qUWXob8ZtxQpGi7cpkrrQFakCGv64N7nbpwkM4V6LGYiRw9wb90nQRbwG4DKlN1B';

  $post = http_build_query(
    array(
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
  $response = file_get_contents('https://api.badad.one/fetchmeta.php', false, $context);
  echo "<div class=\"connected\"><p>$response</p></div>"; // This $response is the HTML payload fetched from our Dev API
  exit();

?>

</body>

</html>
