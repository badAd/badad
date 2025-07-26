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

// Check for form submission and set the $siteID variable
	// First visit
	if ((isset($_POST['s'])) && (preg_match('/[^a-zA-Z0-9]$/i', $_POST['s']))) {$IP = get_ip_addr(); script_kiddy('sk_71', '_POST s', $_POST['s'], $IP);}
	if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['s']))
	&& (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
	$siteID = preg_replace("/[^0-9]/","", $_POST['s']);

	// Include the header file
	$page_title = "Partner Site - Edit Nickname :: $siteTitle";
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
		var y = document.getElementById("edit_website_listing");
		if (y.checked === false) {
			y.checked = true;
		} else {
			y.checked = false;
		}
	}
	</script>
	<?php

} else {
	header("Location: partner.php");
	exit(); // Quit the script
}



if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['edit_website_nickname'])) && (isset($_POST['s']))
&& (isset($_POST['s'])) && (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {

	// Process form
	$edit_website_nickname = $_POST['edit_website_nickname'];
	$siteID = preg_replace("/[^0-9]/","", $_POST['s']);

	// Validate the nickname
	if ($edit_website_nickname == "") {
		$q = "UPDATE partnersites SET nickname=NULL WHERE id='$siteID'";
	} else {
	  function is_valid_domain_nickname($validating_nickname) {
	    return (preg_match('/^[A-Z0-9 \'\/&,-]{1,255}$/i', $validating_nickname));
	  }
	  if (!is_valid_domain_nickname($_POST['edit_website_nickname'])) {
	    echo "<p class=\"note_red\">The nickname may be only letters and numbers and - \' / , &</p>";
	    return;
	  } else {
			$regex_replace = "/[^0-9a-zA-Z_ \'\/&,-]/";
			$edit_website_nickname = preg_replace($regex_replace,"", $_POST['edit_website_nickname']);
	    $edit_website_nickname = mysqli_real_escape_string($srv_dbc, $edit_website_nickname);
			$q = "UPDATE partnersites SET nickname='$edit_website_nickname' WHERE id='$siteID'";
	  }
	}
	// Update the database with the nickname
	$r = mysqli_query ($srv_dbc, $q);
	if (mysqli_affected_rows($srv_dbc) == 1) { // Changes
		$update_nickname_success = true;
	} elseif (!$r) { // Error
		sql_error($q, 'srv_dbc', "sqle_157");
	}

	// Directory Listing
	if ((isset($_POST['edit_website_listing'])) && ($_POST['edit_website_listing'] == 'listed')
	&& (isset($_POST['edit_website_listname'])) && (isset($_POST['edit_website_listurl']))) {

		// Validate the Listed Name
		if (isset($_POST['edit_website_listname'])) {
			$website_listname = $_POST['edit_website_listname'];
			function is_valid_website_listname($validating_listname) {
				return (preg_match('/^[A-Z0-9 \'\/&,-.:?!#$%@|]{0,255}$/i', $validating_listname));
			}
			if (!is_valid_website_listname($website_listname)) {
				echo "<p class=\"note_red\">The Listed Name may only use letters and numbers and - \' / . : ? ! # $ % @ | , &</p>";
				$listing_update = false;
			} else {
				$regex_replace = "/[^0-9a-zA-Z_ \'\/&,-.:?!#$%@|]/";
				$website_listname = preg_replace($regex_replace,"", $_POST['edit_website_listname']);
				if ($website_listname == '') {
					echo "<p class=\"note_red\">To be listed, you must enter a Listed Name using only letters and numbers and - \' / . : ? ! # $ % @ | , &</p>";
					$listing_update = false;
				} else {
					$new_website_listname = mysqli_real_escape_string($srv_dbc, $website_listname);
				}
			}
		}

		// Validate the Listed URL
		if (isset($_POST['edit_website_listurl'])) {
			$website_listurl = $_POST['edit_website_listurl'];
			if ((!filter_var($website_listurl, FILTER_VALIDATE_URL)) || (strlen($website_listurl) > 2048)) {
				echo "<p class=\"note_red\">The source must be a real URL, such as http://example.com/some/url</p>";
				$listing_update = false;
			} else {
				$new_website_listurl = mysqli_real_escape_string($srv_dbc, $website_listurl);
			}
		}

		// Check for domain in Listed URL
		$q = "SELECT domain, directory_name, directory_url FROM partnersites WHERE id='$siteID'";
		$r = mysqli_query ($srv_dbc, $q);
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$site_domain = "$row[0]";
		if (!str_contains($website_listurl, $site_domain)) {
			echo "<p class=\"note_red\">The Listed URL must contain the domain ($site_domain) for this project.</p>";
			$listing_update = false;
		}

		// Check for duplicate Listed URL
		$q = "SELECT id, nickname, domain, user_id FROM partnersites WHERE directory_url='$new_website_listurl' AND directory_listed='listed' AND NOT id='$siteID'";
		$r = mysqli_query ($srv_dbc, $q);
		$rows = mysqli_num_rows($r);
		if ($rows > 0) {
			$row = mysqli_fetch_array($r, MYSQLI_NUM);
			$dup_id = "$row[0]";
			$dup_nickname = "$row[1]";
			$dup_domain = "$row[2]";
			$dup_user_id = "$row[3]";
			echo ($dup_user_id == $userid) ? "<p class=\"note_red\">That URL is already listed for <i>$dup_nickname (#$dup_id, $dup_domain)</i>. Uncheck the Directory Listing option for that project before using the same URL here.</p>" : "<p class=\"note_red\">Can't list that URL because it is already in use.</p>";
			$listing_update = false;
		}

		// Check for duplicate Listed Name
		$q = "SELECT id, nickname, domain FROM partnersites WHERE directory_name='$new_website_listname' AND directory_listed='listed' AND NOT id='$siteID'";
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
			$q = "UPDATE partnersites SET directory_name='$new_website_listname', directory_url='$new_website_listurl', directory_listed='listed' WHERE id='$siteID'";
			$r = mysqli_query ($srv_dbc, $q);
			if (mysqli_affected_rows($srv_dbc) == 1) { // Changes
				echo "<p class=\"note_green\">Site successfully saved &amp; listed!</p>";
			} elseif (!$r) { // Error
				sql_error($q, 'srv_dbc', "sqle_158");
			} elseif (mysqli_affected_rows($srv_dbc) == 0) {
				echo "<p class=\"note_green\">Site listed, no changes!</p>";
			}

		} // Database UPDATE

	} else { // Directory Listing
		$q = "UPDATE partnersites SET directory_listed='no' WHERE id='$siteID'";
		$r = mysqli_query ($srv_dbc, $q);
		if (mysqli_affected_rows($srv_dbc) == 1) { // Changes
			$update_delisting_success = true;
		} elseif (!$r) { // Error
			sql_error($q, 'srv_dbc', "sqle_159");
		}

	} // Directory de-listing

} // Saving form

// Updated?
if ((isset($update_nickname_success)) && ($update_nickname_success == true)) {
	echo "<p class=\"note_green\">Nickname updated!</p>";
}
if ((isset($update_delisting_success)) && ($update_delisting_success == true)) {
	echo "<p class=\"note_green\">Site successfully de-listed!</p>";
}

// Query the Partner Site Domain and Nickname
$q = "SELECT domain, nickname, directory_listed, directory_name, directory_url FROM partnersites WHERE id='$siteID'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$site_domain = "$row[0]";
$site_nickname = "$row[1]";
$site_directory_listed = "$row[2]";
$site_directory_name = "$row[3]";
$site_directory_url = "$row[4]";

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></p>";

// Heading
echo "<h3>Edit nickname</h3>";
echo "<br>";

// Create the form
echo "<form id=\"edit_partner_nickname\" class=\"edit_partner_nickname\" action=\"partnersite_edit_info.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"s\" value=\"$siteID\" />
<p>Project: <b>$site_domain</b></p>
<p><label for=\"edit_website_nickname\">Nickname: <input type=\"text\" name=\"edit_website_nickname\" size=\"48\"";
	if (($site_nickname == NULL ) || ($site_nickname == "" )) {
		echo "placeholder=\"Nickname (optional)\"";
	} else {
		echo "value=\"$site_nickname\"";
	}
echo " /></label></p>
<p><i class=\"note_gray\">Nicknames are optional, you can delete the contents of the field to remove it.</i></p>

	<h3>Directory Listing</h3>
	<p>Optionally, you may list your website project in the badAd Partner Blogs Directory.</p>
	<p><input type=\"checkbox\" name=\"edit_website_listing\" id=\"edit_website_listing\" value=\"listed\" onclick=\"showDirListOptionsBox();\"";
		if ($site_directory_listed == 'listed') {
			echo " checked";
		}
	echo " /><label onclick=\"showDirListOptionsLabel();\"> List in badAd Partner Directory?</label></p>

	<div id=\"directory-listing-form\" style=\"display:";
	if ($site_directory_listed == 'listed') {
		echo "block";
	} else {
		echo "none";
	}
	echo "\">

	<p>Fill in the optional information to list this project. Only list your blog once, even if you have multiple projects for the same site!</p>
	<p><i class=\"note_gray\">This will appear in a listing with many other websites. Use the title the public knows your website for. Avoid redundant words like 'blog' or 'site' unless necessary because the redundancy may confuser visitors looking for your simple name.</i></p>

	<p><label for=\"edit_website_listname\">Listed Name: <input type=\"text\" name=\"edit_website_listname\" size=\"18\"";
		if (($site_directory_name == NULL ) || ($site_directory_name == "")) {
			echo "placeholder=\"John Smith's Everything\"";
		} else {
			echo "value=\"$site_directory_name\"";
		}
	echo " /></label></p>

	<p><i class=\"note_gray\">Listed name and URL must be unique from all other listings!</i></p>

	<p><label for=\"edit_website_listurl\">Listed URL: <input type=\"url\" name=\"edit_website_listurl\" id=\"edit_website_listurl\" size=\"32\" maxlength=\"2048\"";
		if (($site_directory_url == NULL ) || ($site_directory_url == "")) {
			echo "placeholder=\"https://example.com/my_blog/something\"";
		} else {
			echo "value=\"$site_directory_url\"";
		}
	echo " /></label></p>

	</div>

<br /><br />
<input type=\"submit\" value=\"Update\" class=\"formbutton\" />
</form><br /><hr />";

// Include the HTML footer
include ('./includes/footer.html');
?>
