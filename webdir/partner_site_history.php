<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Require the database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Include the header file
$page_title = "Partner Site History :: $siteTitle";
include ('./includes/header.html');


// Check if partner account has been activated
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows == 1) { // partner account exists in database
	$activation = $row[0];
	if ($activation != "Confirmed") { // Not activated
		header("Location: partner.php");
    exit(); // Quit the script
	}
} else { // No partner application entry
		// Check to see if user's email is verified
		$qe = "SELECT email, email_confirmed FROM users WHERE id='$userid'";
		$re = mysqli_query ($dbc, $qe);
		$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
		$email = "$rowe[0]";
		$email_confirmed = "$rowe[1]";
		if ($email != $email_confirmed) {
			header("Location: partner.php");
	    exit(); // Quit the script
	} else { // email verified
		header("Location: partner.php");
    exit(); // Quit the script
	}
} // activated check complete, user is a fully-fledged partner

// Breadcrumb
echo "<p>&larr; Back to the <a title=\"Partner Center\" href=\"partner.php\">Partner Center</a></p>";


// Heading
echo "<h3>Partner Site History</h3>";

// Partner websites & apps
$q = "SELECT id, domain FROM partnersites WHERE user_id='$userid' ORDER BY domain";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) {
	echo "<p>No site history yet. Keep working and make history!</p>";
} else {
	// Start the table
	echo "<br /><div class\"partnersitestable\">\n<h4>History of your site projects:</h4>";
	echo "<table class=\"sitestable\">\n<tbody>\n";
	echo "<tr><th>Domain</th><th>Share Count</th><th>Hits</th><th>Ads Viewed</th><th>badAd Clicks</th><th>Ad Clicks</th><th>Date it became history</th></tr>";
	while ($row = mysqli_fetch_array($r)) {
		$site_id = "$row[0]";
		$site_domain = "$row[1]";
		// Query the history for each partner site
		$qh = "SELECT listed_badad_count, listed_ad_count, clicked_badad_count, clicked_listed_count, date_calculated FROM partnersites_tallied WHERE site_id='$site_id'";
		$rh = mysqli_query ($dbc, $qh);
		$rows = mysqli_num_rows($rh);
		if ($rows == 0) { // If there is no history
			// Domain
			echo "<tr><td align=\"left\"><b>$site_domain</b></td>";
			// Hits
			echo "<td align=\"center\">No records this far back</td>";
			echo "<td align=\"center\"><span class=\"note_gray\">-</span></td><td align=\"center\"><span class=\"note_gray\">-</span></td><td align=\"center\"><span class=\"note_gray\">-</span></td><td align=\"center\"><span class=\"note_gray\">-</span></td>";
			// Date calculated
			echo "<td align=\"center\">";
			echo "<span class=\"note_gray\">NEVER</span>";
			echo "</td>";
			// Finish the row
			echo "</tr>";
		} else {
			$row = mysqli_fetch_array($rh, MYSQLI_NUM);
			$site_listed_badad_count = "$row[0]";
			$site_listed_ad_count = "$row[1]";
			$site_clicked_badad_count = "$row[2]";
			$site_clicked_listed_count = "$row[3]";
			$site_date_calculated = "$row[4]";


			$site_share_count = (($site_clicked_listed_count * 5) + ($site_clicked_badad_count * 5) + $site_listed_ad_count + $site_listed_badad_count);
			// Be Pretty
			$pretty_site_listed_badad_count = number_format($site_listed_badad_count);
			$pretty_site_listed_ad_count = number_format($site_listed_ad_count);
			$pretty_site_clicked_badad_count = number_format($site_clicked_badad_count);
			$pretty_site_clicked_listed_count = number_format($site_clicked_listed_count);
			$pretty_site_share_count = number_format($site_share_count);

			// Iterate each project site into the table
			// Domain
			echo "<tr><td align=\"left\"><b>$site_domain</b></td>";

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

			// Date calculated
			echo "<td align=\"center\">";
			echo "<span class=\"note_gray\">$site_date_calculated</span>";
			echo "</td>";

			// Finish the row
			echo "</tr>";
		}
	}
	// Finish the table
	echo "</tbody>\n</table>\n</div>\n<br />";
	// Close the table section
	echo "<br /><hr />";
}

















// Partner podcasts
// Get the user's info to populate the profile
$q = "SELECT project_id, slug FROM feeds WHERE user_id='$userid' ORDER BY domain";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows > 0) {
	// Heading
	echo "<h3>Partner Podcast History</h3>";

	// Start the table
	echo "<br /><div class\"partnersitestable\">\n<h4>History of your site projects:</h4>";
	echo "<table class=\"sitestable\">\n<tbody>\n";
	echo "<tr><th>Slug (#ID)</th><th>Share Count</th><th>Feed Requests</th><th>Ad Downloads</th><th>Link Clicks</th><th>Date it became history</th></tr>";
	while ($row = mysqli_fetch_array($r)) {
		$project_id = "$row[0]";
		$pod_slug = "$row[1]";
		// Query the history for each partner site
		$qh = "SELECT feed_requested_count, ad_download_count, ad_click_count, date_calculated FROM feeds_tallied WHERE project_id='$project_id'";
		$rh = mysqli_query ($dbc, $qh);
		$rows = mysqli_num_rows($rh);
		if ($rows == 0) { // If there is no history
			// Domain
			echo "<tr><td align=\"left\"><b>$pod_slug</b> (#$project_id)</td>";
			// Hits
			echo "<td align=\"center\">No records this far back</td>";
			echo "<td align=\"center\"><span class=\"note_gray\">-</span></td><td align=\"center\"><span class=\"note_gray\">-</span></td><td align=\"center\"><span class=\"note_gray\">-</span></td>";
			// Date calculated
			echo "<td align=\"center\">";
			echo "<span class=\"note_gray\">NEVER</span>";
			echo "</td>";
			// Finish the row
			echo "</tr>";
		} else {
			$row = mysqli_fetch_array($rh, MYSQLI_NUM);
			$pod_feed_requested_count = "$row[0]";
			$pod_ad_download_count = "$row[1]";
			$pod_ad_click_count = "$row[2]";
			$pod_date_calculated = "$row[3]";

			$pod_share_count = (($pod_ad_click_count * 5) + $pod_ad_download_count + $pod_feed_requested_count);
			// Be Pretty
			$pretty_pod_feed_requested_count = number_format($pod_feed_requested_count);
			$pretty_pod_ad_download_count = number_format($pod_ad_download_count);
			$pretty_pod_ad_click_count = number_format($pod_ad_click_count);
			$pretty_pod_share_count = number_format($pod_share_count);

			// Iterate each project site into the table
			// Domain
			echo "<tr><td align=\"left\"><b>$pod_slug</b> (#$project_id)</td>";

			// Share Count
			echo "<td align=\"left\">$pretty_pod_share_count</td>";

			// Feed Request Count
			echo "<td align=\"left\">$pretty_pod_feed_requested_count</td>";

			// Ad Downloads
			echo "<td align=\"left\">$pretty_pod_ad_download_count</td>";

			// Link Clicks
			echo "<td align=\"left\">$pretty_pod_ad_click_count</td>";

			// Date calculated
			echo "<td align=\"center\">";
			echo "<span class=\"note_gray\">$pod_date_calculated</span>";
			echo "</td>";

			// Finish the row
			echo "</tr>";
		}
	}
	// Finish the table
	echo "</tbody>\n</table>\n</div>\n<br />";
	// Close the table section
	echo "<br /><hr />";
}

// Include the HTML footer
include ('./includes/footer.html');
?>
