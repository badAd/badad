<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Login count page
if ((isset($_SESSION['login_attempt'])) && ($_SESSION['login_attempt'] > 3)) {
	$page_title = "Log In Failure";
	include ('./includes/header.html');
	echo '<h3 class="note_red">Log In Failure</h3><p class="note_red">You tried to login too many times. Try again later.</p>';
	include ('./includes/footer.html');
	exit();
}

// Check temp password in URL
if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['p']))) {
  // Login attempts counter (no _POST required)
  if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = 1;
  } else {
    $_SESSION['login_attempt'] = $_SESSION['login_attempt'] + 1;
  }
  // Set the temp pass key variable
  $tempURLpass = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['p']);

} else {
	header("Location: index.php");
  exit(); // Quit the script
}

// Redirect Logged-in users
if (isset($_SESSION['user_id'])) {
	$userid = $_SESSION['user_id'];
  $q = "UPDATE loginonce SET useable='dead' WHERE useable='live' AND userid='$userid'";
  $r = mysqli_query ($dbc, $q);
  header("Location: index.php");
  exit(); // Quit the script
}

// Check the key
$q = "SELECT userid FROM loginonce WHERE date_dead > CURDATE() AND useable='live' AND binary temppass='$tempURLpass'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$userid = $row[0];
if (mysqli_num_rows($r) == 1) {
  $q = "UPDATE loginonce SET useable='used' WHERE binary temppass='$tempURLpass'";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) == 1) {

    // Retrieve the user info
    $q = "SELECT username, status, type, email, confirmed_email, name, tfa, need_accept_new_tc, need_see_security_notice, need_lookover_account_info FROM users WHERE id='$userid'";
    $r = mysqli_query ($dbc, $q);
    if (mysqli_num_rows($r) == 1) { // Login success
			$row = mysqli_fetch_array ($r, MYSQLI_NUM);
			$username = $row[0];
			$user_status = $row[1];
			$tfa = $row[6];

			// Set our header message here since this login did not originate with the login_check.inc
			$_SESSION['login_success'] = true;

			// Check to make sure email_link is the user's preference
			if ($tfa != 'email_link') {
				$_SESSION['login_attempt'] = 5;
				// Get out of here
				header("Location: index.php");
				exit(); // Quit the script
			}

      // Store the data in a session
      $_SESSION['username'] = $username;
      $_SESSION['user_id'] = $userid;
      $_SESSION['type'] = $row[2];
      $set_email = $row[3];
      $confirmed_email = $row[4];
      $_SESSION['user_name'] = $row[5];
      $_SESSION['user_email'] = $set_email;
			$_SESSION['need_accept_new_tc'] = $row[7];
			$_SESSION['need_see_security_notice'] = $row[8];
			$_SESSION['need_lookover_account_info'] = $row[9];
			if ($_SESSION['need_accept_new_tc'] == false) {unset($_SESSION['need_accept_new_tc']);}
			if ($_SESSION['need_see_security_notice'] == false) {unset($_SESSION['need_see_security_notice']);}
			if ($_SESSION['need_lookover_account_info'] == false) {unset($_SESSION['need_lookover_account_info']);}
			if (isset($_SESSION['tfa_mode'])) {unset($_SESSION['tfa_mode']);}
			if (isset($_SESSION['tfa_email_link'])) {unset($_SESSION['tfa_email_link']);}
			if (isset($_SESSION['login_attempt'])) {unset($_SESSION['login_attempt']);}
      // Email status
      if ($user_status != 'ok') {
        $_SESSION['no_status'] = true;
        header("Location: account_info.php");
        exit(); // Quit the script
      } elseif (($set_email != $confirmed_email) || ($confirmed_email == 'Unconfirmed')) {
        $_SESSION['email_unconfirmed'] = true;
      } elseif ((isset($_SESSION['rememberme_checked'])) && ($_SESSION['rememberme_checked'] == true)) { // Stay logged in
				include_once ('./includes/set_rememberme.inc.php');
			}

      // Indicate if the user's account is admin
      if ($row[3] == 'admin') $_SESSION['user_is_admin'] = true;
      if ($row[3] == 'editor') $_SESSION['user_is_editor'] = true;

			// Kill any loginonce and logincode keys
		  include_once ('./includes/clear_keys.inc.php');
      // Check for old confirm_change links
      $q = "SELECT id FROM confirmchange WHERE userid='$userid' AND useable='live'";
      $r = mysqli_query ($dbc, $q);
      if (mysqli_num_rows($r) > 0) {
        $_SESSION['user_must_verify'] = true;
      }
      // Check for old Partner confirm_change links
      $q = "SELECT id FROM confirmpartnerchange WHERE userid='$userid' AND useable='live'";
      $r = mysqli_query ($srv_dbc, $q);
      if (mysqli_num_rows($r) > 0) {
        $_SESSION['partner_must_verify'] = true;
      }
			// Check for Partner notices
			$q = "SELECT need_accept_new_tc, need_see_new_categories FROM partners WHERE user_id='$userid'";
			$r = mysqli_query ($dbc, $q);
			if (mysqli_num_rows($r) == 1) { // user is Partner
				$row = mysqli_fetch_array ($r, MYSQLI_NUM);
				$_SESSION['partner_need_accept_new_tc'] = $row[0];
				$_SESSION['partner_need_see_new_categories'] = $row[1];
				if ($_SESSION['partner_need_accept_new_tc'] == false) {unset($_SESSION['partner_need_accept_new_tc']);}
				if ($_SESSION['partner_need_see_new_categories'] == false) {unset($_SESSION['partner_need_see_new_categories']);}
			}

			// Go home
			header("Location: index.php");
			exit(); // Quit the script

    } else { // No match was made
        sql_error($q, 'dbc', "sqle_5");
    }
  } else { // Set loginonce key as used
      sql_error($q, 'dbc', "sqle_4");
  }
} else { // If the key is expired and no user logged in
    sql_error($q, 'dbc', "sqle_3");
}

?>
