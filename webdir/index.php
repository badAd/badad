<?php
// In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Declare an ad page for Search, Filter, Category, and Tag tools
$ad_page = true;

// To test the sidebars
// $_SESSION['user_id'] = 1;
// $_SESSION['user_admin'] = true;

// Require the database connection
require (MYSQL);

// Valid the Pagination
if ((isset($_GET['p'])) && (filter_var($_GET['p'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
 $paged = preg_replace("/[^A-Za-z0-9]/","", $_GET['p']);
} else {
 $paged = 1;
}

// Cleanup search form variables
if (isset($_SESSION['catID'])) {unset($_SESSION['catID']);}
if (isset($_SESSION['subcatID'])) {unset($_SESSION['subcatID']);}
if (isset($_SESSION['category'])) {unset($_SESSION['category']);}
if (isset($_SESSION['subcat'])) {unset($_SESSION['subcat']);}
if (isset($_SESSION['tagID'])) {unset($_SESSION['tagID']);}
if (isset($_SESSION['tag'])) {unset($_SESSION['tag']);}
if (isset($_SESSION['searchQuery'])) {unset($_SESSION['searchQuery']);}

// Clear any old SESSION form values
include ('./includes/ad_values_unset.inc.php');

// Include the header
// Set title
if ($paged > 1) {
$page_title = "$siteTitle Text Ads: Monetize, Advertise & Search ($paged)";
} else {
$page_title = "$siteTitle Text Ads: Monetize, Advertise & Search";
}
include ('./includes/header.html');

echo "<h3 class=\"ads\">Text Ads:<br />Monetize, Advertise & Search</h3><br />";

// Referred?
include ('includes/referred.inc.php');

// Insert all ads
include ('inserts/all_ads.ins.php');

 /* End of ads */

// Include the footer file to complete the template
require ('./includes/footer.html');

// Include the routine ad reset (not the best way, but works for now)
// include ('./includes/reset_wk_listed.inc.php'); // DEPRECIATED
?>
