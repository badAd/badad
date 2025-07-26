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
if ((!$_SESSION['user_is_admin']) && (!$_SESSION['user_is_supervisor']) && (!$_SESSION['user_is_publisher']) && (!$_SESSION['user_is_editorvoice']) && (!$_SESSION['user_is_voice'])) {
	header("Location: index.php");
	exit(); // Quit the script
}

// Include the header file
$page_title = "Voice Recording :: $siteTitle";
include ('./includes/header.html');

// Uploaded?
if ((isset($_POST['ad_review'])) && ($_POST['ad_review'] == 'recorded')) {
  echo '<p class="note_green">Audio uploaded!</p>';
}

// Start the page
echo "<h3>Voice Recording</h3>";

// List pending projects
$q = "SELECT ad_id, date_modified FROM pod_ads WHERE (status='approved' OR status='rerecord') AND (voice_user=0 OR voice_user='$userid') ORDER BY FIELD(status, 'rerecord', 'approved') ASC, date_modified ASC";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
// How many items waiting
echo "<p><b>Ads in queue: $rows</b></p>";
if ($rows > 0) {
	echo '<table><tbody>';
	while ($row = mysqli_fetch_array($r)) {
		$ir_ad_id = "$row[0]";
		$ir_date_modified = "$row[1]";
		echo "<tr><td>$ir_ad_id</td><td><i class='note_gray'>$ir_date_modified</i></td><td><a href='https://$siteDomain/voice_record.php?pa=$ir_ad_id'>Record &rarr;</a></td></tr>";

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
