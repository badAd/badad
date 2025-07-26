<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require(MYSQL);

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if ((isset($_POST['a'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['a']))) {$IP = get_ip_addr(); script_kiddy('sk_81', '_POST a', $_POST['a'], $IP);}
if ((isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Get the ad info
$q = "SELECT ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing FROM ads WHERE id='$adID'";
$row = mysqli_query($dbc, $q);
$ad_item = mysqli_fetch_array($row);
	$List_ad_content_hdng = "$ad_item[0]";
	$List_ad_content_dscr = "$ad_item[1]";
	$List_ad_content_info = "$ad_item[2]";
	$List_ad_content_pyrt = "$ad_item[3]";
	$List_ad_content_cntc = "$ad_item[4]";
	$List_ad_content_bizn = "$ad_item[5]";
	$List_ad_biz_listing = "$ad_item[6]";
  // Render the ad preview
  $RenderedadHTML = "<p class=\"badad_ad\" style=\"text-align:center;\"><span class=\"badad_ad badad_ad_heading\"><b class=\"badad_ad\">$List_ad_content_hdng</b></span><br class=\"badad_ad\" /><span class=\"badad_ad badad_ad_description\">$List_ad_content_dscr</span><br class=\"badad_ad\" /><span class=\"badad_ad badad_ad_info\"><em class=\"badad_ad\">$List_ad_content_info</em></span><br class=\"badad_ad\" /><span class=\"badad_ad badad_ad_payrate\">$List_ad_content_pyrt</span><br class=\"badad_ad\" /><span class=\"badad_ad badad_ad_contactLink\"><u class=\"badad_ad\">Contact: $List_ad_content_cntc</u></a></span>";
  // Business listing?
  if ($List_ad_biz_listing == 'biz') {
    $RenderedadHTML = $RenderedadHTML."<br class=\"badad_ad\" /><b class=\"badad_ad badad_ad_content_bizn\">|<i class=\"badad_ad badad_ad_content_bizn\"> $List_ad_content_bizn </i>|</b>";
  }

require ('./includes/form_functions.inc.php');

// Include the header
// Set title
$page_title = "Delete a pending ad";
include ('./includes/header.html');

// Page content
echo "<h3 class=\"ads\">Delete this pending ad?</h3><br />
<p>This ad is not live and there is nothing published that will be changed. You have not been charged. You can safely delete this from your order history forever.</p>
<p class=\"note_red\" style=\"text-align:center;\"><b>Make sure you want to delete this pending ad:</b></p>
<hr />
$RenderedadHTML
<hr />
<br />
<p><a title=\"Return to your order history\" href=\"order_history.php\">No! Get me out of here!</a></h3>
";

set_switch("Delete forever!", "Delete this pending ad from order history", "delpend_ad_confirmed.act.php", "d", $adID, "set_red");

// Include the HTML footer
include ('./includes/footer.html');

?>
