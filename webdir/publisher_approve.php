<?php

// Config
require_once ('includes/config.inc.php');
require_once (MYSQL);

// Login check
include_once ('includes/login_check.inc.php');

// If the user isn't logged in, redirect them
if (!isset($_SESSION['user_id'])) {
	header("Location: index.php");
	exit(); // Quit the script
} else {
	$userid = $_SESSION['user_id'];
}

// If the user doens't have editing privileges, redirect
if ((!$_SESSION['user_is_admin']) && (!$_SESSION['user_is_supervisor']) && (!$_SESSION['user_is_publisher'])) {
	header("Location: index.php");
	exit(); // Quit the script
}

// Process the GET
if (($_SERVER['REQUEST_METHOD'] === 'GET') && (isset($_GET['pa'])) && (filter_var($_GET['pa'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {

	// Podcast ad ID
	$ir_ad_id = preg_replace("/[^0-9]/","", $_GET['pa']);

} else {
	header("Location: index.php");
	exit(); // Quit the script
}

// Assure current user for oversight
$q = "UPDATE pod_ads SET publisher_user='$userid', date_modified=NOW() WHERE ad_id='$ir_ad_id' AND status='recorded' AND (publisher_user=0 OR publisher_user='$userid')";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 0) {
	header("Location: publisher.php");
	exit(); // Quit the script
} elseif (!$r) {
	sql_error($q, 'dbc', "sqle_154");
}

// Load the project for review
$q = "SELECT id, approved_manuscript FROM pod_ads WHERE ad_id='$ir_ad_id' AND status='recorded' AND (publisher_user=0 OR publisher_user='$userid')";
$r = mysqli_query ($dbc, $q);
if ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
	$pod_ad_id = "$row[0]";
	$ir_approved_manuscript = "$row[1]";
} else {
	header("Location: index.php");
	exit(); // Quit the script
}

// Include the header file
$page_title = "Final Publishing :: $siteTitle";
include ('./includes/header.html');

// Settings so <audio> tags work
// Thanks https://stackoverflow.com/questions/49547/how-do-we-control-web-page-caching-across-all-browsers
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1 (needs all three)
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
header("Vary: *"); // HTTPS

// Start the page
echo "<h3>Final Publishing</h3>";

echo "<h4>Manuscript:</h4>";
echo "<br /><hr /><br /><br />";
echo "<div style=\"width:70%;\"><p style=\"text-align:center;\"><big>$ir_approved_manuscript</big></p></div>";

// Audio
echo "<br /><hr /><br />";

echo '<audio><source src="https://'.$podcastServeDomain.'/media/ba-'.$pod_ad_id.'.mp3" type="audio/mpeg"></audio>';
echo '<p><big><a target="_blank" href="https://'.$podcastServeDomain.'/media/ba-'.$pod_ad_id.'.mp3">listen in new tab</a></big></p>';
echo "<br /><br />";

set_switch("Approve &amp; publish", "Approve this ad recording and publish it now", "publisher.php", "publish_approve", $ir_ad_id, "set_green");
echo "<br /><br />";
set_switch("Reject for re-recording", "Reject this ad recording and requeue it for audio recording", "publisher.php", "publish_reject", $ir_ad_id, "set_yellow");
echo "<br /><br />";

// Include the HTML footer
include ('./includes/footer.html');
?>
