<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Declare an ad page for Search, Filter, Category, and Tag tools
$ad_page = true;

// Require the database connection
require (MYSQL);

// _POST the ad ID
if ((isset($_GET['id'])) && (filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $catID = preg_replace("/[^A-Za-z0-9]/","", $_GET['id']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Cleanup search form variables
if (isset($_SESSION['catID'])) {unset($_SESSION['catID']);}
if (isset($_SESSION['subcatID'])) {unset($_SESSION['subcatID']);}
if (isset($_SESSION['category'])) {unset($_SESSION['category']);}
if (isset($_SESSION['subcat'])) {unset($_SESSION['subcat']);}
if (isset($_SESSION['tagID'])) {unset($_SESSION['tagID']);}
if (isset($_SESSION['tag'])) {unset($_SESSION['tag']);}
if (isset($_SESSION['searchQuery'])) {unset($_SESSION['searchQuery']);}

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Get the category title
$catID = mysqli_real_escape_string ($dbc, $catID);
$q = "SELECT category, slug FROM categories WHERE id='$catID'";
$r = mysqli_query($dbc, $q);
if (mysqli_num_rows($r) != 1) { // Problem!
	$category = 'Error!';
	echo '<p class="error">This page has been accessed in error.</p>';
	include ('./includes/footer.html');
	exit();
}
// Fetch the category title and use it as the page title
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$category = "$row[0]";
$catslug = "$row[1]";
$_SESSION['category'] = $category;

// Subcategory?
if (isset($_GET['s'])) {
		// Valid the Subcategory ID
		if (filter_var($_GET['s'], FILTER_VALIDATE_INT, array('min_range' => 1))) {
			$subcatID = preg_replace("/[^A-Za-z0-9]/","", $_GET['s']);
			$_SESSION['subcatID'] = $subcatID;
		} else {
			header("Location: index.php");
			exit(); // Quit the script
		}
	$sq = "SELECT subcat FROM sub_$catslug WHERE id='$subcatID'";
	$sr = mysqli_query($dbc, $sq);
	$srow = mysqli_fetch_array($sr, MYSQLI_NUM);
	$subcat = "$srow[0]";
	$_SESSION['subcat'] = $subcat;

// Prepare a subcategory query
	$subcatYN = true;
	$cq = 'SELECT id, pub_status, date_expires, category_id, subcat_id, role_id, tag_ids, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc FROM ads WHERE category_id=' . $catID . ' AND subcat_id=' . $subcatID . ' AND date_starts < ' . $timeNow . ' ORDER BY week_cat_count, epoch_wk_reset DESC';
} else {
	// Prepare a no-subcategory query
	$subcatYN = false;
	$cq = 'SELECT id, pub_status, date_expires, category_id, subcat_id, role_id, tag_ids, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc FROM ads WHERE category_id=' . $catID . ' AND date_starts < ' . $timeNow . ' ORDER BY week_cat_count, epoch_wk_reset DESC';
}

// Valid the Pagination
if ((isset($_GET['p'])) && (filter_var($_GET['p'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
 $paged = preg_replace("/[^A-Za-z0-9]/","", $_GET['p']);
} else {
 $paged = 1;
}

// Header
// Set title
if ($subcatYN == false) { // No subcategory
$cattitle = "$category";
} else { // With subcategory
	$cattitle = "$category - $subcat";
}
if ($paged > 1) {
$pagetitle = "$cattitle ($paged)";
} else {
$pagetitle = "$cattitle";
}
$page_title = "$pagetitle :: $siteTitle";

// This variable disappears with the header.html file
$_SESSION['catID'] = $catID;
include ('./includes/header.html');
$catID = $_SESSION['catID'];

// Header
echo "<h3 class=\"ads\">$category"; if (isset($subcat)) {echo ": $subcat</h3><br />";} else {echo "</h3><br />";}

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
include ('inserts/category.ins.php');

// Include the HTML footer
include ('./includes/footer.html');
?>
