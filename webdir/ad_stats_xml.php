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
if (($_SERVER['REQUEST_METHOD'] == 'GET') && (isset($_GET['i'])) && (filter_var($_GET['i'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
$adID = preg_replace("/[^A-Za-z0-9]/","", $_GET['i']);
} else {
	header("Location: partner.php");
	exit(); // Quit the script
}

// Check to see if user's email is verified
$userid = mysqli_real_escape_string ($dbc, $userid);
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
$q = "SELECT date_created, ad_comment, ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_lang, category_id, subcat_id, role_id, tag_ids FROM ads WHERE id='$adID' AND user_id='$userid'";
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
	$ad_lang = $row[10];
	$category_id = $row[11];
	$subcat_id = $row[12];
	$role_id = $row[13];
	$tag_ids = $row[14];
	if ($ad_biz_listing == "non") {$ad_content_bizn = "none";}

	// Get the listad role
	$rlaq = "SELECT role FROM roles WHERE id='$role_id'";
	$rlar = mysqli_query ($dbc, $rlaq);
	$rlarow = mysqli_fetch_array($rlar, MYSQLI_NUM);
	$rolename = "$rlarow[0]";

	// Get the category from its ID
	$cq = "SELECT category, slug FROM categories WHERE id='$category_id'";
	$cr = mysqli_query ($dbc, $cq);
	$crow = mysqli_fetch_array($cr, MYSQLI_NUM);
	$catname = "$crow[0]";
	$catslug = "$crow[1]";

	// Get the subcategory from its ID
	$scq = "SELECT subcat FROM sub_$catslug WHERE id='$subcat_id'";
	$scr = mysqli_query ($dbc, $scq);
	$scrow = mysqli_fetch_array($scr, MYSQLI_NUM);
	$subcatname = "$scrow[0]";

	// tags
	$arrayTagIDs = explode(',', $tag_ids);
	$tag_row = '';
	foreach($arrayTagIDs as $tagID){
		// Get the tag from its ID
		$tq = "SELECT tag FROM tags WHERE id='$tagID'";
		$tr = mysqli_query ($dbc, $tq);
		$trow = mysqli_fetch_array($tr, MYSQLI_NUM);
		$tag = "$trow[0]";
		$tag_row .= $tag.', ';
	}

}

$ad_epoch_created = strtotime("$ad_date_created");

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "
<doc_title>$siteTitle - Ad Stats for: $ad_nickname (#$adID)</doc_title>
<badad_url>https://$siteDomain</badad_url>
<title>badAd Ad Stats _ $ad_nickname (#$adID) _ FOR ALL DATES</title>
<ad_id>$adID</ad_id>
<ad_lang>$ad_lang</ad_lang>
<role>$rolename</role>
<category>$catname</category>
<subcategory>$subcatname</subcategory>
<tags>$tag_row</tags>
<ad_comment>$ad_comment</ad_comment>
<ad_nickname>$ad_nickname</ad_nickname>
<ad_heading>$ad_content_hdng</ad_heading>
<ad_description>$ad_content_dscr</ad_description>
<ad_info>$ad_content_info</ad_info>
<ad_payrate>$ad_content_pyrt</ad_payrate>
<ad_contact>$ad_content_cntc</ad_contact>
<ad_businessname>$ad_content_bizn</ad_businessname>
<timezone>$timeZone</timezone>
<ad_date_created>$ad_date_created</ad_date_created>
<ad_epoch_created>$ad_epoch_created</ad_epoch_created>
<notes>source: listed (partner website), view (viewed ordinarily on the badAd website), cat (seen under the category, parent or subcategory), tag (seen under a tag), search (seen in search results, cat_ or tag_ specified in subtext_comment if searched); keytext: category, tag, or searched text; subtext_comment: cat or tag being searched; filters: a w s (Selling - Want - Agent; negative), b (Business-only); not all information is included in this stats XML file, full information on the ad itself can only be seen in Order History raw XML</notes>
";

// Hits
// Loop through each entry in the date range and put them into an array
$q = "SELECT time_date, source, keytext, subkey, filter FROM seen_ad_analytics WHERE ad_id='$adID' ORDER BY time_date";
$r = mysqli_query ($srv_dbc, $q);
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
		$source = $siteRow[1];
		$keytext = $siteRow[2];
		$subkey = $siteRow[3];
		$filter = $siteRow[4];
		if ($keytext == NULL) {$keytext = "none";}
		if ($subkey == NULL) {$subkey = "none";}
		if ($filter == NULL) {$filter = "none";}
		echo "  <hit><date>$date_entry</date><epoch>".strtotime("$date_entry")."</epoch><source>$source</source><keytext>$keytext</keytext><subtext_comment>$subkey</subtext_comment><filters>$filter</filters></hit>
";
	}
	// Finish the hits
	echo "</hits>
	";
}


// Clicks
// Loop through each entry in the date range and put them into an array
$q = "SELECT time_date, source, keytext, subkey, filter FROM clicked_ad_analytics WHERE ad_id='$adID' ORDER BY time_date";
$r = mysqli_query ($srv_dbc, $q);
// Activity check
if (mysqli_num_rows($r) == 0) {
	echo "
<clicks>no_clicks</clicks>";
} else {
	echo "
<clicks>";

	// Iterate the hits
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
		echo "  <click><date>$date_entry</date><epoch>".strtotime("$date_entry")."</epoch><source>$source</source><keytext>$keytext</keytext><subtext_comment>$subkey</subtext_comment><filters>$filter</filters></click>
";
	}
	// Finish the clicks & document
	// Finish the document
	echo "</clicks>";
}
?>
