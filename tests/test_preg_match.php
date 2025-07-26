<?php

// Validate the serial number
$site_serial = $_GET['l'];
if (preg_match('/[^a-zA-Z0-9]/', $site_serial)) {

  exit();

} else {

  // Retrieve the ad content
  echo "$site_serial";
}

?>
