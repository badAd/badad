<h1>Send _POST values via file_get_contents()</h1>

<?php

  $post = http_build_query(
    array(
      'name' => 'Johansen',
      'title' => 'Grand Excellency'
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
  $response = file_get_contents('process.php', false, $context);
  echo "The var_dump:<br />"
  var_dump($response);
  exit();

?>
