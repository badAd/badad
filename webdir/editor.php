<?php

// Config
require ('./includes/config.inc.php');
require_once (MYSQL);

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
if ((!$_SESSION['user_is_admin']) && (!$_SESSION['user_is_supervisor']) && (!$_SESSION['user_is_publisher']) && (!$_SESSION['user_is_editorvoice']) && (!$_SESSION['user_is_editor'])) {
	header("Location: index.php");
	exit(); // Quit the script
}

// Include the header file
$page_title = "Editing & Review :: $siteTitle";
include ('./includes/header.html');

// Process any POST to approve as-is
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['ad_id'])) && (filter_var($_POST['ad_id'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {

	// Podcast ad ID
	$ir_ad_id = preg_replace("/[^0-9]/","", $_POST['ad_id']);

	// Get current situation
	$q = "SELECT original_manuscript, resubmitted_manuscript FROM pod_ads WHERE ad_id='$ir_ad_id' AND (status='inreview' OR ((status='resubmitted') AND editor_user='$userid'))";
	$r = mysqli_query ($dbc, $q);
	if ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
		$ir_original_manuscript = "$row[0]";
		$ir_resubmitted_manuscript = "$row[1]";

		$approve_sql_statement = ($ir_resubmitted_manuscript == '') ? 'approved_manuscript=original_manuscript' : 'approved_manuscript=resubmitted_manuscript' ;

	} else {
		sql_error($q, 'dbc', "sqle_144");
	}

	// Assign current user for oversight
	$q = "UPDATE pod_ads SET status='approved', $approve_sql_statement, date_approved=NOW(), date_modified=NOW() WHERE ad_id='$ir_ad_id' AND editor_user='$userid'";
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
		$canned_email = "podad_approved"; // Slug from the "pantry" table to select the canned email
		$subject_suffix = ": $siteTitle"; // Appends the canned email Subject
		$payload_content = "<p><a href=\"$payloadlink\">View recordable manuscripts in \"Voice Recording\"</a>.</p>"; // Middle of the Body, after the canned email and before the salutation
		$footer_link_content = ""; // After the salutation and before the unsubscribe footer
		include ('./includes/sendusrmail.inc.php');

		// Start the page
		echo '<p class="note_green">Manuscript approved and delivered for voice recording!</p>';

	} else {
		sql_error($q, 'dbc', "sqle_145");
	}

} elseif (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['ad_review'])) && ($_POST['ad_review'] == 'edited')) {
	echo '<p class="note_blue">Manuscript edited and waiting for customer approval!</p>';
}

// Start the page
echo "<h3>Editing & Review</h3>";

// List pending projects
$q = "SELECT ad_id, date_modified FROM pod_ads WHERE (status='inreview' OR status='resubmitted') AND (editor_user=0 OR editor_user='$userid') ORDER BY FIELD(status, 'resubmitted', 'inreview') ASC, date_modified ASC";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
// How many items waiting
echo "<p><b>Ads in queue: $rows</b></p>";
if ($rows > 0) {
	echo '<table><tbody>';
	while ($row = mysqli_fetch_array($r)) {
		$ir_ad_id = "$row[0]";
		$ir_date_modified = "$row[1]";
		echo "<tr><td>$ir_ad_id</td><td><i class='note_gray'>$ir_date_modified</i></td><td><a href='https://$siteDomain/editor_review.php?pa=$ir_ad_id'>Review &rarr;</a></td></tr>";

		// Just one item
		break;
	}
	echo '</tbody></table>';
} else {
	echo '<p>No work here.</p>';
}

// Include the HTML footer
include ('./includes/footer.html');
?>
