<?php

// Configs
require ('./includes/config.inc.php');
require (MYSQL);

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// _GET the ad ID
if ((isset($_GET['a'])) && (filter_var($_GET['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_GET['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Fetch the ad
$adID = mysqli_real_escape_string ($dbc, $adID);
$q = "SELECT ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, category_id, subcat_id, role_id, tag_ids FROM ads WHERE id='$adID'";
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
  $List_ad_cat_id = "$ad_item[8]";
  $List_ad_subcat_id = "$ad_item[9]";
  $List_ad_role_id = "$ad_item[10]";
  $List_ad_tag_ids = "$ad_item[11]";

// Get the listad role
$rlaq = "SELECT initial, role, slug, description FROM roles WHERE id='$List_ad_role_id'";
$rlar = mysqli_query ($dbc, $rlaq);
$rlarow = mysqli_fetch_array($rlar, MYSQLI_NUM);
$role_i = "$rlarow[0]";
$role_Name = "$rlarow[1]";
$role_slug = "$rlarow[2]";
$role_description = "$rlarow[3]";
$role_link = "<a class=\"role role_$role_slug\" title =\"$role_Name: $role_description\">$role_i</a>";

// Get the category from its ID
$cq = "SELECT category, slug FROM categories WHERE id='$List_ad_cat_id'";
$cr = mysqli_query ($dbc, $cq);
$crow = mysqli_fetch_array($cr, MYSQLI_NUM);
$catname = "$crow[0]";
$catslug = "$crow[1]";

// Get the subcategory from its ID
$scq = "SELECT subcat, slug FROM sub_$catslug WHERE id='$List_ad_subcat_id'";
$scr = mysqli_query ($dbc, $scq);
$scrow = mysqli_fetch_array($scr, MYSQLI_NUM);
$subcatname = "$scrow[0]";
$subcatslug = "$crow[1]";

// tags
$arrayTagIDs = explode(',', $List_ad_tag_ids);
$tag_row = '';
foreach($arrayTagIDs as $tagID){
  // Get the tag from its ID
  $tq = "SELECT tag FROM tags WHERE id='$tagID'";
  $tr = mysqli_query ($dbc, $tq);
  $trow = mysqli_fetch_array($tr, MYSQLI_NUM);
  $tag = "$trow[0]";
  $tag_row .= '<b class="rendered_tag" ><a class="rendered_tag" title="See other items with the '.$tag.' tag" href="#">#'.$tag.'</a></b>, ';
}

// Podcast manuscript?
$pmq = "SELECT approved_manuscript FROM pod_ads WHERE ad_id='$adID'";
$pmr = mysqli_query ($dbc, $pmq);
$pmrows = mysqli_num_rows($pmr);
if ($pmrows == 1) {
  $pmrow = mysqli_fetch_array($pmr, MYSQLI_NUM);
  $podad_manuscript = "$pmrow[0]";
  $has_pod_ad = true;
} else {
  $podad_manuscript = 'none';
  $has_pod_ad = false;
}

// Include the header
$page_title = "badAd Ad: $List_ad_nickname";
include ('./includes/header.html');

echo "<h3 class=\"ads\" style=\"text-align:center;\">Ad: \"$List_ad_nickname\"</h3><br />";

// Prep the ad
$RenderedadHTML = "<hr class=\"badad_ad\" /><br /><div class=\"badad_ad_item\" style=\"margin-right:0.5em; margin-left:0.5em;\">";
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
echo "<br /><p class=\"badad_ad\" style=\"text-align:center;\">\"Contact\" URL: $List_ad_content_cntc</p><br />";
// Role
echo "<br /><p class=\"badad_ad\" style=\"text-align:center;\">\"Role\" indicator: $role_link</p><br />";
// Category
echo "<br /><p class=\"badad_ad\" style=\"text-align:center;\">\"Category\": <a class=\"rendered_category\" rel=\"nofollow\" title=\"See other items in '.$catname.'\" href=\"#\">'.$catname.'</a>: <a class=\"rendered_subcategory\" rel=\"nofollow\" title=\"See other items in '.$catname.': '.$subcatname.'\" href=\"#\">'.$subcatname.'</a></p><br />";

// Tags
echo "<br /><p class=\"badad_ad\" style=\"text-align:center;\">\"Tags\": $tag_row</p><br />";

// Podcast?
echo (isset($has_pod_ad) && ($has_pod_ad == true)) ? "<br /><p class=\"badad_ad\" style=\"text-align:center;\"><b>Podcast ad manuscript:</b><br /><br /><i><big>$podad_manuscript</big></i></p><br />" : false;

// Include the footer file to complete the template
require ('./includes/footer.html');

?>
