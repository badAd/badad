<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Declare an ad page for Search, Filter, Category, and Tag tools
$ad_page = true;

// Require the database connection
require (MYSQL);

// For no Category ID
if (!isset($_GET['s'])) {
	header("Location: index.php");
	exit();
}

// Valid the Search contents & sanitise the _GET url
$searchQuery = preg_replace("/[^A-Za-z0-9 \'\/&,:%-]/"," ", $_GET['s']);
$searchQuery = trim($searchQuery);
if ($searchQuery != $_GET['s']) {
 header("Location: search.php?s=$searchQuery");
 exit(); // Quit the script
}

// Valid the Pagination
if ((isset($_GET['p'])) && (filter_var($_GET['p'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
 $paged = preg_replace("/[^A-Za-z0-9]/","", $_GET['p']);
} else {
 $paged = 1;
}

// Header
	// Set title
	if ($paged > 1) {
	$searchtitle = "$searchQuery ($paged)";
} else {
	$searchtitle = "$searchQuery";
}
$page_title = "Search: $searchtitle :: $siteTitle";

// This variable disappears with the header.html file
$_SESSION['searchQuery'] = $searchQuery;
include ('./includes/header.html');
$searchQuery = $_SESSION['searchQuery'];

// Display the search query
echo "<h3 class=\"ads\">Search: $searchQuery</h3><br />";

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
include ('inserts/search.ins.php');

// Include the HTML footer
include ('./includes/footer.html');
?>
