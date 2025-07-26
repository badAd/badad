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

// For storing registration errors (needed whether POST a save or first render)
$reg_errors = array();

// Process the POST
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['ad_id'])) && (filter_var($_POST['ad_id'], FILTER_VALIDATE_INT, array('min_range' => 1)))
&& (isset($_POST['pdcst'])) ) {

	// Podcast ad ID
	$ir_ad_id = preg_replace("/[^0-9]/","", $_POST['ad_id']);

	// Check Podcast Listing
	if (isset($_POST['pdcst'])) {
		include ('includes/bannedwords.inc.php');
		if (preg_match ('/^[A-Z0-9 \'\/.&,:;-]{100,1000}$/i', $_POST['pdcst'])) {
			if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['pdcst'])) {
				if  (str_word_count($_POST['pdcst']) <= 55) {
					$edit_ad_content_pdcst = $_POST['pdcst'];
					$_SESSION['edit_ad_content_pdcst'] = $edit_ad_content_pdcst;
				} else {
					$reg_errors['pdcst'] = 'Maximum 50 words.';
				}
			} else {
				$reg_errors['pdcst'] = 'Podcast Listing: '.$bannedMessage;
			}
		} else {
			$reg_errors['pdcst'] = 'Podcast Listing: 50 word max, 100-1,000 characters. Only letters and numbers and - \' / . , : ; &';
		}
	}

	// Without errors, update and redirect
	$sql_edit_ad_content_pdcst = mysqli_real_escape_string($dbc, $edit_ad_content_pdcst);
	$q = "UPDATE pod_ads SET status='edited', edited_manuscript='$sql_edit_ad_content_pdcst', date_modified=NOW() WHERE (status='inreview' OR status='resubmitted') AND editor_user='$userid' AND ad_id='$ir_ad_id'";
	$r = mysqli_query ($dbc, $q);

  if ($r) {
    // Send an email to customer
		// Get the new user's ID from the database for the email
		$q = "SELECT id FROM users WHERE email='$email'";
		$r = mysqli_query ($dbc, $q);
		$row = mysqli_fetch_array($r, MYSQLI_NUM);
		$userid = "$row[0]";

		// Send an email
		$payloadlink = "https://$siteDomain/order_history.php";
		$emailUserID = $userid;
		$canned_email = "podad_edited"; // Slug from the "pantry" table to select the canned email
		$subject_suffix = ": $siteTitle"; // Appends the canned email Subject
		$payload_content = "<p><a href=\"$payloadlink\">View changes in your \"Order History\"</a>.</p>"; // Middle of the Body, after the canned email and before the salutation
		$footer_link_content = ""; // After the salutation and before the unsubscribe footer
		include ('./includes/sendusrmail.inc.php');

		// Send back good news
		echo "
		<form id=\"jsGoForm\" action=\"https://$siteDomain/editor.php\" method=\"post\">
			<input type=\"hidden\" name=\"ad_review\" value=\"edited\">
		</form>
		<script type=\"text/javascript\">
				document.getElementById('jsGoForm').submit();
		</script>";

	} else {
	  sql_error($q, 'dbc', "sqle_142");
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
$q = "UPDATE pod_ads SET editor_user='$userid', date_modified=NOW() WHERE ad_id='$ir_ad_id' AND (editor_user=0 OR editor_user='$userid')";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 0) {
	header("Location: editor.php");
	exit(); // Quit the script
} elseif (!$r) {
	sql_error($q, 'dbc', "sqle_143");
}

// Load the project for review
$q =  ((!$_SESSION['user_is_admin']) && (!$_SESSION['user_is_supervisor']) && (!$_SESSION['user_is_publisher']))
   ? "SELECT original_manuscript, resubmitted_manuscript FROM pod_ads WHERE ad_id='$ir_ad_id' AND (status='inreview' OR status='resubmitted') AND editor_user='$userid'"
	 : "SELECT original_manuscript, resubmitted_manuscript FROM pod_ads WHERE ad_id='$ir_ad_id' AND (status='inreview' OR status='resubmitted')";
$r = mysqli_query ($dbc, $q);
if ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
	$ir_original_manuscript = "$row[0]";
	$ir_resubmitted_manuscript = "$row[1]";

		// Include the header file
	$page_title = "Editing & Review :: $siteTitle";
	$wordcount_textarea = true;
	include ('./includes/header.html');

	// Ad functions
	require_once ('./includes/ad_functions.inc.php');

	// Start the page
	echo "<h3>Editing & Review</h3>";

	// Current proposal
	$current_proposal = ($ir_resubmitted_manuscript == '') ? $ir_original_manuscript : $ir_resubmitted_manuscript;

	echo "<h4 class=\"note_green\">Proposed manuscript:</h4>";
	echo "<br />";
	echo "<div style=\"width:70%;\"><p style=\"text-align:center;\"><big>$current_proposal</big></p></div>";

	set_switch("Approve as-is", "Approve this ad manuscript and queue it for audio recording", "editor.php", "ad_id", $ir_ad_id, "set_green");

	// Fetch text ad for reference
	$q = "SELECT ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_bizn, ad_biz_listing, role_id FROM ads WHERE id='$ir_ad_id'";
	$r = mysqli_query ($dbc, $q);
	if ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
		$new_ad_heading = "$row[0]";
		$new_ad_description = "$row[1]";
		$new_ad_info = "$row[2]";
		$new_ad_pricing = "$row[3]";
		$new_ad_content_bizn = "$row[4]";
		$new_ad_biz_listing = "$row[5]";
		$roleID = "$row[6]";
	}
	require_once ('./includes/ad_content_set.inc.php');
	echo "<div style=\"width:70%; text-align:center;\" class=\"ad_preview\"><br /><p style=\"text-align: center;\"><b>Text ad (for reference):</b></p><p style=\"text-align:center;\"><hr /><br />$adContent<br /><hr /></p></div>";
	unset($_SESSION['adContent']);
	unset($_SESSION['roleName']);

	// Proposed edits
	// Editing proposal
	$edit_ad_content_pdcst = (isset($_SESSION['edit_ad_content_pdcst'])) ? $_SESSION['edit_ad_content_pdcst'] : $current_proposal;
	unset($_SESSION['edit_ad_content_pdcst']); // We don't need this anymore

	echo "<h4 class=\"note_blue\">Or, edit manuscript:</h4>";
	echo '
	<form id="edit_pa" class="userform" action="editor_review.php" method="post" accept-charset="utf-8">
	<input type="hidden" name="ad_id" value="'.$ir_ad_id.'" />';
	create_new_ad_input('pdcst', 'text', '', $reg_errors, $edit_ad_content_pdcst);
	echo '<br /><br /><span id="wordCount">0</span> word(s), 50 max<br /><br />
	<input type="submit" name="submit_button" value="Submit proposed edits" id="submit_button" class="formbutton_blue" />
	</form>';

} else {
	header("Location: editor.php");
	exit(); // Quit the script
}

// Include the HTML footer
include ('./includes/footer.html');
?>
