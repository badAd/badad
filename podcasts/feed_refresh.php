<?php

// This is the refresh action from partnerpodcast_manage.php
//In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Configs
require_once ('./config.inc.php');
require_once (MYSQL);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Validate the Feed number
if ((isset($_POST['f'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['f']))) {$IP = get_ip_addr(); script_kiddy('sk_88', '_POST f', $_POST['f'], $IP);}
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['f']))
&& (filter_var($_POST['f'], FILTER_VALIDATE_INT, array('min_range' => 1)))
&& (isset($_POST['user_id']))) {

  // IDs
  $user_id = $_POST['user_id'];
  $feed_pid = preg_replace("/[^0-9]/","", $_POST['f']);

  // So the feed_fetch_* includes echo results
  $refresh_action = true;

  // Message
  echo "<h1>Updating feed</h1><p>This may take a brief moment...</p>";

} else {
  header("Location: https://$siteDomain");
  exit;
}

// Functions
function set_status($new_status) {
  global $feed_pid;
  global $srv_dbc;
  global $agg_dbc;

  $qs = "UPDATE partnersites SET useable='$new_status' WHERE id='$feed_pid'";
  $rs = mysqli_query ($srv_dbc, $qs);
  $qa = "UPDATE feeds SET status='$new_status' WHERE project_id='$feed_pid'";
  $ra = mysqli_query ($agg_dbc, $qa);
  if ((!$rs) || (!$ra)) {
    sql_error("$qs ;; $qa", 'srv_dbc ;; agg_dbc', "sqle_131");

  }
}

function send_news($post_name, $post_value) {
  global $siteDomain;
  global $feed_pid;
  echo "
  <form id=\"jsGoForm\" action=\"https://$siteDomain/partnerpodcast_manage.php\" method=\"post\">
    <input type=\"hidden\" name=\"s\" value=\"$feed_pid\">
    <input type=\"hidden\" name=\"$post_name\" value=\"$post_value\">
  </form>
  <script type=\"text/javascript\">
      document.getElementById('jsGoForm').submit();
  </script>";
}

// Fetch the Project information
$q = "SELECT user_id, source, serial_no, global_subcat_ids FROM partnersites WHERE id='$feed_pid' AND user_id='$user_id' AND domain='podcast' AND type='podcast'";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
// No such project
if ($rows == 0) {
  sql_error("$q", 'srv_dbc', "sqle_126");

} else {
  // Assign values
  while ($row = mysqli_fetch_array($r)) {
    $user_id = "$row[0]";
		$source = "$row[1]";
    $slug = "$row[2]";
    $global_cat_id_list = "$row[3]";
  }
  // Check if an entry already exists
  $q = "SELECT id FROM feeds WHERE project_id='$feed_pid' AND source='$source'";
  $r = mysqli_query ($agg_dbc, $q);
  $rows = mysqli_num_rows($r);
  // INSERT
  if ($rows == 0) {
    $q = "INSERT INTO feeds (project_id, user_id, source, slug, global_subcat_ids) VALUES ('$feed_pid', '$user_id', '$source', '$slug', '$global_cat_id_list')";
    $r = mysqli_query ($agg_dbc, $q);
    if (mysqli_affected_rows($agg_dbc) == 1) {
      // Run the feed_fetch_meta process
      include ('./feed_fetch_meta.php');

      // Feed status
      if ((isset($feed_failed)) && ($feed_failed == true)) {
        set_status('failed');

        // Send bad news back to the "Edit podcast" page
        send_news('project_fail', 'fail');

      } elseif ($feed_fetch_meta_success == true) {
        set_status('live');

        // Run the feed_fetch_items process
        include ('./feed_fetch_items.php');

        // Send good news back to the "Edit podcast" page
        send_news('project_updated', 'updated');

      } else {
        echo "Serious error updating feed.";
        sql_error("$q", 'agg_dbc', "sqle_132");

      }

    } else {
      sql_error("$q", 'agg_dbc', "sqle_127");

    }
  // UPDATE
  } else {
    $q = "UPDATE feeds SET slug='$slug', source='$source', global_subcat_ids='$global_cat_id_list' WHERE project_id='$feed_pid' AND source='$source'";
    if ($r = mysqli_query ($agg_dbc, $q)) {
      // Run the feed_fetch process
      include ('./feed_fetch_meta.php');

      // Feed status
      if ((isset($feed_failed)) && ($feed_failed == true)) {
        set_status('failed');

        // Send bad news back to the "Edit podcast" page
        send_news('project_fail', 'fail');

      } elseif ((isset($feed_fetch_meta_success)) && ($feed_fetch_meta_success == true)) {
        set_status('live');

        // Run the feed_fetch_items process
        include ('./feed_fetch_items.php');

        // Send good news back to the "Edit podcast" page
        send_news('project_updated', 'updated');

      } else {
        sql_error("$q", 'agg_dbc', "sqle_133");

      }

    } else {
      sql_error("$q", 'agg_dbc', "sqle_128");

    }

  } // INSERT/UPDATE
} // Podcast found

?>
