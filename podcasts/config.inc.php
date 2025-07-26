<?php

// Are we live?
$live = false;
//$live = true;

// Site-wide names
$siteTitle = 'badAd.one';
$siteDomain = 'badad.one'; // Used in content as text & email links, not in settings
$adServeDomain = 'ads.'.$siteDomain; // Used in ad rendering
$apiServeDomain = 'api.'.$siteDomain; // Used in ad rendering
$podcastServeDomain = 'podcasts.'.$siteDomain; // Used in syndicating podcasts
$tagLine = 'BETA';
$badadsrvdir = '/srv/www/badad/';
$clickShareValue = 1000;
$downloadShareValue = 100;
$podcasterShareValue = 10;

// Errors are emailed here
// These forward to: reports@pdt.news
$php_error_email = 'errors@'.$siteDomain;
$sql_error_email = 'errors@'.$siteDomain;
$sql_error_from_email = 'sqlerrors@'.$siteDomain;
$error_from_email = 'errors@'.$siteDomain;
$feedback_email = 'feedbackform@'.$siteDomain;
$feedback_from_email = 'feedback@'.$siteDomain;

// Site email settings
$site_from_email = 'noreply@'.$siteDomain;
$site_from_email_name = "$siteTitle";
$site_email_footer = "<p>Sincerely,<br />$siteTitle<br /><a href=\"https://$siteDomain\">$siteDomain</a></p>";

// Email "Bcc:" for record keeping
$site_bcc_email = '"Sent Bcc" <bcc_sent@'.$siteDomain.'>';
//$site_bcc_email = 'bcc_sent@'.$siteDomain; // This also works

// HTML email requirements
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";


// Determine location of files and the URL of the site:
define ('BASE_URI', '/srv/www/badad/');
define ('BASE_URL', 'badad.one/');
define ('MYSQL', BASE_URI . 'mysql.inc.php');

// Start the session
session_start();


function badad_error_handler ($e_number, $e_message, $e_file, $e_line) {

	// Need these vars
	global $live, $php_error_email, $error_from_email;

	// Build the error message
	$message = "An error occurred in script '$e_file' on line $e_line:\n$e_message\n";

	// Add the backtrace
	$message .= "<pre>" .print_r(debug_backtrace(), 1) . "</pre>\n";

	// Or just append $e_vars to the message
	//	$message .= "<pre>" . print_r ($e_vars, 1) . "</pre>\n";

	if (!$live) { // Show the error in the browser

		echo '<div class="error">' . nl2br($message) . '</div>';

	} else { // Development (print the error)

		// Send the error in an email
		error_log ($message, 1, $php_error_email, 'From:'.$error_from_email.'');

	} // End of $live IF-ELSE

	return true; // So that PHP doesn't try to handle the error, too

} // End of badad_error_handler() definition

// Use my error handler
set_error_handler ('badad_error_handler');

// SQL errors
// Eg: sql_error($q, 'dbc', "sqle_1"); sql_error($qr, 'srv_dbc', "sqle_2"); sql_error($qc, 'eml_dbc', "sqle_3");
// Multiple queries per test: sql_error("$qp &&& $qt", 'dbc', "sqle_4");
// $check_id should be a unique number or identifier per sql_error() use, either globally or per file
function sql_error($query, $database_config, $check_id) {
	// Need these vars
	global $sql_error_from_email, $sql_error_email, $site_bcc_email, $headers, $siteTitle, $siteDomain;

	// Logged in?
	if (isset($_SESSION['user_id'])) {
		$userIDreport = "User ID: " . $_SESSION['user_id'];
	} else {
		$userIDreport = "User ID: NO_LOGIN";
	}

	// Set the info for the message
	$SQLErrorTimeNow = date("Y-m-d H:i:s");
	$page_file = "Page address: " . $_SERVER['PHP_SELF'] . " @ file: " . __FILE__;
	$sending_body = "<p>An SQL error occured at $siteTitle at: <b>$SQLErrorTimeNow</b></p><p>$userIDreport</p><p>$page_file</p><p>Check num: $check_id</p><p>Database config: $database_config</p><p>SQL Query: $query</p>";

	// Send the email
	$from = '"badAd-SQL Error Handler" <'.$sql_error_from_email.'>';
	$to = '"badAd-SQL Errors" <'.$sql_error_email.'>';
	$subject = 'badAd-SQL Error';
	$message = $sending_body;
	$headers .= "From: " . $from . "\r\n";
	$headers .= "Bcc: " . $site_bcc_email;
	mail($to,$subject,$message,$headers);

	// Redirect
	echo "
	<form id=\"jsGoForm\" action=\"https://$siteDomain/dberror.php\" method=\"post\">
		<input type=\"hidden\" name=\"sql_error\" value=\"error\">
		<input type=\"hidden\" name=\"sql_error_time\" value=\"$SQLErrorTimeNow\">
	</form>
	<script type=\"text/javascript\">
			document.getElementById('jsGoForm').submit();
	</script>";
	exit(); // Quit the script;

}

function redirect_invalid_user($check = 'user_id', $destination = 'index.php', $protocol = 'https://') {

	// Check for the session item:
	if (!isset($_SESSION[$check])) {
		$url = $protocol . BASE_URL . $destination; // Define the URL
		header("Location: $url");
		exit(); // Quit the script
	}

} // End of redirect_invalid_user() function
