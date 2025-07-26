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
// $r = mysqli_query ($srv_dbc, $q);
// $row = mysqli_fetch_array($r);
// $timeZone = "$row[0]";
$timeZone = date("O");

// Check for form submission and set the $adID variable
if (($_SERVER['REQUEST_METHOD'] == 'GET') &&
(isset($_GET['i'])) && (isset($_GET['s'])) && (isset($_GET['e'])) &&
(filter_var($_GET['i'], FILTER_VALIDATE_INT, array('min_range' => 1))) &&
(filter_var($_GET['s'], FILTER_VALIDATE_INT, array('min_range' => 10, 'max_range' => 10))) &&
(filter_var($_GET['e'], FILTER_VALIDATE_INT, array('min_range' => 10, 'max_range' => 10)))) {
$adID = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['i']);
$start_epoch = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['s']);
$end_epoch = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['e']);
$start_date = date("Y-m-d H:i:s", substr($_GET['s'], 0, 10));
$end_date = date("Y-m-d H:i:s", substr($_GET['e'], 0, 10));
} else {
	header("Location: partner.php");
	exit(); // Quit the script
}

// Check to see if user's email is verified
$qe = "SELECT email, confirmed_email FROM users WHERE id='$userid'";
$re = mysqli_query ($dbc, $qe);
$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
$email = "$rowe[0]";
$email_confirmed = "$rowe[1]";
if ($email != $email_confirmed) {
	header("Location: partner.php");
  exit(); // Quit the script
} // activated check complete, user is a fully-fledged partner

// See if user owns the site & get important information
$adID = mysqli_real_escape_string ($dbc, $adID);
$q = "SELECT date_created, ad_comment, ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing FROM ads WHERE id='$adID' AND user_id='$userid'";
$r = mysqli_query ($dbc, $q);
if (mysqli_num_rows($r) == 0) {
	header("Location: partner.php");
	exit(); // Quit the script
} else {
	$row = mysqli_fetch_array($r);
	$ad_date_created = $row[0];
	$ad_comment = $row[1];
	$ad_nickname = $row[2];
	$ad_content_hdng = $row[3];
	$ad_content_dscr = $row[4];
	$ad_content_info = $row[5];
	$ad_content_pyrt = $row[6];
	$ad_content_cntc = $row[7];
	$ad_content_bizn = $row[8];
	$ad_biz_listing = $row[9];
	if ($ad_biz_listing == "non") {$ad_content_bizn = "none";}
}

// Make sure we're not in the future or before site was created
if ($end_date > $timeNow) {$end_date = $timeNow;}
if ($start_date < $ad_date_created) {$start_date = $ad_date_created;}
$ad_epoch_created = strtotime("$ad_date_created");

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "
<doc_title>$siteTitle - Ad Stats for: $ad_nickname (#$adID)</doc_title>
<badad_url>https://$siteDomain</badad_url>
<title>badAd Ad Stats _ $ad_nickname (#$adID) _ $start_date - $end_date</title>
<ad_id>$adID</ad_id>
<ad_comment>$ad_nickname</ad_comment>
<ad_nickname>$ad_comment</ad_nickname>
<ad_heading>$ad_content_hdng</ad_heading>
<ad_description>$ad_content_dscr</ad_description>
<ad_info>$ad_content_info</ad_info>
<ad_payrate>$ad_content_pyrt</ad_payrate>
<ad_contact>$ad_content_cntc</ad_contact>
<ad_businessname>$ad_content_bizn</ad_businessname>
<timezone>$timeZone</timezone>
<ad_date_created>$ad_date_created</ad_date_created>
<report_date_start>$start_date</report_date_start>
<report_date_end>$end_date</report_date_end>
<ad_epoch_created>$ad_epoch_created</ad_epoch_created>
<report_epoch_start>$start_epoch</report_epoch_start>
<report_epoch_end>$end_epoch</report_epoch_end>
<notes>source: listed (partner website), view (viewed ordinarily on the badAd website), cat (seen under the category, parent or subcategory), tag (seen under a tag), search (seen in search results, cat_ or tag_ specified in subtext_comment if searched); keytext: category, tag, or searched text; subtext_comment: cat or tag being searched; filters: a w s (Selling - Want - Agent; negative), b (Business-only)</notes>
";

// Hits
// Loop through each entry in the date range and put them into an array
// Use CAST(time_date AS DATE) per https://dba.stackexchange.com/questions/108287/why-does-my-query-search-datetime-not-match
$q = "SELECT time_date FROM downloaded_ad_analytics WHERE ad_id='$adID' AND CAST(time_date AS DATE) >= '$start_date' AND CAST(time_date AS DATE) <= '$end_date' ORDER BY time_date";
$r = mysqli_query ($agg_dbc, $q);
// Activity check
if (mysqli_num_rows($r) == 0) {
	echo "
<downloads>no_downloads</downloads>";
} else {
	echo "
<downloads>
";

	// Iterate the downloads
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$arrKey = 0;
	while ($siteRow = mysqli_fetch_array($r)) {
		$date_entry = $siteRow[0];
		echo "  <download><date>$date_entry</date><epoch>".strtotime("$date_entry")."</epoch></download>
";
	}
	// Finish the downloads
	echo "</downloads>
	";
}


// Clicks
// Loop through each entry in the date range and put them into an array
// Use CAST(time_date AS DATE) per https://dba.stackexchange.com/questions/108287/why-does-my-query-search-datetime-not-match
$q = "SELECT time_date FROM clicked_ad_analytics WHERE ad_id='$adID' AND CAST(time_date AS DATE) >= '$start_date' AND CAST(time_date AS DATE) <= '$end_date' ORDER BY time_date";
$r = mysqli_query ($srv_dbc, $q);
// Activity check
if (mysqli_num_rows($r) == 0) {
	echo "
<clicks>no_clicks</clicks>";
} else {
	echo "
<clicks>";

	// Iterate the downloads
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$arrKey = 0;
	while ($siteRow = mysqli_fetch_array($r)) {
		$date_entry = $siteRow[0];
		$source = $siteRow[1];
		$keytext = $siteRow[2];
		$subkey = $siteRow[3];
		$filter = $siteRow[4];
		if ($keytext == NULL) {$keytext = "none";}
		if ($subkey == NULL) {$subkey = "none";}
		if ($filter == NULL) {$filter = "none";}
		echo "  <click><date>$date_entry</date><epoch>".strtotime("$date_entry")."</epoch></click>
";
	}
	// Finish the clicks & document
	// Finish the document
	echo "</clicks>";
}
?>
