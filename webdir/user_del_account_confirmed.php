<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Require the database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// If this page _POSTed back to itself with the confirmation
if (($_SERVER['REQUEST_METHOD'] === 'POST') &&
    (isset($_POST['clicked_confirm_delete_user_account'])) &&
    ($_POST['clicked_confirm_delete_user_account'] == $userid) &&
    (isset($_POST['agree_to_delete_userer_account'])) &&
    ($_POST['agree_to_delete_userer_account'] == "true")) {
    // Delete the user's partner profile and all sites
    $qs = "UPDATE ads SET pub_status='dead' WHERE user_id='$userid'";
    $rs = mysqli_query ($dbc, $qs);
    // Now see if they are gone
    $qv = "SELECT user_id FROM ads WHERE user_id='$userid' AND pub_status='live'";
    $rv = mysqli_query ($dbc, $qv);
    if (mysqli_num_rows($rv) == 0) { // No errors=approved, affected rows could be zero, now the Partner
      $qd = "INSERT INTO deletedusers (userid) VALUES ('$userid')";
      $rd = mysqli_query ($dbc, $qd);
      if (mysqli_affected_rows($dbc) != 1) {
        sql_error($qd, 'dbc', "sqle_35");
      }
      $qp = "DELETE FROM users WHERE id='$userid'"; // Sites gone, now the User
      $rp = mysqli_query ($dbc, $qp);
      if (mysqli_affected_rows($dbc) == 1) {
        // Destroy the session
        $_SESSION = array(); // Destroy the variables
        session_destroy(); // Destroy the session itself
        setcookie (session_name(), '', time()-300); // Destroy the cookie
        // Return to Main page
        header("Location: index.php");
        exit(); // Quit the script
      } else {
        sql_error($qp, 'dbc', "sqle_36");
      }
    }
}

// Check temp password in URL
if ((isset($_GET['c'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['c']))) {
  $URLconfirmkey = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['c']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Get the temp URL password time & date and other info
$timeNow = date("Y-m-d H:i:s");

$q = "SELECT date_dead, useable FROM confirmchange WHERE binary confirmkey='$URLconfirmkey'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$datedead = $row[0];
$usable = $row[1];

// Check if the temp URL password even exists
$rows = mysqli_num_rows($r);
if ($rows == 0) {
  // Include the header file
  $page_title = "Wrong Page :: $siteTitle";
  include ('includes/header.html');
  echo "That page doesn't exist.";
  // Include the HTML footer
  include ('includes/footer.html');
  exit();
}

if (($timeNow < $datedead) && ($usable == 'live')) {
  // Disable the temporary link
  $q = "UPDATE confirmchange SET useable='dead' WHERE userid='$userid'";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) > 0) { // If it ran OK.

    // Include the header file
    $page_title = "Delete User account :: $siteTitle";
    include ('includes/header.html');

    // Print a customized message
    echo "<h3 class=\"note_red\">Delete your account?</h3><br /><p class=\"note_red\">Once you do this, there's no going back.</p><p>All the private information we have pertaining to your account, including your Order History and any statistics for business ads, is still available to you free of charge. If you desire to obtain this information, do so before deleting your account. Requests for any such information we may retain after an account is deleted incurs a minimum fee of $1,000 USD, plus tech support time spent obtaining the data, and any such requests can only be made in person.</p><p>Are you sure you are ready to delete your account?</p>";

    echo "<form id=\"userdeleteaccount\" class=\"userform\" action=\"user_del_account_confirmed.php\" method=\"post\" accept-charset=\"utf-8\">
    <input type=\"hidden\" name=\"clicked_confirm_delete_user_account\" value=\"$userid\" />";
    		// Disclaimers
    		echo"
    		<p><input type=\"checkbox\" name=\"agree_to_delete_userer_account\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must indicate that you are sure you are ready to delete your account.')\" onchange=\"this.setCustomValidity('')\"/> <strong>Finally and surely delete my account.</strong></p>
    		<input type=\"submit\" name=\"submit_button\" value=\"Proceed DELETE my account\" id=\"submit_button\" class=\"set_red\" />

    </form>";


    // Include the HTML footer
		include ('includes/footer.html'); // Include the HTML footer
		exit();

	} else {
		sql_error($q, 'dbc', "sqle_58");
	}

} else {
  // Include the header file
  $page_title = "Expired :: $siteTitle";
  include ('includes/header.html');
  echo "Sorry, that link has expired.";
  // Include the HTML footer
  include ('includes/footer.html');
  exit();
}

?>
