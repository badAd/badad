<?php
// User ID
if ((isset($_SESSION['user_id'])) && (isset($_SESSION['rememberme_checked'])) && ($_SESSION['rememberme_checked'] == true)) {
	$userid = $_SESSION['user_id'];
	unset($_SESSION['rememberme_checked']);
} else {
	return;
}

// Get the time
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);
$cookie_expires = $timeNowEpoch + (30 * 24 * 60 * 60);
// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');
// Create the delkey string
$rememberme_key_a = longString(255);
	// Dup check
	$q = "SELECT key_a FROM rememberme WHERE binary key_a='$rememberme_key_a'"; // "binary" makes sure case and characters are exact
	$r = mysqli_query ($dbc, $q);
	while ($dup = mysqli_fetch_array($r)) {
		$rememberme_key_a = longString(255);
	}

	// BROKEN WAY OF DOING THINGS
	// while (mysqli_num_rows($row)) {
	//   $rememberme_key_a = longString(255);
	//   // Check again
	// 	$q = "SELECT key_a FROM rememberme WHERE binary key_a='$rememberme_key_a'"; // "binary" makes sure case and characters are exact
	// 	$r = mysqli_query ($dbc, $q);
	//   if (mysqli_num_rows($row) == 0) {
	//     break;
	//   }
	// }
$rememberme_key_b = longString(255);
	// Dup check
	$q = "SELECT key_b FROM rememberme WHERE binary key_b='$rememberme_key_b'"; // "binary" makes sure case and characters are exact
	$r = mysqli_query ($dbc, $q);
	while ($dup = mysqli_fetch_array($r)) {
		$rememberme_key_b = longString(255);
	}
	// BROKEN WAY OF DOING THINGS
	// while (mysqli_num_rows($row)) {
	// 	$rememberme_key_b = longString(255);
	// 	// Check again
	// 	$q = "SELECT key_b FROM rememberme WHERE binary key_b='$rememberme_key_b'"; // "binary" makes sure case and characters are exact
	// 	$r = mysqli_query ($dbc, $q);
	// 	if (mysqli_num_rows($row) == 0) {
	// 		break;
	// 	}
	// }
// Create & set the cookie values
setcookie("rememberme_a", $rememberme_key_a, $cookie_expires);
setcookie("rememberme_b", $rememberme_key_b, $cookie_expires);

// Database insert
$q ="INSERT INTO rememberme (userid, key_a, key_b, date_expires) VALUES ($userid, '$rememberme_key_a', '$rememberme_key_b', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -30 DAY))";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) != 1) { // If it didn't run OK
	sql_error($q, 'dbc', "sqle_54");
}
