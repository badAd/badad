<?php

// Process & respond to the New Ad entry
// For storing registration errors:
$reg_errors = array();

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Check for New Ad form submission
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['feedback_type'])))  {

  // Start afresh
  unset($_SESSION['validFeedbackForm']);

  // Check reCaptcha
  if ((isset($_POST['g-recaptcha-response'])) && (!empty($_POST['g-recaptcha-response']))) {
    $secret = '6Lfbe9AUAAAAAOTEuStYKboN__tYN9QPpMk5Age0'; // v2 secret
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
    $responseData = json_decode($verifyResponse);
    if ($responseData->success) {
      $recaptcha_success = 'reCaptcha passed'; // This doesn't go anywhere as of implementation time
    } else {
      $reg_errors['recaptcha'] = 'Robot verification failed, please try again.';
    }
  } elseif ((isset($_POST['g-recaptcha-response'])) && (empty($_POST['g-recaptcha-response']))) {
    $reg_errors['recaptcha'] = 'If you are not a robot, you must check the box below:';
  }

  // Check form URL
  if (isset($_POST['form_url'])) {
    $regex_url = '_^(?:(?:https|http)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS';
    if (preg_match($regex_url, $_POST['form_url'])) {
        $formURL = $_POST['form_url'];
        $_SESSION['formURL'] = $formURL;
    } else {
      $reg_errors['form_url'] = 'Enter a valid URL, beginning with http://';
    }
  }

  // Check optional URL
  if ((isset($_POST['url_option'])) && ($_POST['url_option'] != "")) {
    $regex_url = '_^(?:(?:https|http)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS';
    if (preg_match($regex_url, $_POST['url_option'])) {
        $optionalURL = $_POST['url_option'];
        $_SESSION['optionalURL'] = $optionalURL;
    } else {
      $reg_errors['url_option'] = 'Enter a valid URL, beginning with http://';
    }
  } elseif ((isset($_POST['url_option'])) && ($_POST['url_option'] == "")) {
      $optionalURL = "NONE";
      $_SESSION['optionalURL'] = $optionalURL;
  }

  // Check textarea: feedback_text (100 min)
  if (isset($_POST['feedback_text'])) {
    $feedbackText = strip_tags($_POST['feedback_text']);
    $feedbackText = htmlspecialchars($feedbackText);
    $feedbackText = nl2br($feedbackText);
  }
  // Check textarea: feedback_content (10 min)
  if (isset($_POST['feedback_content'])) {
    $feedbackContent = strip_tags($_POST['feedback_content']);
    $feedbackContent = htmlspecialchars($feedbackContent);
    $feedbackContent = nl2br($feedbackContent);
  }
  // Check textarea: feedback_required_text (50 min)
  if (isset($_POST['feedback_required_text'])) {
    $feedbackRequiredText = strip_tags($_POST['feedback_required_text']);
    $feedbackRequiredText = htmlspecialchars($feedbackRequiredText);
    $feedbackRequiredText = nl2br($feedbackRequiredText);
    $_SESSION['feedbackRequiredText'] = $feedbackRequiredText;
  }

  // Check Name
  if (isset($_POST['full_name'])) {
    if (preg_match ('/^[A-Z \'.-]{1,80}$/i', $_POST['full_name'])) {
    	$fullName = mysqli_real_escape_string ($dbc, $_POST['full_name']);
      $_SESSION['fullName'] = $fullName;
    } else {
    	$reg_errors['full_name'] = 'Please enter your name, only letters and hyphens, 80 characters max!';
    }
  }

  // Check Other Subject
  if (isset($_POST['subject_other'])) {
    if (preg_match ('/^[A-Z \'.-]{1,80}$/i', $_POST['subject_other'])) {
    	$subjectOther = mysqli_real_escape_string ($dbc, $_POST['subject_other']);
      $_SESSION['subjectOther'] = $subjectOther;
    } else {
    	$reg_errors['subject_other'] = 'Please enter a subject, only letters and hyphens, 80 characters max!';
    }
  }

  // Check for a Plaintiff
  if (isset($_POST['concerning'])) {
    if (preg_match ('/^[A-Z0-9 \'.-]{0,80}$/i', $_POST['concerning'])) {
    	$Plaintiff = mysqli_real_escape_string ($dbc, $_POST['concerning']);
      $_SESSION['Plaintiff'] = $Plaintiff;
    } else {
    	$reg_errors['concerning'] = 'Project may use letters, numbers, periods, and hyphens only, 80 characters max!';
    }
  }

  // Check for a Firm
  if (isset($_POST['project'])) {
    if (preg_match ('/^[A-Z0-9 \'.-]{0,80}$/i', $_POST['project'])) {
      $Firm = mysqli_real_escape_string ($dbc, $_POST['project']);
      if ($_POST['project'] == "") {$Firm = "NONE";}
      $_SESSION['Firm'] = $Firm;
    } else {
      $reg_errors['project'] = 'Project may use letters, numbers, periods, and hyphens only, 80 characters max!';
    }
  }

  // Check for an email and match against the confirmed email
  if ((isset($_POST['email1'])) && (isset($_POST['email2']))) {
    if ((filter_var($_POST['email1'], FILTER_VALIDATE_EMAIL)) && (filter_var($_POST['email2'], FILTER_VALIDATE_EMAIL))) {
    	if ($_POST['email1'] == $_POST['email2']) {
    		$emailAddress = mysqli_real_escape_string ($dbc, $_POST['email1']);
        $_SESSION['emailAddress'] = $emailAddress;
    	} else {
    		$reg_errors['email2'] = 'Your email addresses did not match!';
    	}
    } else {
    	$reg_errors['email1'] = 'Please enter a valid email address, 90 characters max!';
    }
  }

  // Errors?
  if (empty($reg_errors)) { // If entries pass regex...

		// Set the New Ad as valid (if we're not editing it)
		$_SESSION['validFeedbackForm'] = true;

  } // End errors


} // End submitted form
