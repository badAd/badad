<?php

// This edits the Nickname and slug of a Podcast
// This also has an option to refresh the podcast feed and displays podcast info

//In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Configs
require_once ('./includes/config.inc.php');
require_once (MYSQL);
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('./includes/config_agg.inc.php');
require_once (MYSQL_AGG);

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Errors (for editing feed info)
$check_err = array();

// Check for form submission and set the $feedID variable
// First visit
if ((isset($_POST['s'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['s']))) {$IP = get_ip_addr(); script_kiddy('sk_71', '_POST s', $_POST['s'], $IP);}
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['s']))
&& (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
	$feedID = preg_replace("/[^0-9]/","", $_POST['s']);

	// Include the header file
	$page_title = "Partner Podcast - Manage :: $siteTitle";
	include ('./includes/header.html');

	// JavaScript for Directory Listing form
	?>
	<script>
	// Check/uncheck the box = hide/show the Date Live schedule (p_live_schedule) <div>
	function showDirListOptionsBox() {
		var x = document.getElementById("directory-listing-form");
		if (x.style.display === "block") {
			x.style.display = "none";
		} else {
			x.style.display = "block";
		}
	}
	// JavaScript does not allow onClick action for both the label and the checkbox
	// So, we make the label open the Date Live schedule div AND check the box...
	function showDirListOptionsLabel() {
		// Show the Date Live schedule div
		var x = document.getElementById("directory-listing-form");
		if (x.style.display === "block") {
			x.style.display = "none";
		} else {
			x.style.display = "block";
		}
		// Use JavaScript to check the box
		var y = document.getElementById("edit_podcast_listing");
		if (y.checked === false) {
			y.checked = true;
		} else {
			y.checked = false;
		}
	}
	</script>
	<?php

} else { // Wrong place
	header("Location: partner.php");
	exit(); // Quit the script
}

// Change made to info
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['edit_podcast_nickname'])) && (isset($_POST['s']))) {

	// feedID
	$feedID = preg_replace("/[^0-9]/","", $_POST['s']);

	// Good news
	$feedinfogoodnews = '';

	// Directory Listing?
	if ((isset($_POST['edit_podcast_listing'])) && ($_POST['edit_podcast_listing'] == 'listed')
	&& (isset($_POST['edit_podcast_listname']))) {

		// Validate the Listed Name
		if (isset($_POST['edit_podcast_listname'])) {
			$podcast_listname = $_POST['edit_podcast_listname'];
			function is_valid_podcast_listname($validating_listname) {
				return (preg_match('/^[A-Z0-9 \'\/&,-.:?!#$%@|]{0,255}$/i', $validating_listname));
			}
			if (!is_valid_podcast_listname($podcast_listname)) {
				echo "<p class=\"note_red\">The Listed Name may only use letters and numbers and - \' / . : ? ! # $ % @ | , &</p>";
				$listing_update = false;
			} else {
				$regex_replace = "/[^0-9a-zA-Z_ \'\/&,-.:?!#$%@|]/";
				$podcast_listname = preg_replace($regex_replace,"", $_POST['edit_podcast_listname']);
				if ($podcast_listname == '') {
					echo "<p class=\"note_red\">To be listed, you must enter a Listed Name using only letters and numbers and - \' / . : ? ! # $ % @ | , &</p>";
					$listing_update = false;
				} else {
					$new_podcast_listname = mysqli_real_escape_string($srv_dbc, $podcast_listname);
				}
			}
		}

		// Check for duplicate Listed Name
		$q = "SELECT id, nickname, domain FROM partnersites WHERE directory_name='$new_podcast_listname' AND directory_listed='listed' AND NOT id='$feedID'";
		$r = mysqli_query ($srv_dbc, $q);
		$rows = mysqli_num_rows($r);
		if ($rows > 0) {
			$row = mysqli_fetch_array($r, MYSQLI_NUM);
			$dup_id = "$row[0]";
			$dup_nickname = "$row[1]";
			$dup_domain = "$row[2]";
			echo "<p class=\"note_red\">That Name is already listed for <i>$dup_nickname (#$dup_id, $dup_domain)</i>. Uncheck the Directory Listing option for that project before using the same Name here.</p>";
			$listing_update = false;
		} else {
			$listing_update = true;
		}

		// Without errors, UPDATE the database
		if ($listing_update === true) {
			$q = "UPDATE partnersites SET directory_name='$new_podcast_listname', directory_listed='listed' WHERE id='$feedID'";
			$r = mysqli_query ($srv_dbc, $q);
			if (mysqli_affected_rows($srv_dbc) == 1) { // Changes
				$feedinfogoodnews .= "<p class=\"note_green\">Podcast successfully saved &amp; listed!</p>";
			} elseif (!$r) { // Error
				sql_error($q, 'srv_dbc', "sqle_160");
			} elseif (mysqli_affected_rows($srv_dbc) == 0) {
				$feedinfogoodnews .= "<p class=\"note_green\">Saved!</p><p class=\"note_green\">Podcast listed, no changes!</p>";
			}

		} // Database UPDATE

	} else { // Directory Listing
		$q = "UPDATE partnersites SET directory_listed='no' WHERE id='$feedID'";
		$r = mysqli_query ($srv_dbc, $q);
		if (mysqli_affected_rows($srv_dbc) == 1) { // Changes
			$feedinfogoodnews .= "<p class=\"note_green\">Podcast successfully de-listed!</p>";
		} elseif (!$r) { // Error
			sql_error($q, 'srv_dbc', "sqle_161");
		} elseif (mysqli_affected_rows($srv_dbc) == 0) {
			$feedinfogoodnews .= "<p class=\"note_green\">Saved!</p>";
		}

	} // Directory de-listing

	// Podcast Links
	// Stitcher
	if (($_POST['edit_stitcher_url'] != '') && (filter_var($_POST['edit_stitcher_url'], FILTER_VALIDATE_URL)) && (strlen($_POST['edit_stitcher_url']) <= 2048)) {
		$edit_stitcher_url = $_POST['edit_stitcher_url'];
		if ((!str_contains($edit_stitcher_url, 'https://www.stitcher.com')) && (!str_contains($edit_stitcher_url, 'https://stitcher.com'))) {
			echo "<p class=\"note_red\">The Podcast URL must contain the Stitcher podcast domain (stitcher.com).</p>";
			$podcast_update = false;
		} else {
			$new_stitcher_url = mysqli_real_escape_string($dbc, $edit_stitcher_url);
			$q = "SELECT id, nickname, user_id FROM partnersites WHERE stitcher_url='$new_stitcher_url' AND directory_listed='listed' AND type='podcast' AND NOT id='$feedID'";
			$r = mysqli_query ($srv_dbc, $q);
			$rows = mysqli_num_rows($r);
			if ($rows > 0) {
				$row = mysqli_fetch_array($r, MYSQLI_NUM);
				$dup_id = "$row[0]";
				$dup_nickname = "$row[1]";
				$dup_domain = "$row[2]";
				$dup_user_id = "$row[3]";
				echo ($dup_user_id == $userid) ? "<p class=\"note_red\">That URL is already listed for <i>$dup_nickname (#$dup_id, $dup_domain)</i>. Uncheck the Directory Listing option for that project before using the same URL here.</p>" : "<p class=\"note_red\">Can't list that URL because it is already in use.</p>";
				$podcast_update = false;
			} else {
				$podcast_update = true;
			}
		}
	} else {
		$new_stitcher_url = '';
		$podcast_update = true;
	}
	// Spotify
	if (($_POST['edit_spotify_url'] != '') && (filter_var($_POST['edit_spotify_url'], FILTER_VALIDATE_URL)) && (strlen($_POST['edit_spotify_url']) <= 2048)) {
		$edit_spotify_url = $_POST['edit_spotify_url'];
		if (!str_contains($edit_spotify_url, 'https://open.spotify.com')) {
			echo "<p class=\"note_red\">The Podcast URL must contain the Spotify podcast domain (open.spotify.com).</p>";
			$podcast_update = false;
		} else {
			$new_spotify_url = mysqli_real_escape_string($dbc, $edit_spotify_url);
			$q = "SELECT id, nickname, user_id FROM partnersites WHERE spotify_url='$new_spotify_url' AND directory_listed='listed' AND type='podcast' AND NOT id='$feedID'";
			$r = mysqli_query ($srv_dbc, $q);
			$rows = mysqli_num_rows($r);
			if ($rows > 0) {
				$row = mysqli_fetch_array($r, MYSQLI_NUM);
				$dup_id = "$row[0]";
				$dup_nickname = "$row[1]";
				$dup_domain = "$row[2]";
				$dup_user_id = "$row[3]";
				echo ($dup_user_id == $userid) ? "<p class=\"note_red\">That URL is already listed for <i>$dup_nickname (#$dup_id, $dup_domain)</i>. Uncheck the Directory Listing option for that project before using the same URL here.</p>" : "<p class=\"note_red\">Can't list that URL because it is already in use.</p>";
				$podcast_update = false;
			} else {
				$podcast_update = true;
			}
		}
	} else {
		$new_spotify_url = '';
		$podcast_update = true;
	}
	// Apple
	if (($_POST['edit_apple_url'] != '') && (filter_var($_POST['edit_apple_url'], FILTER_VALIDATE_URL)) && (strlen($_POST['edit_apple_url']) <= 2048)) {
		$edit_apple_url = $_POST['edit_apple_url'];
		if (!str_contains($edit_apple_url, 'https://podcasts.apple.com')) {
			echo "<p class=\"note_red\">The Podcast URL must contain the Apple podcast domain (podcasts.apple.com).</p>";
			$podcast_update = false;
		} else {
			$new_apple_url = mysqli_real_escape_string($dbc, $edit_apple_url);
			$q = "SELECT id, nickname, user_id FROM partnersites WHERE apple_url='$new_apple_url' AND directory_listed='listed' AND type='podcast' AND NOT id='$feedID'";
			$r = mysqli_query ($srv_dbc, $q);
			$rows = mysqli_num_rows($r);
			if ($rows > 0) {
				$row = mysqli_fetch_array($r, MYSQLI_NUM);
				$dup_id = "$row[0]";
				$dup_nickname = "$row[1]";
				$dup_domain = "$row[2]";
				$dup_user_id = "$row[3]";
				echo ($dup_user_id == $userid) ? "<p class=\"note_red\">That URL is already listed for <i>$dup_nickname (#$dup_id, $dup_domain)</i>. Uncheck the Directory Listing option for that project before using the same URL here.</p>" : "<p class=\"note_red\">Can't list that URL because it is already in use.</p>";
				$podcast_update = false;
			} else {
				$podcast_update = true;
			}
		}
	} else {
		$new_apple_url = '';
		$podcast_update = true;
	}
	// UPDATE
	// Without errors, UPDATE the database
	if ($podcast_update === true) {
		$q = "UPDATE partnersites SET stitcher_url='$new_stitcher_url', spotify_url='$new_spotify_url', apple_url='$new_apple_url' WHERE id='$feedID'";
		$r = mysqli_query ($srv_dbc, $q);
		$qf = "UPDATE feeds SET stitcher_url='$new_stitcher_url', spotify_url='$new_spotify_url', apple_url='$new_apple_url' WHERE project_id='$feedID'";
		$rf = mysqli_query ($agg_dbc, $qf);
		if ((mysqli_affected_rows($srv_dbc) == 1) && (mysqli_affected_rows($agg_dbc) == 1)) { // Changes
			$feedinfogoodnews .= "<p class=\"note_green\">Podcast Links updated!</p>";
		} else {
			if (!$r) { // Error
				sql_error($q, 'srv_dbc', "sqle_162");
			}
			if (!$rf) { // Error
				sql_error($qf, 'agg_dbc', "sqle_137");
			}
		}

	} // Database UPDATE

	// Source URL
	// DEV This prevents the podcast source from being changed after 24 hours or validation
	// // Query the Partner Site Domain and Nickname
	// $q = "SELECT useable, date_created FROM partnersites WHERE id='$feedID'";
	// $r = mysqli_query ($srv_dbc, $q);
	// while ($row = mysqli_fetch_array($r)) {
	// 	$podcast_usable = "$row[0]";
	// 	$podcast_created = strtotime($row[1]);
	// }
	// // Current time of SQL server
	// $q = "SELECT CURRENT_TIMESTAMP";
	// $r = mysqli_query ($srv_dbc, $q);
	// while ($row = mysqli_fetch_array($r)) { $curr_time_sql = $row[0]; }
	// $curr_time_php = strtotime($curr_time_sql);
	// // Invalid & new feed? We can still change the URL within 24 hours
	// if (($podcast_usable == 'failed') && ($curr_time_php - $podcast_created < (60*60*24))) {
	// 	$edit_podcast_url = trim($_POST['edit_podcast_url']);
	//   if ((!filter_var($edit_podcast_url, FILTER_VALIDATE_URL)) || (strlen($edit_podcast_url) > 2048)) {
	//     echo "<p class=\"note_red\">The source must be a real URL, such as http://example.com/some/url</p>";
	//     $change_url = false;
	//   } else {
	// 		$change_url = true;
	//     $edit_podcast_url = mysqli_real_escape_string($srv_dbc, $edit_podcast_url);
	//   }
	// }

	// Change the source URL anytime
	if ((isset($_POST['confirm_edit_source'])) && ($_POST['confirm_edit_source'] == 'confirm_edit_source')) {
	  if ((isset($_POST['edit_podcast_url'])) && ($_POST['edit_podcast_url'] != '')) {
			$edit_podcast_url = trim($_POST['edit_podcast_url']);
		  if ((!filter_var($edit_podcast_url, FILTER_VALIDATE_URL)) || (strlen($edit_podcast_url) > 2048)) {
		    echo "<p class=\"note_red\">The source must be a real URL, such as http://example.com/some/url</p>";
		    $change_url = false;
		  } else {
				$change_url = true;
		    $edit_podcast_url = mysqli_real_escape_string($srv_dbc, $edit_podcast_url);
		  }
		}
	}

	// Set the SQL query for the source
	$change_url_sql_string = ((isset($change_url)) && ($change_url == true)) ? "source='$edit_podcast_url', " : "";

	// Validate the slug
	function is_valid_podcast_slug($validating_slug) {
		return (preg_match ('/^[A-Za-z0-9\/-]{1,255}$/i', $validating_slug));
	}
	if ((isset($_POST['confirm_edit_slug'])) && ($_POST['confirm_edit_slug'] == 'confirm_edit_slug')) {
	  if ((isset($_POST['edit_podcast_slug'])) && ($_POST['edit_podcast_slug'] != '')) {
			$edit_podcast_slug = preg_replace("/[^A-Za-z0-9\/-]/","-", $_POST['edit_podcast_slug']); // Rejected to hyphen
	    $edit_podcast_slug = preg_replace('/-+/', '-', $edit_podcast_slug); // Only one hyphen
			$edit_podcast_slug = rtrim($edit_podcast_slug, "-"); // No trailing hyphen
	    if (!is_valid_podcast_slug($edit_podcast_slug)) {
	      echo "<p class=\"note_red\">The slug may only use letters and numbers and - /</p>";
	      return;
	    } else {

	      $edit_podcast_slug = strtolower($edit_podcast_slug);
	      $q = "SELECT serial_no FROM partnersites WHERE serial_no='$edit_podcast_slug' AND NOT id='$feedID'";
	      $row = mysqli_query ($srv_dbc, $q);
	      if (mysqli_num_rows($row) != 0) { // if: has dup
	        $add_num = 0;
	        while (mysqli_num_rows($row) != 0) {
	          $add_num = $add_num + 1;
	          $new_podcast_slug = $edit_podcast_slug.'-'.$add_num;
	          // In case this gets longer than allowed characters
	          $new_podcast_slug = ($add_num == 1) ? substr($new_podcast_slug, 0, 93) : $new_podcast_slug;
	          $new_podcast_slug = ($add_num == 10) ? substr($new_podcast_slug, 0, 92) : $new_podcast_slug;
	          $new_podcast_slug = ($add_num == 100) ? substr($new_podcast_slug, 0, 91) : $new_podcast_slug;
	          $new_podcast_slug = ($add_num == 1000) ? substr($new_podcast_slug, 0, 90) : $new_podcast_slug;
	          $new_podcast_slug = ($add_num == 10000) ? substr($new_podcast_slug, 0, 89) : $new_podcast_slug;
	          $new_podcast_slug = ($add_num == 100000) ? substr($new_podcast_slug, 0, 88) : $new_podcast_slug;

	          // Check again
	          $q = "SELECT serial_no FROM partnersites WHERE serial_no='$new_podcast_slug' AND NOT id='$feedID'";
	          $row = mysqli_query ($srv_dbc, $q);
	          if (mysqli_num_rows($row) == 0) {
	            break;
	          } // check again break
	        } // while
	      } else { // if: no dup
	        $new_podcast_slug = $edit_podcast_slug;
	      }

	      $new_podcast_slug = mysqli_real_escape_string($srv_dbc, $new_podcast_slug);

				// What about the old slug?
				$old_podcast_slug = preg_replace("/[^A-Za-z0-9\/-]/","-", $_POST['old_podcast_slug']); // Rejected to hyphen
		    $old_podcast_slug = preg_replace('/-+/', '-', $old_podcast_slug); // Only one hyphen
				$old_podcast_slug = rtrim($old_podcast_slug, "-"); // No trailing hyphen
		    $old_podcast_slug = (is_valid_podcast_slug($old_podcast_slug)) ? $old_podcast_slug : '';
				if ($old_podcast_slug != $new_podcast_slug) {
					$update_old_podcast_slug = ((isset($_POST['purge_old_slug'])) && ($_POST['purge_old_slug'] == 'purge')) ? '' : $old_podcast_slug;

					$q = "UPDATE feeds SET
					  slug='$new_podcast_slug',
						old_slug='$update_old_podcast_slug'
						WHERE project_id='$feedID'";
						if (!$r = mysqli_query ($agg_dbc, $q)) {
							sql_error("$q", 'agg_dbc', "sqle_137");
						} else {
							$feedinfogoodnews .= "<p class=\"note_green\">Slug updated!</p>";
							$slug_update = true;
						}
				} // new & old slugs differ?
	    } // Valid slug

	  } else {
	    echo "<p class=\"note_red\">A slug is required, using only letters and numbers and - /</p>";
			$slug_update = false;
	  } // slug
	} elseif (isset($_POST['old_podcast_slug'])) {// Confirm change slug
		$old_podcast_slug = preg_replace("/[^A-Za-z0-9\/-]/","-", $_POST['old_podcast_slug']); // Rejected to hyphen
		$old_podcast_slug = preg_replace('/-+/', '-', $old_podcast_slug); // Only one hyphen
		$old_podcast_slug = rtrim($old_podcast_slug, "-"); // No trailing hyphen
		if (!is_valid_podcast_slug($old_podcast_slug)) { // Impossible error
			header("Location: partner.php");
			exit(); // Quit the script
		}
		$new_podcast_slug = mysqli_real_escape_string($srv_dbc, $old_podcast_slug);
	} else { // Impossible error
		header("Location: partner.php");
		exit(); // Quit the script
	}

	// Nickname
	$edit_podcast_nickname = $_POST['edit_podcast_nickname'];
	if ($edit_podcast_nickname == "") {
		$q = "UPDATE partnersites SET $change_url_sql_string nickname=NULL, serial_no='$new_podcast_slug' WHERE id='$feedID'";
	} else {
	  function is_valid_podcast_nickname($validating_nickname) {
	    return (preg_match ('/^[A-Z0-9 \'\/&,-]{1,255}$/i', $validating_nickname));
	    }
	  if (!is_valid_podcast_nickname($edit_podcast_nickname)) {
	    echo "<p class=\"note_red\">The nickname may be only letters and numbers and - \' / , &</p>";
			$nickname_update = false;
	  } else {
			$regex_replace = "/[^0-9a-zA-Z_ \'\/&,-]/";
			$edit_podcast_nickname = preg_replace($regex_replace,"", $_POST['edit_podcast_nickname']);
	    $edit_podcast_nickname = mysqli_real_escape_string($srv_dbc, $edit_podcast_nickname);
			$q = "UPDATE partnersites SET $change_url_sql_string nickname='$edit_podcast_nickname', serial_no='$new_podcast_slug' WHERE id='$feedID'";
			$r = mysqli_query ($srv_dbc, $q);
			if (mysqli_affected_rows($srv_dbc) == 1) { // Changes
				$feedinfogoodnews .= "<p class=\"note_green\">Nicname updated!</p>";
				$nickname_update = true;
			} elseif (!$r) { // Error
				sql_error($q, 'srv_dbc', "sqle_130");
			} else {
				$nickname_update = true;
			}
	  }
	} // Nickname

// Successful changes
if (($podcast_update === true) || ($nickname_update === true) || ($slug_update === true)) {
	echo $feedinfogoodnews;

	// Update the feed if source is new?
	if ($change_url === true) { // If the query ran OK with no error

		// Message
		echo "<h1>Updating feed</h1><p>This may take a brief moment...</p>";

		// Update the feed
		echo "
		<form id=\"jsGoForm\" action=\"https://$podcastServeDomain/feed_refresh.php\" method=\"post\">
			<input type=\"hidden\" name=\"f\" value=\"$feedID\">
			<input type=\"hidden\" name=\"user_id\" value=\"$userid\">
		</form>
		<script type=\"text/javascript\">
				document.getElementById('jsGoForm').submit();
		</script>";

	} // Update feed

} // Successful changes

// Save custom settings
} elseif ( ($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['s'])) && (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))
  && (isset($_POST['custom_settings'])) && ($_POST['custom_settings'] == 'yep') ) {

	$feedID = preg_replace("/[^0-9]/","", $_POST['s']);

	// Override with custom
	if ((isset($_POST['override_feed_settings'])) && ($_POST['override_feed_settings'] == 'override')) {
		$u_override_feed_settings = 'yes';
	} else {
		$u_override_feed_settings = 'no';
	}

	// Title
	$regex_match = '/^[0-9a-zA-Z_ ©™®℠!@&#<>$%.,:;+-–—=\/|]{1,90}$/';
	if ((isset($_POST['ba_title'])) && ($_POST['ba_title'] != '') && ($_POST['ba_title'] != 'ba-empty')
	&& (preg_match($regex_match, $_POST['ba_title']))) {
		$regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,:;+-–—=\/|]/";
		$result = preg_replace($regex_replace,"", $_POST['ba_title']);
		$result = preg_replace($regex_replace,"", $result);
		$result = preg_replace('/([A-Z].[a-z]+)-([A-Z].[a-z]+)/','$1–$2',$result); // Proper noun range to en-dash
		$result = preg_replace('/([0-9]$)+-+([0-9])/','$1–$2',$result); // number range to en-dash
		$result = str_replace(' -- ',' – ',$result); // to en-dash
		$result = str_replace(' --','—',$result); // to em-dash
		$result = str_replace('-- ','—',$result); // to em-dash
		$result = str_replace('---','—',$result); // to em-dash
		$result = str_replace('--','—',$result); // to em-dash
		$result = str_replace('©','&#169;',$result); // &copy; for XML use
		$result = str_replace('™','&#8482;',$result); // &trade; for XML use
		$result = str_replace('®','&#174;',$result); // &reg; for XML use
		$result = strip_tags($result); // Remove any HTML tags
		if ($result == '') {
			$u_ba_title = 'ba-empty';
			$check_err['ba_title'] = 'Not a valid title! (1-90 characters, special characters allowed: ! : @ & # $ % - _ . , + - = / | © ™ ® ℠ )';
		}
		$u_ba_title = substr($result, 0, 255); // Limit to 255 characters for TINYTEXT datatype
	} else {
		$u_ba_title = 'ba-empty';
	}

	// Link
	if ((isset($_POST['ba_link'])) && ($_POST['ba_link'] != '') && ($_POST['ba_link'] != 'ba-empty')) {
		$u_ba_link = trim($_POST['ba_link']);
		$result = ((filter_var($u_ba_link, FILTER_VALIDATE_URL)) && (strlen($u_ba_link) <= 128))
    ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $u_ba_link),0,128) : '';
    if ($result == '') {
			$u_ba_link = 'ba-empty';
      $check_err['ba_link'] = 'Not a valid URL!';
    }
		$u_ba_link = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	} else {
		$u_ba_link = 'ba-empty';
	}

	// Copyright
	if ((isset($_POST['ba_copyright'])) && ($_POST['ba_copyright'] != '') && ($_POST['ba_copyright'] != 'ba-empty')) {
		$regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|]/";
    $result = preg_replace($regex_replace,"", $_POST['ba_copyright']);
		$result = str_replace('©','&#169;',$result); // &copy; for XML use
		$result = str_replace('™','&#8482;',$result); // &trade; for XML use
		$result = str_replace('®','&#174;',$result); // &reg; for XML use
    if ($result == '') {
			$u_ba_copyright = 'ba-empty';
      $check_err['ba_copyright'] = 'Not a valid copyright statement! (1-500 characters, special characters allowed: ! @ & # $ % - _ . , + - = / | © ™ ® ℠ )';
    }
		$u_ba_copyright = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	} else {
		$u_ba_copyright = 'ba-empty';
	}

	// Description
	if ((isset($_POST['ba_description'])) && ($_POST['ba_description'] != '') && ($_POST['ba_description'] != 'ba-empty')) {
    $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|]/";
    $result = preg_replace($regex_replace,"", $_POST['ba_description']);
		$result = preg_replace($regex_replace,"", $result);
		$result = preg_replace('/([A-Z].[a-z]+)-([A-Z].[a-z]+)/','$1–$2',$result); // Proper noun range to en-dash
		$result = preg_replace('/([0-9]$)+-+([0-9])/','$1–$2',$result); // number range to en-dash
		$result = str_replace(' -- ',' – ',$result); // to en-dash
		$result = str_replace(' --','—',$result); // to em-dash
		$result = str_replace('-- ','—',$result); // to em-dash
		$result = str_replace('---','—',$result); // to em-dash
		$result = str_replace('--','—',$result); // to em-dash
		$result = str_replace('©','&#169;',$result); // &copy; for XML use
		$result = str_replace('™','&#8482;',$result); // &trade; for XML use
		$result = str_replace('®','&#174;',$result); // &reg; for XML use
		$result = strip_tags($result); // Remove any HTML tags
		$result = substr($result, 0, 55300); // Limit to 5,530 characters
    if ($result == '') {
			$u_ba_description = 'ba-empty';
      $check_err['ba_description'] = 'Not a valid description! (Special characters allowed: ! @ & # $ % - _ . , + - = / | © ™ ® ℠ )';
    }
		$u_ba_description = substr($result, 0, 55300); // Limit to 5,530 characters
	} else {
		$u_ba_description = 'ba-empty';
	}

	// Image source
	if ((isset($_POST['ba_image_url'])) && ($_POST['ba_image_url'] != '') && ($_POST['ba_image_url'] != 'ba-empty')) {
		$u_ba_image_url = trim($_POST['ba_image_url']);
		$result = ((filter_var($u_ba_image_url, FILTER_VALIDATE_URL)) && (strlen($u_ba_image_url) <= 128))
    ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $u_ba_image_url),0,128) : '';
    if ($result == '') {
			$u_ba_image_url = 'ba-empty';
      $check_err['ba_image_url'] = 'Not a valid URL!';
    }
		$u_ba_image_url = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	} else {
		$u_ba_image_url = 'ba-empty';
	}

	// Image link
	if ((isset($_POST['ba_image_link'])) && ($_POST['ba_image_link'] != '') && ($_POST['ba_image_link'] != 'ba-empty')) {
		$u_ba_image_link = trim($_POST['ba_image_link']);
		$result = ((filter_var($u_ba_image_link, FILTER_VALIDATE_URL)) && (strlen($u_ba_image_link) <= 128))
    ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $u_ba_image_link),0,128) : '';
    if ($result == '') {
			$u_ba_image_link = 'ba-empty';
      $check_err['ba_image_link'] = 'Not a valid URL!';
    }
		$u_ba_image_link = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	} else {
		$u_ba_image_link = 'ba-empty';
	}

	// Image title
	if ((isset($_POST['ba_image_title'])) && ($_POST['ba_image_title'] != '') && ($_POST['ba_image_title'] != 'ba-empty')) {
    $regex_replace = "/[^0-9a-zA-Z_ !@&#$%.,+-=\/|]/";
    $result = preg_replace($regex_replace,"", $_POST['ba_image_title']);
    if ($result == '') {
			$u_ba_image_title = 'ba-empty';
      $check_err['ba_image_title'] = 'Not a valid title! (1-90 characters, special characters allowed: ! @ & # $ % - _ . , + - = / | )';
    }
		$u_ba_image_title = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	} else {
		$u_ba_image_title = 'ba-empty';
	}

	// // iTunes Summary
	// if ((isset($_POST['ba_itunes_summary'])) && ($_POST['ba_itunes_summary'] != '') && ($_POST['ba_itunes_summary'] != 'ba-empty')) {
  //   $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|]/";
  //   $result = preg_replace($regex_replace,"", $_POST['ba_itunes_summary']);
  //   if ($result == '') {
	// 		$u_ba_itunes_summary = 'ba-empty';
  //     $check_err['ba_itunes_summary'] = 'Not a valid summary! (1-500 characters, special characters allowed: ! @ & # $ % - _ . , + - = / | © ™ ® ℠ )';
  //   }
	// 	$u_ba_itunes_summary = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	// } else {
	// 	$u_ba_itunes_summary = 'ba-empty';
	// }
	// Depreciated? Keeping just in case

	// iTunes Title (optional)
	if ($u_ba_itunes_title = $_POST['ba_itunes_title']) {
	$regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|]/";
	$u_ba_itunes_title = preg_replace($regex_replace,"", $u_ba_itunes_title);
	$u_ba_itunes_title = str_replace('©','&#169;',$u_ba_itunes_title); // &copy; for XML use
	$u_ba_itunes_title = str_replace('™','&#8482;',$u_ba_itunes_title); // &trade; for XML use
	$u_ba_itunes_title = str_replace('®','&#174;',$u_ba_itunes_title); // &reg; for XML use
	$u_ba_itunes_title = substr($u_ba_itunes_title, 0, 255); // Limit to 255 characters for TINYTEXT datatype
	} else {
	  $u_ba_itunes_title = 'ba-empty';
	}

	// iTunes image source
	if ((isset($_POST['ba_itunes_image'])) && ($_POST['ba_itunes_image'] != '') && ($_POST['ba_itunes_image'] != 'ba-empty')) {
		$u_ba_itunes_image = trim($_POST['ba_itunes_image']);
		$result = ((filter_var($u_ba_itunes_image, FILTER_VALIDATE_URL)) && (strlen($u_ba_itunes_image) <= 255)
		&& ((strtolower(pathinfo($u_ba_itunes_image, PATHINFO_EXTENSION)) == "png" ) || (strtolower(pathinfo($u_ba_itunes_image, PATHINFO_EXTENSION)) == "jpg" ) || (strtolower(pathinfo($u_ba_itunes_image, PATHINFO_EXTENSION)) == "jpeg" )))
    ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $u_ba_itunes_image),0,255) : '';
    if ($result == '') {
			$u_ba_itunes_image = 'ba-empty';
      $check_err['ba_itunes_image'] = 'Not a valid image URL!';
    }
		$u_ba_itunes_image = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	} else {
		$u_ba_itunes_image = 'ba-empty';
	}

	// iTunes author
	if ((isset($_POST['ba_itunes_author'])) && ($_POST['ba_itunes_author'] != '') && ($_POST['ba_itunes_author'] != 'ba-empty')) {
    $regex_replace = "/[^0-9a-zA-Z_ !@&#$%.,+-=\/|]/";
    $result = preg_replace($regex_replace,"", $_POST['ba_itunes_author']);
    if ($result == '') {
			$u_ba_itunes_author = 'ba-empty';
      $check_err['ba_itunes_author'] = 'Not a valid nickname! (1-90 characters, special characters allowed: ! @ & # $ % - _ . , + - = / | )';
    }
		$u_ba_itunes_author = substr($result, 0, 255); // Limit to 255 characters for TINYTEXT datatype
	} else {
		$u_ba_itunes_author = 'ba-empty';
	}

	// iTunes owner name
	if ((isset($_POST['ba_itunes_owner_name'])) && ($_POST['ba_itunes_owner_name'] != '') && ($_POST['ba_itunes_owner_name'] != 'ba-empty')) {
    $regex_replace = "/[^0-9a-zA-Z_ !@&#$%.,+-=\/|]/";
    $result = preg_replace($regex_replace,"", $_POST['ba_itunes_owner_name']);
    if ($result == '') {
			$u_ba_itunes_owner_name = 'ba-empty';
      $check_err['ba_itunes_owner_name'] = 'Not a valid nickname! (1-90 characters, special characters allowed: ! @ & # $ % - _ . , + - = / | )';
    }
		$u_ba_itunes_owner_name = substr($result, 0, 255); // Limit to 255 characters for TINYTEXT datatype
	} else {
		$u_ba_itunes_owner_name = 'ba-empty';
	}

	// iTunes owner email
	if ((isset($_POST['ba_itunes_owner_email'])) && ($_POST['ba_itunes_owner_email'] != '') && ($_POST['ba_itunes_owner_email'] != 'ba-empty')) {
		$result = ((filter_var($_POST['ba_itunes_owner_email'],FILTER_VALIDATE_EMAIL)) && (strlen($_POST['ba_itunes_owner_email']) <= 128))
    ? substr(preg_replace("/[^a-zA-Z0-9-_@.]/","", $_POST['ba_itunes_owner_email']),0,128) : '';
    if ($result == '') {
			$u_ba_itunes_owner_email = 'ba-empty';
      $check_err['ba_itunes_owner_email'] = 'Not an email!';
    }
		$u_ba_itunes_owner_email = substr($result, 0, 255); // Limit to 255 characters for TINYTEXT datatype
	} else {
		$u_ba_itunes_owner_email = 'ba-empty';
	}

	// iTunes keywords
	if ((isset($_POST['ba_itunes_keywords'])) && ($_POST['ba_itunes_keywords'] != '') && ($_POST['ba_itunes_keywords'] != 'ba-empty')) {
		$regex_match = '/[0-9a-zA-Z-_, ]{1,100}$/';
    $result = preg_replace($regex_replace,"", $_POST['ba_itunes_keywords']);
		// Truncate after 12
		$keyword_items = explode(',', $result);
		$total = count($keyword_items);
		$count = 1;
		$result = '';
		foreach ($keyword_items as $word) {
			$word = preg_replace('/\s+/', '', $word);
			if (!preg_match("/[a-zA-Z]/i", $word)) { $count ++; continue; }
			$result .= $word.', ';
			$count ++;
			if (($count == 13) || ($count == $total + 1)) { break; }
		}
		$result = rtrim($result, ',');

		// echo $result; exit;
    if ($result == '') {
			$u_ba_itunes_keywords = 'ba-empty';
      $check_err['ba_itunes_keywords'] = 'Not valid keywords! (1-100 characters, a comma-separated list, hyphen and underscore allowed)';
    }
		$u_ba_itunes_keywords = substr($result, 0, 65530); // Limit to 65,530 characters for TINYTEXT datatype
	} else {
		$u_ba_itunes_keywords = 'ba-empty';
	}

	// Language
	if ((isset($_POST['ba_language']))) {
    $regex_replace = "/[^0-9a-zA-Z-]/";
    $result = preg_replace($regex_replace,"", $_POST['ba_language']);
    $u_ba_language = ($result == '') ? 'en-us' : strtolower(substr($replaced, 0, 9));
	} else {
		$u_ba_language = 'en-us';
	}

	// iTunes explicit
	if (isset($_POST['ba_itunes_explicit'])) {
		$u_ba_itunes_explicit = (($_POST['ba_itunes_explicit'] == 'false') || ($_POST['ba_itunes_explicit'] == 'no')) ? 'false' : 'true';
	} else {
		$u_ba_itunes_explicit = 'true';
	}

	// iTunes Type (optional)
	$u_ba_itunes_type = ($_POST['ba_itunes_type'] == 'serial') ? 'serial' : 'episodic';

	// iTunes Complete (optional)
	$u_ba_itunes_complete = ($_POST['ba_itunes_complete'] == 'yes') ? 'yes' : 'not';

	// Category 1
	if (isset($_POST['ba_cust_cat1'])) {
		$regex_replace = "/[^a-zA-Z-&;: ]/";
		$result = strip_tags($_POST['ba_cust_cat1']); // Remove any HTML tags
		$result = preg_replace($regex_replace,"-", $result); // Remove non-accepted characters
		$result = substr($result, 0, 255); // Limit to 255 characters
		$result = trim(preg_replace('/\s+/', ' ', $result)); // Trim whitespace
		$u_ba_itunes_cat1 = $result;
	} else {
		$u_ba_itunes_cat1 = 'ba-empty';
	}

	// Category 2
	if (isset($_POST['ba_cust_cat2'])) {
		$regex_replace = "/[^a-zA-Z-&;: ]/";
		$result = strip_tags($_POST['ba_cust_cat2']); // Remove any HTML tags
		$result = preg_replace($regex_replace,"-", $result); // Remove non-accepted characters
		$result = substr($result, 0, 255); // Limit to 255 characters
		$result = trim(preg_replace('/\s+/', ' ', $result)); // Trim whitespace
		$u_ba_itunes_cat2 = $result;
	} else {
		$u_ba_itunes_cat2 = 'ba-empty';
	}

	// Category 3
	if (isset($_POST['ba_cust_cat3'])) {
		$regex_replace = "/[^a-zA-Z-&;: ]/";
		$result = strip_tags($_POST['ba_cust_cat3']); // Remove any HTML tags
		$result = preg_replace($regex_replace,"-", $result); // Remove non-accepted characters
		$result = substr($result, 0, 255); // Limit to 255 characters
		$result = trim(preg_replace('/\s+/', ' ', $result)); // Trim whitespace
		$u_ba_itunes_cat3 = $result;
	} else {
		$u_ba_itunes_cat3 = 'ba-empty';
	}

	// Category 4
	if (isset($_POST['ba_cust_cat4'])) {
		$regex_replace = "/[^a-zA-Z-&;: ]/";
		$result = strip_tags($_POST['ba_cust_cat4']); // Remove any HTML tags
		$result = preg_replace($regex_replace,"-", $result); // Remove non-accepted characters
		$result = substr($result, 0, 255); // Limit to 255 characters
		$result = trim(preg_replace('/\s+/', ' ', $result)); // Trim whitespace
		$u_ba_itunes_cat4 = $result;
	} else {
		$u_ba_itunes_cat4 = 'ba-empty';
	}

	// Category 5
	if (isset($_POST['ba_cust_cat5'])) {
		$regex_replace = "/[^a-zA-Z-&;: ]/";
		$result = strip_tags($_POST['ba_cust_cat5']); // Remove any HTML tags
		$result = preg_replace($regex_replace,"-", $result); // Remove non-accepted characters
		$result = substr($result, 0, 255); // Limit to 255 characters
		$result = trim(preg_replace('/\s+/', ' ', $result)); // Trim whitespace
		$u_ba_itunes_cat5 = $result;
	} else {
		$u_ba_itunes_cat5 = 'ba-empty';
	}

	// // SOME_THING_ELSE
	// if (isset($_POST['ba_SOMETHING'])) {
	//
	// 	$u_ = $result;
	// } else {
	// 	$u_ = 'ba-empty';
	// }

	// Escape
	$esc_u_override_feed_settings = mysqli_real_escape_string($agg_dbc, $u_override_feed_settings);
	$esc_u_ba_title = mysqli_real_escape_string($agg_dbc, $u_ba_title);
	$esc_u_ba_link = mysqli_real_escape_string($agg_dbc, $u_ba_link);
	$esc_u_ba_description = mysqli_real_escape_string($agg_dbc, $u_ba_description);
	$esc_u_ba_copyright = mysqli_real_escape_string($agg_dbc, $u_ba_copyright);
	$esc_u_ba_image_url = mysqli_real_escape_string($agg_dbc, $u_ba_image_url);
	$esc_u_ba_image_title = mysqli_real_escape_string($agg_dbc, $u_ba_image_title);
	$esc_u_ba_image_link = mysqli_real_escape_string($agg_dbc, $u_ba_image_link);
	$esc_u_ba_language = mysqli_real_escape_string($agg_dbc, $u_ba_language);
	$esc_u_ba_itunes_title = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_title);
	$esc_u_ba_itunes_type = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_type);
	$esc_u_ba_itunes_complete = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_complete);
	$esc_u_ba_itunes_image = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_image);
	$esc_u_ba_itunes_author = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_author);
	$esc_u_ba_itunes_summary = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_summary);
	$esc_u_ba_itunes_owner_name = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_owner_name);
	$esc_u_ba_itunes_owner_email = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_owner_email);
	$esc_u_ba_itunes_keywords = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_keywords);
	$esc_u_ba_itunes_explicit = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_explicit);
	$esc_u_ba_itunes_cat1 = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_cat1);
	$esc_u_ba_itunes_cat2 = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_cat2);
	$esc_u_ba_itunes_cat3 = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_cat3);
	$esc_u_ba_itunes_cat4 = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_cat4);
	$esc_u_ba_itunes_cat5 = mysqli_real_escape_string($agg_dbc, $u_ba_itunes_cat5);
	// Update the dateabase
	if (empty($check_err)) {
		$q = "UPDATE feeds SET
		  override_feed_settings='$esc_u_override_feed_settings',
			itunes_status='update',
		  ba_title='$esc_u_ba_title',
		  ba_link='$esc_u_ba_link',
		  ba_description='$esc_u_ba_description',
		  ba_copyright='$esc_u_ba_copyright',
		  ba_image_url='$esc_u_ba_image_url',
		  ba_image_title='$esc_u_ba_image_title',
		  ba_image_link='$esc_u_ba_image_link',
		  ba_language='$esc_u_ba_language',
			ba_itunes_title='$esc_u_ba_itunes_title',
			ba_itunes_type='$esc_u_ba_itunes_type',
			ba_itunes_complete='$esc_u_ba_itunes_complete',
		  ba_itunes_image='$esc_u_ba_itunes_image',
		  ba_itunes_author='$esc_u_ba_itunes_author',
		  ba_itunes_summary='$esc_u_ba_itunes_summary',
		  ba_itunes_owner_name='$esc_u_ba_itunes_owner_name',
		  ba_itunes_owner_email='$esc_u_ba_itunes_owner_email',
		  ba_itunes_keywords='$esc_u_ba_itunes_keywords',
		  ba_itunes_explicit='$esc_u_ba_itunes_explicit',
		  ba_itunes_cat1='$esc_u_ba_itunes_cat1',
		  ba_itunes_cat2='$esc_u_ba_itunes_cat2',
		  ba_itunes_cat3='$esc_u_ba_itunes_cat3',
		  ba_itunes_cat4='$esc_u_ba_itunes_cat4',
		  ba_itunes_cat5='$esc_u_ba_itunes_cat5'
		  WHERE project_id='$feedID'";
		if ($r = mysqli_query ($agg_dbc, $q)) {
			$ba_custom_updated = true;
		// Impossible error
		} else {
			sql_error("$q", 'agg_dbc', "sqle_134");
		}

	// Validation errors
	} else {
		$ba_custom_update_errors = true;
	} // Update database, no validation errors

} // Updating info

// Feed refresh status
if (isset($_POST['project_absent'])) {
	echo "<p class=\"note_red\">Impossible error, podcast does not exist in the database, this is very strange. The error has been reported. You may solve this by deleting the project and starting over.</p>";

} elseif (isset($_POST['project_fail'])) {
	echo "<p class=\"note_yellow\">Project feed failed! You may solve this by updating the feed at the source, entering a new feed URL, or deleting the project and starting over.</p>";

} elseif (isset($_POST['project_error'])) {
	echo "<p class=\"note_red\">Project update experienced a serious error! The error has been reported. You may solve this by deleting the project and starting over.</p>";

} elseif (isset($_POST['project_updated'])) {
	echo "<p class=\"note_green\">Project updated!</p>";
}

// Custom settings updated
echo ((isset($ba_custom_updated)) && ($ba_custom_updated == true)) ? "<p class=\"note_green\">Custom settings updated!</p>" : false;
echo ((isset($ba_custom_update_errors)) && ($ba_custom_update_errors == true)) ? "<p class=\"note_red\">Custom settings have errors! Correct them to continue.</p>" : false;

// Query the Partner Site Domain and Nickname
$q = "SELECT serial_no, nickname, source, useable, date_created, directory_listed, directory_name, stitcher_url, spotify_url, apple_url FROM partnersites WHERE id='$feedID'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$podcast_slug = "$row[0]";
$podcast_nickname = "$row[1]";
$podcast_source = "$row[2]";
$podcast_usable = "$row[3]";
$podcast_created = strtotime($row[4]);
$podcast_directory_listed = "$row[5]";
$podcast_directory_name = "$row[6]";
$podcast_stitcher_url = "$row[7]";
$podcast_spotify_url = "$row[8]";
$podcast_apple_url = "$row[9]";

// Current time of SQL server
$q = "SELECT CURRENT_TIMESTAMP";
$r = mysqli_query ($agg_dbc, $q);
while ($row = mysqli_fetch_array($r)) { $curr_time_sql = $row[0]; }
$curr_time_php = strtotime($curr_time_sql);

// Create nickname
$display_nickname = (($podcast_nickname != NULL) && ($podcast_nickname != '')) ? "<b>$podcast_nickname</b> " : "";
$display_nickname .= "<span class=\"note_gray\">(#$feedID)</span>";

// DEV This prevents the podcast source from being changed after 24 hours or validation
// Invalid & new feed? We can still change the URL within 24 hours
// $can_change_url = (($podcast_usable == 'failed') && ($curr_time_php - $podcast_created < (60*60*24))) ? "<label for=\"edit_podcast_url\"><span class=\"error\">Source:</span> <input type=\"url\" name=\"edit_podcast_url\" id=\"edit_podcast_url\" size=\"28\" maxlength=\"255\" value=\"$podcast_source\" class=\"error\"/></label> <i class=\"error\">Within the first 24 hours you may change the source URL, until the feed is valid.</i>
// " : "Source: <i>[ $podcast_source ]</i>";
//
// $cannot_change_url = (($podcast_usable == 'failed') && ($curr_time_php - $podcast_created >= (60*60*24))) ? '<br /><i class="error">You can no longer change the source URL. Either repair the RSS feed at this web address, <b>then click to "Update" or "Refresh" this podcast,</b> or delete this podcast project and create a new one with the correct feed source URL.</i>' : false;

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></p>";

// Heading
echo "<h3 class=\"note_blue\">Manage podcast info</h3>";
echo "<br>";

// Invalid?
echo ($podcast_usable == 'failed') ? '<h3 class="note_red">Feed source is invalid!</h3><p class="note_red"><i>The source URL needs to be an RSS feed with podcast episodes. For example, WordPress blogs might use: <b>https://example.com/feed</b>.</i></p>' : false;

// Create the form
echo "<form id=\"edit_partner_details\" class=\"edit_partner_details\" action=\"partnerpodcast_manage.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"s\" value=\"$feedID\" />
<p>$display_nickname</p>
<p><label for=\"edit_podcast_nickname\"><b class=\"editpodcastinfo\">Project Nickname:</b> <input type=\"text\" name=\"edit_podcast_nickname\" size=\"56\"";
	if (($podcast_nickname == NULL ) || ($podcast_nickname == "" )) {
		echo "placeholder=\"Nickname (optional)\"";
	} else {
		echo "value=\"$podcast_nickname\"";
	}
echo " /></label></p>
<p><i class=\"note_gray\">Nicknames are optional, you can delete the contents of the field to remove it.</i></p>
<p><label for=\"edit_podcast_url\"><b class=\"editpodcastinfo\">Source:</b> <input type=\"url\" name=\"edit_podcast_url\" id=\"edit_podcast_url\" size=\"72\" maxlength=\"255\" value=\"$podcast_source\"/></label> <label for=\"confirm_edit_source\"><input type=\"checkbox\" id=\"confirm_edit_source\" name=\"confirm_edit_source\" value=\"confirm_edit_source\"> <small class=\"note_gray\">change</small></label></p>";

// DEV This prevents the podcast source from being changed after 24 hours or validation
// echo "<p>$can_change_url<br />$cannot_change_url</p>";

echo "
<p><label for=\"edit_podcast_slug\"><b class=\"editpodcastinfo\">Slug:</b> <span class=\"note_gray\"><code class=\"inline\">https://$podcastServeDomain/</code> </span><input type=\"text\" name=\"edit_podcast_slug\" id=\"edit_podcast_slug\" size=\"48\" maxlength=\"255\" value=\"$podcast_slug\" /></label> <label for=\"confirm_edit_slug\"><input type=\"checkbox\" id=\"confirm_edit_slug\" name=\"confirm_edit_slug\" value=\"confirm_edit_slug\"> <small class=\"note_gray\">change</small></label></p>
<input type=\"hidden\" name=\"old_podcast_slug\" value=\"$podcast_slug\" />
<p><label for=\"purge_old_slug\"><input type=\"checkbox\" id=\"purge_old_slug\" name=\"purge_old_slug\" value=\"purge\"> <small class=\"note_gray\"><i>Erase any history of my most recent podcast slug. I've changed to a new slug and no longer want the old one to forward. (When you change the slug <b>once</b>, the slug just previous <b>may</b> continue to forward to this podcast, until you check this box and save. No promises, but we hope this is helpful.)</i></small></label></p>

<h3 class=\"note_blue\">Podcast Links</h3>
<p><i class=\"note_gray\">Optionally, you may list URLs where your podcast can be found on other platforms.</i></p>

<p><label for=\"edit_stitcher_url\"><b class=\"editpodcastinfo\">Stitcher Podcast URL:</b> <input type=\"url\" name=\"edit_stitcher_url\" id=\"edit_stitcher_url\" size=\"72\" maxlength=\"2048\"";
	if (($podcast_stitcher_url == NULL ) || ($podcast_stitcher_url == "")) {
		echo "placeholder=\"https://www.stitcher.com/...\"";
	} else {
		echo "value=\"$podcast_stitcher_url\"";
	}
echo " /></label></p>

<p><label for=\"edit_spotify_url\"><b class=\"editpodcastinfo\">Spotify Podcast URL:</b> <input type=\"url\" name=\"edit_spotify_url\" id=\"edit_spotify_url\" size=\"72\" maxlength=\"2048\"";
	if (($podcast_spotify_url == NULL ) || ($podcast_spotify_url == "")) {
		echo "placeholder=\"https://open.spotify.com/...\"";
	} else {
		echo "value=\"$podcast_spotify_url\"";
	}
echo " /></label></p>

<p><label for=\"edit_apple_url\"><b class=\"editpodcastinfo\">Apple Podcast URL:</b> <input type=\"url\" name=\"edit_apple_url\" id=\"edit_apple_url\" size=\"72\" maxlength=\"2048\"";
	if (($podcast_apple_url == NULL ) || ($podcast_apple_url == "")) {
		echo "placeholder=\"https://podcasts.apple.com/...\"";
	} else {
		echo "value=\"$podcast_apple_url\"";
	}
echo " /></label></p>

	<h3 class=\"note_blue\">Directory Listing</h3>
	<p><i class=\"note_gray\">Optionally, you may list your website project in the badAd Partner Blogs Directory.</i></p>
	<p><input type=\"checkbox\" name=\"edit_podcast_listing\" id=\"edit_podcast_listing\" value=\"listed\" onclick=\"showDirListOptionsBox();\"";
		if ($podcast_directory_listed == 'listed') {
			echo " checked";
		}
	echo " /><label onclick=\"showDirListOptionsLabel();\"> List in badAd Partner Directory?</label></p>

	<div id=\"directory-listing-form\" style=\"display:";
	if ($podcast_directory_listed == 'listed') {
		echo "block";
	} else {
		echo "none";
	}
	echo "\">

	<p><i class=\"note_gray\">This will appear in a listing with many other podcasts. Use the title the public knows your website for. Avoid redundant words like 'podcast' or 'show' unless they are part of the known title because the redundancy may confuser visitors looking for your simple name.</i></p>
	<p>Enter a Listed Name to list this project. Only list your podcast once, even if you have multiple projects from the same podcast source!</p>

	<p><label for=\"edit_podcast_listname\"><b class=\"editpodcastinfo\">Listed Name:</b> <input type=\"text\" name=\"edit_podcast_listname\" size=\"56\"";
		if (($podcast_directory_name == NULL ) || ($podcast_directory_name == "")) {
			echo "placeholder=\"John Smith [or] The John Smith Show\"";
		} else {
			echo "value=\"$podcast_directory_name\"";
		}
	echo " /></label></p>

	<p><i class=\"note_gray\">Listed name must be unique from all other listings!</i></p>

	</div>

<input type=\"submit\" value=\"Update badAd info\" class=\"formbutton formbutton_blue\" />
</form><br />";

// Link to see where feed can be rendered
echo "<h3>badAd podcast link: <code class=\"inline\"><a target='_blank' href='https://$podcastServeDomain/$podcast_slug'>$podcastServeDomain/$podcast_slug</a></code></h3>";
echo "<br /><hr />";

// Validation status & Podcast info
echo "<br>";
echo '<h3 class="note_violet">Podcast feed meta settings</h3>';
echo '<p class="note_gray"><i>**Readiness of this feed does not guarantee acceptance by podcast aggregators such as iTunes, Stitcher, or Spotify. Follow their respective guidelines for the information entered here.</i></p>';

echo "
	<form action='https://$podcastServeDomain/feed_refresh.php' id='refresh_feed' method='post'>
	  <input type='hidden' name='f' value='$feedID'>
		<input type='hidden' name='user_id' value='$userid'>
	  <input type='submit' title='Refressh title, episodes, etc for this podcast' value='Refresh podcast feed from source' form='refresh_feed' class='set_violet'>
	</form>
";

echo "<br>";

// Function for iTunes categories
function iTunesCat($ba_cust_cat) {
  $cat = 'None'; $val = ''; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Arts'; $val = 'Arts'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Books'; $val = 'Arts::Books'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Design'; $val = 'Arts::Design'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Fashion &amp; Beauty'; $val = 'Arts::Fashion &amp; Beauty'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Food'; $val = 'Arts::Food'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Performing Arts'; $val = 'Arts::Performing Arts'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Visual Arts'; $val = 'Arts::Visual Arts'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Business'; $val = 'Business'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Careers'; $val = 'Business::Careers'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Entrepreneurship'; $val = 'Business::Entrepreneurship'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Investing'; $val = 'Business::Investing'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Management'; $val = 'Business::Management'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Marketing'; $val = 'Business::Marketing'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Non-Profit'; $val = 'Business::Non-Profit'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Comedy'; $val = 'Comedy'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Comedy Interviews'; $val = 'Comedy::Comedy Interviews'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Improv'; $val = 'Comedy::Improv'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Stand-Up'; $val = 'Comedy::Stand-Up'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Education'; $val = 'Education'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Courses'; $val = 'Education::Courses'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- How To'; $val = 'Education::How To'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Language Learning'; $val = 'Education::Language Learning'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Self-Improvement'; $val = 'Education::Self-Improvement'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Fiction'; $val = 'Fiction'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Comedy Fiction'; $val = 'Fiction::Comedy Fiction'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Drama'; $val = 'Fiction::Drama'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Science Fiction'; $val = 'Fiction::Science Fiction'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Government'; $val = 'Government'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'History'; $val = 'History'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Health &amp; Fitness'; $val = 'Health &amp; Fitness'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Alternative Health'; $val = 'Health &amp; Fitness::Alternative Health'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Fitness'; $val = 'Health &amp; Fitness::Fitness'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Medicine'; $val = 'Health &amp; Fitness::Medicine'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Mental Health'; $val = 'Health &amp; Fitness::Mental Health'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Nutrition'; $val = 'Health &amp; Fitness::Nutrition'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Sexuality'; $val = 'Health &amp; Fitness::Sexuality'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Kids &amp; Family'; $val = 'Kids &amp; Family'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Education for Kids'; $val = 'Kids &amp; Family::Education for Kids'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Parenting'; $val = 'Kids &amp; Family::Parenting'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Pets &amp; Animals'; $val = 'Kids &amp; Family::Pets &amp; Animals'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Stories for Kids'; $val = 'Kids &amp; Family::Stories for Kids'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Leisure'; $val = 'Leisure'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Animation &amp; Manga'; $val = 'Leisure::Animation &amp; Manga'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Automotive'; $val = 'Leisure::Automotive'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Aviation'; $val = 'Leisure::Aviation'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Crafts'; $val = 'Leisure::Crafts'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Games'; $val = 'Leisure::Games'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Hobbies'; $val = 'Leisure::Hobbies'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Home &amp; Garden'; $val = 'Leisure::Home &amp; Garden'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Video Games'; $val = 'Leisure::Video Games'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Music'; $val = 'Music'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Music Commentary'; $val = 'Music::Music Commentary'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Music History'; $val = 'Music::Music History'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Music Interviews'; $val = 'Music::Music Interviews'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'News'; $val = 'News'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Business News'; $val = 'News::Business News'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Daily News'; $val = 'News::Daily News'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Entertainment News'; $val = 'News::Entertainment News'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- News Commentary'; $val = 'News::News Commentary'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Politics'; $val = 'News::Politics'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Sports News'; $val = 'News::Sports News'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Tech News'; $val = 'News::Tech News'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Religion &amp; Spirituality'; $val = 'Religion &amp; Spirituality'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Buddhism'; $val = 'Religion &amp; Spirituality::Buddhism'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Christianity'; $val = 'Religion &amp; Spirituality::Christianity'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Hinduism'; $val = 'Religion &amp; Spirituality::Hinduism'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Islam'; $val = 'Religion &amp; Spirituality::Islam'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Judiasm'; $val = 'Religion &amp; Spirituality::Judiasm'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Religion'; $val = 'Religion &amp; Spirituality::Religion'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Spirituality'; $val = 'Religion &amp; Spirituality::Spirituality '; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Science'; $val = 'Science'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Astronomy'; $val = 'Science::Astronomy'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Chemistry'; $val = 'Science::Chemistry'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Earth Sciences'; $val = 'Science::Earth Sciences'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Life Sciences'; $val = 'Science::Life Sciences'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Mathematics'; $val = 'Science::Mathematics'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Natural Sciences'; $val = 'Science::Natural Sciences'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Nature'; $val = 'Science::Nature'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Physics'; $val = 'Science::Physics'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Social Sciences'; $val = 'Science::Social Sciences'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Society &amp; Culture'; $val = 'Society &amp; Culture'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Documentary'; $val = 'Society &amp; Culture::Documentary'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Personal Journals'; $val = 'Society &amp; Culture::Personal Journals'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Philosophy'; $val = 'Society &amp; Culture::Philosophy'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Places &amp; Travel'; $val = 'Society &amp; Culture::Places &amp; Travel'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Relationships'; $val = 'Society &amp; Culture::Relationships'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Sports'; $val = 'Sports'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Baseball'; $val = 'Sports::Baseball'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Basketball'; $val = 'Sports::Basketball'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Cricket'; $val = 'Sports::Cricket'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Fantasy Sports'; $val = 'Sports::Fantasy Sports'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Football'; $val = 'Sports::Football'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Golf'; $val = 'Sports::Golf'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Hockey'; $val = 'Sports::Hockey'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Rugby'; $val = 'Sports::Rugby'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Soccer'; $val = 'Sports::Soccer'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Swimming'; $val = 'Sports::Swimming'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Tennis'; $val = 'Sports::Tennis'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Volleyball'; $val = 'Sports::Volleyball'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Wilderness'; $val = 'Sports::Wilderness'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Wrestling'; $val = 'Sports::Wrestling'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'Technology'; $val = 'Technology'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'True Crime'; $val = 'True Crime'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = 'TV &amp; Film'; $val = 'TV &amp; Film'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- After Shows'; $val = 'TV &amp; Film::After Shows'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Film History'; $val = 'TV &amp; Film::Film History'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Film Interviews'; $val = 'TV &amp; Film::Film Interviews'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- Film Reviews'; $val = 'TV &amp; Film::Film Reviews'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
  $cat = '- TV Reviews'; $val = 'TV &amp; Film::TV Reviews'; echo '<option value="'.$val.'"'; echo ($ba_cust_cat == $val) ? ' selected="selected"' : ''; echo '>'.$cat.'</option>';
}

// Iterate the meta info from the database
$q = "SELECT source, slug, status, itunes_status, override_feed_settings, title, link, description, copyright, image_url, image_title, image_link, language, lastbuilddate,
  itunes_title, itunes_type, itunes_complete, itunes_image, itunes_author, itunes_summary, itunes_owner_name, itunes_owner_email, itunes_keywords, itunes_explicit, itunes_cat1, itunes_cat2, itunes_cat3, itunes_cat4, itunes_cat5,
	ba_title, ba_link, ba_description, ba_copyright, ba_image_url, ba_image_title, ba_image_link, ba_language,
	ba_itunes_title, ba_itunes_type, ba_itunes_complete, ba_itunes_image, ba_itunes_author, ba_itunes_summary, ba_itunes_owner_name, ba_itunes_owner_email, ba_itunes_keywords, ba_itunes_explicit,
	ba_itunes_cat1, ba_itunes_cat2, ba_itunes_cat3, ba_itunes_cat4, ba_itunes_cat5, date_updated FROM feeds WHERE project_id='$feedID'";
$r = mysqli_query ($agg_dbc, $q);
if (mysqli_num_rows($r) == 1) {
  while ($row = mysqli_fetch_array($r)) {
    $f_source = "$row[0]";
		$f_slug = "$row[1]";
		$f_status = "$row[2]";
		$f_itunes_status = "$row[3]";
		$f_override_feed_settings = "$row[4]";
		$f_title = html_entity_decode("$row[5]");
		$f_link = "$row[6]";
		$f_description = html_entity_decode("$row[7]");
		$f_copyright = html_entity_decode("$row[8]");
		$f_image_url = "$row[9]";
		$f_image_title = "$row[10]";
		$f_image_link = "$row[11]";
		$f_language = "$row[12]";
		$f_lastbuilddate = "$row[13]";
		$f_itunes_title = html_entity_decode("$row[14]");
		$f_itunes_type = "$row[15]";
		$f_itunes_complete = "$row[16]";
		$f_itunes_image = "$row[17]";
		$f_itunes_author = html_entity_decode("$row[18]");
		$f_itunes_summary = html_entity_decode("$row[19]");
		$f_itunes_owner_name = html_entity_decode("$row[20]");
		$f_itunes_owner_email = "$row[21]";
		$f_itunes_keywords = "$row[22]";
		$f_itunes_explicit = "$row[23]";
		$f_itunes_cat1 = htmlentities("$row[24]");
		$f_itunes_cat2 = htmlentities("$row[25]");
		$f_itunes_cat3 = htmlentities("$row[26]");
		$f_itunes_cat4 = htmlentities("$row[27]");
		$f_itunes_cat5 = htmlentities("$row[28]");
    $f_ba_title = html_entity_decode("$row[29]");
    $f_ba_link = "$row[30]";
    $f_ba_description = html_entity_decode("$row[31]");
    $f_ba_copyright = "$row[32]";
    $f_ba_image_url = "$row[33]";
    $f_ba_image_title = "$row[34]";
    $f_ba_image_link = "$row[35]";
		$f_ba_language = "$row[36]";
    $f_ba_itunes_title = html_entity_decode("$row[37]");
    $f_ba_itunes_type = "$row[38]";
    $f_ba_itunes_complete = "$row[39]";
    $f_ba_itunes_image = "$row[40]";
    $f_ba_itunes_author = html_entity_decode("$row[41]");
    $f_ba_itunes_summary = html_entity_decode("$row[42]");
    $f_ba_itunes_owner_name = "$row[43]";
    $f_ba_itunes_owner_email = "$row[44]";
    $f_ba_itunes_keywords = "$row[45]";
    $f_ba_itunes_explicit = "$row[46]";
    $f_ba_itunes_cat1 = htmlentities("$row[47]");
    $f_ba_itunes_cat2 = htmlentities("$row[48]");
    $f_ba_itunes_cat3 = htmlentities("$row[49]");
    $f_ba_itunes_cat4 = htmlentities("$row[50]");
    $f_ba_itunes_cat5 = htmlentities("$row[51]");
		$f_date_updated = "$row[52]";

		// Convert special entities to characters
		$f_ba_title = str_replace('&#169;','©',$f_ba_title); // &copy;
		$f_ba_title = str_replace('&#8482;','™',$f_ba_title); // &trade;
		$f_ba_title = str_replace('&#174;','®',$f_ba_title); // &reg;
		$f_ba_copyright = str_replace('&#169;','©',$f_ba_copyright); // &copy;
		$f_ba_copyright = str_replace('&#8482;','™',$f_ba_copyright); // &trade;
		$f_ba_copyright = str_replace('&#174;','®',$f_ba_copyright); // &reg;
		$f_ba_description = str_replace('&#169;','©',$f_ba_description); // &copy;
		$f_ba_description = str_replace('&#8482;','™',$f_ba_description); // &trade;
		$f_ba_description = str_replace('&#174;','®',$f_ba_description); // &reg;
  } // Row
} else { // SQL podcast found
	sql_error("$q", 'agg_dbc', "sqle_136");
}

// Feed meta & edit
switch ($f_itunes_status) {
	case 'ready':
		echo "<p><span class='note_green'>This feed source is complete and ready** for iTunes. You may make </span><b class='note_badad'>Custom</b><span class='note_green'> changes and add extra elements with the form below.</span></p>";
	break;

	case 'partial':
		echo "<p><span class='note_green'>*This feed is </span><i class='note_yellow'>partially ready**</i><span class='note_green'> ready for iTunes. Use the form below to add </span><i class='note_yellow'>recommended</i><span class='note_green'> </span><b class='note_badad'>Custom</b><span class='note_green'> iTunes elements.</span></p>";
	break;

	case 'custom':
		echo "<p><span class='note_green'>This feed is now ready** for iTunes using the </span><b class='note_badad'>Custom</b><span class='note_green'> elements below. You may make changes and additions if you like.</span></p>";
	break;

	case 'absent':
		echo "<p><span class='note_red'>*This feed is not ready** for iTunes. Use the form below to add </span><b class='note_badad'>Custom</b><span class='note_red'> iTunes elements.</span></p>";
	break;

	case 'update':
		echo "<p class=\"note_violet\">You recently made changes. Refresh the podcast feed from the source to check whether this feed is ready**.</p>";
	break;

}

// Feed & custom meta
echo '<h3 class="note_badad">Custom meta settings</h3>';

// Override?
echo '<br><label for="override_feed_settings"><input type="checkbox" form="ba_custom" id="override_feed_settings" name="override_feed_settings" value="override"';
echo ($f_override_feed_settings == 'yes') ? ' checked' : false;
echo '> <small><i><b class="note_badad">Use Custom settings</b> to override the <span class="note_violet">source feed meta settings</span>. (If unchecked, Custom settings will be saved, but not used.)</i></small></label>';


// Warning/notice classes
$class_title = (($f_ba_title == '') || ($f_ba_title == 'ba-empty')) ? 'note_red' : 'note_gray';
$class_description = (($f_ba_description == '') || ($f_ba_description == 'ba-empty')) ? 'note_red' : 'note_gray';
$class_itunes_image = (($f_ba_itunes_image == '') || ($f_ba_itunes_image == 'ba-empty')) ? 'note_red' : 'note_gray';
$class_link = (($f_ba_link == '') || ($f_ba_link == 'ba-empty')) ? 'note_yellow' : 'note_gray';
$class_image_url = (($f_ba_image_url == '') || ($f_ba_image_url == 'ba-empty')) ? 'note_yellow' : 'note_gray';
$class_image_link = (($f_ba_image_link == '') || ($f_ba_image_link == 'ba-empty')) ? 'note_yellow' : 'note_gray';
$class_itunes_author = (($f_ba_itunes_author == '') || ($f_ba_itunes_author == 'ba-empty')) ? 'note_yellow' : 'note_gray';
$class_itunes_owner_name = (($f_ba_itunes_owner_name == '') || ($f_ba_itunes_owner_name == 'ba-empty')) ? 'note_yellow' : 'note_gray';
$class_itunes_owner_email = (($f_ba_itunes_owner_email == '') || ($f_ba_itunes_owner_email == 'ba-empty')) ? 'note_yellow' : 'note_gray';
$class_category = (
	   ($f_ba_itunes_cat1 == '') || ($f_ba_itunes_cat1 == 'ba-empty')
	&& ($f_ba_itunes_cat2 == '') || ($f_ba_itunes_cat2 == 'ba-empty')
	&& ($f_ba_itunes_cat3 == '') || ($f_ba_itunes_cat3 == 'ba-empty')
	&& ($f_ba_itunes_cat4 == '') || ($f_ba_itunes_cat4 == 'ba-empty')
	&& ($f_ba_itunes_cat5 == '') || ($f_ba_itunes_cat5 == 'ba-empty')) ? 'note_red' : 'note_gray';
// Feed meta empty?
$orig_f_title = ($f_title != '') ? $f_title : '<span class="'.$class_title.'">(none)*</span>';
$orig_f_link = ($f_link != '') ? '<small><a href="'.$f_link.'" target="_blank">visit link</a> <code class="inline">'.$f_link.'</code></small>' : '<span class="'.$class_link.'">(none)*</span>';
$orig_f_description = ($f_description != '') ? $f_description : '<span class="'.$class_description.'">(none)*</span>';
$orig_f_copyright = ($f_copyright != '') ? $f_copyright : '<span class="note_gray">(none)</span>';
$orig_f_image_url = ($f_image_url != '') ? '<small><a href="'.$f_image_url.'" target="_blank">view image</a> <code class="inline">'.$f_image_url.'</code></small>' : '<span class="'.$class_image_url.'">(none, needed for non-iTunes podcasts)*</span>';
$orig_f_image_title = ($f_image_title != '') ? $f_image_title : '<span class="note_gray">(none)</span>';
$orig_f_image_link = ($f_image_link != '') ? '<small><a href="'.$f_image_link.'" target="_blank">visit link</a> <code class="inline">'.$f_image_link.'</code></small>' : '<span class="'.$class_image_link.'">(none, needed for many non-iTunes podcasts)*</span>';
$orig_f_language = ($f_language != '') ? $f_language : '<span class="'.$class_language.'">(none)*</span>'; // May not ever be "(none)", but the variable must be set
$orig_f_lastbuilddate = ($f_lastbuilddate != '') ? $f_lastbuilddate : '<span class="note_gray">(none)</span>'; // Not used, perhaps display someday
$orig_f_itunes_image = ($f_itunes_image != '') ? '<small><a href="'.$f_itunes_image.'" target="_blank">view image</a> <code class="inline">'.$f_itunes_image.'</code></small>' : '<span class="'.$class_itunes_image.'">(none)*</span>';
$orig_f_itunes_author = ($f_itunes_author != '') ? $f_itunes_author : '<span class="'.$class_itunes_author.'">(none)*</span>';
// $orig_f_itunes_summary = ($f_itunes_summary != '') ? $f_itunes_summary : '<span class="note_gray">(none)</span>'; // Depreciated? keeping just in case
$orig_f_itunes_title = ($f_itunes_title != '') ? $f_itunes_title : '<span class="note_gray">(none)</span>';
$orig_f_itunes_type = ($f_itunes_type == 'serial') ? $f_itunes_type : 'episodic';
$orig_f_itunes_complete = ($f_itunes_complete == 'yes') ? $f_itunes_complete : 'not';
$orig_f_itunes_owner_name = ($f_itunes_owner_name != '') ? $f_itunes_owner_name : '<span class="'.$class_itunes_owner_name.'">(none)*</span>';
$orig_f_itunes_owner_email = ($f_itunes_owner_email != '') ? $f_itunes_owner_email : '<span class="'.$class_itunes_owner_email.'">(none)*</span>';
$orig_f_itunes_keywords = ($f_itunes_keywords != '') ? $f_itunes_keywords : '<span class="note_gray">(none)</span>';
$orig_f_itunes_cat1 = ($f_itunes_cat1 != '') ? $f_itunes_cat1 : '<span class="'.$class_category.'">(none)*</span>';
$orig_f_itunes_cat2 = ($f_itunes_cat2 != '') ? $f_itunes_cat2 : '<span class="note_gray">(none)</span>';
$orig_f_itunes_cat3 = ($f_itunes_cat3 != '') ? $f_itunes_cat3 : '<span class="note_gray">(none)</span>';
$orig_f_itunes_cat4 = ($f_itunes_cat4 != '') ? $f_itunes_cat4 : '<span class="note_gray">(none)</span>';
$orig_f_itunes_cat5 = ($f_itunes_cat5 != '') ? $f_itunes_cat5 : '<span class="note_gray">(none)</span>';

// Form
echo '<br><br>
<form action="partnerpodcast_manage.php" id="ba_custom" method="post">
<input type="hidden" name="custom_settings" value="yep" />
<input type="hidden" name="s" value="'.$feedID.'" />';

// Top save button
echo '<input type="submit" form="ba_custom" value="Save custom settings" class="formbutton_badad" />';

// Title
echo '<br><br><label for="ba_title"><b>Title:</b> <span class="original_feed">'.$orig_f_title.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_title', 'text', 'Custom Podcast Title', $f_ba_title, $check_err);
echo '</label>';

// Link
echo '<br><br><label for="ba_link"><b>Home Link:</b> <span class="original_feed">'.$orig_f_link.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_link', 'url', 'Custom link', $f_ba_link, $check_err);
echo '</label>';
echo (($f_ba_link != '') && ($f_ba_link != 'ba-empty')) ? ' <small><a href="'.$f_ba_link.'" target="_blank">visit link</a></small>' : false;

// Copyright
echo '<br><br><label for="ba_copyright"><b>&copy; Copyright statement:</b> <span class="original_feed">'.$orig_f_copyright.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_copyright', 'text', '&copy; Copyright...', $f_ba_copyright, $check_err);
echo '</label>';

// Description
echo '<br><br><label for="ba_description"><b>Description:</b> <span class="original_feed">'.$orig_f_description.'</span>
<br><span class="custom_feed">Custom: (HTML tags allowed)</span><br> ';
feed_override('ba_description', 'textarea', 'Custom podcast description...', $f_ba_description, $check_err);
echo '</label>';

// Image source
echo '<br><br><label for="ba_image_url"><b>Image source URL:</b> <span class="original_feed">'.$orig_f_image_url.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_image_url', 'url', 'http(s)://...', $f_ba_image_url, $check_err);
echo '</label>';
echo (($f_ba_image_url != '') && ($f_ba_image_url != 'ba-empty')) ? ' <small><a href="'.$f_ba_image_url.'" target="_blank">view image</a></small>' : false;

// Image link
echo '<br><br><label for="ba_image_link"><b>Image link URL:</b> <span class="original_feed">'.$orig_f_image_link.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_image_link', 'url', 'http(s)://...', $f_ba_image_link, $check_err);
echo '</label>';
echo (($f_ba_image_link != '') && ($f_ba_image_link != 'ba-empty')) ? ' <small><a href="'.$f_ba_image_link.'" target="_blank">visit link</a></small>' : false;

// Image title
echo '<br><br><label for="ba_image_title"><b>Image title (mouse hover tooltip):</b> <span class="original_feed">'.$orig_f_image_title.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_image_title', 'text', 'Custom image title', $f_ba_image_title, $check_err);
echo '</label>';

// // iTunes Summary
// echo '<br><br><label for="ba_itunes_summary"><b>iTunes Summary:</b> <span class="original_feed">'.$orig_f_itunes_summary.'</span>
// <br><span class="custom_feed">Custom:</span><br> ';
// feed_override('ba_itunes_summary', 'textarea', 'Custom iTunes summary...', $f_ba_itunes_summary, $check_err);
// echo '</label>';
// Depreciated? Keeping just in case

// iTunes Title
echo '<br><br><label for="ba_itunes_title"><b>iTunes Title:</b> (optional) <span class="original_feed">'.$orig_f_itunes_title.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_itunes_title', 'text', 'Custom iTunes-only title...', $f_ba_itunes_title, $check_err);
echo '</label>';

// iTunes image source
echo '<br><br><label for="ba_itunes_image"><b>iTunes image source URL</b> (3000x3000 JPEG or PNG <a href="https://podcasters.apple.com/support/" target="_blank">Learn more</a>)<b>:</b> <span class="original_feed">'.$orig_f_itunes_image.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_itunes_image', 'url', 'http(s)://...', $f_ba_itunes_image, $check_err);
echo '</label>';
echo (($f_ba_itunes_image != '') && ($f_ba_itunes_image != 'ba-empty')) ? ' <small><a href="'.$f_ba_itunes_image.'" target="_blank">view image</a></small>' : false;

// iTunes author
echo '<br><br><label for="ba_itunes_author"><b>iTunes Author:</b> <span class="original_feed">'.$orig_f_itunes_author.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_itunes_author', 'text', 'Custom iTunes Author', $f_ba_itunes_author, $check_err);
echo '</label>';

// iTunes owner name
echo '<br><br><label for="ba_itunes_owner_name"><b>iTunes Owner Name:</b> <span class="original_feed">'.$orig_f_itunes_owner_name.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_itunes_owner_name', 'text', 'Custom Owner Name', $f_ba_itunes_owner_name, $check_err);
echo '</label>';

// iTunes owner email
echo '<br><br><label for="ba_itunes_owner_email"><b>iTunes Owner Email:</b> <span class="original_feed">'.$orig_f_itunes_owner_email.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_itunes_owner_email', 'email', 'email@example.com', $f_ba_itunes_owner_email, $check_err);
echo '</label>';

// iTunes keywords
echo '<br><br><label for="ba_itunes_keywords"><b>iTunes Keywords:</b> (max 12, iTunes might ignore this anyway) <span class="original_feed">'.$orig_f_itunes_keywords.'</span>
<br><span class="custom_feed">Custom:</span> ';
feed_override('ba_itunes_keywords', 'text', 'comma, separated, list...', $f_ba_itunes_keywords, $check_err);
echo '</label>';

// Language
// Be pretty
$orig_f_language_display = ($orig_f_language == "en-us") ? 'English (USA)' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "af") ? 'Afrikaans' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "sq") ? 'Albanian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ar") ? 'Arabic' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "en") ? 'English' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "bn") ? 'Bengali' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "cs") ? 'Czech' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "zh") ? 'Chinese' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "nl") ? 'Dutch' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "en") ? 'English' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "fr") ? 'French' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ka") ? 'Georgian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "de") ? 'German' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "el") ? 'Greek' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "gu") ? 'Gujarati' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ha") ? 'Hausa' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "he") ? 'Hebrew' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "hi") ? 'Hindi' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ga") ? 'Irish' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "id") ? 'Indonesian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "it") ? 'Italian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ja") ? 'Japanese' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "jv") ? 'Javanese' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ko") ? 'Korean' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ml") ? 'Malay' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "mr") ? 'Marathi' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "nn") ? 'Norwegian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "fa") ? 'Persian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "pl") ? 'Polish' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "pt") ? 'Portuguese' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "pa") ? 'Punjabi' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ro") ? 'Romanian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ru") ? 'Russian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "sm") ? 'Samoan' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "sd") ? 'Sindhi' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "es") ? 'Spanish' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "su") ? 'Sundanese' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "sw") ? 'Swahili' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ty") ? 'Tahitian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ta") ? 'Tamil' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "te") ? 'Telugu' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "bo") ? 'Tibetan' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "th") ? 'Thai' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "sk") ? 'Slovak' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "sv") ? 'Swedish' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "uk") ? 'Ukrainian' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ur") ? 'Urdu' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "ug") ? 'Uyghur' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "vi") ? 'Vietnamese' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "yo") ? 'Yoruba' : $orig_f_language_display;
$orig_f_language_display = ($orig_f_language == "zu") ? 'Zulu' : $orig_f_language_display;
// Current and option selector
echo '
<br><br><label for="ba_language"><b>Language:</b> <span class="original_feed">'.$orig_f_language_display.'</span>
<br><span class="custom_feed">Custom:</span></label>
<select class="formselect" id="ba_language" name="ba_language" form="ba_custom">
	<option value="en-us" autocomplete="off"'; echo ($f_ba_language == "en-us") ? ' selected="selected"' : ''; echo '>English (USA)</option>
	<option value="af" autocomplete="off"'; echo ($f_ba_language == "af") ? ' selected="selected"' : ''; echo '>Afrikaans</option>
	<option value="sq" autocomplete="off"'; echo ($f_ba_language == "sq") ? ' selected="selected"' : ''; echo '>Albanian</option>
	<option value="ar" autocomplete="off"'; echo ($f_ba_language == "ar") ? ' selected="selected"' : ''; echo '>Arabic</option>
	<option value="en" autocomplete="off"'; echo ($f_ba_language == "en") ? ' selected="selected"' : ''; echo '>English</option>
	<option value="bn" autocomplete="off"'; echo ($f_ba_language == "bn") ? ' selected="selected"' : ''; echo '>Bengali</option>
	<option value="cs" autocomplete="off"'; echo ($f_ba_language == "cs") ? ' selected="selected"' : ''; echo '>Czech</option>
	<option value="zh" autocomplete="off"'; echo ($f_ba_language == "zh") ? ' selected="selected"' : ''; echo '>Chinese</option>
	<option value="nl" autocomplete="off"'; echo ($f_ba_language == "nl") ? ' selected="selected"' : ''; echo '>Dutch</option>
	<option value="en" autocomplete="off"'; echo ($f_ba_language == "en") ? ' selected="selected"' : ''; echo '>English</option>
	<option value="fr" autocomplete="off"'; echo ($f_ba_language == "fr") ? ' selected="selected"' : ''; echo '>French</option>
	<option value="ka" autocomplete="off"'; echo ($f_ba_language == "ka") ? ' selected="selected"' : ''; echo '>Georgian</option>
	<option value="de" autocomplete="off"'; echo ($f_ba_language == "de") ? ' selected="selected"' : ''; echo '>German</option>
	<option value="el" autocomplete="off"'; echo ($f_ba_language == "el") ? ' selected="selected"' : ''; echo '>Greek</option>
	<option value="gu" autocomplete="off"'; echo ($f_ba_language == "gu") ? ' selected="selected"' : ''; echo '>Gujarati</option>
	<option value="ha" autocomplete="off"'; echo ($f_ba_language == "ha") ? ' selected="selected"' : ''; echo '>Hausa</option>
	<option value="he" autocomplete="off"'; echo ($f_ba_language == "he") ? ' selected="selected"' : ''; echo '>Hebrew</option>
	<option value="hi" autocomplete="off"'; echo ($f_ba_language == "hi") ? ' selected="selected"' : ''; echo '>Hindi</option>
	<option value="ga" autocomplete="off"'; echo ($f_ba_language == "ga") ? ' selected="selected"' : ''; echo '>Irish</option>
	<option value="id" autocomplete="off"'; echo ($f_ba_language == "id") ? ' selected="selected"' : ''; echo '>Indonesian</option>
	<option value="it" autocomplete="off"'; echo ($f_ba_language == "it") ? ' selected="selected"' : ''; echo '>Italian</option>
	<option value="ja" autocomplete="off"'; echo ($f_ba_language == "ja") ? ' selected="selected"' : ''; echo '>Japanese</option>
	<option value="jv" autocomplete="off"'; echo ($f_ba_language == "jv") ? ' selected="selected"' : ''; echo '>Javanese</option>
	<option value="ko" autocomplete="off"'; echo ($f_ba_language == "ko") ? ' selected="selected"' : ''; echo '>Korean</option>
	<option value="ml" autocomplete="off"'; echo ($f_ba_language == "ml") ? ' selected="selected"' : ''; echo '>Malay</option>
	<option value="mr" autocomplete="off"'; echo ($f_ba_language == "mr") ? ' selected="selected"' : ''; echo '>Marathi</option>
	<option value="nn" autocomplete="off"'; echo ($f_ba_language == "nn") ? ' selected="selected"' : ''; echo '>Norwegian</option>
	<option value="fa" autocomplete="off"'; echo ($f_ba_language == "fa") ? ' selected="selected"' : ''; echo '>Persian</option>
	<option value="pl" autocomplete="off"'; echo ($f_ba_language == "pl") ? ' selected="selected"' : ''; echo '>Polish</option>
	<option value="pt" autocomplete="off"'; echo ($f_ba_language == "pt") ? ' selected="selected"' : ''; echo '>Portuguese</option>
	<option value="pa" autocomplete="off"'; echo ($f_ba_language == "pa") ? ' selected="selected"' : ''; echo '>Punjabi</option>
	<option value="ro" autocomplete="off"'; echo ($f_ba_language == "ro") ? ' selected="selected"' : ''; echo '>Romanian</option>
	<option value="ru" autocomplete="off"'; echo ($f_ba_language == "ru") ? ' selected="selected"' : ''; echo '>Russian</option>
	<option value="sm" autocomplete="off"'; echo ($f_ba_language == "sm") ? ' selected="selected"' : ''; echo '>Samoan</option>
	<option value="sd" autocomplete="off"'; echo ($f_ba_language == "sd") ? ' selected="selected"' : ''; echo '>Sindhi</option>
	<option value="es" autocomplete="off"'; echo ($f_ba_language == "es") ? ' selected="selected"' : ''; echo '>Spanish</option>
	<option value="su" autocomplete="off"'; echo ($f_ba_language == "su") ? ' selected="selected"' : ''; echo '>Sundanese</option>
	<option value="sw" autocomplete="off"'; echo ($f_ba_language == "sw") ? ' selected="selected"' : ''; echo '>Swahili</option>
	<option value="ty" autocomplete="off"'; echo ($f_ba_language == "ty") ? ' selected="selected"' : ''; echo '>Tahitian</option>
	<option value="ta" autocomplete="off"'; echo ($f_ba_language == "ta") ? ' selected="selected"' : ''; echo '>Tamil</option>
	<option value="te" autocomplete="off"'; echo ($f_ba_language == "te") ? ' selected="selected"' : ''; echo '>Telugu</option>
	<option value="bo" autocomplete="off"'; echo ($f_ba_language == "bo") ? ' selected="selected"' : ''; echo '>Tibetan</option>
	<option value="th" autocomplete="off"'; echo ($f_ba_language == "th") ? ' selected="selected"' : ''; echo '>Thai</option>
	<option value="sk" autocomplete="off"'; echo ($f_ba_language == "sk") ? ' selected="selected"' : ''; echo '>Slovak</option>
	<option value="sv" autocomplete="off"'; echo ($f_ba_language == "sv") ? ' selected="selected"' : ''; echo '>Swedish</option>
	<option value="uk" autocomplete="off"'; echo ($f_ba_language == "uk") ? ' selected="selected"' : ''; echo '>Ukrainian</option>
	<option value="ur" autocomplete="off"'; echo ($f_ba_language == "ur") ? ' selected="selected"' : ''; echo '>Urdu</option>
	<option value="ug" autocomplete="off"'; echo ($f_ba_language == "ug") ? ' selected="selected"' : ''; echo '>Uyghur</option>
	<option value="vi" autocomplete="off"'; echo ($f_ba_language == "vi") ? ' selected="selected"' : ''; echo '>Vietnamese</option>
	<option value="yo" autocomplete="off"'; echo ($f_ba_language == "yo") ? ' selected="selected"' : ''; echo '>Yoruba</option>
	<option value="zu" autocomplete="off"'; echo ($f_ba_language == "zu") ? ' selected="selected"' : ''; echo '>Zulu</option>
</select>
';

// Categories
echo '<br><br><b>Categories:</b>
<br>
<p><i>Tip: Parent categories will automatically be included with subcategories.</i></p>';
echo '<label for="ba_cust_cat1">Category 1: <span class="original_feed">'.$orig_f_itunes_cat1.'</span></label><br><br>
<span class="custom_feed">Custom 1:</span> <select class="formselect" id="ba_cust_cat1" name="ba_cust_cat1" form="ba_custom">';
iTunesCat($f_ba_itunes_cat1);
echo '</select><br><br>';
echo '<label for="ba_cust_cat2">Category 2: <span class="original_feed">'.$orig_f_itunes_cat2.'</span></label><br><br>
<span class="custom_feed">Custom 2:</span> <select class="formselect" id="ba_cust_cat2" name="ba_cust_cat2" form="ba_custom">';
iTunesCat($f_ba_itunes_cat2);
echo '</select><br><br>';
echo '<label for="ba_cust_cat3">Category 3: <span class="original_feed">'.$orig_f_itunes_cat3.'</span></label><br><br>
<span class="custom_feed">Custom 3:</span> <select class="formselect" id="ba_cust_cat3" name="ba_cust_cat3" form="ba_custom">';
iTunesCat($f_ba_itunes_cat3);
echo '</select><br><br>';
echo '<label for="ba_cust_cat4">Category 4: <span class="original_feed">'.$orig_f_itunes_cat4.'</span></label><br><br>
<span class="custom_feed">Custom 4:</span> <select class="formselect" id="ba_cust_cat4" name="ba_cust_cat4" form="ba_custom">';
iTunesCat($f_ba_itunes_cat4);
echo '</select><br><br>';
echo '<label for="ba_cust_cat5">Category 5: <span class="original_feed">'.$orig_f_itunes_cat5.'</span></label><br><br>
<span class="custom_feed">Custom 5:</span> <select class="formselect" id="ba_cust_cat5" name="ba_cust_cat5" form="ba_custom">';
iTunesCat($f_ba_itunes_cat5);
echo '</select>
</td>';

// iTunes explicit
echo '<br><br><label for="ba_itunes_explicit"><b>iTunes "explicit" content setting:</b> <span class="original_feed">';
echo ($f_itunes_explicit == 'false') ? 'clean content' : 'explicit content (default if not in feed source)';
echo '</span><br><span class="custom_feed">Custom:</span>
<br><label for="explicit-true"><input type="radio" id="explicit-true" name="ba_itunes_explicit" value="true" form="ba_custom"'; echo ($f_ba_itunes_explicit == "true") ? ' checked' : ''; echo '> explicit content</label>
<br><label for="explicit-false"><input type="radio" id="explicit-false" name="ba_itunes_explicit" value="false" form="ba_custom"'; echo ($f_ba_itunes_explicit == "false") ? ' checked' : ''; echo '> clean</label>
';

// iTunes type
echo '<br><br><label for="ba_itunes_type"><b>iTunes series type:</b> <span class="original_feed">';
echo ($f_itunes_complete == 'serial') ? 'serial (oldest episodes first)' : 'episodic (newest episodes first, iTunes default)';
echo '</span><br><span class="custom_feed">Custom:</span>
<br><label for="type-episodic"><input type="radio" id="type-episodic" name="ba_itunes_type" value="episodic" form="ba_custom"'; echo ($f_ba_itunes_type == "episodic") ? ' checked' : ''; echo '> episodic (newest episodes first, iTunes default)</label>
<br><label for="type-serial"><input type="radio" id="type-serial" name="ba_itunes_type" value="serial" form="ba_custom"'; echo ($f_ba_itunes_type == "serial") ? ' checked' : ''; echo '> serial (oldest episodes first)</label>
';

// iTunes complete
echo '<br><br><label for="ba_itunes_complete"><b>iTunes series "complete" status:</b> <span class="original_feed">';
echo ($f_itunes_complete == 'yes') ? 'Complete (no new eipsides)' : 'Ongoing (iTunes default)';
echo '</span><br><span class="custom_feed">Custom:</span>
<br><label for="complete-not"><input type="radio" id="complete-not" name="ba_itunes_complete" value="not" form="ba_custom"'; echo ($f_ba_itunes_complete == "not") ? ' checked' : ''; echo '> Ongoing (iTunes default)</label>
<br><label for="complete-yes"><input type="radio" id="complete-yes" name="ba_itunes_complete" value="yes" form="ba_custom"'; echo ($f_ba_itunes_complete == "yes") ? ' checked' : ''; echo '> Complete (no new eipsides)</label>
';

// Bottom save button
echo '<br><br><input type="submit" form="ba_custom" value="Save custom settings" class="formbutton_badad" />';

// Close form
echo '</form>';

// Include the HTML footer
include ('./includes/footer.html');
?>
