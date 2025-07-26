<?php

// Most recent ad
$q = "SELECT date_calculated FROM current_cycle ORDER BY date_calculated DESC LIMIT 1";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$mostRecentAd = ($row[0]) ? $row[0] : 0;

// Current Ad count
$q = "SELECT COUNT(ad_id) FROM current_cycle";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$totalCurrentAds = ($row[0]) ? $row[0] : 0;
$pretty_totalCurrentAds = number_format($totalCurrentAds);

// Tallied Ad count
$q = "SELECT COUNT(ad_id) FROM tallied_cycles";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$totalTalliedAds = ($row[0]) ? $row[0] : 0;
$pretty_totalTalliedAds = number_format($totalTalliedAds);

// Current Price totals
$q = "SELECT SUM(price) FROM current_cycle";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$totalCurrentPrice = ($row[0]) ? $row[0] : 0;
$pretty_totalCurrentPrice = number_format($totalCurrentPrice);

// Tallied Price totals
$q = "SELECT SUM(price) FROM tallied_cycles";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$totalTalliedPrice = ($row[0]) ? $row[0] : 0;
$pretty_totalTalliedPrice = number_format($totalTalliedPrice);

// Weekly Average
/* Not publishing at present
$q = "SELECT avgviews FROM weeklyavgview ORDER BY date_entry DESC LIMIT 1";
if ($r = mysqli_query ($dbc, $q)) {
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$avg_view_per_week = $row[0];
} else {
  $avg_view_per_week = 0;
}
$pretty_avg_view_per_week = number_format($avg_view_per_week);
*/

// Weekly Views
$q = "SELECT sumviews FROM weeklyavgview ORDER BY date_entry DESC LIMIT 1";
if ($r = mysqli_query ($dbc, $q)) {
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$sum_view_per_week = $row[0];
} else {
  $sum_view_per_week = 0;
}
$pretty_sum_view_per_week = number_format($sum_view_per_week);

// Grand Totals
$totalAds = $totalCurrentAds + $totalTalliedAds;
$pretty_totalAds = number_format($totalAds);
$totalPrice = $totalCurrentPrice + $totalTalliedPrice;
$pretty_totalPrice = number_format($totalPrice);
