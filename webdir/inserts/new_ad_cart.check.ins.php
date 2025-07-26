<?php

// Referral values?
if (isset($_SESSION['refUserID'])) { $refUserID = $_SESSION['refUserID']; }
if (isset($_SESSION['rSlug'])) { $rSlug = $_SESSION['rSlug']; }
// REFERRED

// Make sure all is in order and that we need to be here
if ((!isset($_SESSION['validAd'])) && (!isset($_SESSION['new_ad_weekslong']))) { // The $_SESSION['new_ad_weekslong'] check prevents a bug where an edit_ad or mod_ad was stopped by the user in progress to "Buy New Ad", creating a "not-set $pretty_new_ad_weekslong" bug that wouldn't allow a new ad to start
	// Clear any old SESSION form values
  include ('./includes/ad_values_unset.inc.php');
	// Redirect to the New Ad start page
	header("Location: new_ad.php");
	exit(); // Quit the script
}
