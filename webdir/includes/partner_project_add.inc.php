<?php

// Configs
require_once ('./includes/config.inc.php');
require_once (MYSQL);
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('./includes/config_agg.inc.php');
require_once (MYSQL_AGG);

// New Website Project
if ( (isset($_POST['new_site_gen'])) && (isset($_POST['new_site_domain']))) {

  // TC agreement
  if ((!isset($_POST['tc_domain_embed_match'])) || ($_POST['tc_domain_embed_match'] != 'true')) {
    echo "<p class=\"note_red\">You must agree to understand use of the domain with embed codes.</p>";
    return;
  }

  $pdomain = $_POST['new_site_domain'];

  // Validate the nickname
  if (isset($_POST['website_nickname'])) {
    $website_nickname = $_POST['website_nickname'];
    function is_valid_domain_nickname($validating_nickname) {
      return (preg_match('/^[A-Z0-9 \'\/&,-]{0,80}$/i', $validating_nickname));
    }
    if (!is_valid_domain_nickname($website_nickname)) {
      echo "<p class=\"note_red\">The nickname may only use letters and numbers and - \' / , &</p>";
      return;
    } else {
      $new_website_nickname = mysqli_real_escape_string ($dbc, $website_nickname);
      $_SESSION['new_website_nickname'] = $new_website_nickname;
    }
  }

  // Validate domain
  $pdomain = str_replace('www.','',$pdomain);
  function is_valid_domain_name($domain_name) {
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
    && preg_match("/^.{1,255}$/", $domain_name) //overall length check
    && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
  }
  if (!is_valid_domain_name($pdomain)) {
    echo "<p class=\"note_red\">The domain must be a normal domain or subdomain, like \"example.com\" or \"subdomain.example.com\"</p>";
    return;
  } else {
    $pdomain = strtolower($pdomain);
    $pdomain = mysqli_real_escape_string ($dbc, $pdomain);
    $_SESSION['pdomain'] = $pdomain;
  }

  // Set a variable for the userid
  $userid = $_SESSION['user_id'];

  // Generate the ridiculously long random string
  require_once ('./includes/string_functions.inc.php');
  // Create the password link
  $pserial = longString(255);
  // Dup check
  $q = "SELECT serial_no FROM partnersites WHERE binary serial_no='$pserial'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($srv_dbc, $q);
  // while ($dup = mysqli_fetch_array($row)) {
  //   $pserial = longString(255);
  // }
  while (mysqli_num_rows($row) != 0) {
    $pserial = longString(255);
    // Check again
    $q = "SELECT serial_no FROM partnersites WHERE binary serial_no='$pserial'"; // "binary" makes sure case and characters are exact
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
  $q = "INSERT INTO partnersites (user_id, serial_no, badadref_no, domain, nickname, global_subcat_ids) VALUES ('$userid', '$pserial', '$rserial', '$pdomain', '$new_website_nickname', '$global_cat_id_list')";
  $r = mysqli_query ($srv_dbc, $q);

  if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran OK

  	// Get the new user's name from the database for the email
    $q = "SELECT name FROM users WHERE id='$userid'";
    $r = mysqli_query ($dbc, $q);
    $row = mysqli_fetch_array ($r, MYSQLI_NUM);
    $userName = $row[0];

  	// Send the Partner account change email
    $canned_email = "partner_site_added"; // Slug from the "pantry" table to select the canned email
    $payload_content = "<p>You added the domain: <b>$pdomain</b></p>";
    include ('./includes/confirm_partner_change.inc.php');

  	// Print a message and wrap up
  	echo "<h3 class=\"note_green\">Partner site added!</h3><p>$pdomain has been added as a partner site.</p>";

  } else {
    sql_error($q, 'srv_dbc', "sqle_62");
  }

// New Podcast
} elseif ( (isset($_POST['new_podcast_gen'])) && (isset($_POST['new_podcast_source'])) ) {

  // TC agreement
  if ((!isset($_POST['tc_podcast_rights'])) || ($_POST['tc_podcast_rights'] != 'true')) {
    echo "<p class=\"note_red\">You must declare your ownership of rigts to this podcast.</p>";
    return;
  }

  // Validate the nickname
  if (isset($_POST['podcast_nickname'])) {
    $podcast_nickname = $_POST['podcast_nickname'];
    function is_valid_podcast_nickname($validating_nickname) {
      return (preg_match ('/^[A-Z0-9 \'\/&,-]{0,80}$/i', $validating_nickname));
    }
    if (!is_valid_podcast_nickname($podcast_nickname)) {
      echo "<p class=\"note_red\">The nickname may only use letters and numbers and - \' / , &</p>";
      return;
    } else {
      $new_podcast_nickname = mysqli_real_escape_string ($dbc, $podcast_nickname);
      $_SESSION['new_podcast_nickname'] = $new_podcast_nickname;
    }
  }

  // Validate source
  $psource = trim($_POST['new_podcast_source']);
  if ((!filter_var($psource, FILTER_VALIDATE_URL)) || (strlen($psource) > 255)) {
    echo "<p class=\"note_red\">The source must be a real URL, such as http://example.com/some/url</p>";
    return;
  } else {
    $new_podcast_source = mysqli_real_escape_string ($dbc, $psource);
    $_SESSION['new_podcast_source'] = $new_podcast_source;
  }

  // Validate the slug
  if ((isset($_POST['podcast_slug'])) && ($_POST['podcast_slug'] != '')) {
    $podcast_slug = preg_replace("/[^A-Za-z0-9\/-]/","-", $_POST['podcast_slug']); // Rejected to hyphen
    $podcast_slug = preg_replace('/-+/', '-', $podcast_slug); // Only one hyphen
    $podcast_slug = rtrim($podcast_slug, "-"); // No trailing hyphen
    function is_valid_podcast_slug($validating_slug) {
      return (preg_match ('/^[A-Za-z0-9\/-]{0,255}$/i', $validating_slug));
    }
    if (!is_valid_podcast_slug($podcast_slug)) {
      echo "<p class=\"note_red\">The slug may only use letters and numbers and - /</p>";
      return;
    } else {

      $podcast_slug = strtolower($podcast_slug);
      $q = "SELECT serial_no FROM partnersites WHERE BINARY serial_no='$podcast_slug'"; // "binary" makes sure case and characters are exact
      $row = mysqli_query ($srv_dbc, $q);
      if (mysqli_num_rows($row) != 0) { // if: has dup
        $add_num = 0;
        while (mysqli_num_rows($row) != 0) {
          $add_num = $add_num + 1;
          $new_podcast_slug = $podcast_slug.'-'.$add_num;
          // In case this gets longer than allowed characters
          $new_podcast_slug = ($add_num == 1) ? substr($new_podcast_slug, 0, 93) : $new_podcast_slug;
          $new_podcast_slug = ($add_num == 10) ? substr($new_podcast_slug, 0, 92) : $new_podcast_slug;
          $new_podcast_slug = ($add_num == 100) ? substr($new_podcast_slug, 0, 91) : $new_podcast_slug;
          $new_podcast_slug = ($add_num == 1000) ? substr($new_podcast_slug, 0, 90) : $new_podcast_slug;
          $new_podcast_slug = ($add_num == 10000) ? substr($new_podcast_slug, 0, 89) : $new_podcast_slug;
          $new_podcast_slug = ($add_num == 100000) ? substr($new_podcast_slug, 0, 88) : $new_podcast_slug;

          // Check again
          $q = "SELECT serial_no FROM partnersites WHERE BINARY serial_no='$new_podcast_slug'"; // "binary" makes sure case and characters are exact
          $row = mysqli_query ($srv_dbc, $q);
          if (mysqli_num_rows($row) == 0) {
            break;
          } // check again break
        } // while
      } else { // if: no dup
        $new_podcast_slug = $podcast_slug;
      }

      $new_podcast_slug = mysqli_real_escape_string ($dbc, $new_podcast_slug);
      $_SESSION['new_podcast_slug'] = $new_podcast_slug;
    }

  } else {
    echo "<p class=\"note_red\">A slug is required, using only letters and numbers and - /</p>";
    return;
  } // slug

  // Set a variable for the userid
  $userid = $_SESSION['user_id'];

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

  // Add the podcast to the database
  $qs = "INSERT INTO partnersites (user_id, serial_no, badadref_no, domain, source, nickname, type, global_subcat_ids) VALUES ('$userid', '$new_podcast_slug', '$rserial', 'podcast', '$new_podcast_source', '$new_podcast_nickname', 'podcast', '$global_cat_id_list')";
  $rs = mysqli_query ($srv_dbc, $qs);
  $new_feed_id = $srv_dbc->insert_id;

  if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran OK

  	// Get the new user's name from the database for the email
    $q = "SELECT name FROM users WHERE id='$userid'";
    $r = mysqli_query ($dbc, $q);
    $row = mysqli_fetch_array ($r, MYSQLI_NUM);
    $userName = $row[0];

  	// Send the Partner account change email
    $canned_email = "partner_podcast_added"; // Slug from the "pantry" table to select the canned email
    $payload_content = "<p>You added the podcast: <b>$new_podcast_slug</b></p>";
    include ('./includes/confirm_partner_change.inc.php');

    // Display message because this takes a moment

    echo "<h1>Podcast added!</h1>";
    echo "<p>Please wait while we fetch the feed...</p>";

    // Go edit this Podcast
    echo "
    <form id=\"jsGoForm\" action=\"https://$podcastServeDomain/feed_refresh.php\" method=\"post\">
      <input type=\"hidden\" name=\"f\" value=\"$new_feed_id\">
      <input type=\"hidden\" name=\"user_id\" value=\"$userid\">
    </form>
    <script type=\"text/javascript\">
        document.getElementById('jsGoForm').submit();
    </script>";

  } else {
    sql_error($qs.' :: '.$qa, 'srv_dbc :: agg_dbc', "sqle_125");
  }

} else { exit(); }
