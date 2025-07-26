<?php

// This creates a key for mass-email "writeto..." tools, use from the terminal, DO NOT put this into the web directory

// Use from terminal with: php -f keygen.php

// This variable must be set:

$writers_email = 'masswriteadmin@badad.one';


// Config
require ('/srv/www/badad/webdir/includes/config.inc.php');
// Require the database connection
require (MYSQL);

function longString($length = 10) {
    $chrs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chrsL = strlen($chrs);
    $renderedString = '';
    for ($i = 0; $i < $length; $i++) {
        $renderedString .= $chrs[rand(0, $chrsL - 1)];
    }
    return $renderedString;
}

  // Create the new key
  $keystring = longString(255);

  // Put the string in the database
  $qd = "DELETE FROM writerkey WHERE email='$writers_email'";
  $rd = mysqli_query ($dbc, $qd);
  $q = "INSERT INTO writerkey (email, writekey) VALUES ('$writers_email', '$keystring')";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) == 1) {
    echo "writeto key has been reset for: $writers_email\n";
} else {
    echo "Database error with the new key.\n";
}

echo "$keystring\n";
