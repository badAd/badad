<?php

// must me set AFTER stripe.inc.php: $userid, $catID, $subcatID, $roleID, $new_ad_tagIDs, $new_ad_nickname, $new_ad_heading, $new_ad_description, $new_ad_info, $new_ad_pricing, $new_ad_contactURL, $new_ad_weekslong, $adPrice, $discount, $adPricePaying, $token

// Calculate the new credits amount
$creditsAvailable = $_SESSION['creditsAvailable'];
$creditsUsing = $_SESSION['creditsUsing'];
$newCredits = $creditsAvailable - $creditsUsing;

// Update the database
$q = "UPDATE credits SET creditcount='$newCredits' WHERE userid='$userid'";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 1) {
  $q = "SELECT creditcount FROM credits WHERE userid='$userid'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $creditsAvailable = "$row[0]";
  $_SESSION['creditsAvailable'] = $creditsAvailable;
  $newCreditsMessage = "<p>Credits remaining: <b>$creditsAvailable</b></p>";
} else {
  sql_error($q, 'dbc', "sqle_42");
}
