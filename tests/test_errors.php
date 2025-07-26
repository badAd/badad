<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Listing the ads needs the _SRV and _EML configs
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('./includes/config_eml.inc.php');
require_once (MYSQL_EML);


// Include the header file
$page_title = "Error test page";
include ('./includes/header.html');

// Message
echo "<h3>Error test page</h3>";

/* UPDATE
$q = "UPDATE users SET username='$u', email='$e', name='$na', project='$pr', status='ok' WHERE id='$userID'";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 1)
*/

/* SELECT
$q = "SELECT id FROM users";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 1)
*/

// First error
$q = "SELECT id FROM users";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$retrieved_dbc = "$row[0]";
$rows = mysqli_num_rows($r);
if ($rows == 1) {
  sql_error($q, "dbc", "sqle_1");
  trigger_error("Database error!");
} else {
  echo "<br />DBC checks out.<br />";
}

// Second error
$qe = "SELECT id FROM global_subcat_ids";
$re = mysqli_query ($srv_dbc, $qe);
$row = mysqli_fetch_array($re, MYSQLI_NUM);
$retrieved_srv = "$row[0]";
if ($rows != 1) { // should be "== 1", set to trigger the error
  sql_error($qe, "srv_dbc", "sqle_2");
  trigger_error("Database error!");
} else {
  echo "<br />SRV_DBC checks out.<br />";
}

// Third error
$qp = "SELECT id FROM pantry";
$rp = mysqli_query ($eml_dbc, $qp);
$row = mysqli_fetch_array($rp, MYSQLI_NUM);
$retrieved_eml = "$row[0]";
if ($rows == 1) {
  sql_error($qp, "eml_dbc", "sqle_3");
  trigger_error("Database error!");
} else {
  echo "<br />EML_DBC checks out.<br />";
}

// Include the footer file to complete the template
require ('./includes/footer.html');
?>
