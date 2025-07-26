<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Users
if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

// No new ads for at-risk accounts
if (isset($_SESSION['no_status'])) {
	header("Location: account_info.php");
	exit(); // Quit the script
}

// Do we have a freekey?
if ((isset($_GET['p'])) && (preg_match ('/[a-zA-Z0-9]$/i', $_GET['p']))) {
	$p_key = $_GET['p'];
	$p = mysqli_real_escape_string ($dbc, $p_key);
	$q = "SELECT id FROM freekeys WHERE BINARY purchase_key='$p' AND user_id='$userid' AND purchase_useable='live'";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_num_rows($r) == 1) {
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		// Store the key ID in a session
		$_SESSION['purchase_key_id'] = $row[0];
	}
}

// Clear any rerun _SESSION
if (isset($_SESSION['rerun_ad'])) {
  unset($_SESSION['rerun_ad']);
}

// Login form
$login_form_action = "new_ad.php";
// Include the header
$page_title = "Buy a New Ad :: $siteTitle";
$wordcount_textarea = true;
include ('includes/header.html');

// Insert the ad checks
include ('inserts/new_ad.check.ins.php');

// Dup check
if (($_SERVER['REQUEST_METHOD'] != 'POST') || (!isset($_POST['adform']))) {
	include ('./inserts/new_ad_dup.check.ins.php');
} elseif (isset($_POST['adform'])) { // Kiddy Check
	if ((preg_match('/[^a-zA-Z0-9]$/i', $_POST['adform'])) || ($_POST['adform'] != 'submitted')) {$IP = get_ip_addr(); script_kiddy('sk', '_POST adform', $_POST['adform'], $IP);}
}

// Redirect to the New Ad Cart if finished and valid
if ((isset($_SESSION['validAd'])) && ($_SESSION['validAd'] == true) && (!isset($_SESSION['edit_back']))) {
	echo "
  <form id=\"jsGoForm\" action=\"https://$siteDomain/new_ad_cart.php\" method=\"post\">
    <input type=\"hidden\" name=\"ad_ready\" value=\"ready\">
  </form>
  <script type=\"text/javascript\">
      document.getElementById('jsGoForm').submit();
  </script>";

	exit(); // Quit the script
} else {
	unset($_SESSION['edit_back']);
}

// Freekey?
if (isset($_SESSION['purchase_key_id'])) {
	echo '<p class="note_blue">Free key mode, $0/wk</p>';
}

// In-page title
echo "<h3>Buy a New Ad:</h3><br />";

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
include ('inserts/new_ad.ins.php');

 // Include the HTML footer
include ('includes/footer.html');
?>
