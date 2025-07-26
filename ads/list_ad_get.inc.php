<?php
// This actually lists an ad from render_listed_ad.php, which the embedded ad code directs to

// $site_serial must be set

// Listing the ads needs the _SRV config
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Escape globally
$esc_site_serial = mysqli_real_escape_string($srv_dbc, $site_serial);

// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// Get the settings from the partnersites table
$q = "SELECT id, domain, listed_ad_count, listed_badad_count, horizontal_inline, num_ads_to_show, badadref_no FROM partnersites WHERE binary serial_no='$esc_site_serial' AND useable='live' AND type='site'";
$r = mysqli_query($srv_dbc, $q);
	// Get the number of rows returned & exit if none
	$rows = mysqli_num_rows($r);
	if ($rows == 0) {exit();}
// Site variables
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$partnersite_id = "$row[0]";
$site_domain = "$row[1]";
$site_listed_ad_count = "$row[2]";
$site_listed_badad_count = "$row[3]";
$site_horizontal_inline = "$row[4]";
$site_num_ads_to_show = "$row[5]";
$site_badadref_no = "$row[6]";

// Start the ad content
$RenderedadHTML = "<div class=\"badad_ad badad_container\">";
// Horizantal Inline for the full-embed div
$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_ads_top\" /><p class=\"badad_ad badad_link\" style=\"text-align:center;\"><a class=\"badad_ad badad_link\" rel=\"nofollow noreferrer noopener\" href=\"https://badad.one/$site_badadref_no/site.html\"><b>badAd.one</b></a></p><hr class=\"badad_ad badad_link_bottom\" />";
if ($site_horizontal_inline == true) {
	$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ads\" style=\"display:inline-block; text-align:center; display:flex; justify-content:center; align-items:center; width:100%;\">";
} else {
	$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ads\">";
}
// Get the most recently created ads
// Thank you https://stackoverflow.com/a/1386213/10343144
// Thank you https://stackoverflow.com/a/9928621/10343144
$sinquot = "'";
$seenAdsCount = 0;
$q = "SELECT ad_id, serialno, listed_count, epoch_dead, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, list_wk_count, epoch_wk_reset FROM listads JOIN partnersites ON INSTR(partnersites.global_subcat_ids, listads.global_subcat_id) WHERE partnersites.id='$partnersite_id' AND listads.epoch_starts < '$timeNowEpoch' AND listads.pub_status='live' > 0 ORDER BY list_wk_count, epoch_wk_reset, ad_id DESC LIMIT $site_num_ads_to_show";
$row = mysqli_query($srv_dbc, $q);
while ($ad_item = mysqli_fetch_array($row)) {
	$List_ad_ID = "$ad_item[0]";
	$List_serialno = "$ad_item[1]";
	$List_listed_count = "$ad_item[2]";
	$List_epoch_dead = "$ad_item[3]";
	$List_ad_content_hdng = addcslashes ($ad_item[4], $sinquot); // Must do this so the single quote / apostrophe doesn't break the Javascript
	$List_ad_content_dscr = addcslashes ($ad_item[5], $sinquot);
	$List_ad_content_info = addcslashes ($ad_item[6], $sinquot);
	$List_ad_content_pyrt = addcslashes ($ad_item[7], $sinquot);
	$List_ad_content_cntc = addcslashes ($ad_item[8], $sinquot);
	$List_ad_content_bizn = addcslashes ($ad_item[9], $sinquot);
	$List_ad_biz_listing = "$ad_item[10]";
	$List_list_wk_count = "$ad_item[11]";
	$List_epoch_wk_reset = "$ad_item[12]";

	// Kill expired ads
	if ($timeNowEpoch >= $List_epoch_dead) {
		// Update the status
		$q = "UPDATE listads SET pub_status='expired' WHERE ad_id='$List_ad_ID'";
		$r = mysqli_query ($srv_dbc, $q);
		//if (!mysqli_affected_rows($srv_dbc) > 0) {sql_error($q, 'srv_dbc', "sqle_list_ad_get");}
		//continue; //don't "continue" because we want to list this ad one last time so the partnersite isn't missing an ad
	} // End Kill expired ads

	// Reset old week counts
	if ($timeNowEpoch >= $List_epoch_wk_reset) {
		$List_list_wk_count = 0;
		// Loop until it is in the future
		$resetEpoch = ($List_epoch_wk_reset + 604800);
		while ($timeNowEpoch >= $resetEpoch) {
			$resetEpoch = ($resetEpoch + 604800);
		}
		// Update the status
		$q = "UPDATE listads SET epoch_wk_reset='$resetEpoch' WHERE ad_id='$List_ad_ID'";
		$r = mysqli_query ($srv_dbc, $q);
		//if (!mysqli_affected_rows($srv_dbc) > 0) {sql_error($q, 'srv_dbc', "sqle_list_ad_get");}
	} // End reset week counts

	// Update the analytics
	$aq = "INSERT INTO seen_ad_analytics (ad_id, source, time_date, time_epoch) VALUES ('$List_ad_ID', 'listed', '$timeNow', '$timeNowEpoch')";
	$ar = mysqli_query ($srv_dbc, $aq);
	//if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Ad not counted."; }

	// Increment the listed ad count
	$seenAdsCount = $seenAdsCount +1;
	$newListCount = $List_listed_count +1;
	$newWeekListCount = $List_list_wk_count +1;

	// Update the new viewcount
	$q = "UPDATE listads SET listed_count='$newListCount', list_wk_count='$newWeekListCount' WHERE ad_id='$List_ad_ID'";
	$r = mysqli_query ($srv_dbc, $q);

	if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran okay

		// Prep the link
		$List_ad_content_cntc_rd = "https://$adServeDomain/$site_badadref_no/$List_serialno/ct.html";
		// Horizantal Inline for the individual ad
		if ($site_horizontal_inline == true) {
			$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ad_item\" style=\"display:inline-block; margin-right:0.5em; margin-left:0.5em;\">";
		} else {
			$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ad_item\">";
		}
		// Render the embed script
		$RenderedadHTML = $RenderedadHTML."<p class=\"badad_ad badad_ad_item\" style=\"text-align:center;\"><span class=\"badad_ad badad_heading\"><strong class=\"badad_ad badad_heading\">$List_ad_content_hdng</strong></span><br class=\"badad_ad badad_heading\" /><span class=\"badad_ad badad_description\">$List_ad_content_dscr</span><br class=\"badad_ad badad_description\" /><span class=\"badad_ad badad_info\"><em class=\"badad_ad badad_info\">$List_ad_content_info</em></span><br class=\"badad_ad badad_info\" /><span class=\"badad_ad badad_payrate\">$List_ad_content_pyrt</span>&nbsp;<span class=\"badad_ad badad_contact\"><a class=\"badad_ad badad_contact\" rel=\"nofollow\" href=\"$List_ad_content_cntc_rd\"><u class=\"badad_ad badad_contact\">Contact</u></a></span>";
		// Business listing?
		if ($List_ad_biz_listing == 'biz') {
			$RenderedadHTML = $RenderedadHTML."<br class=\"badad_ad badad_biz\" /><strong class=\"badad_ad badad_biz\"><i class=\"badad_ad badad_biz\">$List_ad_content_bizn</i></strong>";
		}
		// Finish the ad
		$RenderedadHTML = $RenderedadHTML."</p>";
		if ($site_horizontal_inline != true) {
			$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_close_vert_ad\" />";
		}
		// Div per ad
			$RenderedadHTML = $RenderedadHTML."</div>";
	} // End ad render
} // End while loop


//// Before finishing the ad cluster render, update the database with what ads we got
	// We use $seenAdsCount, NOT $site_num_ads_to_show, to record what number actually ren, not merely what number was requested
	// Update the analytics
	$aq = "INSERT INTO seen_partnersite_analytics (partnersite_id, num_ads_set, time_date, time_epoch) VALUES ('$partnersite_id', '$seenAdsCount', '$timeNow', '$timeNowEpoch')";
	$ar = mysqli_query ($srv_dbc, $aq);
	//if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Ad not counted."; }

	// Calculation
	$newPartnerListCount = $site_listed_ad_count + $seenAdsCount;
	if ($no_hit == false) {$newPDAListCount = $site_listed_badad_count + 1;} elseif ($no_hit == true) {$newPDAListCount = $site_listed_badad_count; $newPartnerListCount = $newPartnerListCount + 1;}

	// Update the new viewcount
	$q = "UPDATE partnersites SET listed_ad_count='$newPartnerListCount', listed_badad_count='$newPDAListCount' WHERE binary serial_no='$esc_site_serial'";
	$r = mysqli_query ($srv_dbc, $q);
//// End database update


// Div for the add cluster
	$RenderedadHTML = $RenderedadHTML."</div>";

	if ($site_horizontal_inline == true) {
		$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_close_inline_row\" />";
	}

// Div for the full render
	$RenderedadHTML = $RenderedadHTML."</div>";


// Render the .js file
echo "function loadCSS(filename){
  var file = document.createElement(\"link\");
  file.setAttribute(\"rel\", \"stylesheet\");
  file.setAttribute(\"type\", \"text/css\");
  file.setAttribute(\"href\", filename);
  document.head.appendChild(file);
}

loadCSS(\"https://ads.badad.one/ad_style.css\");

document.write('$RenderedadHTML');
";
