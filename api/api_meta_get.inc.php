<?php
// This actually lists an ad from render_listed_ad.php, which the embedded ad code directs to



// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// Get the Dev ID
$q = "SELECT id, test_sec_key, status FROM devkeys WHERE BINARY live_sec_key='$esc_dev_key' OR BINARY old_sec_key='$esc_dev_key' OR BINARY test_sec_key='$esc_dev_key'";
$r = mysqli_query($srv_dbc, $q);
	// Get the number of rows returned & exit if none
	$rows = mysqli_num_rows($r);
	if ($rows == 0) {exit();}
// Site variables
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$dev_authorized_id = "$row[0]";
$test_sec_key = "$row[1]";
$dev_status = "$row[2]";

// Testing?
if (($test_sec_key == $dev_key) && ($dev_status == 'test')) {

	$randomYN = rand(0,1) == 1;
	if ($randomYN == 1) {$site_nickname = "Partner nickname Nick";} else {$site_nickname = "(#0)";}

	$randomYN = rand(0,1) == 1;
	if ($randomYN == 1) {$site_usable = '<span class="badAd_status_live">live</span>';} else {$site_usable = '<span class="badAd_status_off">off</span>';}

	$RenderedMetaHTML = "<div class=\"badad_app_meta\"><div class=\"badad_app_description\">$site_nickname</div><br class=\"badad_app\" /><div class=\"badad_app_status\">Status: <b class=\"badad_app_status\">$site_usable</b></div></div>";

	// Render the contents
	echo $RenderedMetaHTML;

	exit();

} elseif ($dev_status == 'test') {

	exit();

} // End testing


// Credit-reference link?
if ($refcred == true) {
	// Get the settings from the partnersites table
	$q = "SELECT id, badadref_no FROM partnersites WHERE binary call_key='$esc_call_key' AND useable='live' AND dev_authorized_id=$dev_authorized_id AND type='app'";
	$r = mysqli_query($srv_dbc, $q);
		// Get the number of rows returned & exit if none
		$rows = mysqli_num_rows($r);

		// User does not have a referral link setup
		if ($rows == 0) {
			$RenderedadHTML = "no_ref_link";
			exit();

		} else {
		// User has a referral link
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$partnersite_id = "$row[0]";
		$site_badadref_no = "$row[1]";

		$RenderedadHTML = "$site_badadref_no";
		}

	// Render the contents
	echo $RenderedadHTML;

	exit();
} // End Credit-reference


// Get the settings from the partnersites table
$q = "SELECT id, nickname, useable FROM partnersites WHERE BINARY call_key='$esc_call_key' AND dev_authorized_id=$dev_authorized_id AND type='app'";
$r = mysqli_query($srv_dbc, $q);
	// Get the number of rows returned & exit if none
	$rows = mysqli_num_rows($r);
	if ($rows == 0) {exit();}
// Site variables
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$partnersite_id = "$row[0]";
$site_nickname = "$row[1]";
$site_usable = "$row[2]";

if ($site_usable == 'live') {
	$site_usable = '<span class="badAd_status_live">live</span>';
} elseif ($site_usable == 'off') {
	$site_usable = '<span class="badAd_status_off">off</span>';
}

if (($site_nickname == NULL) || ($site_nickname == "")) {$site_nickname = "(#$partnersite_id)";}

$RenderedMetaHTML = "<div class=\"badad_app_meta\"><div class=\"badad_app_description\">$site_nickname</div><br class=\"badad_app\" /><div class=\"badad_app_status\">Status: <b class=\"badad_app_status\">$site_usable</b></div></div>";

// Render the contents
echo $RenderedMetaHTML;
