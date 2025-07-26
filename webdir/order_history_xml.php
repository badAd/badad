<?php

// Configs
require_once ('./includes/config.inc.php');
require_once (MYSQL);

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Get the date
$timeNow = date("Y-m-d H:i:s");
// Time Zone
// $q = "SELECT @@system_time_zone";
// $r = mysqli_query ($dbc, $q);
// $row = mysqli_fetch_array($r);
// $timeZone = "$row[0]";
$timeZone = date("O");

// Check for form submission and set the $adID variable
if (($_SERVER['REQUEST_METHOD'] == 'GET') && (isset($_GET['y'])) && ((filter_var($_GET['y'], FILTER_VALIDATE_INT, array('min_range' => 4, 'max_range' => 4))) || ($_GET['y'] == 'LAST_100'))) {
	$show_hist_year = preg_replace("/[^A-Za-z0-9_]/","", $_GET['y']);
	if ($show_hist_year == "LAST_100") {
		$showing_range = "last 100 orders";
		$qh = "SELECT id, paid_amount, payment_date_time, date_starts, date_expires, rerun_id, rerun_how, ad_lang, category_id, subcat_id, role_id, tag_ids, ad_comment, ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, receipt_url, transaction_id FROM ads WHERE user_id='$userid' ORDER BY id DESC LIMIT 100";
	} else {
		$showing_range = "$show_hist_year";
		$show_hist_year_end = $show_hist_year + 1;
		$show_start_date = "$show_hist_year-01-01";
		$show_end_date = "$show_hist_year_end-01-01";
		$qh = "SELECT id, paid_amount, payment_date_time, date_starts, date_expires, rerun_id, rerun_how, ad_lang, category_id, subcat_id, role_id, tag_ids, ad_comment, ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, receipt_url, transaction_id FROM ads WHERE user_id='$userid' AND payment_date_time >= '$show_start_date' AND payment_date_time < '$show_end_date' ORDER BY id DESC LIMIT 100";
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

// See if user owns the site & get important information
$rh = mysqli_query ($dbc, $qh);
if (mysqli_num_rows($rh) == 0) {
	header("Location: partner.php");
	exit(); // Quit the script
}

// Start the document
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "
<doc_title>$siteTitle - Order History</doc_title>
<badad_url>https://$siteDomain</badad_url>
<title>badAd Order History _ $user_email (#$userid) _ $showing_range</title>
<notes>This does not include statistics dada for any ad; statistics will need the raw XML under Type/Stats in Order History</notes>
";

// Orders
// Activity check
if (mysqli_num_rows($rh) == 0) {
	echo "<orders>none</orders>";
} else {
	echo "<orders>";

	// Iterate the orders
	while ($row = mysqli_fetch_array($rh)) {
		$invoiceNo = $row[0];
		$paymentAmnt = $row[1];
		$paymentDate = $row[2];
		$adStarts = $row[3];
		$runUntil = $row[4];
		$rerun_id = $row[5];
		$rerun_how = $row[6];
		$ad_lang = $row[7];
		$category_id = $row[8];
		$subcat_id = $row[9];
		$role_id = $row[10];
		$tag_ids = $row[11];
		$ad_comment = $row[12];
		$ad_nickname = $row[13];
		$ad_content_hdng = $row[14];
		$ad_content_dscr = $row[15];
		$ad_content_info = $row[16];
		$ad_content_pyrt = $row[17];
		$ad_content_cntc = $row[18];
		$ad_content_bizn = $row[19];
		$ad_biz_listing = $row[20];
		$receipt_url = $row[21];
		$transaction_id = $row[22];
		if ($rerun_id == NULL) {$rerun_id = "none";}
		if ($ad_biz_listing == "non") {$ad_content_bizn = "none";}

		// Epochs
		$paymentDate_epoch = strtotime("$paymentDate");
		$adStarts_epoch = strtotime("$adStarts");
		$runUntil_epoch = strtotime("$runUntil");

		// Get the listad role
		$rlaq = "SELECT role FROM roles WHERE id='$role_id'";
		$rlar = mysqli_query ($dbc, $rlaq);
		$rlarow = mysqli_fetch_array($rlar, MYSQLI_NUM);
		$rolename = "$rlarow[0]";

		// Get the category from its ID
		$cq = "SELECT category, slug FROM categories WHERE id='$category_id'";
		$cr = mysqli_query ($dbc, $cq);
		$crow = mysqli_fetch_array($cr, MYSQLI_NUM);
		$catname = "$crow[0]";
		$catslug = "$crow[1]";

		// Get the subcategory from its ID
		$scq = "SELECT subcat FROM sub_$catslug WHERE id='$subcat_id'";
		$scr = mysqli_query ($dbc, $scq);
		$scrow = mysqli_fetch_array($scr, MYSQLI_NUM);
		$subcatname = "$scrow[0]";

		// tags
		$arrayTagIDs = explode(',', $tag_ids);
		$tag_row = '';
		foreach($arrayTagIDs as $tagID){
			// Get the tag from its ID
			$tq = "SELECT tag FROM tags WHERE id='$tagID'";
			$tr = mysqli_query ($dbc, $tq);
			$trow = mysqli_fetch_array($tr, MYSQLI_NUM);
			$tag = "$trow[0]";
			$tag_row .= $tag.', ';
		}

		// Render
		echo "
  <order>
    <ad_invoice>$invoiceNo</ad_invoice>
    <ad_payment_amnt>$paymentAmnt</ad_payment_amnt>
    <ad_payment_date>$paymentDate</ad_payment_date>
    <ad_payment_date_epoch>$paymentDate_epoch</ad_payment_date_epoch>
    <ad_rerun_type>$rerun_how</ad_rerun_type>
    <ad_rerun_original_id>$rerun_id</ad_rerun_original_id>
    <ad_lang>$ad_lang</ad_lang>
    <role>$rolename</role>
    <category>$catname</category>
    <subcategory>$subcatname</subcategory>
    <tags>$tag_row</tags>
    <ad_comment>$ad_comment</ad_comment>
    <ad_nickname>$ad_nickname</ad_nickname>
    <ad_heading>$ad_content_hdng</ad_heading>
    <ad_description>$ad_content_dscr</ad_description>
    <ad_info>$ad_content_info</ad_info>
    <ad_payrate>$ad_content_pyrt</ad_payrate>
    <ad_contact>$ad_content_cntc</ad_contact>
    <ad_businessname>$ad_content_bizn</ad_businessname>
    <timezone>$timeZone</timezone>
    <ad_starts>$adStarts</ad_starts>
    <ad_runs_until>$runUntil</ad_runs_until>
    <ad_epoch_starts>$adStarts_epoch</ad_epoch_starts>
    <ad_epoch_runs_until>$runUntil_epoch</ad_epoch_runs_until>
    <receipt_url>$receipt_url</receipt_url>
    <transaction_id>$transaction_id</transaction_id>
  </order>";
	}

// Finish the orders
echo "
</orders>";
}

?>
