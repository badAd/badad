<?php

// Listing the ads needs the _SRV  & _AGG configs, and $clickShareValue from config.inc
require_once ('./includes/config.inc.php');
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('./includes/config_agg.inc.php');
require_once (MYSQL_AGG);

// Hit total
$q = "SELECT SUM(listed_badad_count), SUM(listed_ad_count), SUM(clicked_badad_count), SUM(clicked_listed_count) FROM partnersites";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$totalHits_listed_badad_count = $row[0];
$totalHits_listed_ad_count = $row[1];
$totalHits_clicked_badad_count = $row[2];
$totalHits_clicked_listed_count = $row[3];

// Get the count
$q = "SELECT SUM(feed_requested_count), SUM(ad_download_count), SUM(ad_click_count) FROM feeds WHERE user_id='$userid'";
$r = mysqli_query ($agg_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows != 0)	{
  $row = mysqli_fetch_array ($r, MYSQLI_NUM);
  $totalHits_feed_requested_count = $row[0];
  $totalHits_podad_download_count = $row[1];
  $totalHits_podad_click_count = $row[2];
} else {
  $totalHits_feed_requested_count = 0;
  $totalHits_podad_download_count = 0;
  $totalHits_podad_click_count = 0;
}

// Grand totals
$totalHits_listed = ($totalHits_listed_badad_count + $totalHits_listed_ad_count + $totalHits_podad_download_count);
$totalHits_clicked = ($totalHits_clicked_badad_count + $totalHits_clicked_listed_count + $totalHits_podad_click_count);
$totalHits_share_count = ($totalHits_listed + $totalHits_feed_requested_count + ($totalHits_clicked * $clickShareValue));

// Pretty
$pretty_totalHits_listed_badad_count = number_format($totalHits_listed_badad_count);
$pretty_totalHits_listed_ad_count = number_format($totalHits_listed_ad_count);
$pretty_totalHits_clicked_badad_count = number_format($totalHits_clicked_badad_count);
$pretty_totalHits_clicked_listed_count = number_format($totalHits_clicked_listed_count);
$pretty_totalHits_listed = number_format($totalHits_listed);
$pretty_totalHits_clicked = number_format($totalHits_clicked);
