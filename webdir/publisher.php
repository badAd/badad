<?php

// Configs
require_once ('includes/config.inc.php');
require_once (MYSQL);
require_once ('includes/config_agg.inc.php');
require_once (MYSQL_AGG);

// Login check
include_once ('includes/login_check.inc.php');

// If the user isn't logged in, redirect them
if (!isset($_SESSION['user_id'])) {
	header("Location: index.php");
	exit(); // Quit the script
} else {
	$userid = $_SESSION['user_id'];
}

// If the user doens't have editing privileges, redirect
if ((!$_SESSION['user_is_admin']) && (!$_SESSION['user_is_supervisor']) && (!$_SESSION['user_is_publisher'])) {
	header("Location: index.php");
	exit(); // Quit the script
}

// Include the header file
$page_title = "Final Publishing :: $siteTitle";
include ('includes/header.html');

// Process the approval POST
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['publish_approve']))
&& (filter_var($_POST['publish_approve'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {

	// Podcast ad ID
	$ir_ad_id = preg_replace("/[^0-9]/","", $_POST['publish_approve']);

	// List the podcast ad
	$adID = $ir_ad_id;
	include ('includes/list_pod_ad.inc.php');

	if ((isset($pod_ad_listed)) && ($pod_ad_listed == true)) {

		// Get the starting date from pod_ads & ads
		$q = "SELECT date_starts FROM pod_ads WHERE ad_id='$adID'";
		$row = mysqli_query($dbc, $q);
		$ad_item = mysqli_fetch_array($row);
			$pod_ad_date_starts = "$ad_item[0]";
		$q = "SELECT date_starts, date_expires, ad_weekslong FROM ads WHERE id='$adID'";
		$row = mysqli_query($dbc, $q);
		$ad_item = mysqli_fetch_array($row);
			$ad_date_starts = "$ad_item[0]";
			$ad_date_expires = "$ad_item[1]";
			$ad_ad_weekslong = "$ad_item[2]";

		// Get our new date_starts
		$new_date_starts = ($pod_ad_date_starts > $ad_date_starts) ? $pod_ad_date_starts : $ad_date_starts;
		$new_date_starts_epoch = strtotime($new_date_starts);
		$new_date_expires_epoch = $new_date_starts_epoch  + (60 * 60 * 24 * 7 * $ad_ad_weekslong);
		$new_date_expires = date("Y-m-d H:i:s", substr($new_date_expires_epoch, 0, 10));

		// Without errors, update and redirect
		$q = "UPDATE pod_ads SET status='live', date_starts='$new_date_starts', date_expires='$new_date_expires', date_published=NOW(), date_modified=NOW() WHERE status='recorded' AND ad_id='$ir_ad_id'";
		$r = mysqli_query ($dbc, $q);
		if (mysqli_affected_rows($dbc) != 1) {
			sql_error($q, 'dbc', "sqle_171");
		}
		$q = "UPDATE ads SET date_starts='$new_date_starts', date_expires='$new_date_expires' WHERE id='$ir_ad_id'";
		$r = mysqli_query ($dbc, $q);
		if ($r) {
			echo '<p class="note_green">Ad published and live!</p>';

		} else {
			sql_error($q, 'dbc', "sqle_152");
		}
	} else {
		echo '<p class="note_red">Unknown error publishing ad. Not live and still in queue.</p>';
	}

// Process the rejection POST
} elseif (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['publish_reject'])) && (filter_var($_POST['publish_reject'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {

	// Podcast ad ID
	$ir_ad_id = preg_replace("/[^0-9]/","", $_POST['publish_reject']);


	// Without errors, update and redirect
	$q = "UPDATE pod_ads SET status='rerecord', date_modified=NOW() WHERE status='recorded' AND ad_id='$ir_ad_id'";
	$r = mysqli_query ($dbc, $q);

	if (mysqli_affected_rows($dbc) == 1) {
		// Send an email to team
		// Get the new user's ID from the database for the email
		$q = "SELECT id FROM users WHERE email='$email'";
		$r = mysqli_query ($dbc, $q);
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$userid = "$row[0]";

		// Send an email
		$payloadlink = "https://$siteDomain/voice.php";
		$emailUserID = $userid;
		$canned_email = "podad_rerecord"; // Slug from the "pantry" table to select the canned email
		$subject_suffix = ": $siteTitle"; // Appends the canned email Subject
		$payload_content = "<p><a href=\"$payloadlink\">View</a></p>"; // Middle of the Body, after the canned email and before the salutation
		$footer_link_content = ""; // After the salutation and before the unsubscribe footer
		include ('./includes/sendusrmail.inc.php');

		echo '<p class="note_yellow">Ad rejected and queued for re-recording!</p>';

	} else {
		sql_error($q, 'dbc', "sqle_153");
	}

} // Approve/reject POST

// Publishing work
echo "<h3>Final Publishing</h3>";

// List pending projects
$q = "SELECT ad_id, publisher_user, date_modified FROM pod_ads WHERE status='recorded' AND (publisher_user=0 OR publisher_user='$userid') ORDER BY date_recorded ASC";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
// How many items waiting
echo "<p><b>Recorded &amp; in queue for publication: $rows</b></p>";
if ($rows > 0) {
	echo '<table><tbody>';
	$listed = 1;
	while ($row = mysqli_fetch_array($r)) {
		$ir_ad_id = "$row[0]";
		$ir_publisher_user = "$row[1]";
		$ir_date_modified = "$row[2]";
		$trclass = ($ir_publisher_user == $userid) ? 'class ="oversight"' : '' ;
		echo "<tr $trclass><td>$ir_ad_id</td><td><i class='note_gray'>$ir_date_modified</i></td><td><a href='https://$siteDomain/publisher_approve.php?pa=$ir_ad_id'>Review for publication &rarr;</a></td></tr>";

		// New table to separate top item
		echo ($listed == 1) ? "</tbody></table><br /><br /><table><tbody>" : false;

		// Just one hundred items
		$listed ++;
		if ($listed == 100) {break;}
	}
	echo '</tbody></table><br />';
} else {
	echo '<p>No work here.</p>';
}

// All work
echo "<h3>All Publishing Tasks</h3>";

// List all non-published projects
$q = "SELECT ad_id, status, customer_user, editor_user, voice_user, publisher_user, date_modified FROM pod_ads WHERE NOT status='pending' AND NOT status='live' AND NOT status='expired' AND NOT status='dead' ORDER BY date_recorded ASC LIMIT 1000";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
// How many items waiting
echo "<p><b>Ads in queue: $rows</b></p>";
if ($rows > 0) {
	echo '<table><tbody>';
	while ($row = mysqli_fetch_array($r)) {
		$pub_ad_id = "$row[0]";
		$pub_status = "$row[1]";
		$pub_customer_user = "$row[2]";
		$pub_editor_user = "$row[3]";
		$pub_voice_user = "$row[4]";
		$pub_publisher_user = "$row[5]";
		$pub_date_modified = "$row[6]";
		$trclass = ($pub_publisher_user == $userid) ? 'class ="oversight"' : '' ;

		// Customer name
		$q = "SELECT name FROM users WHERE id='$pub_customer_user'";
		$r = mysqli_query ($dbc, $q);
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$name_customer_user = "$row[0]";
		// Editor name
		$q = "SELECT name FROM users WHERE id='$pub_editor_user'";
		$r = mysqli_query ($dbc, $q);
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$name_editor_user = "$row[0]";
		// Voice name
		$q = "SELECT name FROM users WHERE id='$pub_voice_user'";
		$r = mysqli_query ($dbc, $q);
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$name_voice_user = "$row[0]";
		// Publisher name
		$q = "SELECT name FROM users WHERE id='$pub_publisher_user'";
		$r = mysqli_query ($dbc, $q);
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$name_publisher_user = "$row[0]";

		// Action link
		switch ($pub_status) {
			case 'inreview':
				$status_message = "Submitted";
				$action_link = "<a href='https://$siteDomain/editor_review.php?pa=$pub_ad_id'>Review &amp; edit &rarr;</a>";
			break;
			case 'resubmitted':
				$status_message = "Re-submitted";
				$action_link = "<a href='https://$siteDomain/editor_review.php?pa=$pub_ad_id'>Review &amp; edit &rarr;</a>";
			break;
			case 'approved':
				$status_message = "Approved for recording";
				$action_link = "<a href='https://$siteDomain/voice_record.php?pa=$pub_ad_id'>Record &rarr;</a>";
			break;
			case 'rerecord':
				$status_message = "Needs re-recording";
				$action_link = "<a href='https://$siteDomain/voice_record.php?pa=$pub_ad_id'>Record &rarr;</a>";
			break;
			case 'recorded':
				$status_message = "Recorded for publication";
				$action_link = "<a href='https://$siteDomain/publisher_approve.php?pa=$pub_ad_id'>Review for publication &rarr;</a>";
			break;
		}

		echo "<tr $trclass><td>$pub_ad_id</td><td><i class='note_gray'>$pub_date_modified</i></td><td>$status_message</td><td>$action_link</td></tr>";

	}
	echo '</tbody></table><br />';
} else {
	echo '<p>No work here.</p>';
}

// Include the HTML footer
include ('./includes/footer.html');
?>
