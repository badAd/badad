<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);


// List the ad
$List_ad_qt = 4;
include ('./includes/list_ad_get.inc.php');


?>
