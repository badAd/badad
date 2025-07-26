<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Require the database connection
require_once (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Check for form submission and set the $dev_app_id variable
	// Several Kiddy Checks
	if ((isset($_POST['s'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['s']))) {$IP = get_ip_addr(); script_kiddy('sk_48', '_POST s', $_POST['s'], $IP);}
	if ((isset($_POST['dev_domain'])) && (preg_match ('/[^a-zA-Z0-9-.]$/i', $_POST['dev_domain']))) {$IP = get_ip_addr(); script_kiddy('sk_49', '_POST dev_domain', $_POST['dev_domain'], $IP);}
	if ((isset($_POST['dev_name'])) && (preg_match ('/[^A-Z0-9 \'\/&,-]$/i', $_POST['dev_name']))) {$IP = get_ip_addr(); script_kiddy('sk_52', '_POST dev_name', $_POST['dev_name'], $IP);}

	// First visit
	if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['s']))
	&& (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
	$dev_app_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['s']);

// Change made
} elseif (($_SERVER['REQUEST_METHOD'] === 'POST')
	&& (isset($_POST['dapp_callback']))
	&& (isset($_POST['edit_dapp_id']))
	&& (isset($_POST['dev_domain']))
	&& (isset($_POST['dev_name']))
	&& (isset($_POST['old_callback']))) {
		$dapp_callback = $_POST['dapp_callback'];
		$old_callback = $_POST['old_callback'];
		$dev_app_id = $dev_app_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['edit_dapp_id']);
		// Set form values
		$dev_domain = $_POST['dev_domain'];
		$dev_name = preg_replace("/[^A-Z0-9 \'\/&,-]/","", $_POST['dev_name']);

		// Validate callback URL
		$dapp_callback = $_POST['dapp_callback'];
		if ((filter_var($dapp_callback,FILTER_VALIDATE_URL)) && (substr($dapp_callback, 0, 8) === "https://")) {
			$edit_dapp_callback_sql = mysqli_real_escape_string ($srv_dbc, $dapp_callback);
		} else {
			$error_dapp_callback = "<span class=\"note_red\">The callback URL must be real and begin with \"https://\"</span>";
		}

		// Kiddy Check old callback URL
		if ((filter_var($old_callback,FILTER_VALIDATE_URL)) && (substr($old_callback, 0, 8) === "https://")) {
			$old_callback_url_sk_checks_out = true;
		} else {
			$IP = get_ip_addr(); script_kiddy('sk_51', '_POST old_callback', $_POST['old_callback'], $IP);
		}

		// Make sure the callback contains the domain
		//if ((strpos($dapp_callback, "https://$dev_domain") === 0) || (strpos($dapp_callback, "https://www.$dev_domain") === 0)) // too simple, remove if everything works
			if ( (strpos(parse_url($dapp_callback, PHP_URL_HOST), $dev_domain) !== false) &&
					 (substr($dapp_callback, 0, 8) === "https://") ) {
			$callback_domain_ok = true;
		} else {
			$error_domain_callback = "<span class=\"note_red\">The callback URL must begin with <code class=\"inline\">https://</code> and contain the domain.</span>";
		}

		// Validate description
		if ((isset($_POST['dapp_description'])) && (($_POST['dapp_description'] != ""))) {
			function is_valid_app_description($validating_description) {
				return (preg_match ('/^[A-Z0-9 \'\/&,-]{0,80}$/i', $validating_description));
			}
			$dapp_description = $_POST['dapp_description'];
			if (!is_valid_app_description($dapp_description)) {
				$error_dapp_description = "<span class=\"note_red\">The Description may only use letters and numbers and - \' / , &</span>";
			}
		} else {
			$dapp_description = "";
		}
		$edit_dapp_description_sql = mysqli_real_escape_string ($srv_dbc, $dapp_description);

		// Custom callback?
		if ((isset($_POST['custom_callback'])) && ($_POST['custom_callback'] == 'yes')) {
			$custom_callback = 1;
		} else {
			$custom_callback = 0;
		}

		// Error check
		if ((!isset($error_dapp_callback))
		&& (!isset($error_dapp_description))
		&& (!isset($error_domain_callback))) {

			// Update the database
			if ($dapp_description == "") {
				$q = "UPDATE devkeys SET description=NULL, callback='$edit_dapp_callback_sql', date_modified=NOW(), use_custom_callback=$custom_callback WHERE id='$dev_app_id'";
			} else {
				$q = "UPDATE devkeys SET description='$edit_dapp_description_sql', callback='$edit_dapp_callback_sql', date_modified=NOW(), use_custom_callback=$custom_callback WHERE id='$dev_app_id'";
			}
			if ($r = mysqli_query ($srv_dbc, $q)) { // If the query ran OK with no error
				$update_success = true;
			} else {
				sql_error($q, 'srv_dbc', "sqle_99");
			}

		} // End error check

	// Wrong place
	} else {
		header("Location: partner.php");
		exit(); // Quit the script
	}

// Include the header file
$page_title = "Develeoper Center - Edit App :: $siteTitle";
include ('./includes/header.html');

// Updated?
if ((isset($update_success)) && ($update_success == true)) {
	// Send email about callback change?
	if ((isset($old_callback)) && ($old_callback != $dapp_callback)) {
		$payload_content = "<p>For the App: <b>$dev_name</b> ($dev_domain), your new Callback URL is: <b>$dapp_callback</b></p>"; // Start custom content to be appended by the canned email body content.
		include ('./includes/confirm_partner_dev_change.inc.php');
	}
	// Display a message
	echo "<p class=\"note_green\">Dev App updated!</p>";
} elseif ((isset($error_dapp_callback)) || (isset($error_dapp_description)) || (isset($error_domain_callback))) {
	echo "<p class=\"note_red\">Can't make that change. Try again.</p>";
} else {
	// First arrival, Query the Partner Site Domain and Nickname
	$q = "SELECT domain, name, description, callback FROM devkeys WHERE id='$dev_app_id'";
	$r = mysqli_query ($srv_dbc, $q);
	$row = mysqli_fetch_array($r, MYSQLI_NUM);
	$dev_domain = "$row[0]";
	$dev_name = "$row[1]";
	$dapp_description = "$row[2]";
	$dapp_callback = "$row[3]";
}

// Breadcrumb
set_switch("&larr; Back to the Developer Center", "Go to the Developer Center", "partner_dev.php", "partner_dev", $userid, "set_gray");
echo "<br />";


// Heading
echo "<h3>Edit Dev App</h3>";

// Create the form

// Any domain-callback conflict error
if (isset($error_domain_callback)) {echo "<br /><p>$error_domain_callback</p>";}

echo "<form id=\"edit_dev_partner_app\" class=\"edit_partner_nickname\" action=\"partner_dev_edit_app.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"edit_dapp_id\" value=\"$dev_app_id\" />
<input type=\"hidden\" name=\"dev_domain\" value=\"$dev_domain\" />
<input type=\"hidden\" name=\"dev_name\" value=\"$dev_name\" />
<input type=\"hidden\" name=\"old_callback\" value=\"$dapp_callback\" />
<p>App Name: <b>$dev_name</b></p><p";
if (isset($error_domain_callback)) {echo ' class="error"';}
echo ">Domain: <b>$dev_domain</b></p>
<p><i class=\"note_gray\">Descriptions are optional, you can delete the contents of the field to remove it.</i></p>
<p";
if (isset($error_dapp_description)) {echo ' class="error"';}
echo ">Description: <input";
if (isset($error_dapp_description)) {echo ' class="error"';}
echo " type=\"text\" name=\"dapp_description\" size=\"18\"";
	if (($dapp_description == NULL ) || ($dapp_description == "" )) {
		echo "placeholder=\"Description (optional)\"";
	} else {
		echo "value=\"$dapp_description\"";
	}
echo " />";
if (isset($error_dapp_description)) {echo " $error_dapp_description";}
echo "</p>
<p";
if ((isset($error_dapp_callback)) || (isset($error_domain_callback))) {echo ' class="error"';}
echo ">Callback URL: <i class=\"note_gray\">(native)</i> <input";
if ((isset($error_dapp_callback)) || (isset($error_domain_callback))) {echo ' class="error"';}
echo " type=\"url\" name=\"dapp_callback\" size=\"18\" value=\"$dapp_callback\" />";
if (isset($error_dapp_callback)) {echo " $error_dapp_callback";}
echo "</p>
<p><label for=\"custom_callback\"><input type=checkbox name=\"custom_callback\" name=\"custom_callback\" value=\"yes\" /> <b>Use custom callback? (likely no)</b> <i class=\"note_gray\">(This requires the <code class=\"inline\">custom_callback</code> argument in the API, only for complex use, leave unchecked if in doubt.)</i></label></p>
<input type=\"submit\" value=\"Update\" class=\"formbutton\" />
</form><br /><hr />";

// New Keys

echo "<br /><hr class=\"note_red\" /><br /><h3 class=\"note_red\">Make new keys</h3><br /><p><b class=\"note_red\">CAUTION!</b> This will generate a new live key pair and set the old keys to expire after a few days. This will not affect test keys.</p>";

echo "<form id=\"new_keys\" action=\"partner_dev_new_keys.act.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"s\" value=\"$dev_app_id\" />
<br />
<p><input type=\"checkbox\" name=\"ready\" value=\"true\" required /> <b>I am ready:</b> This will generate new keys and the old keys will expire soon after. I need to implement my new keys quickly or my live app might stop working until I do.</p>
<input type=\"submit\" value=\"Generate new keys\" class=\"formbutton_red\" />";

// Include the HTML footer
include ('./includes/footer.html');
?>
