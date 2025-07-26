<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Login check
include_once ('includes/login_check.inc.php');

// If the user isn't logged in, redirect them
if (!isset($_SESSION['user_id'])) {
	header("Location: partner.php");
	exit(); // Quit the script
} else {
	$userid = $_SESSION['user_id'];
}

// Make sure we're not here on accident
if (!isset($_POST['partner_dev'])) {
  header("Location: partner.php");
  exit(); // Quit the script
} elseif ($_POST['partner_dev'] != $userid) {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Include the header file
$page_title = "Developer API :: $siteTitle";
include ('./includes/header.html');

// New Partner App Project form submission
if ((isset($_POST['papp_name'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['papp_name']))) {$IP = get_ip_addr(); script_kiddy('sk_59', '_POST papp_name', $_POST['papp_name'], $IP);}
if (isset($_POST['papp_name'])) {
   $pappdomain = preg_replace("/[^A-Za-z0-9]/","", $_POST['papp_name']);

  // Validate the nickname
  if ((isset($_POST['papp_nickname'])) && ($_POST['papp_nickname'] != "")) {
    $papp_nickname = $_POST['papp_nickname'];
    if (!preg_match ('/^[A-Z0-9 \'\/&,-]{0,80}$/i', $papp_nickname)) {
      $error_app_nickname = "<span class=\"note_red\">The nickname may be only letters and numbers and - \' / , &</span>";
    } else {
      $new_papp_nickname = "<i>(with the nickname: $papp_nickname)</i>";
      $new_papp_nickname_sql = mysqli_real_escape_string ($srv_dbc, $papp_nickname);
    }
  } else {
		$new_papp_nickname = "<i>(with no nickname)</i>";
    $new_papp_nickname_sql = "";
  }

  // Prep the temp name for SQL
  $pappdomain_sql = mysqli_real_escape_string ($srv_dbc, $pappdomain);

  if (!isset($error_app_nickname)) { // No errors, create the App Project

    // Set the serials to "w"
    $pserial = "w";

    // Generate the ridiculously long random string
    require_once ('./includes/string_functions.inc.php');

		// Create the badadref_no link
		$rserial = longString(255);
		// Dup check
		$q = "SELECT badadref_no FROM partnersites WHERE binary badadref_no='$rserial'";// "binary" makes sure case and characters are exact
		$row = mysqli_query ($srv_dbc, $q);
		// while ($dup = mysqli_fetch_array($row)) {
		//   $rserial = longString(255);
		// }
		while (mysqli_num_rows($row) != 0) {
		  $rserial = longString(255);
		  // Check again
			$q = "SELECT badadref_no FROM partnersites WHERE binary badadref_no='$rserial'";// "binary" makes sure case and characters are exact
			$row = mysqli_query ($srv_dbc, $q);
		  if (mysqli_num_rows($row) == 0) {
		    break;
		  }
		}

    // Create the papp_key
    $papp_key = longString(64);
		$papp_key = "app_key_$papp_key";

    // Dup check
    $q = "SELECT papp_key FROM partnersites WHERE binary papp_key='$papp_key'"; // "binary" makes sure case and characters are exact
    $row = mysqli_query ($srv_dbc, $q);
    // while ($dup = mysqli_fetch_array($row)) {
    //   $papp_key = longString(64);
		// 	$papp_key = "app_key_$papp_key";
    // }
		while (mysqli_num_rows($row) != 0) {
			$papp_key = longString(64);
			$papp_key = "app_key_$papp_key";
			// Check again
			$q = "SELECT papp_key FROM partnersites WHERE binary papp_key='$papp_key'"; // "binary" makes sure case and characters are exact
	    $row = mysqli_query ($srv_dbc, $q);
			if (mysqli_num_rows($row) == 0) {
				break;
			}
		}

    // Get user's email
    $q = "SELECT email FROM users WHERE id='$userid'";
    $r = mysqli_query ($dbc, $q);
    $row = mysqli_fetch_array ($r, MYSQLI_NUM);
    $email = $row[0];

    // Get the current list of global category IDs
    $q = "SELECT id FROM global_subcat_ids";
    $row = mysqli_query($srv_dbc, $q);
    while ($gcid = mysqli_fetch_array($row)) {
    		// Assign variables
        if (!isset($global_cat_id_list)) {
          $global_cat_id_list = $gcid[0];
        } else {
    		  $global_cat_id_list = $global_cat_id_list.', '.$gcid[0];
        }
      }

    // Add the link to the database
    $q = "INSERT INTO partnersites (user_id, serial_no, badadref_no, domain, nickname, global_subcat_ids, papp_key, type) VALUES ('$userid', '$pserial', '$rserial', '$pappdomain_sql', '$new_papp_nickname_sql', '$global_cat_id_list', '$papp_key', 'app')";
    $r = mysqli_query ($srv_dbc, $q);

    if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran OK

    	// Get the new user's name from the database for the email
      $q = "SELECT name FROM users WHERE id='$userid'";
      $r = mysqli_query ($dbc, $q);
      $row = mysqli_fetch_array ($r, MYSQLI_NUM);
      $userName = $row[0];

			// Send the Partner account change email
			$canned_email = "partner_site_added"; // Slug from the "pantry" table to select the canned email
			$payload_content = "<p>This new Partner App $new_papp_nickname has been added with a an App Key from the Developer Center. Once you use that App Key to connect your badAd.one account to a third party app, you will see this new App Project listed in the Partner Center.</p><p>For security, there is no way to view that App Key again from our website. If you do not uses the App Key to make the connection within a few days, the App Key will automatically be deleted. If you wait too long or if you lose the key, you can add other App Projects this same way anytime.</p>";
			include ('./includes/confirm_partner_change.inc.php');

    	// Print a message and wrap up
    	echo "<h3 class=\"note_green\">Partner App Key added!</h3><br /><p>A new Partner App $new_papp_nickname has been added as a partner App Project.</p>
      <p class=\"app_key\"><b>You will never be able to see this key again!</b> Your Partner App Key is: <pre class=\"app_key\">$papp_key</pre></p><p>Copy and paste this App Key into the form in the third party website or app where you intend to use it. Once connected, you will see the App Project listed in the Partner Center.</p>";

      // Breadcrumb
      echo "<h3 class=\"note_blue\">&larr; I'm finished, back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></h3>";

      // Include the HTML footer
      include ('./includes/footer.html');
      exit();

    } else {
      sql_error($q, 'srv_dbc', "sqle_91");
    }
  } // New App Project form approved



// New Dev Project submission
} elseif ((isset($_POST['dapp_domain'])) && (isset($_POST['dapp_description'])) && (isset($_POST['dapp_callback']))) {
	$dapp_domain = $_POST['dapp_domain'];

	// Validate domain
	$dapp_domain = str_replace('www.','',$dapp_domain);
	function is_valid_domain($domain_name) {
	  return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
	  && preg_match("/^.{1,253}$/", $domain_name) //overall length check
	  && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)); //length of each label
	}
	if (!is_valid_domain($dapp_domain)) {
	  $error_dapp_domain = "<span class=\"note_red\">The domain must be a normal domain or subdomain, like \"example.com\" or \"subdomain.example.com\"</span>";
	} else {
		$new_dapp_domain_sql = mysqli_real_escape_string ($srv_dbc, $dapp_domain);
	}

	// Validate App Name
	$dapp_name = $_POST['dapp_name'];
	function is_valid_app_name($validating_name) {
		return (preg_match ('/^[A-Z0-9 \'\/&,-]{0,80}$/i', $validating_name));
	}
	if (!is_valid_app_name($dapp_name)) {
		$error_dapp_name = "<span class=\"note_red\">The App Name may only use letters and numbers and - \' / , &</span>";
	} else {
		$new_dapp_name_sql = mysqli_real_escape_string ($srv_dbc, $dapp_name);
	}

	// Validate description
	if ((isset($_POST['dapp_description'])) && (($_POST['dapp_description'] != ""))) {
		function is_valid_app_description($validating_description) {
			return (preg_match ('/^[A-Z0-9 \'\/&,-]{0,80}$/i', $validating_description));
		}
		$dapp_description = $_POST['dapp_description'];
		$dapp_description_pretty = $dapp_description;
		if (!is_valid_app_description($dapp_description)) {
			$error_dapp_description = "<span class=\"note_red\">The Description may only use letters and numbers and - \' / , &</span>";
		}
	} else {
		$dapp_description = "";
		$dapp_description_pretty = "(none)";
	}
	$new_dapp_description_sql = mysqli_real_escape_string ($srv_dbc, $dapp_description);

	// Validate callback URL
	$dapp_callback = $_POST['dapp_callback'];
	if ((filter_var($dapp_callback,FILTER_VALIDATE_URL)) && (substr($dapp_callback, 0, 8) === "https://")) {
		$new_dapp_callback_sql = mysqli_real_escape_string ($srv_dbc, $dapp_callback);
	} else {
		$error_dapp_callback = "<span class=\"note_red\">The callback URL must be real and begin with \"https://\"</span>";
	}

	// Make sure the callback contains the domain
	//if ((strpos($dapp_callback, "https://$dapp_domain") === 0) || (strpos($dapp_callback, "https://www.$dapp_domain") === 0)) // too simple, remove if everything works
		if ( (strpos(parse_url($dapp_callback, PHP_URL_HOST), $dapp_domain) !== false) &&
		     (substr($dapp_callback, 0, 8) === "https://") ) {
		$callback_domain_ok = true;
	} else {
		$error_domain_callback = "<span class=\"note_red\">The callback URL must begin with <code class=\"inline\">https://</code> and contain the domain.</span>";
	}

	// Custom callback?
	if ((isset($_POST['custom_callback'])) && ($_POST['custom_callback'] == 'yes')) {
		$custom_callback = 1;
	} else {
		$custom_callback = 0;
	}

 // No errors, create Dev App
if ((isset($new_dapp_domain_sql))
&& (isset($new_dapp_name_sql))
&& (isset($new_dapp_callback_sql))
&& (!isset($error_dapp_name))
&& (!isset($error_dapp_description))
&& (!isset($error_dapp_domain))
&& (!isset($error_dapp_callback))
&& (!isset($error_domain_callback))) {

	// Generate the ridiculously long random string
	require_once ('./includes/string_functions.inc.php');

	// Create the test_pub_key
	$dev_test_pub_key = longString(64);
	$dev_test_pub_key = "test_pub_$dev_test_pub_key";

	// Dup check
	$q = "SELECT test_pub_key FROM devkeys WHERE binary test_pub_key='$dev_test_pub_key'"; // "binary" makes sure case and characters are exact
	$row = mysqli_query ($srv_dbc, $q);
	// while ($dup = mysqli_fetch_array($row)) {
	// 	$dev_test_pub_key = longString(64);
	// 	$dev_test_pub_key = "test_pub_$dev_test_pub_key";
	// }
	while (mysqli_num_rows($row) != 0) {
		$dev_test_pub_key = longString(64);
		$dev_test_pub_key = "test_pub_$dev_test_pub_key";
		// Check again
		$q = "SELECT test_pub_key FROM devkeys WHERE binary test_pub_key='$dev_test_pub_key'"; // "binary" makes sure case and characters are exact
		$row = mysqli_query ($srv_dbc, $q);
		if (mysqli_num_rows($row) == 0) {
			break;
		}
	}

	// Create the test_sec_key
	$dev_test_sec_key = longString(64);
	$dev_test_sec_key = "test_sec_$dev_test_sec_key";

	// Dup check
	$q = "SELECT test_sec_key FROM devkeys WHERE binary test_sec_key='$dev_test_sec_key'"; // "binary" makes sure case and characters are exact
	$row = mysqli_query ($srv_dbc, $q);
	// while ($dup = mysqli_fetch_array($row)) {
	// 	$dev_test_sec_key = longString(64);
	// 	$dev_test_sec_key = "test_sec_$dev_test_sec_key";
	// }
	while (mysqli_num_rows($row) != 0) {
		$dev_test_sec_key = longString(64);
		$dev_test_sec_key = "test_sec_$dev_test_sec_key";
		// Check again
		$q = "SELECT test_sec_key FROM devkeys WHERE binary test_sec_key='$dev_test_sec_key'"; // "binary" makes sure case and characters are exact
		$row = mysqli_query ($srv_dbc, $q);
		if (mysqli_num_rows($row) == 0) {
			break;
		}
	}

	// Create the live_pub_key
	$dev_live_pub_key = longString(64);
	$dev_live_pub_key = "live_pub_$dev_live_pub_key";

	// Dup check
	$q = "SELECT live_pub_key FROM devkeys WHERE binary live_pub_key='$dev_live_pub_key'"; // "binary" makes sure case and characters are exact
	$row = mysqli_query ($srv_dbc, $q);
	// while ($dup = mysqli_fetch_array($row)) {
	// 	$dev_live_pub_key = longString(64);
	// 	$dev_live_pub_key = "live_pub_$dev_live_pub_key";
	// }
	while (mysqli_num_rows($row) != 0) {
		$dev_live_pub_key = longString(64);
		$dev_live_pub_key = "live_pub_$dev_live_pub_key";
		// Check again
		$q = "SELECT live_pub_key FROM devkeys WHERE binary live_pub_key='$dev_live_pub_key'"; // "binary" makes sure case and characters are exact
		$row = mysqli_query ($srv_dbc, $q);
		if (mysqli_num_rows($row) == 0) {
			break;
		}
	}

	// Create the live_sec_key
	$dev_live_sec_key = longString(64);
	$dev_live_sec_key = "live_sec_$dev_live_sec_key";

	// Dup check
	$q = "SELECT live_sec_key FROM devkeys WHERE binary live_sec_key='$dev_live_sec_key'"; // "binary" makes sure case and characters are exact
	$row = mysqli_query ($srv_dbc, $q);
	// while ($dup = mysqli_fetch_array($row)) {
	// 	$dev_live_sec_key = longString(64);
	// 	$dev_live_sec_key = "live_sec_$dev_live_sec_key";
	// }
	while (mysqli_num_rows($row) != 0) {
		$dev_live_sec_key = longString(64);
		$dev_live_sec_key = "live_sec_$dev_live_sec_key";
		// Check again
		$q = "SELECT live_sec_key FROM devkeys WHERE binary live_sec_key='$dev_live_sec_key'"; // "binary" makes sure case and characters are exact
		$row = mysqli_query ($srv_dbc, $q);
		if (mysqli_num_rows($row) == 0) {
			break;
		}
	}

	// Add it to the database
	$q = "INSERT INTO devkeys (user_id, domain, name, description, callback, test_pub_key, test_sec_key, live_pub_key, live_sec_key, use_custom_callback)
	VALUES ('$userid', '$new_dapp_domain_sql', '$new_dapp_name_sql', '$new_dapp_description_sql', '$new_dapp_callback_sql', '$dev_test_pub_key', '$dev_test_sec_key', '$dev_live_pub_key', '$dev_live_sec_key', $custom_callback)";
	$r = mysqli_query ($srv_dbc, $q);
	if (mysqli_affected_rows($srv_dbc) != 1) {
		sql_error($q, 'srv_dbc', "sqle_95");
	} else {
		$_SESSION['new_dev_success'] = $dapp_name;

		// Send the confirmation email
		$canned_email = "confirm_partner_dev_change"; // Slug from the "pantry" table to select the canned email
		$payload_content = "<p>You added a new Dev App in the Developer Center.</p>
		<p>Name: <b>$dapp_name</b><br />
		Domain: <b>$dapp_domain</b><br />
		Description: <b>$dapp_description_pretty</b><br />
		Callback URL <i>(native)</i>: <b>$dapp_callback</b></p>"; // Start custom content to be appended by the canned email body content.
		include ('./includes/confirm_partner_dev_change.inc.php');
	}
} // No errors
} // End Dev Project form submission

// Start building the page

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></p>";

// Action messages?
if (isset($_SESSION['new_dev_key'])) {
  $action_message = $_SESSION['new_dev_key'];
  echo "<p class=\"note_green\"><b>New key:</b> $action_message... You should receive an email for final confirmation.</p>";
  unset($_SESSION['new_dev_key']);
} elseif (isset($_SESSION['del_dev_key'])) {
  $action_message = $_SESSION['del_dev_key'];
  echo "<p class=\"note_red\">$action_message has been deleted!</p>";
  unset($_SESSION['del_dev_key']);

// No Partner App Nickname errors
} elseif (!isset($error_app_nickname)) {

	// App del revived message?
	if (isset($_SESSION['rev_dev_app'])) {
	  $action_message = $_SESSION['rev_dev_app'];
	  echo "<p class=\"note_green\">$action_message is revived and the <b>delete request has been cancelled</b>!</p>";
	  unset($_SESSION['rev_dev_app']);
	}

	// Page for activated partners
	echo "<h3>Developer Center</h3>";

	// No Dev Project table if new Dev App Project errors
	if ((!isset($error_dapp_name))
	&& (!isset($error_dapp_description))
	&& (!isset($error_dapp_domain))
	&& (!isset($error_dapp_callback))
	&& (!isset($error_domain_callback))) {
		// Developer table
		echo '<h4>Develper keys</h4>';

		// Get the Partner's sites' info to populate the profile
		$q = "SELECT id, domain, name, description, callback, status, date_created, date_modified, test_pub_key, test_sec_key, live_pub_key, live_sec_key, old_pub_key, old_sec_key FROM devkeys WHERE user_id='$userid' ORDER BY name, domain, description, id";
		$r = mysqli_query ($srv_dbc, $q);
		$rows = mysqli_num_rows($r);
		if ($rows == 0) {
			echo "<p>No Developer App Projects yet!</p>";
		} else {

			// New Dev App success message
			if (isset($_SESSION['new_dev_success'])) {
				$new_dev_success = $_SESSION['new_dev_success'];
				echo "<p class=\"note_green\">Success! New project <b>$new_dev_success</b> created!</p>";
				unset($_SESSION['new_dev_success']);
			} elseif (isset($_SESSION['new_key_success'])) {
				$new_key_success = $_SESSION['new_key_success'];
				echo "<p class=\"note_green\">Success! The project <b>$new_key_success</b> has new keys! Install them soon, before the old keys stop working.</p>";
				unset($_SESSION['new_key_success']);
			}

			// Start the table
			echo "<div class\"devkeytable\">\n<table class=\"sitestable\">\n";
			echo "<th>Dev App</th><th>Keys</th><th>Status</th>";
			while ($row = mysqli_fetch_array($r)) {
		    $dev_app_id = "$row[0]";
		    $dev_domain = "$row[1]";
		    $dev_name = "$row[2]";
		    $dev_description = "$row[3]";
		    $dev_callback = "$row[4]";
		    $dev_status = "$row[5]";
		    $dev_date_created = "$row[6]";
		    $dev_date_modified = "$row[7]";
		    $dev_test_pub_key = "$row[8]";
		    $dev_test_sec_key = "$row[9]";
		    $dev_live_pub_key = "$row[10]";
		    $dev_live_sec_key = "$row[11]";
		    $dev_old_pub_key = "$row[12]";
		    $dev_old_sec_key = "$row[13]";

				// Iterate each Project site into the table
		    // Dev App
				echo '<tr><td align="left" rowspan="2"><p><b>'.$dev_name.'</b><br />'.$dev_domain.'<br />';
				if ($dev_description != NULL) {echo "<i class=\"note_gray\">$dev_description</i>";}
				echo "</p>";
				set_switch("edit", "Edit the Description and Callback URL for $dev_name", "partner_dev_edit_app.php", "s", $dev_app_id, "set_gray");
				echo '</td>';

				// Keys & Status
				echo '<td align="center" class="nobottomborder">';
				if ($dev_status == 'live') {
					echo '<div id="dev_key_mode_div_'.$dev_app_id.'">'; // AJAX response div

					echo '<pre class="dev_code callback"><b>Callback URL:</b> '.$dev_callback.'</pre>';

					echo "<pre class=\"dev_key live_key\"><b>Public Key:</b> $dev_live_pub_key<br /><b>Secret Key:</b> <div class=\"sec_hide\" id=\"sec_$dev_app_id\">$dev_live_sec_key</div></pre>";
					if (($dev_old_pub_key != "") || ($dev_old_pub_key != NULL)) {echo "<pre class=\"dev_key note_red\">You recently made new keys. The old <b>test</b> keys are dead. The old <b>live</b> keys should continue to work for <b>only a short time</b>.</pre>";}
					echo "</td>";
					echo '<td class="nobottomborder"><br />';
					set_switch("live", "Switch to test mode", "partner_dev_app_status_test.act.php", "s", $dev_app_id, "set_green");
					// <input type="submit" title="Switch to test mode" value="live" class="set_green">
					// AJAX form
					// DEV: need to add
						// ID to tr
						// Revamp table structure so AJAX reponse can go inside one, single td
					// <td><br>
					// <form id="dev_key_mode_form_'.$dev_app_id.'">
					// 	<input type="hidden" name="s" value="'.$dev_app_id.'">
					// 	<button type="button" class="formbutton_green" title="Switch to test mode" onclick="ajaxFormData("dev_key_mode_form_'.$dev_app_id.'", "partner_dev_app_status_test.ajax.php", "dev_key_mode_div_'.$site_id.'");">live</button>
					// </form></td>

					// Show/hide button
					echo '</tr><tr><td>
					<button class="formbutton_gray" onclick="showSec$dev_app_id()">Show/hide secret key</button>
					<script> function showSec$dev_app_id() { var element = document.getElementById("sec_$dev_app_id"); element.classList.toggle("sec_hide"); element.classList.toggle("sec_show"); }; </script>
					</td><td></td></tr>
					</div>'; // AJAX response div

				} elseif ($dev_status == 'test') {
					echo '<div id="dev_key_mode_div_'.$dev_app_id.'">'; // AJAX response div

					echo '<pre class="dev_code callback"><b>Callback URL:</b> '.$dev_callback.'</pre>';

					echo "<pre class=\"dev_key test_key\"><b>Public Key:</b> $dev_test_pub_key<br /><b>Secret Key:</b> <div class=\"sec_hide\" id=\"sec_$dev_app_id\">$dev_test_sec_key</div></pre>";
					if (($dev_old_pub_key != "") || ($dev_old_pub_key != NULL)) {echo "<pre class=\"dev_key note_yellow\">You recently made new keys. The old <b>test</b> keys are dead. The old <b>live</b> keys should continue to work for <b>only a short time</b>.</pre>";}
					echo "</td>";
					echo '<td class="nobottomborder"><br />';
					set_switch("test", "Switch to live mode", "partner_dev_app_status_live.act.php", "s", $dev_app_id, "set_yellow");
					// <input type="submit" title="Switch to live mode" value="test" class="set_yellow">
					// AJAX form
					// DEV: need to add
						// ID to tr
						// Revamp table structure so AJAX reponse can go inside one, single td
					// <td><br>
					// <form id="dev_key_mode_form_'.$dev_app_id.'">
					// 	<input type="hidden" name="s" value="'.$dev_app_id.'">
					// 	<button type="button" class="formbutton_yellow" title="Switch to live mode" onclick="ajaxFormData("dev_key_mode_form_'.$dev_app_id.'", "partner_dev_app_status_live.ajax.php", "dev_key_mode_div_'.$site_id.'");">test</button>
					// </form></td>

					// Show/hide button
					echo '</tr><tr><td>
					<button class="formbutton_gray" onclick="showSec$dev_app_id()">Show/hide secret key</button>
					<script> function showSec$dev_app_id() { var element = document.getElementById("sec_$dev_app_id"); element.classList.toggle("sec_hide"); element.classList.toggle("sec_show"); }; </script>
					</td><td></td></tr>
					</div>'; // AJAX response div

			  } elseif ($dev_status == 'deleted') {
			    echo '<br />
					<p class="note_gray" style="text-align:left;"><b>Pending delete confirmation from you, check your email inbox</b></p></td>
					<td class="nobottomborder">
					<br />';
			    set_switch("cancel delete", "I changed my mind", "partner_dev_app_predel_revive.act.php", "s", $dev_app_id, "set_yellow");
					echo '</tr><tr><td></td><td></td></tr>';
				}
				echo '</td></tr>';

			}
			// Finish the table
			echo "</table></div><br />";

			// Close the table section
			echo "<br /><hr /><br />";

		} // Have sites check
	} // New Partner App error check

	// Form for adding a developer Project
	echo "<h4>New Developer App for API:</h4>";

	// Any domain-callback conflict error
	if (isset($error_domain_callback)) {echo "<br /><p>$error_domain_callback</p>";}

	echo "
	<form id=\"add_partner_site\" class=\"new_partner_site_form\" action=\"partner_dev.php\" method=\"post\" accept-charset=\"utf-8\">
	<input type=\"hidden\" name=\"partner_dev\" value=\"$userid\" />
	<br />
	<p><input type=\"checkbox\" name=\"ready_copy_paste\" value=\"true\" required /> <b>I understand and agree:</b> I will <b>not</b> use this app to embed ads in push notifications, and my app is no way part of an advertising service that uses push-notifications containing advertised content.</p>
	<p class=\"note_gray\"><i>You are allowed to use the same domain multiple times. And, though you can use the same Callback URL multiple times, the only Callback URL that will work is the one with the Public Key listed in the &lt;head&gt;.</i></p>
	<p class=\"note_gray\"><i>Your App name and Domain will be shown to users when they authorize connection to your App 1. at the initial handshake (if using login to connect), 2. in their Partner Project list, 3. in any list of connections you display to your clients. The Description is only for your reference. The Domain <b>must</b> be part of your Callback URL, (mydomain.com for https://call.mydomain.com/callback works).</p>
	<p class=\"note_gray\">Your App name and Domain <b>cannot</b> be changed later, the Description and Callback URL can be changed.</i></p>
	</p><p";
	if (isset($error_dapp_name)) {echo ' class="error"';}
	echo ">
	<label for=\"dapp_name\"><b>Your App name:</b> <input";
	if (isset($error_dapp_name)) {echo ' class="error"';}
	echo " type=\"text\" name=\"dapp_name\" id=\"dapp_name\" size=\"32\" placeholder=\"Social Media Service, My Website, My App etc\" required ";
	if (isset($dapp_name)) {echo " value=\"$dapp_name\"";}
	echo "/></label>";
	if (isset($error_dapp_name)) {echo " $error_dapp_name";}
	echo "</p><p";
	if (isset($error_dapp_description)) {echo ' class="error"';}
	echo ">
	<label for=\"dapp_description\"><i>Description:</i> <input";
	if (isset($error_dapp_description)) {echo ' class="error"';}
	echo " type=\"text\" name=\"dapp_description\" id=\"dapp_description\" size=\"32\" placeholder=\"For your reference (optional)\" ";
	if (isset($dapp_description)) {echo " value=\"$dapp_description\"";}
	echo "/> <i class=\"note_gray\">(can change)</i></label>";
	if (isset($error_dapp_description)) {echo " $error_dapp_description";}
	echo "</p><p";
	if ((isset($error_dapp_domain)) || (isset($error_domain_callback))) {echo ' class="error"';}
	echo ">
	<label for=\"dapp_domain\"><b>Domain:</b> <input";
	if ((isset($error_dapp_domain)) || (isset($error_domain_callback))) {echo ' class="error"';}
	echo " type=\"text\" name=\"dapp_domain\" id=\"dapp_domain\" size=\"32\" placeholder=\"example.com OR sub.example.com etc\" required ";
	if (isset($dapp_domain)) {echo " value=\"$dapp_domain\"";}
	echo "/></label>";
	if (isset($error_dapp_domain)) {echo " $error_dapp_domain";}
	echo "</p><p";
	if ((isset($error_dapp_callback)) || (isset($error_domain_callback))) {echo ' class="error"';}
	echo ">
	<label for=\"dapp_callback\"><i>Callback URL:</i> <i class=\"note_gray\">(native)</i> <input";
	if ((isset($error_dapp_callback)) || (isset($error_domain_callback))) {echo ' class="error"';}
	echo " type=\"url\" name=\"dapp_callback\" id=\"dapp_callback\" size=\"40\" placeholder=\"https://example.com/where/handshake/happens.php\" required ";
	if (isset($dapp_callback)) {echo " value=\"$dapp_callback\"";}
	echo "/> <i class=\"note_gray\">(can change)</i></label>";
	if (isset($error_dapp_callback)) {echo " $error_dapp_callback";}
	echo "</p>
	<p><label for=\"custom_callback\"><input type=checkbox name=\"custom_callback\" name=\"custom_callback\" value=\"yes\" /> <b>Use custom callback? (likely no)</b> <i class=\"note_gray\">(This requires the <code class=\"inline\">custom_callback</code> argument in the API, only for complex use, leave unchecked if in doubt.)</i></label></p>
	<input type=\"submit\" value=\"Create Developer Project\" class=\"formbutton\" />
	</form><br /><hr /><br /><br />";

} // End Partner App Nickname error check

// Dev Help
echo "<p>For help with the Developer's API, go to <a title=\"Developer Help\" href=\"help_dev.php\">Developer Help &rarr;</a></p>";

// Spacer & notice
echo "<br /><hr class=\"note_blue\" /><br />";
echo "<h3 class=\"note_blue\">Partner Area</h3>";

// Form for adding a new App Project
echo "<br /><hr class=\"note_blue\" /><h4 class=\"note_blue\">New Partner App Key:</h4>";

echo "
<form id=\"add_partner_site\" class=\"new_partner_site_form\" action=\"partner_dev.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"partner_dev\" value=\"$userid\" /><br />
<p class=\"note_blue\"><input type=\"checkbox\" name=\"ready_copy_paste\" value=\"true\" required /> <b>I'm ready:</b> When I click \"Create Partner App Key\", I will be shown a code which I will only see once. I am ready to copy and paste that code into the app or website it will be used for.</p>
<input type=\"text\" name=\"papp_name\" value=\"w\" hidden required />";
echo '<p class="';
if (isset($error_app_nickname)) {echo 'error';} else {echo 'note_blue';}
echo '">Nickname: <input';
if (isset($error_app_nickname)) {echo ' class="error"';}
echo ' type="text" name="papp_nickname" size="32" placeholder="Nickname (optional, can change later)" />';
if (isset($error_app_nickname)) {echo " $error_app_nickname";}
echo "</p>
<input type=\"submit\" value=\"Create Partner App Key\" class=\"formbutton_blue\" />
</form>
<br /><hr class=\"note_blue\" />";

// Danger area
echo "<br /><h3 class=\"note_red\">Danger Zone</h3><br />";
// Delete a Project
set_switch("Delete a Dev App...", "Go to the Delete Dev App area", "partner_dev_del_app.php", "del_develeoper_app_page", $userid, "set_red");
echo "<br /><hr />";


// Include the HTML footer
include ('./includes/footer.html');

?>
