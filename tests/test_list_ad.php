<?php

require ('./includes/config.inc.php');

// We need database connection
require (MYSQL);

// Include the header file
$page_title = "List Ad TEST :: $siteTitle";
include ('./includes/header.html');

// List the ad
$adID = 35;
include ('./includes/list_ad.inc.php');

// Include the HTML footer
include ('./includes/footer.html');
?>
