<?php

if ((isset($_SESSION['refUserID'])) && (isset($_SESSION['rSlug'])) && ($userid != $refUserID)) {
	$q = "INSERT INTO referrals (referring_id, referred_id)  VALUES ('$refUserID', '$userid')";
	$r = mysqli_query ($dbc, $q);
	if (mysqli_affected_rows($dbc) == 1) { // If it ran OK
		// Check to ensure this user has no credits
		$q = "SELECT creditcount FROM credits WHERE userid='$userid'";
		$r = mysqli_query($dbc, $q);
		$rows = mysqli_num_rows($r);
		if ($rows == 0) { // No entry yet
			// Add the credit to the new user
			$q = "INSERT INTO credits (userid, creditcount) VALUES ('$userid', '1')";
			$r = mysqli_query ($dbc, $q);
			if (mysqli_affected_rows($dbc) == 1) {
			echo "<p class=\"note_green\"><b>And... Yeah! You got your free ad credit!</b></p>";
		} else {
			echo "<p class=\"note_red\">We had a database error processing your credit. Please note the time and contact us about this because we want you to have your credit.</p>";
		}
			// Add the credit to the referring user
			$q = "SELECT creditcount FROM credits WHERE userid='$refUserID'";
			$r = mysqli_query($dbc, $q);
			$rows = mysqli_num_rows($r);
			if ($rows == 0) { // Referring user has no credits yet, add the first
				$q = "INSERT INTO credits (userid, creditcount) VALUES ('$refUserID', '1')";
				$r = mysqli_query ($dbc, $q);
			} elseif ($rows == 1) { // Referring user already has credits, add to them
				$row = mysqli_fetch_array($r, MYSQLI_NUM);
				$credCount = "$row[0]";
				$credCount = $credCount + 1;
				$q = "UPDATE credits SET creditcount='$credCount' WHERE userid='$refUserID'";
				$r = mysqli_query ($dbc, $q);
			}
		} else { // If the user already had an ad credit, such as via the "back" button
			echo "<p class=\"note_green\">You have a free ad credit!</p>";
		}
		// Unset the _SESSION Referral
		unset ($_SESSION['refUserID']);
		unset ($_SESSION['rSlug']);
	}
}
