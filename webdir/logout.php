<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Check for _SESSION login count
if (isset($_SESSION['login_attempt'])) {$login_count = $_SESSION['login_attempt'];}

// Destroy the session
$_SESSION = array(); // Destroy the variables
session_destroy(); // Destroy the session itself
setcookie(session_name(), null, 86401); // Set any _SESSION cookies to expire in Jan 1970

// Destroy any persistent login cookies (small steps to be sure)
if ((isset($_COOKIE['rememberme_a'])) && (isset($_COOKIE['rememberme_b']))) {
  $rememberme_key_a = $_COOKIE['rememberme_a'];
  $rememberme_key_b = $_COOKIE['rememberme_b'];
  $q = "DELETE FROM rememberme WHERE binary key_a='$rememberme_key_a' AND binary key_b='$rememberme_key_b'"; // "binary" makes sure case and characters are exact
	$r = mysqli_query ($dbc, $q);
}
if (isset($_COOKIE['rememberme_a'])) {
    unset($_COOKIE['rememberme_a']);
    setcookie('rememberme_a', null, -1, '/');
}
if (isset($_COOKIE['rememberme_b'])) {
    unset($_COOKIE['rememberme_b']);
    setcookie('rememberme_b', null, -1, '/');
}


// Process the after-logout
if (isset($login_count)) { // Restore any _SESSION login count

  // Redirect via Javascript wtih _POST set for security
  // Thanks https://stackoverflow.com/a/5576700/10343144
  echo "
  <form id=\"jsGoForm\" action=\"logout_redirect.php\" method=\"post\">
    <input type=\"hidden\" name=\"lc\" value=\"$login_count\">
  </form>
  <script type=\"text/javascript\">
      document.getElementById('jsGoForm').submit();
  </script>";

} else { // If no _SESSION login count, just go easy
  header("Location: logout_redirect.php"); // We can't set $_SESSION['logged_out'] on the same page we destroyed the session
  exit(); // Quit the script
}

?>
