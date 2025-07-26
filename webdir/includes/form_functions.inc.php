<?php

// This function generates a form INPUT or TEXTAREA tag
// It takes three arguments
// - The name to be given to the element
// - The type of element (text, password, textarea)
// - An array of errors
function create_form_input($name, $type, $errors) {

	// Assume no value already exists
	$value = false;

	// Check for a value in POST
	if (isset($_POST[$name])) $value = $_POST[$name];

	// Conditional to determine what kind of element to create

	// text email password
	if ( ($type == 'text') || ($type == 'password') ) {

		// Start creating the input
		echo '<input';

		// Don't require it for some forms, by name
		if ($name != 'project') {
			echo ' required';
		}

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Continue the input
		echo ' type="' . $type . '" name="' . $name . '" id="' . $name . '" class="' . $name;

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// email
	} elseif ($type == 'email') {

		// Start creating the input
		echo '<input';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Continue the input
		echo ' required type="email" name="' . $name . '" id="' . $name . '" class="email';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// url
	} elseif  ($type == 'url') {

		// Start creating the input
		echo '<input';

		// Don't require it for some forms, by name
		if ($name != 'url_option') {
			echo ' required';
		}

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Continue the input
		echo ' type="' . $type . '" name="' . $name . '" id="' . $name . '" class="' . $name;

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// number
	} elseif  ($type == 'number') {

		// Start creating the input
		echo '<input required type="' . $type . '" name="' . $name . '" id="' . $name . '" class="' . $name;

		// TFA?
		if (($name == 'email_code') || ($name == 'sms_code') || ($name == 'google_auth')) {
			echo ' tfa_code';
		}

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// textarea
	} elseif ($type == 'textarea') {

		// Display the error first
		if (array_key_exists($name, $errors)) echo ' <span class="error">' . $errors[$name] . '</span>';

		// Start creating the textarea
		echo '<textarea required name="' . $name . '" id="' . $name . '"';

		// Feedback form?
		if ($name == 'feedback_content') {
			echo ' maxlength="1000" minlength="10" rows="3" cols="50"';
		} elseif ($name == 'feedback_text') {
			echo ' maxlength="1000" minlength="100" rows="3" cols="50"';
		} elseif ($name == 'feedback_required_text') {
			echo ' maxlength="1000" minlength="50" rows="3" cols="50"';
		} else {
			echo ' maxlength="1000" rows="5" cols="75"';
		}

		// Add the error class, if applicable
		if (array_key_exists($name, $errors)) {
			echo ' class="error">';
		} else {
			echo '>';
		}

		// Add the value to the textarea
		if ($value) echo $value;

		// Complete the textarea
		echo '</textarea>';

	} // End of primary IF-ELSE

} // End of the create_form_input() function

// Like create_form_input(), but takes a $value argument
function update_form_input($name, $type, $errors, $value) {

	// Check for a value in POST
	if (isset($_POST[$name])) $value = $_POST[$name];

	// Conditional to determine what kind of element to create

	// text password
	if ( ($type == 'text') || ($type == 'password') ) {

		// Start creating the input
		echo '<input';

		// Don't require it for some forms, by name
		if ($name != 'project') {
			echo ' required';
		}

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Continue the input
		echo ' type="' . $type . '" name="' . $name . '" id="' . $name . '" class="' . $name;

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// email
	} elseif ($type == 'email') {

		// Start creating the input
		echo '<input';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Continue the input
		echo ' required type="email" name="' . $name . '" id="' . $name . '" class="email';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// textarea
	} elseif ($type == 'textarea') {

		// Display the error first
		if (array_key_exists($name, $errors)) echo ' <span class="error">' . $errors[$name] . '</span>';

		// Start creating the textarea
		echo '<textarea required name="' . $name . '" id="' . $name . '" rows="5" cols="75"';

		// Add the error class, if applicable
		if (array_key_exists($name, $errors)) {
			echo ' class="error">';
		} else {
			echo '>';
		}

		// Add the value to the textarea
		if ($value) echo $value;

		// Complete the textarea
		echo '</textarea>';

	} // End of primary IF-ELSE

} // End of the update_form_input() function

// Like update_form_input(), but differences 'ba-empty' defaults
function feed_override($name, $type, $placeholder, $value, $errors) {

	// Text
	if ($type == 'text') {

		// Start creating the input
		echo '<input';

		// Add the value to the input
		echo (($value) && ($value != 'ba-empty')) ? ' value="' . htmlspecialchars($value) . '"' : false;

		// Continue the input
		echo ' type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" size="64"';

		// Title
		if (($name == 'ba_itunes_author') || ($name == 'ba_image_title') || ($name == 'ba_itunes_owner_name') || ($name == 'ba_itunes_owner_email')) {
			echo ' maxlength="255"';
		} elseif (($name == 'ba_title') || ($name == 'ba_itunes_title')) {
			echo ' maxlength="255"';
		} elseif (($name == 'ba_itunes_keywords') || ($name == 'ba_itunes_keywords')) {
			echo ' maxlength="1000"';
		} else {
			echo ' maxlength="100"';
		}

		// Class
		echo ' class="feed';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// Textarea
	} elseif ($type == 'textarea') {

		// Display the error first
		if (array_key_exists($name, $errors)) echo ' <span class="error">' . $errors[$name] . '</span>';

		// Start creating the textarea
		echo '<textarea name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" cols="70" rows="5"';

		// Description/summary?
		if (($name == 'ba_description') || ($name == 'ba_itunes_summary')) {
			echo ' maxlength="55300"';
		} else {
			echo ' maxlength="1000"';
		}

		// Add the error class, if applicable
		if (array_key_exists($name, $errors)) {
			echo ' class="error">';
		} else {
			echo '>';
		}

		// Add the value to the textarea
		if (($value) && ($value != 'ba-empty')) echo $value;

		// Complete the textarea
		echo '</textarea>';

	// Links
	} elseif  ($type == 'url') {

		// Start creating the input
		echo '<input';

		// Add the value to the input
		echo (($value) && ($value != 'ba-empty')) ? ' value="' . htmlspecialchars($value) . '"' : false;

		// Continue the input
		echo ' type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" size="64" class="' . $name;

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// Email
	} elseif ($type == 'email') {

		// Start creating the input
		echo '<input';

		// Add the value to the input
		echo (($value) && ($value != 'ba-empty')) ? ' value="' . htmlspecialchars($value) . '"' : false;

		// Continue the input
		echo ' type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" size="64" class="' . $name;

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	// Checkbox // DEV currently not used, made for "explicit" setting replaced with non-function radios
	} elseif ($type == 'checkbox') {

		// Start creating the input
		echo '<input';

		// Checked?
		echo (($value) && ($value != 'false')) ? ' checked' : false;

		// Continue the input
		echo ' type="' . $type . '" name="' . $name . '" id="' . $name . '" class="' . $name;

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' error" /> <span class="error">' . $errors[$name] . '</span>';
		} else {
			echo '" />';
		}

	} // End type conditionals

} // End of feed_override() function

function set_switch($text, $title, $action, $_post_name, $_post_value, $class) {
	echo "<form action=\"$action\" method=\"post\">
  <input type=\"hidden\" name=\"$_post_name\" value=\"$_post_value\" />
  <input type=\"submit\" title=\"$title\" value=\"$text\" class=\"$class\" />
	</form>";
} // End of the set_switch() function
