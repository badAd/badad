<?php
// This processes the _POST request from a Developer's API to connect a Dev Project to a Parner's App Project

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Login check
include_once ('includes/login_check.inc.php');

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// API or Logged-in form submission
// Hacker check
if ((isset($_POST['dev_key'])) && (preg_match ('/[^a-zA-Z0-9_]$/i', $_POST['dev_key']))) {$IP = get_ip_addr(); script_kiddy('sk_a6', '_POST dev_key', $_POST['dev_key'], $IP);}
// Legit form submission to get here
if ((isset($_POST['partner_app_key']) && (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['partner_app_key'])))
&& ((isset($_SESSION['dev_pub_key'])) || ((isset($_POST['dev_key'])) && (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['dev_key']))))) {

  // Set our variables
  $papp_key = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['partner_app_key']);
  $esc_papp_key = mysqli_real_escape_string($srv_dbc, $papp_key);
  $pserial = "none";

  // Check custom callback
  if ((isset($_POST['custom_callback'])) && (filter_var($_POST['custom_callback'], FILTER_VALIDATE_URL))) {
    $_SESSION['custom_callback'] = $_POST['custom_callback'];
  }

  // API submission
  if ((isset($_POST['dev_key'])) && (!isset($_POST['papp_nickname']))) {

  // Key-connect API, add without nickname check
    $dev_key = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['dev_key']);
    $qd="SELECT id, domain, name, test_pub_key, live_pub_key, old_pub_key, callback, status, test_sec_key, live_sec_key, old_sec_key, use_custom_callback FROM devkeys WHERE binary live_sec_key='$dev_key' OR binary old_sec_key='$dev_key' OR binary test_sec_key='$dev_key'";
    $rd = mysqli_query ($srv_dbc, $qd);
    if (mysqli_num_rows($rd) == 1) {
      // Assign the values
      $row = mysqli_fetch_array($rd);
      $dev_id = "$row[0]";
      $dev_domain = "$row[1]";
      $dev_name = "$row[2]";
      $test_pub_key = "$row[3]";
      $live_pub_key = "$row[4]";
      $old_pub_key = "$row[5]";
      $dev_callback = "$row[6]";
      $dev_status = "$row[7]";
      $test_sec_key = "$row[8]";
      $live_sec_key = "$row[9]";
      $old_sec_key = "$row[10]";
      $allow_custom_callback = "$row[11]";

      // Custom callback?
      if ( (isset($_SESSION['custom_callback'])) && ($allow_custom_callback == true) &&
           (strpos(parse_url($_SESSION['custom_callback'], PHP_URL_HOST), $dev_domain) !== false) &&
           (substr($_SESSION['custom_callback'], 0, 8) === "https://") ) {
        $dev_callback = $_SESSION['custom_callback'];
      }

      // Set our keys
      if (($dev_status == "test") && ($dev_key == $test_sec_key)) {
        $dev_pub_key = $test_pub_key;
        $test_key_mode = true;
        $_SESSION['test_key_mode'] = $test_key_mode;
      } elseif (($dev_status == "live") && ($dev_key == $live_sec_key)) {
        $dev_pub_key = $live_pub_key;
      } elseif (($dev_status == "live") && ($dev_key == $old_sec_key)) {
        $dev_pub_key = $old_pub_key;
      } elseif ($dev_status == "deleted") {

        // Unset any _SESSION variables
        if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
        if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
        if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
        if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
        if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

        echo "These keys were deleted. Use current keys.";
        exit();
      } else {

        // Unset any _SESSION variables
        if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
        if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
        if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
        if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
        if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

        echo "That does not exist. Make sure keys are correct and live and test modes agree.";
        exit();
      }

    } else {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "That does not exist. Make sure keys are correct and live and test modes agree.";
      exit();
    }

    // Test mode
    if ((isset($test_key_mode)) && ($test_key_mode = true)) {

      // Make sure the callback URL exists
      $headers = @get_headers($dev_callback);
      if ((!$headers) || (strpos($headers[0], '404'))) {

        // Unset any _SESSION variables
        if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
        if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
        if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
        if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
        if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

        echo "Callback URL does not exist or the page is broken.";
        exit();
      }

      // Ping the callback URL to see if the key in the header matches the $dev_pub_key
      $meta_check = get_meta_tags($dev_callback);
      if ((!isset($meta_check['badad_api_dev_key']))
      || ($meta_check['badad_api_dev_key'] !== $dev_pub_key)) {

        // Unset any _SESSION variables
        if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
        if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
        if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
        if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
        if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

        echo "Callback failed.";
        exit();
      }

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      // Set the test $call_key
      $call_key = 'call_key_test0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0';
      $refcredSLUG = "0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0";

      // Return the _POST to the Developer's App
      echo "
      <form id=\"jsGoForm\" action=\"$dev_callback\" method=\"post\">
        <input type=\"hidden\" name=\"badad_connect_response\" value=\"ture\">
        <input type=\"hidden\" name=\"partner_refcred\" value=\"$refcredSLUG\" /><br />
        <input type=\"hidden\" name=\"partner_call_key\" value=\"$call_key\">
        <input type=\"hidden\" name=\"partner_app_key\" value=\"$papp_key\">
      </form>
      <script type=\"text/javascript\">
          document.getElementById('jsGoForm').submit();
      </script>";
      exit(); // Quit the script

    }

    $qu="SELECT user_id, nickname, dev_authorized_id, badadref_no FROM partnersites WHERE papp_key='$esc_papp_key'";
    $ru = mysqli_query ($srv_dbc, $qu);
    if (mysqli_num_rows($ru) == 1) {
      // Assign the values
      $row = mysqli_fetch_array($ru);
      $userid = "$row[0]";
      $new_papp_nickname_sql = "$row[1]";
      $temp_dev_id = "$row[2]";
      $rserial = "$row[3]";
      $refcredSLUG = "$rserial";
    } else {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "That does not exist. Make sure keys are correct and live and test modes agree.";
      exit();
    }

  // Logged-in form submission: nickname submitted, we need to do checks
  } elseif ((isset($_SESSION['dev_pub_key'])) && (isset($_POST['partner_refcred'])) && (isset($_POST['papp_nickname']))) {

    if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['partner_refcred'])) {$IP = get_ip_addr(); script_kiddy('sk_a7', '_POST partner_refcred', $_POST['partner_refcred'], $IP);}

    // Login check
    include_once ('includes/login_check.inc.php');
    // Get $userid from login, which is required
    $userid = $_SESSION['user_id'];

    // Get the Credit-referral
    $refcredSLUG = preg_replace("/[^A-Za-z0-9]/","", $_POST['partner_refcred']);

    // No form errors, get the Dev info
    $dev_pub_key = $_SESSION['dev_pub_key'];
    $qd="SELECT id, domain, callback, test_pub_key, test_sec_key, status FROM devkeys WHERE binary live_pub_key='$dev_pub_key' OR binary old_pub_key='$dev_pub_key' OR binary test_sec_key='$dev_pub_key'";
    $rd = mysqli_query ($srv_dbc, $qd);
    if (mysqli_num_rows($rd) == 1) {
      // Assign the values
      $row = mysqli_fetch_array($rd);
      $dev_id = "$row[0]";
      $dev_domain = "$row[1]";
      $dev_callback = "$row[2]";
      $test_pub_key = "$row[3]";
      $test_sec_key = "$row[4]";
      $dev_status = "$row[5]";
      $temp_dev_id = $dev_id;
    } else {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "That does not exist. Make sure keys are correct and live and test modes agree.";
      exit();
    }

    // Validate the nickname
    if (isset($_POST['papp_nickname'])) {
      $papp_nickname = $_POST['papp_nickname'];
      if (!preg_match ('/^[A-Z0-9 \'\/&,-]{0,80}$/i', $papp_nickname)) {
        $error_app_nickname = "<span class=\"note_red\">The nickname may be only letters and numbers and - \' / , &</span>";
      } else {
        $new_papp_nickname = $papp_nickname;
        // Set SQL value
        $new_papp_nickname_sql = mysqli_real_escape_string ($srv_dbc, $new_papp_nickname);
      }
    } else { // Not entered, leave it empty
      $new_papp_nickname_sql = "";
    }

    // Send the Partner account change email
    $canned_email = "partner_site_added"; // Slug from the "pantry" table to select the canned email
    $payload_content = "<p>This new Partner App $new_papp_nickname has been added through a third-party app with you logging in to authorize. You should now see this new App Project listed in the Partner Center.</p>";
    include ('./includes/confirm_partner_change.inc.php');

  } // End API/Form if

  // Error check
  if (!isset($error_app_nickname)) { // End form error check


    // Check the callback to be a URL
    if ((filter_var($dev_callback,FILTER_VALIDATE_URL)) && (substr($dev_callback, 0, 8) === "https://")) {
      $callback_url_ok = true;
    } else {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      header("Location: https://badad.one");
      exit(); // Quit the script
    }

    // Make sure the callback URL exists
    $headers = @get_headers($dev_callback);
    if ((!$headers) || (strpos($headers[0], '404'))) {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "Callback URL does not exist or the page is broken.";
      exit();
    }

    // Ping the callback URL to see if the key in the header matches the $dev_pub_key
    $meta_check = get_meta_tags($dev_callback);
    if ((!isset($meta_check['badad_api_dev_key']))
    || ($meta_check['badad_api_dev_key'] !== $dev_pub_key)) {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "Callback failed.";
      exit();
    }

  // Create the new string
    // Generate the ridiculously long random string
    require_once ('./includes/string_functions.inc.php');

    // Create the badadref_no link
    $call_key = longString(64);
    $call_key = "call_key_$call_key";

    // Dup check
    $q = "SELECT papp_key FROM partnersites WHERE binary call_key='$call_key'"; // "binary" makes sure case and characters are exact
    $row = mysqli_query ($srv_dbc, $q);
    // while ($dup = mysqli_fetch_array($row)) {
    //   $call_key = longString(64);
    //   $call_key = "call_key_$call_key";
    // }
    while (mysqli_num_rows($row) != 0) {
      $call_key = longString(64);
      $call_key = "call_key_$call_key";
      // Check again
      $q = "SELECT papp_key FROM partnersites WHERE binary call_key='$call_key'"; // "binary" makes sure case and characters are exact
      $row = mysqli_query ($srv_dbc, $q);
      if (mysqli_num_rows($row) == 0) {
        break;
      }
    }

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

    // Set SQL value
    $pappdomain_sql = mysqli_real_escape_string ($srv_dbc, $dev_domain);
    $dev_callback_sql = mysqli_real_escape_string ($srv_dbc, $dev_callback);
    // Update the partnersites entry
    $qp = "UPDATE partnersites
    SET serial_no='$pserial', domain='$pappdomain_sql', nickname='$new_papp_nickname_sql', global_subcat_ids='$global_cat_id_list', call_key='$call_key', papp_key='connected', dev_authorized_id='$dev_id', connected_callback='$dev_callback_sql'
    WHERE user_id='$userid' AND binary papp_key='$esc_papp_key' AND serial_no='w' AND domain='w' AND dev_authorized_id='$temp_dev_id' AND type='app'";
    $rp = mysqli_query ($srv_dbc, $qp);
    if (mysqli_affected_rows($srv_dbc) != 1) {
      sql_error($qp, 'srv_dbc', "sqle_94");
    }

    // Unset the _SESSION variables
    if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
    if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
    if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
    if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
    if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

    // Return the _POST to the Developer's App
    echo "
    <form id=\"jsGoForm\" action=\"$dev_callback\" method=\"post\">
      <input type=\"hidden\" name=\"badad_connect_response\" value=\"ture\">
      <input type=\"hidden\" name=\"partner_refcred\" value=\"$refcredSLUG\" /><br />
      <input type=\"hidden\" name=\"partner_call_key\" value=\"$call_key\">
      <input type=\"hidden\" name=\"partner_app_key\" value=\"$papp_key\">
    </form>
    <script type=\"text/javascript\">
        document.getElementById('jsGoForm').submit();
    </script>";
    exit(); // Quit the script

  } // End error check
// End form submission



// If sent here from Dev app
} elseif ((isset($_POST['dev_key'])) && (preg_match ('/[a-zA-Z0-9]$/i', $_POST['dev_key']))) {
  $dev_key = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['dev_key']);

  $qd="SELECT id, domain, name, test_pub_key, live_pub_key, old_pub_key, callback, status, test_sec_key, live_sec_key, old_sec_key, use_custom_callback FROM devkeys WHERE binary live_sec_key='$dev_key' OR binary old_sec_key='$dev_key' OR binary test_sec_key='$dev_key'";
  $rd = mysqli_query ($srv_dbc, $qd);
  if (mysqli_num_rows($rd) == 1) {
    // Assign the values
    $row = mysqli_fetch_array($rd);
    $dev_id = "$row[0]";
    $dev_domain = "$row[1]";
    $dev_name = "$row[2]";
    $test_pub_key = "$row[3]";
    $live_pub_key = "$row[4]";
    $old_pub_key = "$row[5]";
    $dev_callback = "$row[6]";
    $dev_status = "$row[7]";
    $test_sec_key = "$row[8]";
    $live_sec_key = "$row[9]";
    $old_sec_key = "$row[10]";
    $allow_custom_callback = "$row[11]";

    // Custom callback?
    if ( (isset($_SESSION['custom_callback'])) && ($allow_custom_callback == true) &&
         (strpos(parse_url($_SESSION['custom_callback'], PHP_URL_HOST), $dev_domain) !== false) &&
         (substr($_SESSION['custom_callback'], 0, 8) === "https://") ) {
      $dev_callback = $_SESSION['custom_callback'];
    } else {
      $dev_callback = $dev_callback;
    }

    // Set our keys
    if (($dev_status == "test") && ($dev_key == $test_sec_key)) {
      $dev_pub_key = $test_pub_key;
      $test_key_mode = true;
      $_SESSION['test_key_mode'] = $test_key_mode;
    } elseif (($dev_status == "live") && ($dev_key == $live_sec_key)) {
      $dev_pub_key = $live_pub_key;
    } elseif (($dev_status == "live") && ($dev_key == $old_sec_key)) {
      $dev_pub_key = $old_pub_key;
    } elseif ($dev_status == "deleted") {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "These keys were deleted. Use current keys.";
      exit();
    } else {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "That does not exist. Make sure keys are correct and live and test modes agree.";
      exit();
    }
  } else {

    // Unset any _SESSION variables
    if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
    if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
    if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
    if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
    if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

    echo "That does not exist. Make sure keys are correct and live and test modes agree.";
    exit();
  }

  $_SESSION['dev_pub_key'] = $dev_pub_key;

// How did we get here!? Get out!
} elseif ((!isset($_SESSION['dev_pub_key'])) && (!isset($error_app_nickname))) {

  // Unset any _SESSION variables
  if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
  if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
  if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
  if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
  if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

  header("Location: https://badad.one");
  exit(); // Quit the script
}

// If this isn't a re-try form because the dev_pub_key is not yet set
if ((!isset($_SESSION['dev_id']))
|| (!isset($_SESSION['dev_domain']))
|| (!isset($_SESSION['dev_name']))
|| (!isset($_SESSION['dev_pub_key']))
|| (!isset($_SESSION['dev_callback']))) {

  $qd="SELECT id, domain, name, test_pub_key, live_pub_key, old_pub_key, callback, status, test_sec_key, live_sec_key, old_sec_key FROM devkeys WHERE binary live_sec_key='$dev_key' OR binary old_sec_key='$dev_key' OR binary test_sec_key='$dev_key'";
  $rd = mysqli_query ($srv_dbc, $qd);
  if (mysqli_num_rows($rd) == 1) {
    // Assign the values
    $row = mysqli_fetch_array($rd);
    $dev_id = "$row[0]";
    $dev_domain = "$row[1]";
    $dev_name = "$row[2]";
    $test_pub_key = "$row[3]";
    $live_pub_key = "$row[4]";
    $old_pub_key = "$row[5]";
    $dev_callback = "$row[6]";
    $dev_status = "$row[7]";
    $test_sec_key = "$row[8]";
    $live_sec_key = "$row[9]";
    $old_sec_key = "$row[10]";

    if (($dev_status == "test") && ($dev_key == $test_sec_key)) {
      $dev_pub_key = $test_pub_key;
      $test_key_mode = true;
    } elseif (($dev_status == "live") && ($dev_key == $live_sec_key)) {
      $dev_pub_key = $live_pub_key;
    } elseif (($dev_status == "live") && ($dev_key == $old_sec_key)) {
      $dev_pub_key = $old_pub_key;
    } elseif ($dev_status == "deleted") {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "These keys were deleted. Use current keys.";
      exit();
    } else {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "That does not exist. Make sure keys are correct and live and test modes agree.";
      exit();
    }

    // Check the callback to be a URL
    if ((filter_var($dev_callback,FILTER_VALIDATE_URL)) && (substr($dev_callback, 0, 8) === "https://")) {
      $callback_url_ok = true;
    } else {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      header("Location: https://badad.one");
      exit(); // Quit the script
    }

    // Make sure the callback URL exists
    $headers = @get_headers($dev_callback);
    if ((!$headers) || (strpos($headers[0], '404'))) {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "Callback URL does not exist or the page is broken.";
      exit();
    }

    // Ping the callback URL to see if the key in the header matches the $dev_pub_key
    $meta_check = get_meta_tags($dev_callback);
    if ((!isset($meta_check['badad_api_dev_key']))
    || ($meta_check['badad_api_dev_key'] !== $dev_pub_key)) {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      echo "Callback failed.";
      exit();
    }

    // Test mode
    if ((isset($test_key_mode)) && ($test_key_mode = true)) {

      // Unset any _SESSION variables
      if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
      if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
      if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
      if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
      if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

      // Set the test $call_key
      $call_key = 'call_key_test0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0';
      $papp_key = 'app_key_test0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0';
      $refcredSLUG = "0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0";

      // Return the _POST to the Developer's App
      echo "
      <form id=\"jsGoForm\" action=\"$dev_callback\" method=\"post\">
        <input type=\"hidden\" name=\"badad_connect_response\" value=\"ture\">
        <input type=\"hidden\" name=\"partner_refcred\" value=\"$refcredSLUG\" /><br />
        <input type=\"hidden\" name=\"partner_call_key\" value=\"$call_key\">
        <input type=\"hidden\" name=\"partner_app_key\" value=\"$papp_key\">
      </form>
      <script type=\"text/javascript\">
          document.getElementById('jsGoForm').submit();
      </script>";
      exit(); // Quit the script

    }

    $_SESSION['dev_id'] = $dev_id;
    $_SESSION['dev_domain'] = $dev_domain;
    $_SESSION['dev_name'] = $dev_name;
    $_SESSION['dev_pub_key'] = $dev_pub_key;
    $_SESSION['dev_callback'] = $dev_callback;

  } else {

    // Unset any _SESSION variables
    if (isset($_SESSION['dev_id'])) {unset($_SESSION['dev_id']);}
    if (isset($_SESSION['dev_domain'])) {unset($_SESSION['dev_domain']);}
    if (isset($_SESSION['dev_name'])) {unset($_SESSION['dev_name']);}
    if (isset($_SESSION['dev_pub_key'])) {unset($_SESSION['dev_pub_key']);}
    if (isset($_SESSION['dev_callback'])) {unset($_SESSION['dev_callback']);}

    echo "That does not exist. Make sure keys are correct and live and test modes agree.";
    exit();
  }
} else { // _SESSION for dev already set
  $dev_id = $_SESSION['dev_id'];
  $dev_domain = $_SESSION['dev_domain'];
  $dev_name = $_SESSION['dev_name'];
  $dev_pub_key = $_SESSION['dev_pub_key'];
  $dev_callback = $_SESSION['dev_callback'];
}

// Build the form
// Login check
include_once ('includes/login_check.inc.php');

// Include the header file
$page_title = "Connect Partner App :: $siteTitle";
include ('./includes/header.html');

// Heading
echo "<h3>Creat an new Partner App Project to connect to:<br /><i><b>$dev_name</b> ($dev_domain)</i></h3>";

// Got here by login-to-create
if (!isset($_POST['partner_app_key'])) {
  // If the user isn't logged in, redirect them
  if (!isset($_SESSION['user_id'])) {
    echo "<h2>Login to continue</h2>";
  	include ('includes/login_form.inc.php');

    // Include the HTML footer
    include ('./includes/footer.html');
    exit();

  } else { // No need for login
    // Perp the variables and database
  	$userid = $_SESSION['user_id'];

    // Set the serials to none
    $pserial = "w";

    // Generate the ridiculously long random string
    require_once ('./includes/string_functions.inc.php');

    // Create the badadref_no link
    $papp_key = longString(64);
    $papp_key = "app_key_$papp_key";

    // Dup check
    $q = "SELECT papp_key FROM partnersites WHERE binary papp_key='$papp_key'";// "binary" makes sure case and characters are exact
    $row = mysqli_query ($srv_dbc, $q);
    // while ($dup = mysqli_fetch_array($row)) {
    //   $papp_key = longString(64);
    //   $papp_key = "app_key_$papp_key";
    // }

    while (mysqli_num_rows($row) != 0) {
      $papp_key = longString(64);
      $papp_key = "app_key_$papp_key";
      // Check again
      $q = "SELECT papp_key FROM partnersites WHERE binary papp_key='$papp_key'";// "binary" makes sure case and characters are exact
      $row = mysqli_query ($srv_dbc, $q);
      if (mysqli_num_rows($row) == 0) {
        break;
      }
    }

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

    $q = "INSERT INTO partnersites (user_id, serial_no, badadref_no, domain, papp_key, dev_authorized_id, type)
    VALUES ('$userid', '$pserial', '$rserial', 'w', '$papp_key', '$dev_id', 'app')";
    $r = mysqli_query ($srv_dbc, $q);
    if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran OK
      $refcredSLUG = "$rserial";
    } else { // If the entry didn't work
      sql_error($q, 'srv_dbc', "sqle_98");
    }
  } // end login check
} elseif ((isset($_POST['partner_app_key'])) && (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['partner_app_key']))) { // End re-try check
      $papp_key = preg_replace("/[^a-zA-Z0-9_]/","", $_POST['partner_app_key']);
}
$logged_in_form_connect = true;


// Create new Partner App form
if (((isset($logged_in_form_connect))
|| (isset($error_app_nickname)))
&& (isset($papp_key))
&& (isset($dev_name))
&& (isset($dev_domain))) {

  echo "
  <form id=\"add_partner_site\" class=\"new_partner_site_form\" action=\"connect_app.php\" method=\"post\" accept-charset=\"utf-8\">
  <input type=\"hidden\" name=\"partner_app_key\" value=\"$papp_key\" /><br />
  <input type=\"hidden\" name=\"partner_refcred\" value=\"$refcredSLUG\" /><br />
  <input type=\"hidden\" name=\"login_app_connect\" value=\"true\" /><br />
  <p>App name: <b>$dev_name</b><br />Domain: <b>$dev_domain</b></p>
  <p";
  if (isset($error_app_nickname)) {echo ' class="error"';}
  echo ">Nickname: <input";
  if (isset($error_app_nickname)) {echo ' class="error"';}
  echo " type=\"text\" name=\"papp_nickname\" size=\"32\" placeholder=\"Nickname (optional, can change later)\" />";
  if (isset($error_app_nickname)) {echo " $error_app_nickname";}
  echo "</p>
  <input type=\"submit\" value=\"Connect as new Partner App Project\" class=\"formbutton\" />
  </form>";

  // Include the HTML footer
  include ('./includes/footer.html');
  exit();
}

// We shoudn't be here, but in case we are

header("Location: https://badad.one");
exit(); // Quit the script


?>
