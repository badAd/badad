<?php

// Errors
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Are we live?
//$live = false;
$live = true;

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
$php_error_email = 'errors@'.$siteDomain;
$php_error_from_email = 'phperrors@'.$siteDomain;
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
		//error_log ($message, 1, $php_error_email, 'From:'.$error_from_email.'');


		// Need these vars
		global $php_error_from_email, $php_error_email, $site_bcc_email, $headers, $siteTitle, $siteDomain;

		// Logged in?
		if (isset($_SESSION['user_id'])) {
			$useridreport = "User ID: " . $_SESSION['user_id'];
		} else {
			$useridreport = "User ID: NO_LOGIN";
		}

		// Set the info for the message
		$PHPErrorTimeNow = date("Y-m-d H:i:s");
		$page_file = "Page address: " . $_SERVER['PHP_SELF'];
		$sending_body = "<p>An error occured at $siteTitle at: <b>$PHPErrorTimeNow</b></p><p>$useridreport</p><p>$page_file</p><p>Error message: <br />" . nl2br($message) . "</p>";

		// Send the email
		$from = '"badAd-PHP Error Handler" <'.$php_error_from_email.'>';
		$to = '"badAd-PHP Errors" <'.$php_error_email.'>';
		$subject = 'badAd-PHP Error';
		$message = $sending_body;
		$headers .= "From: " . $from . "\r\n";
		$headers .= "Bcc: " . $site_bcc_email;
		mail($to,$subject,$message,$headers);

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
		$useridreport = "User ID: " . $_SESSION['user_id'];
	} else {
		$useridreport = "User ID: NO_LOGIN";
	}

	// Set the info for the message
	$SQLErrorTimeNow = date("Y-m-d H:i:s");
	$page_file = "Page address: " . $_SERVER['PHP_SELF'] . " @ file: " . __FILE__;
	$sending_body = "<p>An SQL error occured at $siteTitle at: <b>$SQLErrorTimeNow</b></p><p>$useridreport</p><p>$page_file</p><p>Check num: $check_id</p><p>Database config: $database_config</p><p>SQL Query: $query</p>";

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

} // End of sql_error() function

function redirect_invalid_user($check = 'user_id', $destination = 'index.php', $protocol = 'https://') {

	// Check for the session item:
	if (!isset($_SESSION[$check])) {
		$url = $protocol . BASE_URL . $destination; // Define the URL
		header("Location: $url");
		exit(); // Quit the script
	}

} // End of redirect_invalid_user() function

// Get IP
function get_ip_addr(){ // Thanks https://stackoverflow.com/q/1634782/10343144
  foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
    if (array_key_exists($key, $_SERVER) === true){
      foreach (explode(',', $_SERVER[$key]) as $ip){
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
          return $ip;
        }
      }
    }
  }
}

// Script Kiddies
function script_kiddy($refno, $method_name, $value, $IP) {
	// Need these vars
	global $sql_error_from_email, $sql_error_email, $site_bcc_email, $headers, $siteTitle;

	// Sanitize
	$value = htmlentities($value);

	// Logged in?
	if (isset($_SESSION['user_id'])) {
		$useridreport = "User ID: " . $_SESSION['user_id'];
	} else {
		$useridreport = "User ID: NO_LOGIN";
	}

	// Set the info for the message
	$SQLErrorTimeNow = date("Y-m-d H:i:s");
	$page_file = "Page address: " . $_SERVER['PHP_SELF'] . " @ file: " . __FILE__;
	$sending_body = "<p>An SQL error occured at $siteTitle at: <b>$SQLErrorTimeNow</b></p><p>$useridreport</p><p>$page_file</p><p>IP: $IP</p><p>Method Name: $method_name</p><p>Value: $value</p><p>Ref num: $refno</p>";

	// Send the email
	$from = '"badAd Script Kiddy Handler" <'.$sql_error_from_email.'>';
	$to = '"badAd Script Kiddy" <'.$sql_error_email.'>';
	$subject = 'badAd Script Kiddy';
	$message = $sending_body;
	$headers .= "From: " . $from . "\r\n";
	$headers .= "Bcc: " . $site_bcc_email;
	mail($to,$subject,$message,$headers);

	// Gotcha
	echo '<h1 class="note_red">Gotcha '.$IP.'! Walk away. Just walk away.</h1>';
	exit(); // Quit the script;

}
