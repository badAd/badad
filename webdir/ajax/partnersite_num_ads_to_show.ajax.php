<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];


// Check for an "Ads to Show" submission
if ((isset($_POST['num_ads_to_show'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['num_ads_to_show']))) {$IP = get_ip_addr(); script_kiddy('sk_72', '_POST num_ads_to_show', $_POST['num_ads_to_show'], $IP);}
if ((isset($_POST['num_ads_site_id'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['num_ads_site_id']))) {$IP = get_ip_addr(); script_kiddy('sk_73', '_POST num_ads_site_id', $_POST['num_ads_site_id'], $IP);}

if (($_SERVER['REQUEST_METHOD'] == 'POST')
&& (isset($_POST['num_ads_to_show']))
&& (filter_var($_POST['num_ads_to_show'], FILTER_VALIDATE_INT, array('min_range' => 1)))
&& (isset($_POST['num_ads_site_id']))
&& (filter_var($_POST['num_ads_site_id'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $new_num_ads_to_show = preg_replace("/[^A-Za-z0-9]/","", $_POST['num_ads_to_show']);
  $new_num_ads_site_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['num_ads_site_id']);

  // Require the database connection
  require (MYSQL);

  // Listing the ads needs the _SRV config
  require_once ('./includes/config_srv.inc.php');
  require_once (MYSQL_SRV);


	// Validate the numbers
	if (filter_var($new_num_ads_to_show, FILTER_VALIDATE_INT) === false) {$new_num_ads_to_show = 1;}
	if ($new_num_ads_to_show > 20) {$new_num_ads_to_show = 20;}
	if ($new_num_ads_to_show < 0) {$new_num_ads_to_show = 0;}

	// Update the database
	$q = "UPDATE partnersites SET num_ads_to_show='$new_num_ads_to_show' WHERE id='$new_num_ads_site_id'";

  if ($r = mysqli_query ($srv_dbc, $q)) {

    // echo the new form
    echo '<form id="ads_to_show_form_'.$site_id.'" class="ads_to_show" accept-charset="utf-8">
      <input type="hidden" name="num_ads_site_id" value="'.$site_id.'" />
      <input type="number" class="num_ads" name="num_ads_to_show" value="'.$site_ads_to_show.'" step="1" min="0" max="20" />
      <br><br>
      <button type="button" class="formbutton_violet" title="Switch on" onclick="ajaxFormData("ads_to_show_form_'.$site_id.'", "partnersite_num_ads_to_show.ajax.php", "ads_to_show_td_'.$site_id.'");">vertical</button>
    </form>';
    exit(); // Quit the script

  } else {
		sql_error($q, 'srv_dbc', "sqle_80");
	}

} // "Ads to Show" form submission check
