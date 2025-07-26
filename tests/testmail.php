<?php

// Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
set_error_handler("var_dump");
error_reporting(-1);

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Include the header file
$page_title = 'Test Mail';
include ('./includes/header.html');

// Test Mail


// Using the ini_set()
//ini_set("SMTP", "jesse.verb.ink");
//ini_set("smtp_port", "25");
//ini_set("username", "mailer@pacificdailyads.com");
//ini_set("password", "123456789abcdefg");
//ini_set("sendmail_from", 'mailer@pacificdailyads.com');
//echo ini_get('display_errors');
/*
// The message
$message = "This is a test email.";

// Send
$headers = 'From: "JC News" <jc@pdt.news>';

mail('"Ink Me" <me@ink.verb.ink>', 'My Subject', $message, $headers);

echo "Check your email now….<BR>";
*/

// HTML email requirements
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
$site_bcc_email = '"Sent Bcc" <bcc_sent@badad.one>';
//$site_bcc_email = 'bcc_sent@badad.one'; // This also works

// function testmail() {
  global $siteTitle, $headers, $site_bcc_email;
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
$from = '"'.$siteTitle.'" <noreply@badad.one>';
$to = '"badAd" <badadone@outlook.com>, "badAd One Gmail" <badaddotone@gmail.com>';
$subject = "PDT Mail Test script";
$message = "This is a test to check the PHP Mail functionality";
$headers .= "From: " . $from . "\r\n";
$headers .= "Bcc: " . $site_bcc_email;
//DEV the new $message has links and HTML, which proved to be the only factor in marking spam for Outlook, but not Gmail
//$message = '<p>badAd,</p> <p>You requested to change your password.</p> <p><a href="https://badad.one/login_recovery.php?p=LONG_HASH">This is the link to reset your password</a> and it will expire after 40 minutes.</p> <p>Sincerely,<br />badAd.one<br /><a href="https://badad.one">badad.one</a></p> <br /><br /><br /><table width="100%" bgcolor="#000" border="0" cellspacing="0" cellpadding="3"><tr align="center"><td style="color: #fff"><a style="color: #fff; text-decoration: none" title="badad.one" href="https://badad.one">badAd.one</a> | <a style="color: #fff; text-decoration: none" title="Unsubscribe immediately" href="https://badad.one/LONG_HASH/LONG_HASH/unsubscribe.html">Unsubscribe</a></td></tr></table>';
// echo htmlentities("from: $from<br><br>mail($to,$subject,$message,$headers);");
//exit();
//DEV
if (mail($to,$subject,$message,$headers)) {
  echo "Test email sent<br />";
  echo "$from";
} else {
  echo "failed<br />";
}
// }

// testmail();
// Needs PHPMailer...

/*

// $email and $message are the data that is being
// posted to this page from our html contact form
//$email = $_REQUEST['email'] ;
//$message = $_REQUEST['message'] ;
$email = '"JC News" <jc@pdt.news>' ;
$message = 'This is a test message.' ;

// When we unzipped PHPMailer, it unzipped to
// public_html/PHPMailer_5.2.0
require("class.phpmailer.php");

$mail = new PHPMailer();

// set mailer to use SMTP
$mail->IsSMTP();

// As this email.php script lives on the same server as our email server
// we are setting the HOST to localhost
$mail->Host = "localhost";  // specify main and backup server

$mail->SMTPAuth = true;     // turn on SMTP authentication

// When sending email using PHPMailer, you need to send from a valid email address
// In this case, we setup a test email account with the following credentials:
// email: send_from_PHPMailer@bradm.inmotiontesting.com
// pass: password
$mail->Username = "mailer@pacificdailyads.com";  // SMTP username
$mail->Password = "123456789abcdefg"; // SMTP password

// $email is the user's email address the specified
// on our contact us page. We set this variable at
// the top of this page with:
// $email = $_REQUEST['email'] ;
$mail->From = $email;

// below we want to set the email address we will be sending our email to.
$mail->AddAddress("me@ink.verb.ink", "Ink Me");

// set word wrap to 50 characters
$mail->WordWrap = 50;
// set email format to HTML
$mail->IsHTML(true);

$mail->Subject = "You have received feedback from your website Etutionhub!";

// $message is the user's message they typed in
// on our contact us page. We set this variable at
// the top of this page with:
// $message = $_REQUEST['message'] ;
$mail->Body    = $message;
$mail->AltBody = $message;

if(!$mail->Send())
{
   echo "Message could not be sent. <p>";
   echo "Mailer Error: " . $mail->ErrorInfo;
   exit;
}

echo "Message has been sent";
*/


// Include the HTML footer
include ('./includes/footer.html');
?>
