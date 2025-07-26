<?php
//In case you want to show errors
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Configs
require_once ('./includes/config.inc.php');
require_once (MYSQL);
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('./includes/config_agg.inc.php');
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

// No partner activity for at-risk accounts
if (isset($_SESSION['no_status'])) {
	header("Location: account_info.php");
	exit(); // Quit the script
}

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Check if partner account has been activated
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows == 1) { // partner account exists in database
	$activation = $row[0];
	if ($activation != "Confirmed") { // Not activated
		// Resend activation email
		// Include the header file
    $page_title = "Confirmation Needed First :: $siteTitle";
    include ('./includes/header.html');
    echo '<h3 class="note_yellow">Confirmation Needed First</h3>';
		echo "<p><b>Good news, partner application already processed!</b> Now, confirm your payout email.</p>";
		// Process the email confirmation if changed
		include ('includes/confirm_partner_email.inc.php');
		// Include the HTML footer
		include ('./includes/footer.html');
		exit();
	}
} else { // No partner application entry or email not confirmed
		// Check to see if user's email is verified
		$qe = "SELECT email, confirmed_email FROM users WHERE id='$userid'";
		$re = mysqli_query ($dbc, $qe);
		$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
		$email = "$rowe[0]";
		$email_confirmed = "$rowe[1]";
		if ($email != $email_confirmed) {
			// Process the email confirmation
			// Include the header file
	    $page_title = "Confirmation Needed First :: $siteTitle";
	    include ('./includes/header.html');
	    echo '<h3 class="note_yellow">Confirmation Needed First</h3>';
			echo "<p>Confirm your email ($email).</p>";
			include ('includes/confirm_email.inc.php');
			// Include the HTML footer
			include ('./includes/footer.html');
			exit();
	} else { // email verified

		// Partner signup
		// Include the header file
		$page_title = "Partner Center :: $siteTitle";
		include ('./includes/header.html');
		$rformaction = "partner.php";
		include ('includes/register_partner.inc.php');
		include ('./includes/footer.html');
		exit();
	}
} // activated check complete, user is a fully-fledged partner

// // Check for an "Ads to Show" submission
// if ((isset($_POST['num_ads_to_show'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['num_ads_to_show']))) {$IP = get_ip_addr(); script_kiddy('sk_72', '_POST num_ads_to_show', $_POST['num_ads_to_show'], $IP);}
// if ((isset($_POST['num_ads_site_id'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['num_ads_site_id']))) {$IP = get_ip_addr(); script_kiddy('sk_73', '_POST num_ads_site_id', $_POST['num_ads_site_id'], $IP);}
//
// if (($_SERVER['REQUEST_METHOD'] == 'POST')
// && (isset($_POST['num_ads_to_show']))
// && (filter_var($_POST['num_ads_to_show'], FILTER_VALIDATE_INT, array('min_range' => 1)))
// && (isset($_POST['num_ads_site_id']))
// && (filter_var($_POST['num_ads_site_id'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
// $new_num_ads_to_show = preg_replace("/[^A-Za-z0-9]/","", $_POST['num_ads_to_show']);
// $new_num_ads_site_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['num_ads_site_id']);
//
// 	// Validate the numbers
// 	if (filter_var($new_num_ads_to_show, FILTER_VALIDATE_INT) === false) {$new_num_ads_to_show = 1;}
// 	if ($new_num_ads_to_show > 20) {$new_num_ads_to_show = 20;}
// 	if ($new_num_ads_to_show < 0) {$new_num_ads_to_show = 0;}
//
// 	// Update the database
// 	$q = "UPDATE partnersites SET num_ads_to_show='$new_num_ads_to_show' WHERE id='$new_num_ads_site_id'";
// 	$r = mysqli_query ($srv_dbc, $q);
//
// 	if (!$r) { // If it didn't run okay
// 		sql_error($q, 'srv_dbc', "sqle_80");
// 	}
// } // "Ads to Show" form submission check

// Checks all done, start building the page

// Include the header file
$page_title = "Partner Center :: $siteTitle";
include ('./includes/header.html');

// AJAX scripts
?>
	<script>
		function ajaxFormData(formID, postTo, ajaxUpdate) { // These arguments can be anything, same as used in this function
			// Bind a new event listener every time the <form> is changed:
			const FORM = document.getElementById(formID); // <form> by ID to access, formID is the JS argument in the function
			const AJAX = new XMLHttpRequest(); // AJAX handler
			const FD = new FormData(FORM); // Bind to-send data to form element

			AJAX.addEventListener( "load", function(event) { // This runs when AJAX responds
				document.getElementById(ajaxUpdate).innerHTML = event.target.responseText; // HTML element by ID to update, ajaxUpdate is the JS argument in the function
			} );

			AJAX.addEventListener( "error", function(event) { // This runs if AJAX fails
				document.getElementById(ajaxUpdate).innerHTML =  'Oops! Something went wrong.';
			} );

			AJAX.open("POST", postTo); // Send data, postTo is the .php destination file, from the JS argument in the function

			AJAX.send(FD); // Data sent is from the form

		} // ajaxFormData() function
	</script>
<?php

// Check for a "New Project" submission
if ( ($_SERVER['REQUEST_METHOD'] == 'POST') && ( (isset($_POST['new_site_gen'])) || (isset($_POST['new_podcast_gen'])) ) ) {

	// Include the site key generation action
	include ('./includes/partner_project_add.inc.php');
} // "New Project" form submission check

// Any repair info message
if (isset($_SESSION['partner_dev_must_verify'])) { // Use the switch to clear out all verification emails
	echo '<p class="note_blue">There have been some changes in your Developer account. Please double check that your information here is correct in the Developer Center:</p>';
	set_switch("Developer Center...", "Go to the Developer Center", "partner_dev.php", "partner_dev", $userid, "set_black");
	echo '<br /><p class="note_blue">Then confirm that the info is correct.</p>';
	set_switch("Yes, my Developer info is correct!", "Confirm this information", "partner_dev_verify_info.act.php", "uid", $userid, "set_blue");
	echo '<br /><br />';
} elseif (isset($_SESSION['partner_must_verify'])) { // Use the switch to clear out all verification emails
	echo '<p class="note_blue">There have been some changes on your Partner account. Please double check that your information here is correct.</p>';
	set_switch("Yes, my info is correct!", "Confirm this information", "partner_verify_info.act.php", "uid", $userid, "set_blue");
	echo '<br /><br />';
} elseif (isset($_SESSION['partner_all_confirmed'])) {
	echo '<p class="note_blue">Your Partner account information has been confirmed. Thank you!</p>';
	unset($_SESSION['partner_all_confirmed']);
}

// Page for activated partners
echo "<h3>Partner Center</h3><br /><hr />";

// Form for adding a new domain Project
echo "<h4 class=\"note_blue\">New Website Project:</h4>
<form id=\"add_partner_site\" class=\"new_partner_project_form\" action=\"partner.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"new_site_gen\" value=\"submitted\" />
<br />
<p class=\"note_blue\"><input type=\"checkbox\" name=\"tc_domain_embed_match\" value=\"true\" required /> <b>I understand &amp; declare:</b>I own this domain or hold rights to this domain, and I have legal permission to use this domain in this manner. Ad <i>embed codes</i> must be used on the same domain/subdomain as their Website Project, otherwise they may not be counted for statistics or payout.<br />Of course, you may have multiple Website Projects with the same domain and a domain may be used on all subdomains, <i>if you own or have rights to the domain</i>. However, other web or mobile apps using <a title=\"Dev Help\" href=\"help_dev.php\">our API</a> from the <a title=\"Developer Center\" href=\"partner_dev.php\">Developer Center</a> are not restricted to this rule. Use this form (below) to create normal Website Projects using an <i>embed code</i>. <a title=\"View the instructions\" href=\"partner_help.php#domains\">Learn more about domains</a>.</p>
<p class=\"note_gray\"><i>The Domain <b>cannot</b> be changed later; the Nickname can be changed.</i></p>
<p class=\"note_blue\"><label for=\"new_site_domain\">Website Domain: <input type=\"text\" name=\"new_site_domain\" id=\"new_site_domain\" size=\"32\" maxlength=\"255\" placeholder=\"example.com OR sub.example.com etc\" required /></label>
<br /><br />
<label for=\"website_nickname\">Nickname: <input type=\"text\" name=\"website_nickname\" id=\"website_nickname\" size=\"48\" maxlength=\"80\" placeholder=\"Nickname (optional)\" /></label>
<br /><br />
<input type=\"submit\" form=\"add_partner_site\" value=\"Add Website\" class=\"formbutton_blue\" /></p>
</form><br /><hr />";

// Form for adding a new podcast Project
echo "<h4 class=\"note_violet\">New Podcast Project:</h4>
<form id=\"add_partner_podcast\" class=\"new_partner_project_form\" action=\"partner.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"new_podcast_gen\" value=\"submitted\" />
<br />
<p class=\"note_violet\"><input type=\"checkbox\" name=\"tc_podcast_rights\" value=\"true\" required /> <b>I declare:</b> I own this podcast or hold rights to this podcast, and I have legal permission to use this podcast in this manner.</p>
<p class=\"note_gray\"><i>Tip: Turn a WordPress blog into a podcast by adding <code class=\"inline\">\"/feed\"</code>, for example: <code class=\"inline\"><b>https://mywpsite.tld/feed</b></code></i></p>
<p class=\"note_violet\"><label for=\"new_podcast_source\">Podcast Source URL: <input type=\"text\" name=\"new_podcast_source\" id=\"new_podcast_source\" size=\"72\" maxlength=\"255\" placeholder=\"https://example.com/my/podcast/feed\" required /></label>
<br /><br />
<label for=\"podcast_slug\">Slug: <b class=\"note_gray\">https://$podcastServeDomain/ </b><input type=\"text\" name=\"podcast_slug\" id=\"podcast_slug\" size=\"48\" maxlength=\"255\" placeholder=\"my-example/slug\" /></label>
<br /><br />
<label for=\"podcast_nickname\">Nickname: <input type=\"text\" name=\"podcast_nickname\" id=\"podcast_nickname\" size=\"48\" maxlength=\"80\" placeholder=\"Nickname (optional)\" /></label>
<br /><br />
<input type=\"submit\" form=\"add_partner_podcast\" value=\"Add Podcast\" class=\"formbutton_violet\" /></p>
</form><br /><hr />";

//REMOVED from above form:
//<input type=\"checkbox\" name=\"allow_subdomains\" value=\"true\" checked /> Allow subdomains?

// Get the site info to populate the profile
$q = "SELECT id, domain, nickname, horizontal_inline, num_ads_to_show, serial_no, listed_badad_count, listed_ad_count, clicked_badad_count, clicked_listed_count, useable, date_tallied, type, dev_authorized_id, directory_listed FROM partnersites WHERE user_id='$userid' AND domain!='podcast' AND type!='podcast' AND NOT useable='deleted' AND NOT (NOT papp_key='connected' AND type='app') ORDER BY domain, nickname, id";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) {
	$havesites = false;
} else {
	$havesites = true;
}

if ($havesites == false) {

	if (isset($_SESSION['partner_need_see_new_categories'])) {
		// Redirect via Javascript wtih _POST set for security
		// Thanks https://stackoverflow.com/a/5576700/10343144
		echo "
		<form id=\"jsGoForm\" action=\"partner_newcat_ok.act.php\" method=\"post\">
			<input type=\"hidden\" name=\"uid\" value=\"$userid\">
		</form>
		<script type=\"text/javascript\">
				document.getElementById('jsGoForm').submit();
		</script>";

	}

	echo "<br /><p>No websites yet! Add your first Website Project to get started.</p>";
} elseif ($havesites == true) {

	// Any new category notice
	if (isset($_SESSION['partner_need_see_new_categories'])) { // Use the switch to clear out all verification emails
		echo '<div><p class="note_blue">We updated categories! You may want to double-check which you want to include on each Project.</p><p class="note_blue">See the latest categories under each Project:<br /><b>Categories > choose</b></p>';
		set_switch("Okay I get it, new categories, it affects me, dismiss this", "Got it!", "partner_newcat_ok.act.php", "uid", $userid, "set_blue");
		echo '</div><br /><br />';
	}

	// Start the table
	echo "<br /><div class=\"partnersitestable\">\n
	<h4>Your Website Projects: <i class=\"note_gray\">(numbers for current payout cycle)</i></h4>";
	echo "<table class=\"sitestable\">\n<tbody>\n
	<tr><th>Domain / App</th><th>Action</th><th>Share Count</th><th>Hits</th><th>Ads Viewed</th><th>badAd Clicks</th><th>Ad Clicks</th><th>Categories</th><th>Status</th><th>Per-Ad Alignment</th><th>Ads to Show</th><th>Embed code</th><th>Counting since</th></tr>";
	//REMOVED
	//<th>Subdomains</th>
	while ($row = mysqli_fetch_array($r)) {
		$site_id = "$row[0]";
		$site_domain = "$row[1]";
		$site_nickname = "$row[2]";
		$site_horizontal_inline = "$row[3]";
		$site_ads_to_show = "$row[4]";
		$site_serial_no = "$row[5]";
		$site_listed_badad_count = "$row[6]";
		$site_listed_ad_count = "$row[7]";
		$site_clicked_badad_count = "$row[8]";
		$site_clicked_listed_count = "$row[9]";
		$site_useable = "$row[10]";
		$site_date_tallied = "$row[11]";
		$project_type = "$row[12]";
		$dev_authorized_id = "$row[13]";
		$directory_listed = "$row[14]";

		$site_share_count = (($site_clicked_listed_count * $clickShareValue) + ($site_clicked_badad_count * $clickShareValue) + $site_listed_ad_count + $site_listed_badad_count);
		// Be Pretty
		$pretty_site_listed_badad_count = number_format($site_listed_badad_count);
		$pretty_site_listed_ad_count = number_format($site_listed_ad_count);
		$pretty_site_clicked_badad_count = number_format($site_clicked_badad_count);
		$pretty_site_clicked_listed_count = number_format($site_clicked_listed_count);
		$pretty_site_share_count = number_format($site_share_count);

		// Project is App? Get Developer info
		if ($project_type == 'app') {
			$qd="SELECT name FROM devkeys WHERE id='$dev_authorized_id'";
			$rd = mysqli_query($srv_dbc, $qd);
			if ($rd) {
			$row = mysqli_fetch_array($rd);
			$dev_name = "$row[0]";
			} else {
				sql_error($qd, 'srv_dbc', "sqle_92");
			}
		}

		// Listed class?
		$trclass = ($directory_listed == 'listed') ? 'class ="oversight"' : '' ;
		$site_domain_directory = ($directory_listed == 'listed') ? "<b>$site_domain</b><br /><small>(Listed)</small>" : "<b>$site_domain</b>" ;

		// Iterate each Project site into the table
		// Domain
		echo "<tr $trclass><td align=\"left\">";

		if ($project_type == 'app') {
			echo "<i>$site_domain_directory</i>";
		} else {
			echo "$site_domain_directory";
		}
		echo "<br /><br />";

		if ($site_nickname != NULL) {echo "$site_nickname";} else {echo "<span class=\"note_gray\">(#$site_id)</span>";}

		echo "</td>";

		// Action
		echo "<td align=\"center\">";

		set_switch("stats", "View stats for $site_domain", "partner_site_stats.php", "s", $site_id, "set_blue");
		echo "<br />";
		set_switch("edit", "Edit info for $site_domain", "partnersite_edit_info.php", "s", $site_id, "set_gray");

		echo "</td>";

		// Share Count
		echo "<td align=\"left\">$pretty_site_share_count</td>";

		// badAd Viewed (Hits)
		echo "<td align=\"left\">$pretty_site_listed_badad_count</td>";

		// Ads Viewed
		echo "<td align=\"left\">$pretty_site_listed_ad_count</td>";

		// badAd Clicks
		echo "<td align=\"left\">$pretty_site_clicked_badad_count</td>";

		// Ad Clicks
		echo "<td align=\"left\">$pretty_site_clicked_listed_count</td>";

		// Category management
		echo "<td align=\"center\"><a class=\"set_blue\" title=\"Manage categories of ads you want to appear on this site\" href=\"partner_site_subcats.php?s=$site_id\">choose</a></td>";


		// Usable status
		echo '<td align="center" id="psite_status_td_'.$site_id.'">';
		if ($site_useable == 'live') {

			echo '
			<form id="psite_status_form_'.$site_id.'">
				<input type="hidden" name="s" value="'.$site_id.'">
				<button type="button" class="formbutton_green" title="Switch off" onclick="ajaxFormData("psite_status_form_'.$site_id.'", "partnerproject_useable_off.ajax.php", "psite_status_td_'.$site_id.'");">live</button>
			</form>';

			//set_switch("live", "Switch off", "partnerproject_useable_off.act.php", "s", $site_id, "set_green");
			//<input type="submit" title="Switch off" value="live" class="set_green">

		} elseif ($site_useable == 'off') {

			echo '
			<form id="psite_status_form_'.$site_id.'">
			  <input type="hidden" name="s" value="'.$site_id.'">
				<button type="button" class="formbutton_gray" title="Switch on" onclick="ajaxFormData("psite_status_form_'.$site_id.'", "partnerproject_useable_on.ajax.php", "psite_status_td_'.$site_id.'");">off</button>
			</form>';

			//set_switch("off", "Switch on", "partnerproject_useable_on.act.php", "s", $site_id, "set_gray");
			//<input type="submit" title="Switch on" value="off" class="set_gray">
		}
		echo '</td>';

	// Project type?
	if ($project_type == "site") { // Project is website

		// Horizanta or vertical?
		echo '<td align="center" id="psite_horizantal_td_'.$site_id.'">';
			if ($site_horizontal_inline == true) {

				echo '
				<form id="psite_horizantal_form_'.$site_id.'">
				  <input type="hidden" name="s" value="'.$site_id.'">
					<button type="button" class="formbutton_blue" title="Switch on" onclick="ajaxFormData("psite_horizantal_form_'.$site_id.'", "partnersite_horizantal_off.ajax.php", "psite_horizantal_td_'.$site_id.'");">inline</button>
				</form>';

				//set_switch("inline", "Click to disable horizantal ad lists via CSS-display:inline-block;", "partnersite_horizantal_off.act.php", "s", $site_id, "set_blue");
				//<input type="submit" title="Click to enable horizantal ad lists via CSS-display:inline-block;" value="vertical" class="set_blue">
		} elseif ($site_horizontal_inline == false) {

			echo '
			<form id="psite_horizantal_form_'.$site_id.'">
				<input type="hidden" name="s" value="'.$site_id.'">
				<button type="button" class="formbutton_orange" title="Switch on" onclick="ajaxFormData("psite_horizantal_form_'.$site_id.'", "partnersite_horizantal_on.ajax.php", "psite_horizantal_td_'.$site_id.'");">vertical</button>
			</form>';

				//set_switch("vertical", "Click to enable horizantal ad lists via CSS-display:inline-block;", "partnersite_horizantal_on.act.php", "s", $site_id, "set_orange");
				//<input type="submit" title="Click to disable horizantal ad lists via CSS-display:inline-block;" value="inline" class="set_orange">
		}
		echo '</td>';

		// Subdomains allowed?
		/*
		echo "<td align=\"center\">";
		if ($site_sub_allowedYN == true) {
			set_switch("yes", "Change to turn off subdomains", "partner_site_subdomains_off.act.php", "s", $site_id, "set_blue");
	} elseif ($site_sub_allowedYN == false)  {
			set_switch("no", "Change to allows subdomains", "partner_site_subdomains_on.act.php", "s", $site_id, "set_yellow");
	}
		echo "</td>";
		*/

		// Ads to Show
		echo '<td align="center" id="ads_to_show_td_'.$site_id.'">';

		echo '<form id="ads_to_show_form_'.$site_id.'" class="ads_to_show" accept-charset="utf-8">
			<input type="hidden" name="num_ads_site_id" value="'.$site_id.'" />
			<input type="number" class="num_ads" name="num_ads_to_show" value="'.$site_ads_to_show.'" step="1" min="0" max="20" />
			<br><br>
			<button type="button" class="formbutton_violet" title="Switch on" onclick="ajaxFormData("ads_to_show_form_'.$site_id.'", "partnersite_num_ads_to_show.ajax.php", "ads_to_show_td_'.$site_id.'");">vertical</button>
		</form>';

		//			<input type="submit" value="set" class="set_violet" />

		echo '</td>';

		// Code
		echo "<td align=\"center\">";
		// The copyable embed code
    $payloadCode = '<script type=&quot;text/javascript&quot; src=&quot;https://'.$adServeDomain.'/'.$site_serial_no.'/ads.js&quot;></script>';

    // Show the link
    echo "
    <input type=\"text\" value=\"$payloadCode\" class=\"copy_code\" id=\"embed_code_$site_id\" />
		<br><br>
    <button class=\"copy_button\" onclick=\"copyLink_$site_id()\">copy</button>
    <script>function copyLink_$site_id() {var copyText = document.getElementById(\"embed_code_$site_id\"); copyText.select(); document.execCommand(\"copy\"); }</script>
		<script>document.getElementById(\"embed_code_$site_id\").readOnly = true; </script>";
		echo "</td>";

	} elseif (($project_type == "app") && (isset($dev_name))) { // Website Project ends, below is App project

		echo '<td colspan="3">';

		echo "<i>Connected app: <b>$dev_name</b></i>";

		echo '</td>';
	}

		// Date counting since
		echo "<td align=\"center\">";
		echo "<span class=\"note_gray\">$site_date_tallied</span>";
		echo "</td>";

		// Finish the row
		echo "</tr>";

	}
	// Finish the table
	echo "</tbody>\n</table>\n</div>\n<br />";

} // Have sites check

// Get the podcast info to populate the profile
$qp = "SELECT id, nickname, serial_no, useable, date_tallied, directory_listed FROM partnersites WHERE user_id='$userid' AND domain='podcast' AND type='podcast' AND NOT useable='deleted' ORDER BY nickname, id";
$rp = mysqli_query ($srv_dbc, $qp);
$rows = mysqli_num_rows($rp);
if ($rows == 0) {
	$havecasts = false;
} else {
	$havecasts = true;
}

if ($havecasts == false) {

	echo "<br /><p>No podcasts yet! Add your first Podcast Project to get started.</p>";

} elseif ($havecasts == true) {

	// Any new category notice
	if (isset($_SESSION['partner_need_see_new_categories'])) { // Use the switch to clear out all verification emails
		echo '<div><p class="note_blue">We updated categories! You may want to double-check which you want to include on each Project.</p><p class="note_blue">See the latest categories under each Project:<br /><b>Categories > choose</b></p>';
		set_switch("Okay I get it, new categories, it affects me, dismiss this", "Got it!", "partner_newcat_ok.act.php", "uid", $userid, "set_blue");
		echo '</div><br /><br />';
	}

	// Start the table
	echo "<br /><div class=\"partnersitestable\">\n
	<h4>Your Podcast Projects: <i class=\"note_gray\">(numbers for current payout cycle)</i></h4>";
	echo "<table class=\"sitestable\">\n<tbody>\n
	<tr><th>Slug / Name</th><th>Action</th><th>Share Count</th><th>Feed Requests<br /><i>not payable</i></th><th>Ad Downloads<br /><i>one ad per episode</i></th><th>Link Clicks</th><th>Categories</th><th>Status</th><th>Syndicated URL</th><th>Counting since</th></tr>";
	while ($rowp = mysqli_fetch_array($rp)) {
		$site_id = "$rowp[0]";
		$site_nickname = "$rowp[1]";
		$site_serial_no = "$rowp[2]";
		$site_useable = "$rowp[3]";
		$site_date_tallied = "$rowp[4]";
		$directory_listed = "$rowp[5]";

		$qc = "SELECT feed_requested_count, ad_download_count, ad_click_count FROM feeds WHERE project_id='$site_id'";
		$rc = mysqli_query ($agg_dbc, $qc);
		$rowc = mysqli_fetch_array($rc);
		$site_feed_requested_count = "$rowc[0]";
		$site_ad_download_count = "$rowc[1]";
		$site_clicked_listed_count = "$rowc[2]";

		$site_share_count = ((($site_clicked_listed_count * $clickShareValue) + ($site_ad_download_count * $downloadShareValue)) * ($podcasterShareValue));

		// Be Pretty
		$pretty_site_clicked_listed_count = number_format($site_clicked_listed_count);
		$pretty_site_feed_requested_count = number_format($site_feed_requested_count);
		$pretty_site_ad_download_count = number_format($site_ad_download_count);
		$pretty_site_share_count = number_format($site_share_count);

		// Project is App? Get Developer info
		if ($project_type == 'app') {
			$qd="SELECT name FROM devkeys WHERE id='$dev_authorized_id'";
			$rd = mysqli_query($srv_dbc, $qd);
			if ($rd) {
				$row = mysqli_fetch_array($rd);
				$dev_name = "$row[0]";
			} else {
				sql_error($qd, 'srv_dbc', "sqle_124");
			}
		}

		// Listed class?
		$trclass = ($directory_listed == 'listed') ? 'class ="oversight"' : '' ;
		$podcast_slug = ($directory_listed == 'listed') ? "<b>$site_serial_no</b><br /><small>(Listed)</small>" : "<b>$site_serial_no</b>" ;

		// Iterate each Project site into the table
		// Slug/name
		echo "<tr $trclass><td align=\"left\">";

		echo $podcast_slug;
		echo "<br /><br />";

		if ($site_nickname != NULL) {echo "$site_nickname";} else {echo "<span class=\"note_gray\">(#$site_id)</span>";}

		echo "</td>";

		// Action
		echo "<td align=\"center\">";

		set_switch("stats", "View stats for $site_serial_no", "partner_site_stats.php", "s", $site_id, "set_blue");
		echo "<br />";
		set_switch("manage", "Change and view info for $site_serial_no", "partnerpodcast_manage.php", "s", $site_id, "set_gray");

		echo "</td>";

		// Share Count
		echo "<td align=\"left\">$pretty_site_share_count</td>";

		// Feed Requests
		echo "<td align=\"left\">$pretty_site_feed_requested_count</td>";

		// Ads Downloaded
		echo "<td align=\"left\">$pretty_site_ad_download_count</td>";

		// Link Clicks
		echo "<td align=\"left\">$site_clicked_listed_count</td>";

		// Category management
		echo "<td align=\"center\"><a class=\"set_blue\" title=\"Manage categories of ads you want to appear on this site\" href=\"partner_site_subcats.php?s=$site_id\">choose</a></td>";

		// Usable status
		echo '<td align="center" id="podcast_status_td_'.$site_id.'">';
		if ($site_useable == 'live') {

			// AJAX
			// echo '
			// <form id="podcast_status_form_'.$site_id.'">
			// 	<input type="hidden" name="s" value="'.$site_id.'">
			// 	<button type="button" class="formbutton_green" title="Switch off" onclick="ajaxFormData("podcast_status_form_'.$site_id.'", "partnerproject_useable_off.ajax.php", "podcast_status_td_'.$site_id.'");">live</button>
			// </form>';

			// Page-wide
			set_switch("live", "Switch off", "partnerproject_useable_off.act.php", "s", $site_id, "set_green");
			//<input type="submit" title="Switch off" value="live" class="set_green"> // AJAX ref, the submit input the AJAX button replaces

		} elseif ($site_useable == 'off') {

			// AJAX
			// echo '
			// <form id="podcast_status_form_'.$site_id.'">
			//   <input type="hidden" name="s" value="'.$site_id.'">
			// 	<button type="button" class="formbutton_gray" title="Switch on" onclick="ajaxFormData("podcast_status_form_'.$site_id.'", "partnerproject_useable_on.ajax.php", "podcast_status_td_'.$site_id.'");">off</button>
			// </form>';

			// Page-wide
			set_switch("off", "Switch on", "partnerproject_useable_on.act.php", "s", $site_id, "set_gray");
			//<input type="submit" title="Switch on" value="off" class="set_gray"> // AJAX ref, the submit input the AJAX button replaces
		}
		echo '</td>';

		// Sindicated URL
		echo "<td align=\"center\">";
			// The copyable embed code
			$payloadCode = 'https://'.$podcastServeDomain.'/'.$site_serial_no;

			// Show the link
			echo "
			<input type=\"text\" value=\"$payloadCode\" class=\"copy_code\" id=\"syndicate_url_$site_id\" />
			<br><br>
			<button class=\"copy_button\" onclick=\"copyLink_$site_id()\">Copy</button>
			<script>function copyLink_$site_id() {var copyText = document.getElementById(\"syndicate_url_$site_id\"); copyText.select(); document.execCommand(\"copy\"); }</script>
			<script>document.getElementById(\"syndicate_url_$site_id\").readOnly = true; </script>";
		echo "</td>";

	  // Date counting since
		echo "<td align=\"center\">";
		echo "<span class=\"note_gray\">$site_date_tallied</span>";
		echo "</td>";

		// Finish the row
		echo "</tr>";

	}
	// Finish the table
	echo "</tbody>\n</table>\n</div>\n<br />";


} // Have casts check

if (($havesites = true) || ($havecasts = true)) {

	// Notes
	echo "<p class=\"note_gray\"><i>Numbers in these tables are for current payout cycle. For payout numbers prior to the 'Counting since' date, see Site History in the All-site actions area. These numbers are estimation, are subject to change, and are in no way legally binding.</i></p>";

	// Total Hits
	include ('includes/totalhits.inc.php');

	// All Partner's sites
	// Get the count
	$q = "SELECT SUM(listed_badad_count), SUM(listed_ad_count), SUM(clicked_badad_count), SUM(clicked_listed_count) FROM partnersites WHERE user_id='$userid'";
	$r = mysqli_query ($srv_dbc, $q);
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$all_listed_badad_count = $row[0];
	$all_listed_ad_count = $row[1];
	$all_clicked_badad_count = $row[2];
	$all_clicked_listed_count = $row[3];

	// Get the count
	$q = "SELECT SUM(feed_requested_count), SUM(ad_download_count), SUM(ad_click_count) FROM feeds WHERE user_id='$userid'";
	$r = mysqli_query ($agg_dbc, $q);
	$rows = mysqli_num_rows($r);
	if ($rows != 0)	{
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
		$all_feed_requested_count = $row[0];
		$all_podad_download_count = $row[1];
		$all_podad_click_count = $row[2];
	} else {
		$all_feed_requested_count = 0;
		$all_podad_download_count = 0;
		$all_podad_click_count = 0;
	}

	// Grand totals
	$all_listed = ($all_listed_badad_count + $all_listed_ad_count + $all_podad_download_count);
	$all_clicked = ($all_clicked_badad_count + $all_clicked_listed_count + $all_podad_click_count);
	$all_share_count = ($all_listed + $all_feed_requested_count + ($all_clicked * $clickShareValue));

	// Calculate the share count percent
	if (($totalHits_share_count !=0) && ($all_share_count !=0)) {
		$contributionPercent = ($all_share_count / $totalHits_share_count)*100;
	} else {
		$contributionPercent = 0;
	}

	// Be Pretty
	$pretty_all_listed_badad_count = number_format($all_listed_badad_count);
	$pretty_all_listed_ad_count = number_format($all_listed_ad_count);
	$pretty_all_clicked_badad_count = number_format($all_clicked_badad_count);
	$pretty_all_clicked_listed_count = number_format($all_clicked_listed_count);
	$pretty_all_share_count = number_format($all_share_count);
	$pretty_contributionPercent = number_format($contributionPercent);
	$pretty_clickShareValue = number_format($clickShareValue);

	// Create the table
	echo "<br /><div class=\"partnersitestable\">\n";
	echo "<table class=\"sitestable\">\n<tbody>\n
	<tr><th colspan=\"6\">All-site actions</th></tr>
	<tr><td>";
	// All site buttons
	set_switch("Switch all sites live", "Go live with all sites", "partnerproject_useable_all_on.act.php", "s", $userid, "set_green");
	echo "</td><td>";
	set_switch("Switch all sites off", "Temporarily disable all sites", "partnerproject_useable_all_off.act.php", "s", $userid, "set_yellow");
	echo "</td>";
	// Link for history
	echo "<td><a title=\"View history not on this page\" href=\"partner_site_history.php\">Site History (past reports)</a></td>";
	echo "<td></td><td></td><td></td></tr>
	<tr><th>Total Share Count</th><th>Share %</th><th>Shares per click</th><th>All Hits &amp; Feed Requests</th><th>All Ad Views &amp; Listens</th><th>All badAd Clicks</th><th>All Ad Clicks</th></tr>";
	echo "<tr><td>";
	echo $pretty_all_share_count;
	echo "</td><td>";
	echo $pretty_contributionPercent."%";
	echo "</td><td>";
	echo $pretty_clickShareValue;
	echo "</td><td>";
	echo $pretty_all_listed_badad_count;
	echo "</td><td>";
	echo $pretty_all_listed_ad_count;
	echo "</td><td>";
	echo $pretty_all_clicked_badad_count;
	echo "</td><td>";
	echo $pretty_all_clicked_listed_count;
	echo "</td></tr>
	</tbody>\n</table>\n</div>";

	echo "<br /><hr />";

	// Help links
	echo "<h4>Help</h4>";
	echo "<a title=\"View the instructions\" href=\"partner_help.php#zero\">\"Ads to Show\" at zero only lists <b><u>$siteDomain</u></b>, 5 share counts per click</a><br />";
	echo "<a title=\"View the instructions\" href=\"partner_help.php#domains\">You can use the same domain more than once</a><br />";
	echo "<a title=\"View the instructions\" href=\"partner_help.php#hits\">What are hits?</a><br />";
	echo "<a title=\"View the instructions\" href=\"partner_help.php#code\">How to embed the code?</a><br />";

}

// Dev area
echo "<br /><hr /><br />";
set_switch("Developer Center...", "Go to the Developer Center", "partner_dev.php", "partner_dev", $userid, "set_black");
echo "<br /><hr />";

// Danger area
echo "<br /><h3 class=\"note_red\">Danger Zone</h3><br />";

// Delete a Project
if ($havesites == true) {
	set_switch("Delete a Project...", "Go to the Delete site area", "partner_del_project.php", "del_partner_site_page", $userid, "set_red");
	echo "<br /><hr /><br />";
}

// Echo the email for payment
$q = "SELECT email FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows != 0 ) {
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$partner_email = $row[0];
	echo "PayPal payout email address: <b>$partner_email</b>";
	echo "<br /><br />";
	set_switch("Change PayPal payout email address...", "Change this email", "partner_change_email.php", "change_partner_payout_email", $userid, "set_red");
	echo "<br /><hr /><br />";
} // Email check

set_switch("Delete my Partner account...", "Go to the page to delete this Partner account", "partner_del_account.php", "del_partner_account_page", $userid, "set_red");
echo "<br /><hr /><br />";

// Include the HTML footer
include ('./includes/footer.html');
?>
