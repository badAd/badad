<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// Check temp password in URL
if ((isset($_GET['c'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['c']))) {
  $URLconfirmkey = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['c']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

// If the user isn't logged in, redirect them
if (!isset($_SESSION['user_id'])) {
  if (!isset($_SESSION['user_id'])) {
    // Include the header file
    $page_title = "Password Required :: $siteTitle";
    include ('includes/header.html');
    echo '<p class="note_red">You must be logged-in to access this page.</p>';
    $lformaction = "partner_del_account_confirmed.php?c=$URLconfirmkey"; // This must be set for login_form.inc.php to work
    include ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
    // Include the HTML footer
    include ('includes/footer.html');
    exit();
  }
}
$userid = $_SESSION['user_id'];

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// If this page _POSTed back to itself with the confirmation
if (($_SERVER['REQUEST_METHOD'] === 'POST') &&
    (isset($_POST['clicked_confirm_delete_partner_account'])) &&
    ($_POST['clicked_confirm_delete_partner_account'] == $userid) &&
    (isset($_POST['agree_to_delete_partner_account'])) &&
    ($_POST['agree_to_delete_partner_account'] == "true")) {
    // Delete the user's partner profile and all sites
    $qs = "DELETE FROM partnersites WHERE user_id='$userid'";
    $rs = mysqli_query ($srv_dbc, $qs);
    // Now see if they are gone
    $qv = "SELECT user_id FROM partnersites WHERE user_id='$userid'";
    $rv = mysqli_query ($srv_dbc, $qv);
    if (mysqli_num_rows($rv) == 0) { // No errors=approved, affected rows could be zero, now the Partner

      // Get the id from the partners row
      $q = "SELECT id FROM partners WHERE user_id='$userid'";
      $r = mysqli_query ($dbc, $q);
      $row = mysqli_fetch_array ($r, MYSQLI_NUM);
      $partnerrowid = $row[0];

      $qd = "INSERT INTO deletedpartners (userid, partnerid) VALUES ('$userid', '$partnerrowid')";
      $rd = mysqli_query ($dbc, $qd);
      if (mysqli_affected_rows($dbc) != 1) {
        sql_error($q, 'dbc', "sqle_9");
      }
      $qp = "DELETE FROM partners WHERE user_id='$userid'"; // Sites gone, now the Partner
      $rp = mysqli_query ($dbc, $qp);
      if (mysqli_affected_rows($dbc) == 1) {
        // Return to Main page
        header("Location: index.php");
        exit(); // Quit the script
      } else {
        sql_error($q, 'dbc', "sqle_10");
      }
    }
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
    $page_title = "Delete Partner Account :: $siteTitle";
    include ('includes/header.html');

    // Print a customized message
    echo "<h3 class=\"note_red\">Delete your Partner account?</h3><br /><p class=\"note_red\">Once you do this, there's no going back. Any records in <a title=\"View history\" href=\"partner_site_history.php\">History</a> must be kept for our bookkeeping.</p><p>All the private information we have pertaining to your Partner account is still available to you free of charge. If you desire to obtain this information, do so before deleting your Partner account. Requests for any such information we may retain after an account is deleted incurs a minimum fee of $1,000 USD, plus tech support time spent obtaining the data, and any such requests can only be made in person.</p><p>Are you sure you are ready to delete your Partner account?</p>";

    echo "<form id=\"partnerdeleteaccount\" class=\"userform\" action=\"partner_del_account_confirmed.php?c=$URLconfirmkey\" method=\"post\" accept-charset=\"utf-8\">
    <input type=\"hidden\" name=\"clicked_confirm_delete_partner_account\" value=\"$userid\" />";
    		// Disclaimers
    		echo"
    		<p><input type=\"checkbox\" name=\"agree_to_delete_partner_account\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must indicate that you are sure you are ready to delete your account.')\" onchange=\"this.setCustomValidity('')\"/> <strong>Finally and surely delete my Partner account.</strong></p>
    		<input type=\"submit\" name=\"submit_button\" value=\"Proceed DELETE my Partner account\" id=\"submit_button\" class=\"set_red\" />

    </form>";

    // Include the HTML footer
		include ('includes/footer.html'); // Include the HTML footer
		exit();

	} else {
		sql_error($q, 'dbc', "sqle_66");
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
