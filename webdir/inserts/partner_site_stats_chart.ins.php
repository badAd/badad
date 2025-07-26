<?php

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Set a variable for the userid
$userid = $_SESSION['user_id'];

// $siteID must be set

// Several Kiddy Checks
if ((isset($_POST['clickathon_cust'])) && (preg_match ('/[^0-9]$/i', $_POST['clickathon_cust']))) {$IP = get_ip_addr(); script_kiddy('sk_34', '_POST clickathon_cust', $_POST['clickathon_cust'], $IP);}
if ((isset($_POST['clickathon_367'])) && (preg_match ('/[^0-9]$/i', $_POST['clickathon_367']))) {$IP = get_ip_addr(); script_kiddy('sk_35', '_POST clickathon_367', $_POST['clickathon_367'], $IP);}
if ((isset($_POST['clickathon_183'])) && (preg_match ('/[^0-9]$/i', $_POST['clickathon_183']))) {$IP = get_ip_addr(); script_kiddy('sk_36', '_POST clickathon_183', $_POST['clickathon_183'], $IP);}
if ((isset($_POST['clickathon_92'])) && (preg_match ('/[^0-9]$/i', $_POST['clickathon_92']))) {$IP = get_ip_addr(); script_kiddy('sk_37', '_POST clickathon_92', $_POST['clickathon_92'], $IP);}
if ((isset($_POST['clickathon_31'])) && (preg_match ('/[^0-9]$/i', $_POST['clickathon_31']))) {$IP = get_ip_addr(); script_kiddy('sk_38', '_POST clickathon_31', $_POST['clickathon_31'], $IP);}
if ((isset($_POST['clickathon_15'])) && (preg_match ('/[^0-9]$/i', $_POST['clickathon_15']))) {$IP = get_ip_addr(); script_kiddy('sk_39', '_POST clickathon_15', $_POST['clickathon_15'], $IP);}
if ((isset($_POST['clickathon_48'])) && (preg_match ('/[^0-9]$/i', $_POST['clickathon_48']))) {$IP = get_ip_addr(); script_kiddy('sk_40', '_POST clickathon_48', $_POST['clickathon_48'], $IP);}

// Check clickathon
if (isset($_POST['clickathon_cust'])) {$clickathon = $_POST['clickathon_cust'] +1;}
elseif (isset($_POST['clickathon_367'])) {$clickathon = $_POST['clickathon_367'] +1;}
elseif (isset($_POST['clickathon_183'])) {$clickathon = $_POST['clickathon_183'] +1;}
elseif (isset($_POST['clickathon_92'])) {$clickathon = $_POST['clickathon_92'] +1;}
elseif (isset($_POST['clickathon_31'])) {$clickathon = $_POST['clickathon_31'] +1;}
elseif (isset($_POST['clickathon_15'])) {$clickathon = $_POST['clickathon_15'] +1;}
elseif (isset($_POST['clickathon_48'])) {$clickathon = $_POST['clickathon_48'] +1;}
else {$clickathon = 0;}
	// Redirect if too much
	if ($clickathon > 20) {
		$_SESSION['stop_clicking'] = true;
		header("Location: stop_clicking_partner.php");
		exit(); // Quit the script
	}

// See if user owns the site & get Date created
$q = "SELECT date_created, domain, nickname, type, serial_no FROM partnersites WHERE id='$siteID' AND user_id='$userid'";
$r = mysqli_query ($srv_dbc, $q);
if (mysqli_num_rows($r) == 0) {
	header("Location: partner.php");
	exit(); // Quit the script
} else {
	$row = mysqli_fetch_array($r);
	$site_date_created = $row[0];
	$site_domain = $row[1];
	$site_nickname = $row[2];
	$site_type = $row[3];
	$slug = $row[4];
}

// Nice number function
// Thanks https://stackoverflow.com/a/10221725/10343144
function nice_number($n) {
		// Strip any formatting
		$n = (0+str_replace(",", "", $n));

		// Real number?
		if (!is_numeric($n)) return false;

		// Filter
		if ($n > 1000000000000) return round(($n/1000000000000), 2).'T';
		elseif ($n > 1000000000) return round(($n/1000000000), 2).'B';
		elseif ($n > 1000000) return round(($n/1000000), 2).'M';
		elseif ($n > 1000) return round(($n/1000), 2).'k';

		// Return the value
		return number_format($n);
}

// Page title
$pretty_title = ($site_type == 'podcast') ? "Podcast '$site_nickname' - $slug" : "$site_domain - $site_nickname";
if ($site_nickname != NULL) {echo "<h3 class=\"stats\">Site: $pretty_title <span class=\"note_gray\">(#$siteID)</span></h3><br />";} else {echo "<h3 class=\"stats\">Site: $site_domain <span class=\"note_gray\">(#$siteID)</span></h3><br />";}

// Set the date range & time settings
	// Get the date
	$timeNow = date("Y-m-d H:i:s");

	// Recent To-Date
	if ((isset($_POST['d'])) && (filter_var($_POST['d'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
		$dago = $_POST['d'];
	} else {
		$dago = 15;
	}

	// Start Date Range
	if (isset($_POST['start_date'])) {
		$start_date = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
	} else {
		$start_date = date("Y-m-d H:i:s", strtotime("-$dago days"));
	}

	// End Date Range
	if (isset($_POST['end_date'])) {
		$end_date = date("Y-m-d H:i:s", strtotime($_POST['end_date']));
	} else {
		$end_date = date("Y-m-d H:i:s"); // Set here if no _POST
	}


	// Make sure the end date range is not 00:00:00
	if ((isset($_POST['cust_date_calpick'])) && ($_POST['cust_date_calpick'] == true)) {
		$corrected_end_date = strtotime("$end_date") + 86399;
		$end_date = date("Y-m-d H:i:s", substr($corrected_end_date, 0, 10));
	}

	// Make sure we're not in the future or before site was created
	if ($end_date > $timeNow) {$end_date = $timeNow;}
	if ($start_date < $site_date_created) {$start_date = $site_date_created; $range_before_created = true;}

	// Calpicker format
	$calpick_start_date = date("m/d/Y", strtotime($start_date));
	$calpick_end_date = date("m/d/Y", strtotime($end_date));
	$calpick_date_created = date("m/d/Y", strtotime($site_date_created));
	$calpick_timeNow = date("m/d/Y", strtotime($timeNow));

	// Human-pretty format
	$pretty_timeNow = date("m/d/Y H:i:s", strtotime($timeNow));
	$pretty_site_date_created = date("m/d/Y H:i:s", strtotime($site_date_created));
	$pretty_start_date = date("m/d/Y H:i:s", strtotime($start_date));
	$pretty_end_date = date("m/d/Y H:i:s", strtotime($end_date));

	// Hours or Days
	if ((isset($_POST['i'])) && (ctype_alpha($_POST['i'])) && ($_POST['i'] == 'hour')) {
		$chart_interval = 'hour';
		$pretty_chart_int = 'Hourly';
		$start_period = date("Y-m-d H:i:s", strtotime("-$dago days"));
	} else {
		$chart_interval = 'day';
		$pretty_chart_int = 'Daily';
		$start_period = date("Y-m-d H:i:s", strtotime("-$dago days"));
	}

	// Date range
	$start_date_epoch = strtotime("$start_date");
	$end_date_epoch = strtotime("$end_date");
	$date_difference = $end_date_epoch - $start_date_epoch;
	if ($chart_interval == 'day') {$start_period_long = date("Y-m-d", substr($start_date_epoch, 0, 10)); $start_period_entry = date("d", substr($start_date_epoch, 0, 10));}
	if ($chart_interval == 'hour') {$start_period_long = date("Y-m-d-H", substr($start_date_epoch, 0, 10)); $start_period_entry = date("H", substr($start_date_epoch, 0, 10));}

	// Add empty date arrays
		// $num_hit_entries: Total time periods in the range
		if ((isset($_POST['cust_date_calpick'])) && ($_POST['cust_date_calpick'] == true)) { // Deal with a rounding fluke and date picking
			if ($chart_interval == 'day') {$num_hit_entries = floor($date_difference / (60 * 60 * 24));}
			if ($chart_interval == 'hour') {$num_hit_entries = floor($date_difference / (60 * 60));}
		} else {
			if ($chart_interval == 'day') {$num_hit_entries = round($date_difference / (60 * 60 * 24));}
			if ($chart_interval == 'hour') {$num_hit_entries = round($date_difference / (60 * 60));}
		}


// Set our arrays
$arrayHits = array();
$arrayPercents = array();
$arrayEntries = array();

// Podcast or website/app?
if (($site_type == 'site') || ($site_type == 'app')) {	// Use CAST(time_date AS DATE) per https://dba.stackexchange.com/questions/108287/why-does-my-query-search-datetime-not-match
	//$q = "SELECT time_date, id FROM seen_partnersite_analytics WHERE project_id='$siteID' AND CAST(time_date AS DATE) >= '$start_date' AND CAST(time_date AS DATE) <= '$end_date' ORDER BY time_date"; // broken
	$q = "SELECT time_date, id FROM seen_partnersite_analytics WHERE project_id='$siteID' AND time_date >= '$start_date' AND time_date <= '$end_date' ORDER BY time_date";
	$r = mysqli_query ($srv_dbc, $q);
} elseif ($site_type == 'podcast') {
	$q = "SELECT time_date, id FROM request_feed_analytics WHERE project_id='$siteID' AND time_date >= '$start_date' AND time_date <= '$end_date' ORDER BY time_date";
	$r = mysqli_query ($agg_dbc, $q);
}

// Loop through each entry in the date range and put them into an array

// Activity check
if (mysqli_num_rows($r) == 0) {
	echo "<h3>Nothing here!</h3><br />";
} else {

		// Show the stats
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
		$arrKey = 0;
		while ($siteRow = mysqli_fetch_array($r)) {
			$date_entry = date_create($siteRow[0]);
		  if ($chart_interval == 'day') {$time_mark_long = date_format($date_entry,"Y-m-d"); $entry_mark = date_format($date_entry,"d");}
			if ($chart_interval == 'hour') {$time_mark_long = date_format($date_entry,"Y-m-d-H"); $entry_mark = date_format($date_entry,"H");}
			// Very first entry
			if (!isset($last_time_mark)) {
				$last_time_mark = $time_mark_long;
				$hitCount = 0;
				$largest_hitCount = 0;
			}

			// Current time period
			if ($time_mark_long == $last_time_mark) {
				$hitCount = $hitCount + 1;

			// Up to a new time period this cycle
			} else {
				$last_time_mark = $time_mark_long; // New time mark
				$arrKey = $arrKey + 1; // Next array key entry
				$hitCount = 1; // First hit for the time mark
			}

			// Make the entry
			$entry = array($hitCount, $time_mark_long, $entry_mark);
			if ($hitCount > $largest_hitCount) {$largest_hitCount = $hitCount;}
			$arrayHits[$arrKey] = $entry;

		} // Date range loop

	// Calculate each entry
	foreach ($arrayHits as $hitCount) {
		$arrayPercents[] = $hitCount[0] / $largest_hitCount * 100;

	} // Percentage array

		// Iterate each entry
		$stat_entry = 1;
		$arrsKey = 0;
		$current_period_epoch = $start_date_epoch;
		$current_period_long = $start_period_long;
		$current_period_entry = $start_period_entry;

		while ($stat_entry <= $num_hit_entries+1) { // This needs to add 1 because of a calculation fluke about rounding and what a range actually is

			// Check & Set the entry
			if ((array_key_exists($arrsKey, $arrayHits)) && ($arrayHits[$arrsKey][1] == $current_period_long)) {
				$hitCount = $arrayHits[$arrsKey][0];
				$hitPercent = $arrayPercents[$arrsKey];
				// Done, increment
				$arrsKey = $arrsKey + 1;
			} else {
				$hitCount = 0;
				$hitPercent = 0;
			}

			// Make the entry
			$nice_hits = nice_number($hitCount);
			$entry = array($current_period_long, $current_period_entry, $hitCount, $hitPercent, $nice_hits);
			$arrayEntries[] = $entry;

			// Increment
			$stat_entry = $stat_entry + 1;
			if ($chart_interval == 'day') {$current_period_epoch = $current_period_epoch + (60 * 60 * 24); $current_period_long = date("Y-m-d", substr($current_period_epoch, 0, 10)); $current_period_entry = date("d", substr($current_period_epoch, 0, 10));}
			if ($chart_interval == 'hour') {$current_period_epoch = $current_period_epoch + (60 * 60); $current_period_long = date("Y-m-d-H", substr($current_period_epoch, 0, 10)); $current_period_entry = date("H", substr($current_period_epoch, 0, 10));}
		} // End add empty dates
//// End loop through each entry in the date range and put them into an array

	// Start the chart
	if ($chart_interval == 'day') {echo "<h3>$pretty_chart_int Stats for ".date("Y-m-d", substr($start_date_epoch, 0, 10))." : ".date("Y-m-d", substr($end_date_epoch, 0, 10))." (EDT)</h3>";}
	if ($chart_interval == 'hour') {echo "<h3>$pretty_chart_int Stats for ".date("Y-m-d H:00", substr($start_date_epoch, 0, 10))." &ndash; ".date("Y-m-d H:00", substr($end_date_epoch, 0, 10))." (EDT)</h3>";}

	echo "
	<section class=\"stats\">
		<div class=\"stat_box\">";

	// Populate the chart
		// Calculate total periods in the range
		$num_entries = max(array_keys($arrayEntries)) +1; // This provides the highest array key, which is the number of entries, currently not using
		// Start the entry counter
		$stat_entry = 1;
		$stat_key = 0;

		// Populate the entries
		while ($stat_entry <= $num_entries) {
			echo '
			<div class="stat_entry" title="'.$arrayEntries[$stat_key][2].' hits, '.$arrayEntries[$stat_key][0].'">
	      <div class="stat_graph" style="height: '.$arrayEntries[$stat_key][3].'%">
	        <div class="stat_count">'.$arrayEntries[$stat_key][4].'</div>
	      </div>';
				// Styles
				if ($chart_interval == 'day') {
					if ($arrayEntries[$stat_key][1] == 01) {
						echo '<div class="stat_name stat_name_date_first">'.$arrayEntries[$stat_key][1].'</div>';
					} elseif ($arrayEntries[$stat_key][1] == 15) {
						echo '<div class="stat_name stat_name_date_fortnite">'.$arrayEntries[$stat_key][1].'</div>';
					} else {
						echo '<div class="stat_name stat_name_date">'.$arrayEntries[$stat_key][1].'</div>';
					}
				}
				if ($chart_interval == 'hour') {echo '<div class="stat_name stat_name_hour">'.$arrayEntries[$stat_key][1].'</div>';}
			echo '
	    </div>';

			$stat_entry = $stat_entry + 1;
			$stat_key = $stat_key + 1;
		}

	// Finish the chart
	echo "
	  </div>
	</section>";

	// Message
	echo '<p>Current time (EDT): <b>'.$pretty_timeNow.'</b> <span class="note_gray"><br />Time range (EDT): <b>'.$pretty_start_date.'</b> &ndash; <b>'.$pretty_end_date.'</b></span>';
	if ((isset($range_before_created)) && ($range_before_created == true)) {echo '<br /><span class="note_blue">Site created (EDT): <b>'.$pretty_site_date_created.'; no stats available before that time.</b></span>';} else {echo '<br /><span class="note_gray">Site created (EDT): <b>'.$pretty_site_date_created.'</b></span>';}
	echo '</p>';

	// Download XML
	$file_start_date_range = date("Y-m-d_H-i-s", strtotime($start_date));
	$file_end_date_range = date("Y-m-d_H-i-s", strtotime($end_date));
	$file_range_name = $site_domain.'_'.$siteID.'_'.$file_start_date_range.'--'.$file_end_date_range;
	$url_range_name = $site_domain.'_'.$siteID.'_'.$start_date_epoch.'-'.$end_date_epoch;
	echo '
	<a href="'.$url_range_name.'_badad_site_stats.xml" download="badad_'.$file_range_name.'.xml">Download raw XML for this range ('.$start_date.' - '.$end_date.')</a>
	<br />
	<br />
	';

	// Time notice
	echo '<p class="note_gray"><i>Statistics are calculated in Eastern Daylight Time. The most recent time segment may vary from eventual reporting. Statistics may not be perfectly accurate. Embedding a code twice may result in double hits, three times tripple, etc. Hits could also be triggered while editing a blog post or website content with the embed code.</i><br /></p>';

} // Finish if statements for Stats in date range


// Choose different stats
echo '<hr /><br />
<div class="choose_stat_range">
	<div class="recent_stat_range_buttons" style="display:inline-block; text-align:left; justify-content:left; align-items:left; width:100%;">';
	// Last 48 hours
	echo '<div class="recent_stat_range_button" style="display:inline-block; margin:0.5em;">
		<form id="last48" action="partner_site_stats.php" method="post">
				<input type="hidden" name="clickathon_48" value="'.$clickathon.'" />
				<input type="hidden" name="i" value="hour" />
				<input type="hidden" name="d" value="2" />
				<input type="hidden" name="s" value="'.$siteID.'" />
				<input type="submit" name="submit_button" value="Last 48 hours" class="set_stat" />
		</form></div>';
	// Last 15 days
	echo '<div class="recent_stat_range_button" style="display:inline-block; margin:0.5em;">
		<form id="last15" action="partner_site_stats.php" method="post">
				<input type="hidden" name="clickathon_15" value="'.$clickathon.'" />
				<input type="hidden" name="i" value="day" />
				<input type="hidden" name="d" value="15" />
				<input type="hidden" name="s" value="'.$siteID.'" />
				<input type="submit" name="submit_button" value="Last 15 days" class="set_stat" />
		</form></div>';
	// Last 31 days
	echo '<div class="recent_stat_range_button" style="display:inline-block; margin:0.5em;">
		<form id="last31" action="partner_site_stats.php" method="post">
				<input type="hidden" name="clickathon_31" value="'.$clickathon.'" />
				<input type="hidden" name="i" value="day" />
				<input type="hidden" name="d" value="31" />
				<input type="hidden" name="s" value="'.$siteID.'" />
				<input type="submit" name="submit_button" value="Last 31 days" class="set_stat" />
		</form></div>';
	// Last 92 days
	echo '<div class="recent_stat_range_button" style="display:inline-block; margin:0.5em;">
		<form id="last92" action="partner_site_stats.php" method="post">
				<input type="hidden" name="clickathon_92" value="'.$clickathon.'" />
				<input type="hidden" name="i" value="day" />
				<input type="hidden" name="d" value="92" />
				<input type="hidden" name="s" value="'.$siteID.'" />
				<input type="submit" name="submit_button" value="Last 92 days" class="set_stat" />
		</form></div>';
	// Last 183 days
	echo '<div class="recent_stat_range_button" style="display:inline-block; margin:0.5em;">
		<form id="last183" action="partner_site_stats.php" method="post">
				<input type="hidden" name="clickathon_183" value="'.$clickathon.'" />
				<input type="hidden" name="i" value="day" />
				<input type="hidden" name="d" value="183" />
				<input type="hidden" name="s" value="'.$siteID.'" />
				<input type="submit" name="submit_button" value="Last 183 days" class="set_stat" />
		</form></div>';
	// Last 367 days
	echo '<div class="recent_stat_range_button" style="display:inline-block; margin:0.5em;">
		<form id="last376" action="partner_site_stats.php" method="post">
				<input type="hidden" name="clickathon_367" value="'.$clickathon.'" />
				<input type="hidden" name="i" value="day" />
				<input type="hidden" name="d" value="367" />
				<input type="hidden" name="s" value="'.$siteID.'" />
				<input type="submit" name="submit_button" value="Last 367 days" class="set_stat" />
		</form></div>';

	// End of options
	echo '</div></div>';

	// Custom date range
			// Javascript to make it all work
			// Thanks https://github.com/asterixcapri/salsa-calendar
			echo '
			<script src="js/SalsaCalendar.min.js"></script>';

	echo '<br />
		<form id="last183" action="partner_site_stats.php" method="post">
			<input type="hidden" name="clickathon_cust" value="'.$clickathon.'" />
			<input type="hidden" name="i" value="day" />
			<input type="hidden" name="s" value="'.$siteID.'" />
			<input type="hidden" name="cust_date_calpick" value="true" />
			Choose a date range in EDT:<br /><br />
			Start date:<br />
			<input type="text" id="start_date" name="start_date" class="salsa-calendar-input" autocomplete="off" value="'.$calpick_start_date.'"><br /><br />
			End date:<br />
			<input type="text" id="end_date" name="end_date" class="salsa-calendar-input" autocomplete="off" value="'.$calpick_end_date.'"><br /><br />
			<script>
			var calendar_from = new SalsaCalendar({
					inputId: \'start_date\',
					lang: \'en\',
					range: {
							min: \''.$calpick_date_created.'\',
							max: \''.$calpick_timeNow.'\'
					},
					calendarPosition: \'top\',
					fixed: false
			});

			var calendar_to = new SalsaCalendar({
					inputId: \'end_date\',
					lang: \'en\',
					range: {
							min: \''.$calpick_date_created.'\',
							max: \''.$calpick_timeNow.'\'
					},
					calendarPosition: \'top\',
					fixed: false
			});
			</script>
			<input type="submit" name="submit_button" value="Custom date range" class="set_stat" style="margin:0.5em;" />
		</form>
		<br />
		<hr />
		<br />
		<br />';
