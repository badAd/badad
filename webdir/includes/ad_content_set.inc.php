<?php

// Role Link
// Get the listad role
$rlaq = "SELECT initial, role, slug, description FROM roles WHERE id='$roleID'";
$rlar = mysqli_query ($dbc, $rlaq);
$rlarow = mysqli_fetch_array($rlar, MYSQLI_NUM);
$role_i = "$rlarow[0]";
$role_Name = "$rlarow[1]";
$role_slug = "$rlarow[2]";
$role_description = "$rlarow[3]";
$role_link = "<a class=\"role role_$role_slug\" title =\"$role_Name: $role_description\">$role_i</a>";

// Create the ad content entry
$adContent = "<div class=\"badad_ad\"><strong class=\"badad_ad\">$new_ad_heading</strong><br class=\"badad_ad\" /><span class=\"badad_ad descr\">$new_ad_description</span><br class=\"badad_ad\" /><em class=\"badad_ad\">$new_ad_info</em><br class=\"badad_ad\" /><span class=\"badad_ad price\">$new_ad_pricing</span> <a class=\"badad_ad disabled_preview\" href=\"#\"><u class=\"badad_ad\">Contact</u></a> $role_link";
// Business Listing?
if ($new_ad_biz_listing == 'biz') {
  $adContent = $adContent."<br class=\"badad_ad\" /><b class=\"badad_ad badad_ad_content_bizn\">|<i class=\"badad_ad badad_ad_content_bizn\"> $new_ad_content_bizn </i>|</b>";
}
$adContent = $adContent."</div>";

$_SESSION['adContent'] = $adContent;
$_SESSION['roleName'] = $role_Name;
