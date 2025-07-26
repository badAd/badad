<?php

/* This creates and calculates extra valiables for the beta boost promotion period (adding free weeks before 1M ads)
This:
- Modifies $new_ad_weekslong (for the final database entry)
- Creates:
  - $new_ad_boosted_weekslong
	- $new_ad_boost_weeks
	- $beta_boost
	- $beta_boost_pretty
*/

// Get current boosted weeks
	// Using $totalAds from tallies.inc, from header.htm
if ($totalAds > 1000000) {
	return;
} elseif ($totalAds > 775000) {
	$beta_boost = 1;
} elseif ($totalAds > 500000) {
	$beta_boost = 2;
} elseif ($totalAds > 300000) {
	$beta_boost = 3;
} elseif ($totalAds > 250000) {
	$beta_boost = 4;
} elseif ($totalAds > 220000) {
	$beta_boost = 5;
} elseif ($totalAds > 180000) {
	$beta_boost = 6;
} elseif ($totalAds > 155000) {
	$beta_boost = 7;
} elseif ($totalAds > 145000) {
	$beta_boost = 8;
} elseif ($totalAds > 135000) {
	$beta_boost = 9;
} elseif ($totalAds > 130000) {
	$beta_boost = 10;
} elseif ($totalAds > 125000) {
	$beta_boost = 11;
} elseif ($totalAds > 120000) {
	$beta_boost = 12;
} elseif ($totalAds > 115000) {
	$beta_boost = 13;
} elseif ($totalAds > 110000) {
	$beta_boost = 14;
} elseif ($totalAds > 105000) {
	$beta_boost = 15;
} elseif ($totalAds > 100000) {
	$beta_boost = 16;
} elseif ($totalAds > 96000) {
	$beta_boost = 17;
} elseif ($totalAds > 92000) {
	$beta_boost = 18;
} elseif ($totalAds > 88000) {
	$beta_boost = 19;
} elseif ($totalAds > 84000) {
	$beta_boost = 20;
} elseif ($totalAds > 80000) {
	$beta_boost = 21;
} elseif ($totalAds > 76000) {
	$beta_boost = 22;
} elseif ($totalAds > 72000) {
	$beta_boost = 23;
} elseif ($totalAds > 68000) {
	$beta_boost = 24;
} elseif ($totalAds > 64000) {
	$beta_boost = 25;
} elseif ($totalAds > 60000) {
	$beta_boost = 26;
} elseif ($totalAds > 56000) {
	$beta_boost = 27;
} elseif ($totalAds > 53000) {
	$beta_boost = 28;
} elseif ($totalAds > 51000) {
	$beta_boost = 29;
} elseif ($totalAds > 48000) {
	$beta_boost = 30;
} elseif ($totalAds > 45000) {
	$beta_boost = 31;
} elseif ($totalAds > 42000) {
	$beta_boost = 32;
} elseif ($totalAds > 39000) {
	$beta_boost = 33;
} elseif ($totalAds > 36000) {
	$beta_boost = 34;
} elseif ($totalAds > 33000) {
	$beta_boost = 35;
} elseif ($totalAds > 30000) {
	$beta_boost = 36;
} elseif ($totalAds > 28000) {
	$beta_boost = 37;
} elseif ($totalAds > 26000) {
	$beta_boost = 38;
} elseif ($totalAds > 24000) {
	$beta_boost = 39;
} elseif ($totalAds > 22000) {
	$beta_boost = 40;
} elseif ($totalAds > 20000) {
	$beta_boost = 41;
} elseif ($totalAds > 18000) {
	$beta_boost = 42;
} elseif ($totalAds > 16000) {
	$beta_boost = 43;
} elseif ($totalAds > 14000) {
	$beta_boost = 44;
} elseif ($totalAds > 12000) {
	$beta_boost = 45;
} elseif ($totalAds > 10000) {
	$beta_boost = 46;
} elseif ($totalAds > 8000) {
	$beta_boost = 47;
} elseif ($totalAds > 6000) {
	$beta_boost = 48;
} elseif ($totalAds > 4000) {
	$beta_boost = 49;
} elseif ($totalAds > 3000) {
	$beta_boost = 50;
} elseif ($totalAds > 2000) {
	$beta_boost = 51;
} elseif ($totalAds > 1000) {
	$beta_boost = 52;
} elseif ($totalAds > 0) {
	$beta_boost = 53;
}

// Podcast ads are half
$beta_boost = ($_SESSION['new_ad_pod_listing'] == 'pod') ? ceil($beta_boost / 2) : $beta_boost;

$beta_boot_text = ($_SESSION['new_ad_pod_listing'] == 'pod') ? "podcast ad" : "business or normal ad";

$_SESSION['beta_boost'] = $beta_boost;

// Determine beta boost status
if ($beta_boost > 1 ) { // Plural
	$beta_boost_pretty = "$beta_boost free weeks with purchase of $beta_boot_text";
} elseif ($beta_boost == 1 ) { // Singular
	$beta_boost_pretty = "$beta_boost free week with purchase of $beta_boot_text";
}

// Remember original weeks of the order
if (!isset($weeksPaying)) {
	$weeksPaying = $new_ad_weekslong;
}
$new_ad_base_weeks = $weeksPaying;

// Calculate our boosted week totals
$new_ad_boosted_weekslong = $new_ad_weekslong+$beta_boost;
$_SESSION['new_ad_boosted_weekslong'] = $new_ad_boosted_weekslong;
