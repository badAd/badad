<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Make sure we're not here on accident
if (!isset($_POST['del_partner_account_page'])) {
  header("Location: partner.php");
  exit(); // Quit the script
} elseif ($_POST['del_partner_account_page'] != $userid) {
  header("Location: partner.php");
  exit(); // Quit the script
}

// We need database connection
require (MYSQL);

// Include the header file
$page_title = "Delete Partner Account :: $siteTitle";
include ('./includes/header.html');

// Breadcrumb
echo "<p class=\"note_gray\">&larr; Return to the <a title=\"Partner Center\" href=\"partner.php\">Partner center</a>?</p>";

// Print a customized message
echo "<h3 class=\"note_red\">Delete your Partner account?</h3><br /><p>All the private information we have pertaining to your Partner account is still available to you free of charge. If you desire to obtain this information, do so before deleting your Partner account. Requests for any such information we may retain after an account is deleted incurs a minimum fee of $1,000 USD, plus tech support time spent obtaining the data, and any such requests can only be made in person.</p><p>To prevent abuse, we will email you a link to confirm before deleting your Partner account. If you continue, this will delete your Partner account and all of your Partner website projects under our current cycle. Any records in <a title=\"View history\" href=\"partner_site_history.php\">History</a> must be kept for our bookkeeping. We also must retain copies of all correspndence, including email.</p><p>Are you sure you are ready to proceed?</p>";

echo "<form id=\"partnerdeleteaccount\" class=\"userform\" action=\"confirm_partner_del_account.php\" method=\"post\" accept-charset=\"utf-8\">
<input type=\"hidden\" name=\"clicked_delete_partner_account\" value=\"$userid\" />";
		// Disclaimers
		echo"
		<p><input type=\"checkbox\" name=\"agree_to_delete_partner_account\" value=\"true\" required oninvalid=\"this.setCustomValidity('You must indicate that you are sure you are ready to proceed.')\" onchange=\"this.setCustomValidity('')\"/> <strong>I am ready to proceed and delete my Partner account.</strong></p>
		<input type=\"submit\" name=\"submit_button\" value=\"Proceed and DELETE my Partner account\" id=\"submit_button\" class=\"set_red\" />

</form>";

echo '<br /><p><b><a href="partner.php" class="note_blue">No! Get me out of here!</a></b></p>';

// Include the HTML footer
include ('./includes/footer.html');
?>
