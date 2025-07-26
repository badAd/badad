<?php

// Configs
require_once ('./includes/config.inc.php');
require_once (MYSQL);
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Get the date
$timeNow = date("Y-m-d H:i:s");
// Time Zone
// $q = "SELECT @@system_time_zone";
// $r = mysqli_query ($agg_dbc, $q);
// $row = mysqli_fetch_array($r);
// $timeZone = "$row[0]";
$timeZone = date("O");

// Check for form submission and set the $siteID variable
if (($_SERVER['REQUEST_METHOD'] == 'GET') &&
(isset($_GET['i'])) && (isset($_GET['s'])) && (isset($_GET['e'])) &&
(filter_var($_GET['i'], FILTER_VALIDATE_INT, array('min_range' => 1))) &&
(filter_var($_GET['s'], FILTER_VALIDATE_INT, array('min_range' => 10, 'max_range' => 10))) &&
(filter_var($_GET['e'], FILTER_VALIDATE_INT, array('min_range' => 10, 'max_range' => 10)))) {
$siteID = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['i']);
$start_epoch = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['s']);
$end_epoch = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['e']);
$start_date = date("Y-m-d H:i:s", substr($_GET['s'], 0, 10));
$end_date = date("Y-m-d H:i:s", substr($_GET['e'], 0, 10));
} else {
	header("Location: partner.php");
	exit(); // Quit the script
}

// Check if partner account has been activated
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows == 1) { // partner account exists in database
	$activation = $row[0];
	if ($activation != "Confirmed") { // Not activated
		header("Location: partner.php");
    exit(); // Quit the script
	}
} else { // No partner application entry
		// Check to see if user's email is verified
		$qe = "SELECT email, confirmed_email FROM users WHERE id='$userid'";
		$re = mysqli_query ($dbc, $qe);
		$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
		$email = "$rowe[0]";
		$email_confirmed = "$rowe[1]";
		if ($email != $email_confirmed) {
			header("Location: partner.php");
	    exit(); // Quit the script
	} else { // email verified
		header("Location: partner.php");
    exit(); // Quit the script
	}
} // activated check complete, user is a fully-fledged partner

// See if user owns the site & get important information
$q = "SELECT date_created, domain, nickname, connected_callback, dev_authorized_id, type, source, serial_no FROM partnersites WHERE id='$siteID' AND user_id='$userid'";
$r = mysqli_query ($srv_dbc, $q);
if (mysqli_num_rows($r) == 0) {
	echo "noragne";
	//header("Location: partner.php");
	exit(); // Quit the script
} else {
	$row = mysqli_fetch_array($r);
	$site_date_created = $row[0];
	$site_domain = $row[1];
	$site_nickname = $row[2];
	$site_connect_callback = $row[3];
	$site_dev_authorized_id = $row[4];
	$site_type = $row[5];
	$site_source = $row[6];
	$site_slug = $row[7];

	if ($site_nickname == NULL) {$site_nickname = "none";}
	if ($site_connect_callback == NULL) {$site_connect_callback = "none";}
	if ($site_dev_authorized_id == 0) {$site_dev_authorized_id = "none";}
}

// Dev API info
$q = "SELECT domain, name FROM devkeys WHERE id='$site_dev_authorized_id'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array($r);
if (mysqli_num_rows($r) == 0) {
	$site_dev_domain = "not_found/deleted";
	$site_dev_name = "not_found/deleted";
} else {
	$site_dev_domain = $row[0];
	$site_dev_name = $row[1];
}

// Make sure we're not in the future or before site was created
if ($end_date > $timeNow) {$end_date = $timeNow;}
if ($start_date < $site_date_created) {$start_date = $site_date_created;}
$site_epoch_created = strtotime("$site_date_created");

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "
<doc_title>$siteTitle - Partner Site Stats</doc_title>
";
// Podcast or website/app?
if (($site_type == 'site') || ($site_type == 'app')) {
	echo "
	<badad_url>https://$siteDomain</badad_url>
	<title>badAd Stats _ $site_domain (#$siteID) _ $start_date - $end_date</title>
	<domain>$site_domain</domain>
	<site_id>$siteID</site_id>
	<site_nickname>$site_nickname</site_nickname>
	<site_connect_callback>$site_connect_callback</site_connect_callback>
	<dev_app_name>$site_dev_name</dev_app_name>
	<dev_app_domain>$site_dev_domain</dev_app_domain>
	";
} elseif ($site_type == 'podcast') {
	echo "
	<source_url>$site_source</source_url>
	<title>badAd Stats _ Podcast - $site_slug (#$siteID) _ $start_date - $end_date</title>
	<slug>$site_slug</slug>
	<site_id>$siteID</site_id>
	<site_nickname>$site_nickname</site_nickname>
	";
}

echo "
<timezone>$timeZone</timezone>
<site_date_created>$site_date_created</site_date_created>
<report_date_start>$start_date</report_date_start>
<report_date_end>$end_date</report_date_end>
<site_epoch_created>$site_epoch_created</site_epoch_created>
<report_epoch_start>$start_epoch</report_epoch_start>
<report_epoch_end>$end_epoch</report_epoch_end>
";

// Loop through each entry in the date range and put them into an array
// Use CAST(time_date AS DATE) per https://dba.stackexchange.com/questions/108287/why-does-my-query-search-datetime-not-match
if (($site_type == 'site') || ($site_type == 'app')) {
	$q = "SELECT time_date FROM seen_partnersite_analytics WHERE partnersite_id='$siteID' AND CAST(time_date AS DATE) >= '$start_date' AND CAST(time_date AS DATE) <= '$end_date' ORDER BY time_date";
	$r = mysqli_query ($srv_dbc, $q);
} elseif ($site_type == 'podcast') {
	$q = "SELECT time_date FROM request_feed_analytics WHERE partnersite_id='$siteID' AND CAST(time_date AS DATE) >= '$start_date' AND CAST(time_date AS DATE) <= '$end_date' ORDER BY time_date";
	$r = mysqli_query ($agg_dbc, $q);
}
// Activity check
if (mysqli_num_rows($r) == 0) {
	echo "
<hits>no_hits</hits>";
} else {
	echo "
<hits>
";

	// Iterate the hits
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$arrKey = 0;
	while ($siteRow = mysqli_fetch_array($r)) {
		$date_entry = $siteRow[0];
		echo "  <hit><date>$date_entry</date><epoch>".strtotime("$date_entry")."</epoch></hit>
";
	}
	// Finish the hits
	echo "</hits>
	";
}

?>
