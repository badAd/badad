<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Require the database connection
require (MYSQL);

// _GET the ad ID
if ((isset($_GET['a'])) && (filter_var($_GET['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Fetch the ad
$q = "SELECT ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing FROM ads WHERE id='$adID'";
// AND user_id='$userid'
$row = mysqli_query($dbc, $q);
$ad_item = mysqli_fetch_array($row);
	$List_ad_nickname = "$ad_item[0]";
	$List_ad_content_hdng = "$ad_item[1]";
	$List_ad_content_dscr = "$ad_item[2]";
	$List_ad_content_info = "$ad_item[3]";
	$List_ad_content_pyrt = "$ad_item[4]";
	$List_ad_content_cntc = "$ad_item[5]";
	$List_ad_content_bizn = "$ad_item[6]";
	$List_ad_biz_listing = "$ad_item[7]";

// Create the header
echo "<!DOCTYPE html>
<html>
<head>
<title>$siteTitle Preview: $List_ad_nickname</title>
</head>
<body>";

echo "<h3 class=\"ads\" style=\"text-align:center;\">Ad: \"$List_ad_nickname\"</h3>";

// Prep the ad
$RenderedadHTML = "<hr class=\"badad_ad\" /><div class=\"badad_ad_item\" style=\"margin-right:0.5em; margin-left:0.5em;\">";
$RenderedadHTML = $RenderedadHTML."<p class=\"badad_ad\" style=\"text-align:center;\"><span class=\"badad_ad badad_ad_heading\"><strong class=\"badad_ad\">$List_ad_content_hdng</strong></span><br class=\"badad_ad\" /><span class=\"badad_ad badad_ad_description\">$List_ad_content_dscr</span><br class=\"badad_ad\" /><span class=\"badad_ad badad_ad_info\"><em class=\"badad_ad\">$List_ad_content_info</em></span><br class=\"badad_ad\" /><span class=\"badad_ad badad_ad_payrate\">$List_ad_content_pyrt</span>&nbsp;<span class=\"badad_ad badad_ad_contactLink\"><a class=\"badad_ad badad_ad_contactLink\" rel=\"nofollow\" href=\"#\"><u class=\"badad_ad\">Contact</u></a></span>";
// Business listing?
if ($List_ad_biz_listing == 'biz') {
  $RenderedadHTML = $RenderedadHTML."<br class=\"badad_ad\" /><strong class=\"badad_ad badad_ad_content_bizn\"><i class=\"badad_ad badad_ad_content_bizn\">$List_ad_content_bizn</i></strong>";
}
// Finish the ad
$RenderedadHTML = $RenderedadHTML."</p><hr class=\"badad_ad\" />";
$RenderedadHTML = $RenderedadHTML."</div>";

// Render the ad
echo $RenderedadHTML;
// Contact
echo "<p class=\"badad_ad\" style=\"text-align:center;\">\"Contact\" URL: $List_ad_content_cntc</p><br />";

// End the html file
echo "</body>
</html>";
?>
