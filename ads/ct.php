<?php
// This receives a "Contact" link, counts analytics, the redirects to the ad's contact link

// Configs
require_once ('./config.inc.php');
require_once (MYSQL);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Validate the serial number: a-zA-Z0-9
if ((!isset($_GET['l'])) || (!isset($_GET['p']))) {
  header("Location: https://badad.one");
  exit(); // Quit the script
}

// Validate the serial number: a-zA-Z0-9
$site_badadref_no = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
if (preg_match('/[^a-zA-Z0-9]/', $site_badadref_no)) {exit();}
$ad_serial = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['p']);
if (preg_match('/[^a-zA-Z0-9]/', $ad_serial)) {exit();}

// Set role & business filters
$analytics_filter = "";
if ((isset($_SESSION['filter_w'])) && ($_SESSION['filter_w'] == true)) { $analytics_filter = $analytics_filter."w,"; }
if ((isset($_SESSION['filter_s'])) && ($_SESSION['filter_s'] == true)) { $analytics_filter = $analytics_filter."s,"; }
if ((isset($_SESSION['filter_a'])) && ($_SESSION['filter_a'] == true)) { $analytics_filter = $analytics_filter."a,"; }
if ((isset($_SESSION['filter_b'])) && ($_SESSION['filter_b'] == true)) { $analytics_filter = $analytics_filter."b,"; }

// Listing the ads needs the _SRV config
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Escape globally
$esc_site_badadref_no = mysqli_real_escape_string($srv_dbc, $site_badadref_no);
$esc_ad_serial = mysqli_real_escape_string($srv_dbc, $ad_serial);

// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// For Search
if ($site_badadref_no == "s") {
  $ad_count_method = "search_click_count";
  $clicked_ad_analytics_source = "search";
  // Tag, Category, or All
  if ((isset($_SESSION['tagID'])) && (isset($_SESSION['tag']))) {
    $subkey = "_".$_SESSION['tag'];
    $key_id = $_SESSION['tagID'];
  } elseif ((isset($_SESSION['catID'])) && (isset($_SESSION['subcatID'])) && (isset($_SESSION['category'])) && (isset($_SESSION['subcat']))) {
    $subkey = $_SESSION['category'].": ".$_SESSION['subcat'];
    $subkey = mysqli_real_escape_string($srv_dbc, $subkey);
      // Global subcat ID for key_id
      $catID = $_SESSION['catID'];
      $subcatID = $_SESSION['subcatID'];
      $gcq = "SELECT id FROM global_subcat_ids WHERE cat_id=$catID AND subcat_id=$subcatID";
      $gcr = mysqli_query ($srv_dbc, $gcq);
      $gcrow = mysqli_fetch_array($gcr, MYSQLI_NUM);
    $key_id = $gcrow[0];
  } elseif ((isset($_SESSION['catID'])) && (isset($_SESSION['category']))) {
    $subkey = $_SESSION['category'];
    $subkey = mysqli_real_escape_string($srv_dbc, $subkey);
    $key_id = $_SESSION['catID'];
  } else {
    $subkey = "none";
    $key_id = "NULL";
  }
  // Search?
  if (isset($_SESSION['searchQuery'])) {
    $searchQuery = $_SESSION['searchQuery'];
    $keytext = mysqli_real_escape_string($srv_dbc, $searchQuery);
  } else {
    $keytext = "none";
    $key_id = "NULL";
  }


// For Tag
} elseif ($site_badadref_no == "t") {
  $ad_count_method = "tag_click_count";
  $clicked_ad_analytics_source = "tag";
  // Tag
  if ((isset($_SESSION['tagID'])) && (isset($_SESSION['tag']))) {
    $keytext = $_SESSION['tag'];
    $subkey = "none";
    $key_id = $_SESSION['tagID'];
  } else {
    $keytext = "none";
    $subkey = "none";
    $key_id = "NULL";
  }

// For Category
} elseif ($site_badadref_no == "c") {
  $ad_count_method = "cat_click_count";
  $clicked_ad_analytics_source = "cat";
  if ((isset($_SESSION['catID'])) && (isset($_SESSION['subcatID'])) && (isset($_SESSION['category'])) && (isset($_SESSION['subcat']))) {
    $keytext = "";
    $subkey = $_SESSION['category'].": ".$_SESSION['subcat'];
    $subkey = mysqli_real_escape_string($srv_dbc, $keytext);
      // Global subcat ID for key_id
      $catID = $_SESSION['catID'];
      $subcatID = $_SESSION['subcatID'];
      $gcq = "SELECT id FROM global_subcat_ids WHERE cat_id=$catID AND subcat_id=$subcatID";
      $gcr = mysqli_query ($srv_dbc, $gcq);
      $gcrow = mysqli_fetch_array($gcr, MYSQLI_NUM);
      $key_id = $gcrow[0];
  } elseif ((isset($_SESSION['catID'])) && (isset($_SESSION['category']))) {
    $keytext = "none";
    $subkey = $_SESSION['category'];
    $subkey = mysqli_real_escape_string($srv_dbc, $keytext);
    $key_id = $_SESSION['catID'];
  } else {
    $keytext = "none";
    $subkey = "none";
    $key_id = "NULL";
  }


// For site View
} elseif ($site_badadref_no == "v") {
  $ad_count_method = "view_click_count";
  $clicked_ad_analytics_source = "view";
  $keytext = "none";
  $subkey = "none";
  $key_id = "NULL";


// For Partner Listing
} else {
  $ad_count_method = "listed_click_count";
  $clicked_ad_analytics_source = "listed";
  $keytext = "none";
  $subkey = "none";
  $key_id = "NULL";


  // Get the settings from the partnersites table
  $q = "SELECT id, type FROM partnersites WHERE badadref_no='$esc_site_badadref_no'";
  $r = mysqli_query($srv_dbc, $q);
  	// Get the number of rows returned & exit if none
  	$rows = mysqli_num_rows($r);
  	if ($rows == 0) {exit();}
  // Site variables
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $site_id = "$row[0]";
  $ad_type = "$row[1]";

  // If partnersite count ran okay
  //if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Partner site click not counted."; }
}

// Get the settings from the partnersites table
$q = "SELECT ad_id, $ad_count_method, ad_content_cntc FROM listads WHERE BINARY serialno='$esc_ad_serial'";
$r = mysqli_query($srv_dbc, $q);
  // Get the number of rows returned & exit if none
  $rows = mysqli_num_rows($r);
  if ($rows == 0) {exit();}
// Site variables
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$ad_id = "$row[0]";
$ad_click_count = "$row[1]";
$ad_content_cntc = "$row[2]";

// Text ads will use $ad_count_method, etc
if (($ad_type == 'site') || ($ad_type == 'app')) {

  // Update the analytics
  $aq = "INSERT INTO clicked_ad_analytics (ad_id, source, keytext, subkey, key_id, filter, time_date, time_epoch) VALUES ('$ad_id', '$clicked_ad_analytics_source', '$keytext', '$subkey', $key_id, '$analytics_filter', '$timeNow', '$timeNowEpoch')";
  $ar = mysqli_query ($srv_dbc, $aq);
  //if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Ad not counted."; }

  // Increment the count
  $newAdClickCount = $ad_click_count +1;

  // Get the settings from the partnersites table
  $q = "SELECT clicked_listed_count FROM partnersites WHERE badadref_no='$esc_site_badadref_no'";
  $r = mysqli_query($srv_dbc, $q);
    // Get the number of rows returned & exit if none
    $rows = mysqli_num_rows($r);
    if ($rows == 0) {exit();}
  // Site variables
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $clicked_listed_count = "$row[0]";

  // Increment the count
  $newPartnerClickCount = $clicked_listed_count +1;

  // Update the new counts for the ad
  $q = "UPDATE listads SET $ad_count_method='$newAdClickCount' WHERE BINARY serialno='$esc_ad_serial'";
  $r = mysqli_query ($srv_dbc, $q);

  // Update the new counts for the partner
  $q = "UPDATE partnersites SET clicked_listed_count='$newPartnerClickCount' WHERE badadref_no='$esc_site_badadref_no'";
  $r = mysqli_query ($srv_dbc, $q);

  if (mysqli_affected_rows($srv_dbc) == 1 ) { // If partnersite count ran okay

    // Go to the Contact URL
  	header("Location: $ad_content_cntc");
    exit(); // Quit the script

	}

// Podcast ads won't use $ad_count_method, etc
} elseif ($ad_type == 'podcast') {
  // Listad count
  // Get the settings from the partnersites table
  $q = "SELECT ad_id, ad_click_count FROM podcastads WHERE BINARY ad_id='$ad_id'";
  $r = mysqli_query($agg_dbc, $q);
  	// Get the number of rows returned & exit if none
  	$rows = mysqli_num_rows($r);
  	if ($rows == 0) {exit();}
  // Site variables
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $ad_id = "$row[0]";
  $ad_click_count = "$row[1]";

  // Increment the count
  $newAdClickCount = $ad_click_count +1;

  // Get the settings from the partnersites table
  $q = "SELECT ad_click_count FROM feeds WHERE project_id='$site_id'";
  $r = mysqli_query($srv_dbc, $q);
    // Get the number of rows returned & exit if none
    $rows = mysqli_num_rows($r);
    if ($rows == 0) {exit();}
  // Site variables
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $clicked_listed_count = "$row[0]";

  // Increment the count
  $newPartnerClickCount = $clicked_listed_count +1;

  // Update the analytics
  $aq = "INSERT INTO clicked_pod_ad_analytics (ad_id, time_date, time_epoch) VALUES ('$ad_id', '$timeNow', '$timeNowEpoch')";
  $ar = mysqli_query ($srv_dbc, $aq);
  //if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Ad not counted."; }

  // Update the new counts for the ad
  $q = "UPDATE podcastads SET ad_click_count='$newAdClickCount' WHERE BINARY ad_id='$ad_id'";
  $r = mysqli_query ($agg_dbc, $q);

  // Update the new counts for the partner
  $q = "UPDATE feeds SET ad_click_count='$newPartnerClickCount' WHERE project_id='$site_id'";
  $r = mysqli_query ($agg_dbc, $q);

  if (mysqli_affected_rows($agg_dbc) == 1 ) { // If partnersite count ran okay

    // Go to the Contact URL
  	header("Location: $ad_content_cntc");
    exit(); // Quit the script

	}
}

// If something fails
header("Location: /index.php");
exit(); // Quit the script

?>
