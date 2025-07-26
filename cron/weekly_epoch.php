<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('/srv/www/badad/webdir/includes/config.inc.php');
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('/srv/www/badad/webdir/includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Podcast ads needs the _AGG config
require_once ('/srv/www/badad/webdir/includes/config_agg.inc.php');
require_once (MYSQL_AGG);

// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// Ads expire
$q = "UPDATE ads SET pub_status='expired' WHERE pub_status='live' date_expires < '$timeNow'";
$r = mysqli_query ($dbc, $q);

// Listads expire
$q = "UPDATE listads SET pub_status='expired' WHERE pub_status='live' epoch_dead < '$timeNowEpoch'";
$r = mysqli_query ($srv_dbc, $q);

// Ads expire
$q = "UPDATE pod_ads SET pub_status='expired' WHERE pub_status='live' date_expires < '$timeNow'";
$r = mysqli_query ($dbc, $q);

// Listads expire
$q = "UPDATE podcastads SET pub_status='expired' WHERE pub_status='live' epoch_dead < '$timeNowEpoch'";
$r = mysqli_query ($srv_dbc, $q);

// Text ads //
// Ads wk_reset
$ads_queried = 0;
$ads_totalviews = 0;
$q = "SELECT id, date_expires, epoch_wk_reset, week_view_count FROM ads WHERE pub_status='live' AND (epoch_wk_reset <= $timeNowEpoch)";
$row = mysqli_query($dbc, $q);
while ($ad_item = mysqli_fetch_array($row)) {
	$ad_ID = "$ad_item[0]";
  $ad_date_expires = "$ad_item[1]";
	$ad_epoch_wk_reset = "$ad_item[2]";
	$ad_week_view_count = "$ad_item[3]";

	// Averaging
	$ads_queried++;
	$ads_totalviews = $ads_totalviews+$ad_week_view_count;

  // Expired ads
  if ($timeNow > $ad_date_expires) {$set_pub_status = 'expired';} else {$set_pub_status = 'live';}

  // Reset old week counts
	if ($timeNowEpoch >= $ad_epoch_wk_reset) {
		$ad_list_wk_count = 0;
		// Loop until it is in the future
		$resetEpoch = ($ad_epoch_wk_reset + 604800);
		while ($timeNowEpoch >= $resetEpoch) {
			$resetEpoch = ($resetEpoch + 604800);
		}
		// Update the status
		$q = "UPDATE ads SET pub_status='$set_pub_status', week_view_count=$ad_list_wk_count, week_cat_count=$ad_list_wk_count, week_tag_count=$ad_list_wk_count, week_search_count=$ad_list_wk_count, epoch_wk_reset=$resetEpoch WHERE id=$ad_ID";
		$r = mysqli_query ($dbc, $q);
		if (!mysqli_affected_rows($dbc) > 0) { // If it didn't run okay
			sql_error($q, 'dbc', "sqle_32");
		}
	} // End reset week counts
}


// List ads wk_reset
$q = "SELECT ad_id, epoch_dead, epoch_wk_reset, list_wk_count FROM listads WHERE pub_status='live' AND (epoch_wk_reset <= $timeNowEpoch)";
$row = mysqli_query($srv_dbc, $q);
$listads_queried = 0;
$listads_totalviews = 0;
while ($ad_item = mysqli_fetch_array($row)) {
	$List_ad_ID = "$ad_item[0]";
  $List_ad_epoch_dead = "$ad_item[1]";
	$List_epoch_wk_reset = "$ad_item[2]";
	$List_list_wk_count = "$ad_item[3]";

	// Averaging
	$listads_queried++;
	$listads_totalviews = $listads_totalviews+$List_list_wk_count;

  // Expired ads
  if ($timeNowEpoch > $List_ad_epoch_dead) {$set_pub_status = 'expired';} else {$set_pub_status = 'live';}

  // Reset old week counts
	if ($timeNowEpoch >= $List_epoch_wk_reset) {
		$List_list_wk_count = 0;
		// Loop until it is in the future
		$resetEpoch = ($List_epoch_wk_reset + 604800);
		while ($timeNowEpoch >= $resetEpoch) {
			$resetEpoch = ($resetEpoch + 604800);
		}
		// Update the status
		$q = "UPDATE listads SET epoch_wk_reset=$resetEpoch, list_wk_count=$List_list_wk_count, pub_status='$set_pub_status' WHERE ad_id=$List_ad_ID";
		$r = mysqli_query ($srv_dbc, $q);
		if (!mysqli_affected_rows($srv_dbc) > 0) { // If it didn't run okay
			sql_error($q, 'srv_dbc', "sqle_33");
		}
	} // End reset week counts

}

// Log averages
	$sum_queried = $ads_queried+$listads_queried;
	$sum_views = $ads_totalviews+$listads_totalviews;
if ($sum_queried > 0) { // If there are ads to calculate
	$avgviews = $sum_views/$sum_queried;
	$avgviews = round($avgviews); // This column is an integer, round it now just to be safe
	$sum_views = round($sum_views); // This column is an integer, round it now just to be safe
	$sum_queried = round($sum_queried); // This column is an integer, round it now just to be safe

	$q = "INSERT INTO weeklyavgview (epoch, avgviews, sumviews, sumquery) VALUES ('$timeNowEpoch', '$avgviews', '$sum_views', '$sum_queried')";
	$r = mysqli_query ($dbc, $q);
	if (!mysqli_affected_rows($dbc) > 0) { // If it didn't run okay
		sql_error($q, 'dbc', "sqle_34");
	}
}

// Podcast ads //
// Podcast ads wk_reset
$q = "SELECT pod_ad_id, epoch_dead, epoch_wk_reset, list_wk_count FROM podcastads WHERE pub_status='live' AND (epoch_wk_reset <= $timeNowEpoch)";
$row = mysqli_query($srv_dbc, $q);
$podcastads_totallistens = 0;
while ($ad_item = mysqli_fetch_array($row)) {
	$Podcast_ad_ID = "$ad_item[0]";
  $Podcast_ad_epoch_dead = "$ad_item[1]";
	$Podcast_epoch_wk_reset = "$ad_item[2]";
	$Podcast_list_wk_count = "$ad_item[3]";

	// Averaging
	$podcastads_totallistens = $podcastads_totallistens+$Podcast_list_wk_count;

  // Expired ads
  if ($timeNowEpoch > $Podcast_ad_epoch_dead) {$set_pub_status = 'expired';} else {$set_pub_status = 'live';}

  // Reset old week counts
	if ($timeNowEpoch >= $Podcast_epoch_wk_reset) {
		$Podcast_list_wk_count = 0;
		// Loop until it is in the future
		$resetEpoch = ($Podcast_epoch_wk_reset + 604800);
		while ($timeNowEpoch >= $resetEpoch) {
			$resetEpoch = ($resetEpoch + 604800);
		}
		// Update the status
		$q = "UPDATE podcastads SET epoch_wk_reset=$resetEpoch, list_wk_count=$Podcast_list_wk_count, pub_status='$set_pub_status' WHERE pod_ad_id=$Podcast_ad_ID";
		$r = mysqli_query ($srv_dbc, $q);
		if (!mysqli_affected_rows($srv_dbc) > 0) { // If it didn't run okay
			sql_error($q, 'srv_dbc', "sqle_177");
		}
	} // End reset week counts

}

// Log averages
	$sum_listens = $podcastads_totallistens;
if ($sum_queried > 0) { // If there are ads to calculate
	$sum_listens = round($sum_listens); // This column is an integer, round it now just to be safe

	$q = "INSERT INTO weeklyavglisten (epoch, sumlisten) VALUES ('$timeNowEpoch', '$sum_listens')";
	$r = mysqli_query ($dbc, $q);
	if (!mysqli_affected_rows($dbc) > 0) { // If it didn't run okay
		sql_error($q, 'dbc', "sqle_178");
	}
}
?>
