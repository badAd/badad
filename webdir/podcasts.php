<?php

// Config
require ('./includes/config.inc.php');
require_once (MYSQL);

// Search query?
if ((!isset($_GET['s'])) || ($_GET['s'] == '')) {
	$searchQuery = '';

} else {
  // Valid the Search contents & sanitise the _GET url
  $searchQuery = preg_replace("/[^A-Z0-9 \'\/&,-.:?!#$%@|]/","", $_GET['s']);
  $regex_replace = "/[^0-9a-zA-Z_ \'\/&,-.:?!#$%@|]/";
  $searchQuery = preg_replace($regex_replace,"", $_GET['s']);
  $searchQuery = trim($searchQuery);
  if ($searchQuery != $_GET['s']) {
   header("Location: blogs.php?s=$searchQuery");
   exit(); // Quit the script
  }
}

// Valid the Pagination
if ((isset($_GET['p'])) && (filter_var($_GET['p'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
 $paged = preg_replace("/[^A-Za-z0-9]/","", $_GET['p']);
} else {
 $paged = 1;
}

// Include the header
// Set title
if ($paged > 1) {
$page_title = "$siteTitle Partner Podcasts ($paged)";
} else {
$page_title = "$siteTitle Partner Podcasts";
}
include ('./includes/header.html');

echo "<h3 class=\"ads\">Partner Podcasts</h3><br />";

// Referred?
include ('includes/referred.inc.php');

// Insert all ads
include ('inserts/podcasts.ins.php');

 /* End of ads */

// Include the footer file to complete the template
require ('./includes/footer.html');

// Include the routine ad reset (not the best way, but works for now)
// include ('./includes/reset_wk_listed.inc.php'); // DEPRECIATED
?>
