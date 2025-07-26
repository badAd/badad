<?php

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

// _GET t
if (!isset($_GET['t'])) {
	header("Location: index.php");
	exit();
} else {
	$tag_page_name = preg_replace("/[^A-Za-z0-9 ]/","", $_GET['t']);
	$tag_page_name = strtolower($tag_page_name);
}

// Cleanup search form variables
if (isset($_SESSION['catID'])) {unset($_SESSION['catID']);}
if (isset($_SESSION['subcatID'])) {unset($_SESSION['subcatID']);}
if (isset($_SESSION['category'])) {unset($_SESSION['category']);}
if (isset($_SESSION['subcat'])) {unset($_SESSION['subcat']);}
if (isset($_SESSION['tagID'])) {unset($_SESSION['tagID']);}
if (isset($_SESSION['tag'])) {unset($_SESSION['tag']);}
if (isset($_SESSION['searchQuery'])) {unset($_SESSION['searchQuery']);}

// Get the ID for the tag
$tag_page_name = mysqli_real_escape_string ($dbc, $tag_page_name);
$q = "SELECT id FROM tags WHERE tag='$tag_page_name'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$tag_page_ID = "$row[0]";
$_SESSION['tagID'] = $tag_page_ID;

// Valid the Pagination
if ((isset($_GET['p'])) && (filter_var($_GET['p'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
 $paged = preg_replace("/[^A-Za-z0-9]/","", $_GET['p']);
} else {
 $paged = 1;
}

// Header
// Set title
if ($paged > 1) {
$tagtitle = "#$tag_page_name ($paged)";
} else {
$tagtitle = "#$tag_page_name";
}

// Set the title for the header
$page_title = "$tagtitle :: $siteTitle";
// This variable disappears with the header.html file
$_SESSION['tag'] = $tag_page_name;
include ('./includes/header.html');
$tag_page_name = $_SESSION['tag'];

// In-page title
echo "<h3 class=\"ads\">Tag: #$tag_page_name</h3><br />";

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
include ('inserts/tag.ins.php');

// Include the footer file to complete the template
require ('./includes/footer.html');
?>
