<?php
// This actually lists an ad from render.php, which the embedded ad code directs to

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

	// Counter
	$counter = 1;

	// Start the ad content
	$RenderedadHTML = "<div class=\"badad_ad badad_container\">";
	// Horizantal Inline for the full-embed div
	$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_ads_top\" />";
	if ($show_badad_link == true) {$RenderedadHTML = $RenderedadHTML."<p class=\"badad_ad badad_link\" style=\"text-align:center;\"><a class=\"badad_ad badad_link\" rel=\"nofollow noreferrer noopener\" href=\"https://badad.one/\"><b>badAd.one</b></a></p><hr class=\"badad_ad badad_link_bottom\" />";}
		if ($inline_div == true) {
			$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ads\" style=\"display:inline-block; text-align:center; display:flex; justify-content:center; align-items:center; width:100%;\">";
		} else {
			$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ads\">";
		}

	while ($num_ads >= $counter) { // If it ran okay

		$List_ad_content_hdng = "Test Ad Heading $counter"; // Must do this so the single quote / apostrophe doesn't break the Javascript
		$List_ad_content_dscr = "Test Ad Description $counter";
		$List_ad_content_info = "info, badad, listed, items, here, badad$counter";
		$List_ad_content_pyrt = "badAdTestrate $$counter/wk";
		$List_ad_content_bizn = "badAd $counter";

		// Randomizer for business
		$bizYN = rand(0,1) == 1;
		if ($bizYN == 1) {$List_ad_biz_listing = "biz";} else {$List_ad_biz_listing = "non";}

		// Prep the link
		$List_ad_content_cntc_rd = "https://badad.one/";
		// Horizantal Inline for the individual ad
		if ($inline_div == true) {
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
		if ($inline_div != true) {
			$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_close_vert_ad\" />";
		}
		// Div per ad
			$RenderedadHTML = $RenderedadHTML."</div>";

			// Counter
			$counter = $counter +1;
	} // End ad render


	// Div for the add cluster
	$RenderedadHTML = $RenderedadHTML."</div>";

	if ($inline_div == true) {
		$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_close_inline_row\" />";
	}

	// Div for the full render
	$RenderedadHTML = $RenderedadHTML."</div>";

	// Render the contents
	echo $RenderedadHTML;

	exit();

} elseif (($test_sec_key == $dev_key) || ($dev_status == 'test')) { // Testing in one place, but not the other
	exit();
} // End testing


// Get the settings from the partnersites table
$q = "SELECT id, domain, listed_ad_count, listed_badad_count, badadref_no FROM partnersites WHERE BINARY call_key='$esc_call_key' AND useable='live' AND dev_authorized_id=$dev_authorized_id AND type='app'";
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
$site_badadref_no = "$row[4]";

// Update the analytics
$aq = "INSERT INTO seen_partnersite_analytics (partnersite_id, num_ads_set, time_date, time_epoch) VALUES ('$partnersite_id', '$num_ads', '$timeNow', '$timeNowEpoch')";
$ar = mysqli_query ($srv_dbc, $aq);
//if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Ad not counted."; }

// We make up for listed_badad_counts not shown or no_hit by adding to or removing from listed_ad_count so shares remain the same
$newPartnerListCount = $site_listed_ad_count + $num_ads;
if ($no_hit == false) {$newPDAListCount = $site_listed_badad_count + 1;} elseif ($no_hit == true) {$newPDAListCount = $site_listed_badad_count; $newPartnerListCount = $newPartnerListCount + 1;}
if ($show_badad_link != true) {$newPartnerListCount = $newPartnerListCount - 1;}

// Update the new viewcount
$q = "UPDATE partnersites SET listed_ad_count='$newPartnerListCount', listed_badad_count='$newPDAListCount' WHERE id='$partnersite_id'";
$r = mysqli_query ($srv_dbc, $q);

// Start the ad content
$RenderedadHTML = "<div class=\"badad_ad badad_container\">";
// Horizantal Inline for the full-embed div
$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_ads_top\" />";
if ($show_badad_link == true) {$RenderedadHTML = $RenderedadHTML."<p class=\"badad_ad badad_link\" style=\"text-align:center;\"><a class=\"badad_ad badad_link\" rel=\"nofollow noreferrer noopener\" href=\"https://badad.one/$site_badadref_no/site.html\"><b>badAd.one</b></a></p><hr class=\"badad_ad badad_link_bottom\" />";}
	if ($inline_div == true) {
		$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ads\" style=\"display:inline-block; text-align:center; display:flex; justify-content:center; align-items:center; width:100%;\">";
	} else {
		$RenderedadHTML = $RenderedadHTML."<div class=\"badad_ad badad_ads\">";
	}

// Get the most recently created ads
// Thank you https://stackoverflow.com/a/1386213/10343144
// Thank you https://stackoverflow.com/a/9928621/10343144
$sinquot = "'";
$q = "SELECT ad_id, serialno, listed_count, epoch_dead, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, list_wk_count, epoch_wk_reset FROM listads JOIN partnersites ON INSTR(partnersites.global_subcat_ids, listads.global_subcat_id) WHERE partnersites.id='$partnersite_id' AND listads.epoch_starts < '$timeNowEpoch' AND listads.pub_status='live' > 0 ORDER BY list_wk_count, epoch_wk_reset, ad_id DESC LIMIT $num_ads";
$row = mysqli_query($srv_dbc, $q);
while ($ad_item = mysqli_fetch_array($row)) {
	$List_ad_ID = "$ad_item[0]";
	$List_serialno = "$ad_item[1]";
	$List_listed_count = "$ad_item[2]";
	$List_epoch_dead = "$ad_item[3]";
	$List_ad_content_hdng = $ad_item[4];
	$List_ad_content_dscr = $ad_item[5];
	$List_ad_content_info = $ad_item[6];
	$List_ad_content_pyrt = $ad_item[7];
	$List_ad_content_cntc = $ad_item[8];
	$List_ad_content_bizn = $ad_item[9];
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
	$newListCount = $List_listed_count +1;
	$newWeekListCount = $List_list_wk_count +1;

	// Update the new viewcount
	$q = "UPDATE listads SET listed_count='$newListCount', list_wk_count='$newWeekListCount' WHERE ad_id='$List_ad_ID'";
	$r = mysqli_query ($srv_dbc, $q);

	if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran okay

		// Prep the link
		$List_ad_content_cntc_rd = "https://$adServeDomain/$site_badadref_no/$List_serialno/ct.html";
		// Horizantal Inline for the individual ad
		if ($inline_div == true) {
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
		if ($inline_div != true) {
			$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_close_vert_ad\" />";
		}
		// Div per ad
			$RenderedadHTML = $RenderedadHTML."</div>";
	} // End ad render
} // End while loop

// Div for the add cluster
	$RenderedadHTML = $RenderedadHTML."</div>";

	if ($inline_div == true) {
		$RenderedadHTML = $RenderedadHTML."<hr class=\"badad_ad badad_close_inline_row\" />";
	}

// Div for the full render
	$RenderedadHTML = $RenderedadHTML."</div>";

// Render the contents
echo $RenderedadHTML;
