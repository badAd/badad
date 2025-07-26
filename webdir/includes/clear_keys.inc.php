<?php

// These must be set: $new_ad_weekslong, $sformaction
// This comes later to work with calculate_total.inc.php

// User ID
if (isset($_SESSION['user_id'])) {
	$userid = $_SESSION['user_id'];
} else {
	return;
}

// Require the database connection
require_once (MYSQL);

// Clear the keys
$q = "UPDATE loginonce SET useable='dead' WHERE useable='live' AND userid='$userid'";
$r = mysqli_query ($dbc, $q);
$q = "UPDATE logincode SET useable='dead' WHERE useable='live' AND userid='$userid'";
$r = mysqli_query ($dbc, $q);
$q = "UPDATE rememberme SET useable='expired' WHERE useable='live' AND userid='$userid' AND date_expires <= CURRENT_TIMESTAMP";
$r = mysqli_query ($dbc, $q);
