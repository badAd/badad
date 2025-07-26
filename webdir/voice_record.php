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

// Process the POST
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['ad_id'])) && (filter_var($_POST['ad_id'], FILTER_VALIDATE_INT, array('min_range' => 1)))
&& (isset($_FILES['recording_upload'])) ) {

	// Podcast ad ID
	$ir_ad_id = preg_replace("/[^0-9]/","", $_POST['ad_id']);

	// Uploaded recording
	// Was a file uploaded?
	if ($_FILES['recording_upload']['size'] == 0) {
		echo '<p class="note_red">No file selected</p>';
	} else {

		// Set some basics
		$upload_dir = $badadsrvdir.'media/';
		$file_name = basename($_FILES['recording_upload']['name']);
		$file_path_dest = $upload_dir.$file_name;
		$temp_file = $_FILES['recording_upload']['tmp_name'];
		$file_mime = mime_content_type($temp_file);
    $file_extension = strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
    $file_basename = basename($file_name,'.'.$file_extension); // Strip off the extension
    $file_name = $file_basename.'.'.$file_extension; // Reassign extension with no caps
    $file_size = $_FILES['recording_upload']['size'];
    $size_limit = 1000000; // 1MB
		// For storing registration errors:
		$errors = '';

		// File requirements
		if (($file_extension == 'mp3') && ($file_mime == 'audio/mpeg')) {

			if ($file_size <= $size_limit) {

				// Confirm file name
				$q = "SELECT id, ad_id FROM pod_ads WHERE ad_id='$ir_ad_id' AND (status='approved' OR status='rerecord') AND voice_user='$userid'";
				$r = mysqli_query ($dbc, $q);
				if ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
					$pod_ad_id = "$row[0]";
					$ad_id = "$row[1]";
				} else {
					sql_error($q, 'dbc', "sqle_148");
				}
				if ($file_name == "ba-{$pod_ad_id}.mp3") {

					// DEV script to get duration
					$file_duration = 'empty';

					// Upload the file and check in one command
					if (move_uploaded_file($temp_file, $file_path_dest)) {

						// Successfully moved file
						$upload_success = true;

					} else {
						$errors = "File could not be saved, reason unknown!";
					} // End upload file move test

				} else {
					$errors = "Wrong name! Use: ba-{$pod_ad_id}.mp3.";
				} // end file name test

			} else {
				$errors = 'Too big! Limit 1 MB.';
			} // end format test

		} else {
			$errors = 'Not mp3!';
		} // end format test

	} // End empty file check

	// Add to badadfeeddb database
	if (($upload_success == true) && ($errors == '')) {

		// Without errors, update and redirect
		$q = "UPDATE pod_ads SET status='recorded', length='$file_size', duration='$file_duration', date_recorded=NOW(), date_modified=NOW() WHERE (status='approved' OR status='rerecord') AND ad_id='$ir_ad_id'";
		$r = mysqli_query ($dbc, $q);

		if (mysqli_affected_rows($dbc) == 1) {
			// Send an email to team
			// Get the new user's ID from the database for the email
			$q = "SELECT id FROM users WHERE email='$email'";
			$r = mysqli_query ($dbc, $q);
			$row = mysqli_fetch_array($r, MYSQLI_NUM);
			$userid = "$row[0]";

			// Send an email
			$payloadlink = "https://$siteDomain/publisher.php";
			$emailUserID = $userid;
			$canned_email = "podad_recorded"; // Slug from the "pantry" table to select the canned email
			$subject_suffix = ": $siteTitle"; // Appends the canned email Subject
			$payload_content = "<p><a href=\"$payloadlink\">View</a></p>"; // Middle of the Body, after the canned email and before the salutation
			$footer_link_content = ""; // After the salutation and before the unsubscribe footer
			include ('./includes/sendusrmail.inc.php');

			// Send back good news
			echo "
			<form id=\"jsGoForm\" action=\"https://$siteDomain/voice.php\" method=\"post\">
				<input type=\"hidden\" name=\"ad_review\" value=\"recorded\">
			</form>
			<script type=\"text/javascript\">
					document.getElementById('jsGoForm').submit();
			</script>";

		} else {
			sql_error($q, 'dbc', "sqle_150");
		}


	} else {
		// Cant output before header() functions
		$show_errors = true;
	}

// Process the GET
} elseif (($_SERVER['REQUEST_METHOD'] === 'GET') && (isset($_GET['pa'])) && (filter_var($_GET['pa'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {

	// Podcast ad ID
	$ir_ad_id = preg_replace("/[^0-9]/","", $_GET['pa']);

} else {
	header("Location: index.php");
	exit(); // Quit the script
}

// Assure current user for oversight
$q = "UPDATE pod_ads SET voice_user='$userid', date_modified=NOW() WHERE ad_id='$ir_ad_id' AND (status='approved' OR status='rerecord') AND (voice_user=0 OR voice_user='$userid')";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 0) {
	header("Location: editor.php");
	exit(); // Quit the script
} elseif (!$r) {
	sql_error($q, 'dbc', "sqle_151");
}

// Load the project for review
$q =  ((!$_SESSION['user_is_admin']) && (!$_SESSION['user_is_supervisor']) && (!$_SESSION['user_is_publisher']))
   ? "SELECT id, approved_manuscript FROM pod_ads WHERE ad_id='$ir_ad_id' AND (status='approved' OR status='rerecord') AND voice_user='$userid'"
	 : "SELECT id, approved_manuscript FROM pod_ads WHERE ad_id='$ir_ad_id' AND (status='approved' OR status='rerecord')";

$r = mysqli_query ($dbc, $q);
if ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
	$pod_ad_id = "$row[0]";
	$ir_approved_manuscript = "$row[1]";
} else {
	header("Location: index.php");
	exit(); // Quit the script
}

// Display any errors
echo (isset($show_errors)) ? "<p class='note_red'>$errors</p>" : false;

// Start the page
echo "<h3>Voice Recording</h3>";

echo "<h4>Manuscript:</h4>";

echo "<br /><hr style=\"width:70%;\" /><br />";
echo "<div style=\"width:70%;\"><p style=\"text-align:center;\"><big>$ir_approved_manuscript</big></p></div>";

// Upload form
echo "<br /><hr style=\"width:70%;\" /><br />";

echo "<p><i>File name: <b>ba-{$pod_ad_id}.mp3</b> Format: mp3, 96 kbps, joint stereo / mono</i></p>";

echo '
<form id="voice_upload" class="userform" action="voice_record.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="ad_id" value="'.$ir_ad_id.'" />
<input type="file" name="recording_upload" id="recording_upload">
<input type="submit" name="submit_voice" value="Upload recording" id="submit_voice" class="formbutton_blue" />
</form>';

// Include the HTML footer
include ('./includes/footer.html');
?>
