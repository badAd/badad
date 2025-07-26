<?php

// This talles and archives all partner site hit totals and starts a new cycle

// This uses a key made with tallykeygen.php

// Access from webdir with: partnersitehitcalculation.php?k=LONGWRITERSKEY


// Configs
require_once ('./config.inc.php');
require_once (MYSQL);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userID = $_SESSION['user_id'];

// Make sure $_GET['k'] is set
if (!isset($_GET['k'])) {
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
} else {
  $calc_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['k']);
}

// Check to see if the user is admin
$q = "SELECT type, email FROM users WHERE id='$userID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$userType = "$row[0]";
$calculators_email = "$row[1]";
if ($userType != "admin") {
  // Destroy the session
  $_SESSION = array(); // Destroy the variables
  session_destroy(); // Destroy the session itself
  setcookie (session_name(), '', time()-300); // Destroy the cookie
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
}

// Check the key
$q = "SELECT calckey FROM tallykey WHERE email='$calculators_email'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) { // No keys
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
} else {
  // Delete the old key
  $q = "DELETE FROM writerkey WHERE email='$writers_email' AND writekey='$writers_key'";
  $r = mysqli_query ($dbc, $q);
}

// Key exists, check it
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$keyonfile = "$row[0]";
if ($keyonfile != $calc_key) {
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
}

// Do the tally if _POSTed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ((!isset($_POST['payitout'])) && (!isset($_POST['submit_button']))) {
    // Destroy the session
    $_SESSION = array(); // Destroy the variables
    session_destroy(); // Destroy the session itself
    setcookie (session_name(), '', time()-300); // Destroy the cookie
    // Get out of here
    header("Location: index.php");
    exit(); // Quit the script
  }

  // Delete the old key
  $q = "DELETE FROM tallykey WHERE email='$calculators_email'";
  $r = mysqli_query ($dbc, $q);

  // Partner Site Hits
  // Get Partners' hits from the database
  $q = "SELECT id, user_id, listed_ad_count, listed_pda_count, clicked_listed_count, clicked_pda_count FROM partnersites";
  $rows = mysqli_query ($srv_dbc, $q);
  // Reset the result message
  $result = "Tally Results:";
  while ($row = mysqli_fetch_array($rows)) {
    // Set the email value
    $site_tally_ID = $row[0];
    $site_tally_user_ID = $row[1];
    $site_tally_listed_ad_count = $row[2];
    $site_tally_listed_pda_count = $row[3];
    $site_tally_clicked_listed_count = $row[4];
    $site_tally_clicked_pda_count = $row[5];

    // Tally the payment
    $q = "INSERT INTO partnersites_tallied (user_id, site_id, listed_ad_count, listed_pda_count, clicked_listed_count, clicked_pda_count, tallying_user_id) VALUES ('$site_tally_user_ID', '$site_tally_ID', '$site_tally_listed_ad_count', '$site_tally_listed_pda_count', '$site_tally_clicked_listed_count', '$site_tally_clicked_pda_count', '$userID')";
    $r = mysqli_query ($dbc, $q);
    if (mysqli_affected_rows($dbc) == 1) {

      // Reset the count on the Partner site
      $q = "UPDATE partnersites SET listed_ad_count='0', listed_pda_count='0', clicked_listed_count='0', clicked_pda_count='0', date_tallied=CURRENT_TIMESTAMP WHERE id='$site_tally_ID'";
      $r = mysqli_query ($srv_dbc, $q);
      if (mysqli_affected_rows($srv_dbc) == 1) {
        $result = "$result<br />Site: $site_tally_ID Tallied: $site_tally_listed_ad_count";
      } else {
        sql_error($q, 'srv_dbc', "sqle_179");
      }
    } else {
      sql_error($q, 'dbc', "sqle_180");
    }
  }

  // Feed counts
  // Get Feeds' hits from the database
  $q = "SELECT id, project_id, user_id, feed_requested_count, ad_download_count, ad_click_count FROM feeds";
  $rows = mysqli_query ($srv_dbc, $q);
  // Reset the result message
  $result = "Tally Results:";
  while ($row = mysqli_fetch_array($rows)) {
    // Set the email value
    $site_tally_ID = $row[0];
    $site_project_ID = $row[1];
    $site_tally_user_ID = $row[2];
    $site_tally_feed_requested_count = $row[3];
    $site_tally_ad_download_count = $row[4];
    $site_tally_ad_click_count = $row[5];

    // Tally the payment
    $q = "INSERT INTO feeds_tallied (user_id, project_id, feed_requested_count, ad_download_count, ad_click_count, tallying_user_id) VALUES ('$site_tally_user_ID', '$site_project_ID', '$site_tally_feed_requested_count', '$site_tally_ad_download_count', '$site_tally_ad_click_count', '$userID')";
    $r = mysqli_query ($dbc, $q);
    if (mysqli_affected_rows($dbc) == 1) {

      // Reset the count on the Partner site
      $q = "UPDATE feeds SET feed_requested_count='0', ad_download_count='0', ad_click_count='0', date_tallied=CURRENT_TIMESTAMP WHERE id='$site_tally_ID'";
      $r = mysqli_query ($agg_dbc, $q);
      if (mysqli_affected_rows($agg_dbc) == 1) {
        $result = "$result<br />Site: $site_project_ID Tallied: $site_tally_listed_ad_count";
      } else {
        sql_error($q, 'agg_dbc', "sqle_181");
      }
    } else {
      sql_error($q, 'dbc', "sqle_182");
    }
  }

  // Current Tally Figures
  // Get prices from the database
  $q = "SELECT ad_id, price FROM current_cycle";
  $rows = mysqli_query ($dbc, $q);
  // Reset the result message
  $prices = "Tallied Prices:";
  while ($row = mysqli_fetch_array($rows)) {
    // Set the email value
    $tallied_ID = $row[0];
    $tallied_price = $row[1];

    // Tally the payment
    $q = "INSERT INTO tallied_cycles (ad_id, price, tallying_user_id) VALUES ('$tallied_ID', '$tallied_price', '$userID')";
    $r = mysqli_query ($dbc, $q);
    if (mysqli_affected_rows($dbc) == 1) {

      // Remove the entry from current_cycle
      $q = "DELETE FROM current_cycle WHERE ad_id='$tallied_ID'";
      $r = mysqli_query ($dbc, $q);
      if (mysqli_affected_rows($dbc) == 1) {
        $prices = "$prices<br />Ad ID: $tallied_ID Price: $tallied_price";
      } else {
        sql_error($q, 'dbc', "sqle_183");
      }
    } else {
      sql_error($q, 'dbc', "sqle_184");
    }
  }

  // Print a message and wrap up
  echo "<h3>Hits tallied!</h3><p class=\"note_green\">All partner site hits calculated.</p>";
  echo $result;
  echo "<br /><br />";
  echo $prices;
  echo "<br /><br />";

}

// Build the form for sending
echo "<form action=\"partnersitehitcalculation.php?k=$calc_key\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"payitout\" />
<input type=\"submit\" name=\"submit_button\" value=\"Hit it!\" />
</form>
";
