<?php

// Insert one of these where you want a message to appear if there were too many logins:
/*

// Login count message
if ((isset($_SESSION['login_attempt'])) && ($_SESSION['login_attempt'] > 3)) {
	echo '<h3 class="note_red">Log In Failure</h3><p class="note_red">You tried to login too many times. Try again later.</p>';
	exit();
}

OR

// Login count page
if ((isset($_SESSION['login_attempt'])) && ($_SESSION['login_attempt'] > 3)) {
	$page_title = "Log In Failure";
	include ('./includes/header.html');
	echo '<h3 class="note_red">Log In Failure</h3><p class="note_red">You tried to login too many times. Try again later.</p>';
	include ('./includes/footer.html');
	exit();
}

*/

// Use this to include the counter (it is already included in login_check.inc, which is in the header)
/*
// Login attempts counter
require_once ('/includes/login_count.inc.php');
*/

// This counts revent login attempts in the _SESSION to deter bf attacks
if (($_SERVER['REQUEST_METHOD'] == 'POST') && ((isset($_POST['username'])) && (isset($_POST['pass']))) || (isset($_POST['tfa_mode']))) {
	if (!isset($_SESSION['login_attempt'])) {
		$_SESSION['login_attempt'] = 1;
	} else {
		$_SESSION['login_attempt'] = $_SESSION['login_attempt'] + 1;
	}
}
