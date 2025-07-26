<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// To test the sidebars
// $_SESSION['user_id'] = 1;
// $_SESSION['user_admin'] = true;

// Require the database connection
require (MYSQL);

// Include the header
$page_title = "Partner Help :: $siteTitle";
include ('./includes/header.html');

// Breadcrumb
echo "<p class=\"note_gray\">&larr; Return to the <a title=\"Partner Center\" href=\"partner.php\">Partner center</a></p>";

// Heading
echo "<h3>Partner Help</h3>";

// Insert all ads
include ('inserts/partner_help.ins.php');

// Breadcrumb
echo "<p class=\"note_gray\">&larr; Return to the <a title=\"Partner Center\" href=\"partner.php\">Partner center</a></p>";

// Include the footer file to complete the template
require ('./includes/footer.html');
?>
