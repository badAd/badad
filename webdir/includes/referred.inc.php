<?php

// This checks and displays a message for new referred users
if ((isset($_SESSION['refUserID'])) && (isset($_SESSION['rSlug']))) {
	// Honesty check
	if (isset($userid)) {
		if ($userid == $_SESSION['refUserID']) {
			echo "<p class=\"note_red\">Referral credit is not granted to a user's own referral link.</p>";
			unset($_SESSION['rSlug']);
			unset($_SESSION['refUserID']);
		} else {
		  // Check to ensure this user has no credits
		  $cq = "SELECT creditcount FROM credits WHERE userid='$userid'";
		  $cr = mysqli_query($dbc, $cq);
		  $crows = mysqli_num_rows($cr);
			$rq = "SELECT id FROM referrals WHERE referred_id='$userid'";
			$rr = mysqli_query($dbc, $rq);
			$rrows = mysqli_num_rows($rr);
		  if (($crows == 0) && ($rrows == 0)) { // No entry yet
				echo "<p class=\"note_blue\">Congratulations! You have been referred and get 1 extra free week of any ad with a purchase. (<a href=\"help_videos.php\">Learn more</a>) Complete your purchase to claim your credit...</p>";
			} else { // if the user already received credits
			  echo "<p class=\"note_red\">Your account already received ad credits. You can get free weeks of ads by referring to others, but you are not eligible for a free week with purchase.</p>";
				unset($_SESSION['rSlug']);
				unset($_SESSION['refUserID']);
			}
		}
	} else {
		echo "<p class=\"note_blue\">Congratulations! You have been referred and get 1 extra free week of any ad with a purchase. (<a href=\"help_videos.php\">Learn more</a>) Complete your purchase to claim your credit...</p>";
	}
} // REFERRED
