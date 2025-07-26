<?php

// Require the configuration before any PHP code as the configuration controls error reporting
function create_new_ad_input($name, $type, $placeholder, $errors, $value) {

	// Assume no value already exists
	//$value = false;

	// Check for a value in POST
	if (isset($_POST[$name])) $value = $_POST[$name];

	// Conditional to determine what kind of element to create

	// Nickname
	if ($name == 'nick') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

		// Start creating the input
		echo '<input minlength="3" maxlength="80" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" required ';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo 'class="' . $name . ' error ad_text_input" /><br />';
		} else {
			echo 'class="' . $name . ' ad_text_input" /><br />';
		}

	// Heading
	} elseif ($name == 'hdng') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

		// Start creating the input
		echo '<input minlength="3" maxlength="80" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" required ';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo 'class="' . $name . ' error ad_text_input" /><br />';
		} else {
			echo 'class="' . $name . ' ad_text_input" /><br />';
		}

	// Description
	} elseif ($name == 'dscr') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

		// Start creating the input
		echo '<input minlength="3" maxlength="80" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" required ';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo 'class="' . $name . ' error ad_text_input" /><br />';
		} else {
			echo 'class="' . $name . ' ad_text_input" /><br />';
		}

	// Info
	} elseif ($name == 'info') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

		// Start creating the input
		echo '<input minlength="3" maxlength="80" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" required ';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo 'class="' . $name . ' error ad_text_input" /><br />';
		} else {
			echo 'class="' . $name . ' ad_text_input" /><br />';
		}

	// Payrate
	} elseif ($name == 'pyrt') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

		// Start creating the input
		echo '<input minlength="3" maxlength="80" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" required ';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo 'class="' . $name . ' error ad_text_input" /><br />';
		} else {
			echo 'class="' . $name . ' ad_text_input" /><br />';
		}

	// Contact URL
	} elseif ($name == 'cntc') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

		// Start creating the input
		echo '<input minlength="11" maxlength="520" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" required ';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo 'class="' . $name . ' error ad_text_input" /><br />';
		} else {
			echo 'class="' . $name . ' ad_text_input" /><br />';
		}

	// Business Listing?
	} elseif ($name == 'bizn') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

		// Start creating the input
		echo '<input minlength="3" maxlength="80" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" required ';

		// Add the value to the input
		if ($value) echo ' value="' . htmlspecialchars($value) . '"';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo 'class="' . $name . ' error ad_text_input" /><br />';
		} else {
			echo 'class="' . $name . ' ad_text_input" /><br />';
		}

	// Podcast Listing?
	} elseif ($name == 'pdcst') {
		// Error message
		if (array_key_exists($name, $errors)) {
			echo '<span class="error">' . $errors[$name] . '</span><br />';
		}

				// Start creating the textarea
		echo '<textarea minlength="3" maxlength="1000" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" rows="4" cols="70" class="writingArea" required ';

		// Check for an error
		if (array_key_exists($name, $errors)) {
			echo ' class="error">';
		} else {
			echo '>';
		}

		// Add the value to the textarea (unlike an input, a textarea value goes between the open & close html tags)
		if ($value) echo htmlspecialchars($value);

		// Complete the textarea
		echo '</textarea>';

		// Tag
	} elseif (($name == 'taglist') ||($name == 'taglist1') || ($name == 'taglist2') || ($name == 'taglist3') || ($name == 'taglist4') || ($name == 'taglist5')) {
			// Error message
			if (array_key_exists($name, $errors)) {
				echo '<span class="error">' . $errors[$name] . '</span><br />';
			}

				// Display the error first
				if (array_key_exists($name, $errors)) echo '';

				// Start creating the textarea
				echo '<input minlength="2" maxlength="220" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" list="tag_list" rows="1" cols="3"';

				if ($name == 'taglist1') {echo ' required';} // Require at least one tag
				if ($name == 'taglist') {echo ' required multiple="multiple"';} // Require at least one tag

				// Add the value to the input
				if ($value) echo ' value="' . htmlspecialchars($value) . '"';

				// Check for an error
				if (array_key_exists($name, $errors)) {
					echo ' class="taglist ad_text_input error" /><br />';
				} else {
					echo ' class="taglist ad_text_input" /><br />';
				}


		} // End of the tag datalist input

		/* Flexdatalist version
		// Tag
		} elseif ($name == 'taglist') {
			// Error message
			if (array_key_exists($name, $errors)) {
				echo '<span class="error">' . $errors[$name] . '</span><br />';
			}

				// Display the error first
				if (array_key_exists($name, $errors)) echo '';

				// Start creating the textarea
				echo '<input list="taglist" type="' . $type . '" name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" ';

				// Flexdatalist
				echo '
				data-value-property="value"
				data-searchContain="true"
				data-selection-required="true"
				data-min-length="1"
				data-toggle-selected="true"
				';

				echo ' multiple="multiple" rows="1" cols="22" ';

				// Add the value to the input
				if ($value) echo ' value="' . htmlspecialchars($value) . '"';

				// Flexdatalist
				// Check for an error
				if (array_key_exists($name, $errors)) {
					echo ' class="error flexdatalist form-control">';
				} else {
					echo ' class="flexdatalist form-control" />';
				}

				// Check for an error
				if (array_key_exists($name, $errors)) {
					echo 'class="' . $name . ' error ad_text_input" /><br />';
				} else {
					echo 'class="' . $name . ' ad_text_input" /><br />';
				}

		} // End of the tag datalist input
		*/

		/* Old taglist, useful for textarea
		// Tag
		} elseif ($name == 'taglist') {

				// Display the error first
				if (array_key_exists($name, $errors)) echo '';

				// Start creating the textarea
				echo '<textarea name="' . $name . '" id="' . $name . '" placeholder="' . $placeholder . '" rows="2" cols="22" required ';

				// Check for an error
				if (array_key_exists($name, $errors)) {
					echo ' class="error">';
				} else {
					echo '>';
				}

				// Add the value to the textarea (unlike an input, a textarea value goes between the open & close html tags)
				if ($value) echo htmlspecialchars($value);

				// Complete the textarea
				echo '</textarea>';

		// End of the form inputs
		}
		*/

} // End of the create_form_input() function

// Set SESSION values to populate a New Ad form
function rerun_new_ad($cat, $rol, $new_ad_nickname, $new_ad_subcat, $new_ad_heading, $new_ad_description, $new_ad_info, $new_ad_pricing, $new_ad_contactURL, $new_ad_bizn, $new_ad_bizListing, $new_ad_tagIDs, $new_ad_tagList) {

	// Set the SESSION values
	$_SESSION['new_ad_nickname'] = $new_ad_nickname;
	$_SESSION['new_ad_subcat'] = $new_ad_subcat;
	$_SESSION['new_ad_heading'] = $new_ad_heading;
	$_SESSION['new_ad_description'] = $new_ad_description;
	$_SESSION['new_ad_info'] = $new_ad_info;
	$_SESSION['new_ad_pricing'] = $new_ad_pricing;
	$_SESSION['new_ad_contactURL'] = $new_ad_contactURL;
	$_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
	$_SESSION['new_ad_tagList'] = $new_ad_tagList;

	// Redirect to the New Ad form
	header("Location: new_ad.php?c=$cat&r=$rol");
	exit();

}
