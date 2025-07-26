<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Include the header file
$page_title = 'Test SQL Error';
include ('./includes/header.html');

// Create a bogus query
$q = "SELECT nothere FROM notatable";
$r = mysqli_query ($dbc, $q);
if (mysqli_num_rows($r) > 0) {
  echo "Success!";
} else {
sql_error($q, dbc, "sqle_1");
}

// Include the HTML footer
include ('./includes/footer.html');
?>
