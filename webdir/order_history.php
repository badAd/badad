<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
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

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Get email confirmation status from the dateabse
$q = "SELECT email, confirmed_email FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$email = "$row[0]";
$confirmed_email = "$row[1]";
if ($email == $confirmed_email) {
	$confirmedYN = "Confirmed";
	if (isset($_SESSION['email_unconfirmed'])) {unset($_SESSION['email_unconfirmed']);}
} else {
	$confirmedYN = "Unconfirmed";
	$_SESSION['email_unconfirmed'] = true;
}

// Get the year range
if ((isset($_POST['order_history_year'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['order_history_year']))) {$IP = get_ip_addr(); script_kiddy('sk_74', '_POST order_history_year', $_POST['order_history_year'], $IP);}
if (isset($_POST['order_history_year']) && (filter_var($_POST['order_history_year'], FILTER_VALIDATE_INT, array('min_range' => 4, 'max_range' => 4)))) {
 $show_hist_year = preg_replace("/[^A-Za-z0-9]/","", $_POST['order_history_year']);
 $showing_range = "$show_hist_year";
 $show_hist_year_end = $show_hist_year + 1;
 $show_start_date = "$show_hist_year-01-01";
 $show_end_date = "$show_hist_year_end-01-01";
 $qh = "SELECT id, paid_amount, payment_date_time, ad_nickname, date_starts, date_expires, ad_biz_listing, pub_status, modified_yn, rerun_id, rerun_how, podcast_ad FROM ads WHERE user_id='$userid' AND payment_date_time >= '$show_start_date' AND payment_date_time < '$show_end_date' ORDER BY id DESC LIMIT 100";
} else {
 $show_hist_year = "LAST_100";
 $showing_range = "last 100 orders";
 $qh = "SELECT id, paid_amount, payment_date_time, ad_nickname, date_starts, date_expires, ad_biz_listing, pub_status, modified_yn, rerun_id, rerun_how, podcast_ad FROM ads WHERE user_id='$userid' ORDER BY id DESC LIMIT 100";
}

// Include the header
$page_title = "Your Past Orders :: $siteTitle";
include ('./includes/header.html');

// Successful approval?
if ((isset($_POST['ad_review'])) && ($_POST['ad_review'] == 'resubmitted')) {
 echo '<p class="note_blue">Manuscript updated and queued for review!</p>';
} elseif ((isset($_POST['ad_review'])) && ($_POST['ad_review'] == 'approved')) {
	// Start the page
	echo '<p class="note_green">Manuscript approved and queued for voice recording!</p>';
}

// Podcast ads requiring action?
$qa = "SELECT ad_id, date_modified, status FROM pod_ads WHERE customer_user='$userid' AND status='edited' ORDER BY ad_id DESC LIMIT 100";
$ra = mysqli_query($dbc, $qa);
$qb = "SELECT ad_id, date_modified, status FROM pod_ads WHERE customer_user='$userid' AND status='inreview' OR status='resubmitted' OR status='approved' OR status='record' OR status='rerecord' ORDER BY ad_id DESC LIMIT 100";
$rb = mysqli_query($dbc, $qb);

if ((mysqli_num_rows($ra) > 0) || (mysqli_num_rows($rb) > 0)) {
	// Podcasts?
	echo '<h3>Pending Podcast Ads</h3>';

	if (mysqli_num_rows($ra) > 0) {

		echo '<h4>Podcast ads waiting for <span class="note_badad">your</span> attention:</h4>';
		echo '<table><tbody>';
		while ($row = mysqli_fetch_array($ra, MYSQLI_NUM)) {
			$ad_id = "$row[0]";
			$date_modified = "$row[1]";
			$status = "$row[2]";

			// Get nickname & invoice
			$qn = "SELECT ad_nickname FROM ads WHERE user_id='$userid' AND id='$ad_id'";
			$rn = mysqli_query($dbc, $qn);
			$rown = mysqli_fetch_array($rn, MYSQLI_NUM);
			$ad_nickname = "$rown[0]";

			// echo the row
			echo "<td>Inv. $ad_id</td><td>\"$ad_nickname\"</td><td>Edit submitted: $date_modified</td><td><a href='https://$siteDomain/ad_review.php?pa=$ad_id'>Review changes &rarr;</a></td>";

		}
		echo '</tbody></table><br />';
	}

	// Podcast ads waiting for approval
	if (mysqli_num_rows($rb) > 0) {

		echo '<h4>Podcast ads waiting for the <span class="note_badad">badAd team\'s</span> attention:</h4>';
		echo '<table><tbody>';
		while ($row = mysqli_fetch_array($rb, MYSQLI_NUM)) {
			$ad_id = "$row[0]";
			$date_modified = "$row[1]";
			$status = "$row[2]";

			// Get nickname & invoice
			$qn = "SELECT ad_nickname FROM ads WHERE user_id='$userid' AND id='$ad_id'";
			$rn = mysqli_query($dbc, $qn);
			$rown = mysqli_fetch_array($rn, MYSQLI_NUM);
			$ad_nickname = "$rown[0]";

			// Pretty status
			switch ($status) {
				case 'inreview':
					$status_message = "Manuscript submitted and in review";
				break;
				case 'resubmitted':
					$status_message = "Manuscript re-submitted and in review";
				break;
				case 'approved':
					$status_message = "Manuscript approved, queued for recording";
				break;
				case 'recorded'|'rerecord':
					$status_message = "Recording finished, awaiting final approval";
				break;
			}
			// echo the row
			echo "<td>Inv. $ad_id</td><td>\"$ad_nickname\"</td><td>Last action:</td><td>$date_modified</td><td>$status_message</td>";

		}
		echo '</tbody></table><br />';
	}
}

// Order history
echo '<h3>Order History</h3><p><b>Orders for '.$showing_range.':</b> <a title="View simple list for printing" href="badad_order_history_'.$show_hist_year.'.html" target="_blank">HTML printable version</a> | <a href="badad_order_history_'.$show_hist_year.'.xml" download="badad_order_history_'.$show_hist_year.'.xml">Download raw XML for '.$showing_range.'</a></p>';

/* This should be adopted for an admin order history page
// Get the orders
// Admin
	if (isset($_SESSION['user_admin'])) {
		$q = "SELECT id, paid_amount, payment_date_time, ad_nickname, date_starts, date_expires, ad_biz_listing, pub_status, modified_yn, rerun_id, rerun_how FROM ads ORDER BY id DESC";
	} else {

// Normal user
		$q = "SELECT id, paid_amount, payment_date_time, ad_nickname, date_starts, date_expires, ad_biz_listing, pub_status, modified_yn, rerun_id, rerun_how FROM ads WHERE user_id='$userid' ORDER BY id DESC";
} */


$rh = mysqli_query($dbc, $qh);
if (mysqli_num_rows($rh) > 0) {

	// Get the time
	$timeNow = date("Y-m-d H:i:s");

  // Date range
  echo "<form action=\"order_history.php\"  method=\"post\" accept-charset=\"utf-8\" class=\"rangehistform\">
  <select class=\"formselect\" name=\"order_history_year\">
  	<option disabled value=\"right_now\" selected hidden>Choose a specific year...</option>";
  $q = "SELECT DISTINCT YEAR(payment_date_time) FROM ads WHERE user_id='$userid' ORDER BY YEAR(payment_date_time) DESC";
  $r = mysqli_query($dbc, $q);
  while ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
  	$hist_year = "$row[0]";
  	echo "<option value=\"$hist_year\">$hist_year</option>";
  }
  echo "</select>
  <input type=\"submit\" name=\"submit_button\" value=\"Go &rarr;\" id=\"submit_button\" class=\"formbutton\" />
  </form><br />";

	// Start the table
	echo "<div class=\"ordertable\">\n<table class=\"ordertable\">\n";
	echo "<tr><th>Inv No</th><th>Amt</th><th>Nickname <i>(click to view)</i></th><th>Created</th><th>Runs (from/to)</th><th style='text-align:center;'>Type</th><th style='text-align:center;'>Stats</th><th style='text-align:center;'>Status</th><th style='text-align:center;'>Actions</th></tr>";

	// Get info from each order
	while ($row = mysqli_fetch_array($rh, MYSQLI_NUM)) {
		$invoiceNo = "$row[0]";
		$paymentAmnt = "$row[1]";
		$paymentDate = "$row[2]";
		$adNickname = "$row[3]";
		$adStarts = "$row[4]";
		$runUntil = "$row[5]";
		$bizListing = "$row[6]";
		$pubStatus = "$row[7]";
		$modified_yn = "$row[8]";
		$rerun_id = "$row[9]";
		$rerun_how = "$row[10]";
		$podcast_ad = "$row[11]";

		// Business Listing?
		if ($bizListing == 'non') {
			$listAs = "Normal";
		} elseif ($bizListing == 'biz') {
			$listAs = ($podcast_ad == 0) ? "Business" : "Business<br>&amp;<br>Podcast";
		}

		// Check if expired
		if (($timeNow > $runUntil) && ($pubStatus == 'live')) {
			$pubStatus = 'expired';
			$q = "UPDATE ads SET pub_status='expired' WHERE id='$invoiceNo'";
			$r = mysqli_query ($dbc, $q);
			if (mysqli_affected_rows($dbc) == 1) {
			  $expired_set = true;
			}
		 }

		 // Pretty Origin
		 if ($rerun_how != 'Original') {
			 $prettyOrigin = "$rerun_how from #$rerun_id";
		 } else {
			 $prettyOrigin = $rerun_how;
		 }

			// Pretty payment amount
			if ($paymentAmnt != NULL) {
			$prettyPaymentAmnt = "\$$paymentAmnt";
		} else {
			$prettyPaymentAmnt = "N/A";
		}
			// Display each ad record
			if (($timeNow < $adStarts) && ($pubStatus == 'live')) { // waiting
				$pubStatus = 'waiting';
				echo "<tr class=\"waiting\"><td>$invoiceNo</td><td>$prettyPaymentAmnt</td><td><a target=\"_blank\" title=\"Click to view\" href=\"view_ad.php?a=$invoiceNo\">$adNickname</a></td><td>$paymentDate<br />$prettyOrigin</td><td><b>$adStarts</b><br />$runUntil</td><td style='text-align:center;'>$listAs</td><td></td><td style='text-align:center;'>$pubStatus</td>";
			} elseif ($timeNow < $adStarts) {
				echo "<tr><td>$invoiceNo</td><td>$prettyPaymentAmnt</td><td><a target=\"_blank\" title=\"Click to view\" href=\"view_ad.php?a=$invoiceNo\">$adNickname</a></td><td>$paymentDate<br />$prettyOrigin</td><td>$adStarts<br />$runUntil</td><td style='text-align:center;'>$listAs</td><td>";
				// Business ad?
				if ($bizListing == 'biz') {
					if ($confirmedYN == "Confirmed") {
					// Separator
					echo "<br /><br />";
					if ($podcast_ad == 0) {
						set_switch("stats", "View stats for $adNickname", "business_ad_stats.php", "a", $invoiceNo, "set_blue");
					} else {
						set_switch("stats", "View stats for $adNickname", "podcast_ad_stats.php", "a", $invoiceNo, "set_blue");
					}
					// Separator
					echo "<br />";
				} else {
					echo "<a class=\"note_gray\" title=\"Click to send a confirmation link to your email address\" href=\"confirm_email.php\">confirm email to view stats</a><br /><br />";
				}
				}
				echo"</td><td style='text-align:center;'>$pubStatus</td>";
			} elseif ($timeNow > $adStarts) { // running
				echo "<tr><td>$invoiceNo</td><td>$prettyPaymentAmnt</td><td><a target=\"_blank\" title=\"Click to view\" href=\"view_ad.php?a=$invoiceNo\">$adNickname</a></td><td>$paymentDate<br />$prettyOrigin</td><td>$adStarts<br />$runUntil</td><td style='text-align:center;'>$listAs</td><td style='text-align:center;'>";
        // Pending?
        if ($pubStatus != "pending") {

  				// Business ad?
  				if ($bizListing == 'biz') {
						if ($confirmedYN == "Confirmed") {

						if ($podcast_ad == 0) {
							set_switch("stats", "View stats for $adNickname", "business_ad_stats.php", "a", $invoiceNo, "set_blue");
						} else {
							set_switch("biz stats", "View business ad stats for $adNickname", "business_ad_stats.php", "a", $invoiceNo, "set_blue");
							echo "<br />";
							set_switch("pod stats", "View podcast ad stats for $adNickname", "podcast_ad_stats.php", "a", $invoiceNo, "set_blue");
						}
						// Separator
						echo "<br />";
						} else {
							echo "<a class=\"note_gray\" title=\"Click to send a confirmation link to your email address\" href=\"confirm_email.php\">confirm email to view stats</a><br /><br />";
						}
  				}
          // raw XML for all info
					if ($podcast_ad != 0) {
						echo "<a title=\"Download all podcast ad stat history in raw XML for this ad\" href=\"".$invoiceNo."_badad_pod_stats.xml\" download=\"badad_pod_hist_$invoiceNo.xml\">raw pod XML</a>";
						echo "<br />";
						echo "<a title=\"Download all text ad stat history in raw XML for this ad\" href=\"".$invoiceNo."_badad_ad_stats.xml\" download=\"badad_ad_hist_$invoiceNo.xml\">raw text XML</a>";
					} else {
          	echo "<a title=\"Download all text ad stat history in raw XML for this ad\" href=\"".$invoiceNo."_badad_ad_stats.xml\" download=\"badad_ad_hist_$invoiceNo.xml\">raw text XML</a>";
					}
        } // Not pending

        // Separator
        echo "<br />";

				echo"</td><td style='text-align:center;'>$pubStatus</td>";
			}

			// Actions
			echo "<td align=\"center\">";
			// Rerun
			if ($pubStatus != "pending") { // Not a pending ad
        if ($rerun_id == NULL) {$rerun_act_id = $invoiceNo;} else {$rerun_act_id = $rerun_id;} // Use the Original ad's ID for reruns
				set_switch("rerun", "Run this same ad again", "rerun_ad.act.php", "a", $rerun_act_id, "set_green");
			} else {
				set_switch("remove", "Remove this pending ad from order history", "delpend_ad.act.php", "a", $invoiceNo, "set_violet");

			}
			// Separator
			echo "<br />";

			// Modify
				// Check whether the mod is allowed
				$epochNow = strtotime($timeNow);
				$epochStart = strtotime($adStarts);
				$epochExpire = strtotime($runUntil);
				$epochChangable = ($epochStart + 4838400); // Add 8 weeks to the start epoch
				$epochLastChange = ($epochExpire - 604800); // One week before ad expires

			if (($bizListing == 'biz') && ($modified_yn == 'Final') && ($epochLastChange > $epochNow) && ($epochLastChange > $epochChangable) && ($pubStatus != "waiting")) {
				set_switch("modify", "Make changes to this ad (once per 8 weeks)", "mod_ad.act.php", "a", $invoiceNo, "set_orange");
				// Separator
				echo "<br />";
			}

			// Kill
			if ($pubStatus == "live") {
				set_switch("kill", "Temporarily kill this ad before its expiration date", "kill_ad.act.php", "a", $invoiceNo, "set_red");
			} elseif ($pubStatus == "dead") {
				set_switch("go live", "Make the ad live again", "resurrect_ad.act.php", "a", $invoiceNo, "set_violet");
			} elseif ($pubStatus == "pending") {
				set_switch("checkout", "Finish and pay for this ad", "finish_ad.act.php", "a", $invoiceNo, "set_green");
			} elseif ($pubStatus == "waiting") {
				set_switch("edit", "Make changes to this ad before it runs", "edit_ad.act.php", "a", $invoiceNo, "set_yellow");
				// Separator
				echo "<br />";
				set_switch("kill", "Temporarily kill this ad before its expiration date", "kill_ad.act.php", "a", $invoiceNo, "set_red");
			} elseif ($pubStatus == "expired") {
				echo "<span class=\"note_gray\">expired</span>";
			}

			echo "</td></tr>";

	}
	// End the table
	echo "</table></div>";

} else { // No pages available.
	echo '<p>No order history to show.</p>';
}

// Include the HTML footer
include ('./includes/footer.html');
?>
