<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Require the database connection
require (MYSQL);

// Get the year range
if (($_SERVER['REQUEST_METHOD'] == 'GET') && (isset($_GET['y'])) && ((filter_var($_GET['y'], FILTER_VALIDATE_INT, array('min_range' => 4, 'max_range' => 4))) || ($_GET['y'] == 'LAST_100'))) {
	 $show_hist_year = preg_replace("/[^A-Za-z0-9_]/","", $_GET['y']);
	 if ($show_hist_year == "LAST_100") {
		 $showing_range = "last 100 orders";
		 $qh = "SELECT id, paid_amount, payment_date_time, ad_nickname, date_starts, date_expires, ad_biz_listing, rerun_id, rerun_how FROM ads WHERE user_id='$userid' ORDER BY id DESC LIMIT 100";
	 } else {
		 $showing_range = "$show_hist_year";
		 $show_hist_year_end = $show_hist_year + 1;
		 $show_start_date = "$show_hist_year-01-01";
		 $show_end_date = "$show_hist_year_end-01-01";
		 $qh = "SELECT id, paid_amount, payment_date_time, ad_nickname, date_starts, date_expires, ad_biz_listing, rerun_id, rerun_how FROM ads WHERE user_id='$userid' AND payment_date_time >= '$show_start_date' AND payment_date_time < '$show_end_date' ORDER BY id DESC LIMIT 100";
	 }
 } else {
	 header("Location: index.php");
	 exit(); // Quit the script
 }

// Get the user's email
$q = "SELECT email FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$user_email = $row[0];

echo "<h3>$siteTitle - order history ($showing_range) for: $user_email</h3>
<i>Email address may differ from those receipts were issued to.</i>";

// Get the orders
		$rh = mysqli_query($dbc, $qh);
if (mysqli_num_rows($rh) > 0) {

		// Start the table
		echo "<div>\n<table style=\"text-align: left;\" cellspacing=\"21\">\n";
		echo "<tr><th>Inv No</th><th>Amt</th><th>Created</th><th>Runs (from/to)</th><th>Nickname</th><th>List As</th></tr>";

		// Get info from each order
		while ($row = mysqli_fetch_array($rh, MYSQLI_NUM)) {
			$invoiceNo = "$row[0]";
			$paymentAmnt = "$row[1]";
			$paymentDate = "$row[2]";
			$adNickname = "$row[3]";
			$adStarts = "$row[4]";
			$runUntil = "$row[5]";
			$bizListing = "$row[6]";
			$rerun_id = "$row[7]";
			$rerun_how = "$row[8]";

			// Business Listing?
			if ($bizListing == 'non') {
				$listAs = "Normal";
			} elseif ($bizListing == 'biz') {
				$listAs = "Business";
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
		echo "<tr><td>$invoiceNo</td><td>$prettyPaymentAmnt</td><td>$paymentDate<br />$prettyOrigin</td><td>$adStarts<br />$runUntil</td><td>$adNickname</td><td>$listAs</td></tr>";
}
		// End the table
		echo "</table></div>";

} else { // No pages available.
	echo '<p>No order history to show.</p>';
}

?>
