<?php

// This comes early to work with credit.inc.php

// Make sure we aren't recapitulating old _SESSION discounts
unset($_SESSION['creditsUsing']);
unset($_SESSION['discount']);

// Price
// Add the credits if _POSTed
if ((isset($_POST['creditsUsing'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['creditsUsing']))) {$IP = get_ip_addr(); script_kiddy('sk_84', '_POST creditsUsing', $_POST['creditsUsing'], $IP);}
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['creditsUsing']))
 && (filter_var($_POST['creditsUsing'], FILTER_VALIDATE_INT, array('min_range' => 0)))) {
	$creditsUsing = preg_replace("/[^A-Za-z0-9]/","", $_POST['creditsUsing']);
	$_SESSION['creditsUsing'] = $creditsUsing;
} // Done with _POST check

// Week noun number
$week_noun = ($new_ad_weekslong == 1) ? 'week' : 'weeks';

// Podcast / normal-business weeks calculation
if ((isset($new_ad_content_pdcst)) && ($new_ad_content_pdcst != NULL) && ($new_ad_pod_listing == 'pod') && ($adPodcastPrice != 0)) {
  $per_week_total = $adPricePerWeek * $new_ad_weekslong;
  $per_week_explanation = "$$adPodcastPrice for a new podcast<br />$$per_week_total for this run ($$adPricePerWeek per week, for $new_ad_weekslong $week_noun)";
} else {
  $per_week_explanation = "$$adPricePerWeek per week, for $new_ad_weekslong $week_noun";
}

// Calculate credits
if (isset($_SESSION['creditsUsing'])) { // $creditsUsing must be set by credit.inc.php
	$creditsUsing = $_SESSION['creditsUsing'];
	// Recalculate $adPrice
	$weeksPaying = $new_ad_weekslong - $creditsUsing;
	// Calculate total paying price
	$adPriceTotal = (($new_ad_pod_listing == 'pod') && ($adPodcastPrice != 0)) ? ($adPodcastPrice + abs($weeksPaying*$adPricePerWeek)) : abs($weeksPaying*$adPricePerWeek);
	// Check if decimals were removed
	if (strpos($adPriceTotal, ".") == false) { $adPriceTotal = $adPriceTotal.'.00'; }

	$discount = $adPrice - $adPriceTotal;
	// Check if decimals were removed
	if (strpos($discount, ".") == false) { $discount = $discount.'.00'; }
	// Set the discount figures
	$adPricePaying = $adPriceTotal;
	$_SESSION['adPricePaying'] = $adPricePaying;
	$_SESSION['discount'] = $discount;

	// Beta boost
	include_once ('./includes/betaboost.inc.php');
  echo "<div class=\"price\">$per_week_explanation<br />Credits used: $creditsUsing <i>($$discount discount)</i><br />";
	if ((isset($beta_boost)) && (isset($new_ad_boosted_weekslong)) && (isset($beta_boost_pretty))) {
		// Show the totals for beta boost
		echo "<div class=\"beta_boost_box\">*Current beta boost: $beta_boost_pretty<br />*Full duration of your ad: $new_ad_boosted_weekslong weeks</div><br />";
	}

  echo "Total: <b>$$adPricePaying USD</b><br /><br />";

  if ($_SESSION['user_is_admin']) {
    $adPricePaying = '0.00';
    $adPriceTotal = '0.00';
  	$_SESSION['adPricePaying'] = $adPricePaying;
    $_SESSION['discount'] = '0.00';
    echo "Admin Total: <b>$$adPricePaying USD</b>";
  }
  echo "</div>";

} else { // No credits
	// Calculate total paying price
  $adPriceTotal = (($new_ad_pod_listing == 'pod') && ($adPodcastPrice != 0)) ? ($adPodcastPrice + abs($new_ad_weekslong*$adPricePerWeek)) : abs($new_ad_weekslong*$adPricePerWeek);
	// Check if decimals were removed
	if (strpos($adPriceTotal, ".") == false) { $adPriceTotal = $adPriceTotal.'.00'; }

	// No empty variables for non-discount purchases
	$discount = '0.00';
	$adPricePaying = $adPriceTotal;
	$_SESSION['adPricePaying'] = $adPricePaying;
	$_SESSION['discount'] = $discount;

	// Beta boost
	include_once ('./includes/betaboost.inc.php');
  echo "<div class=\"price\">$per_week_explanation<br />";
	if ((isset($beta_boost)) && (isset($new_ad_boosted_weekslong)) && (isset($beta_boost_pretty))) {
		// Show the totals for beta boost
		echo "<div class=\"beta_boost_box\">*Current beta boost: $beta_boost_pretty<br />*Full duration of your ad: $new_ad_boosted_weekslong weeks</div><br />";
	}

  echo "Total: <b>$$adPricePaying USD</b><br /><br />";

  if ($_SESSION['user_is_admin']) {
    $adPricePaying = '0.00';
    $adPriceTotal = '0.00';
    $_SESSION['adPricePaying'] = $adPricePaying;
    $_SESSION['discount'] = '0.00';
    echo "Admin Total: <b>$$adPricePaying USD</b>";
  }
  echo "</div>";

} // End credits if

// Itemized statement
$itemized_receipt_statement = $per_week_explanation.'<br />Total: <b>$'.$adPricePaying.' USD</b>';
$_SESSION['itemized_receipt_statement'] = $itemized_receipt_statement;
