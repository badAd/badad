<?php

// Process & respond to the New Ad entry

// For storing registration errors:
$reg_errors = array();

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Referral values?
if (!isset($refUserID)) { $refUserID = ''; }
if (!isset($rSlug)) { $rSlug = ''; }
if (isset($_SESSION['refUserID'])) { $refUserID = $_SESSION['refUserID']; }
if (isset($_SESSION['rSlug'])) { $rSlug = $_SESSION['rSlug']; }
// REFERRED

// Validate _GET values
  // Category
    // If clicked/linked to
      if ( empty($_POST) ) {
        if (isset($_GET['c'])) {
          // Is the category legitimate?
        	$cat_slug = preg_replace("/[^A-Za-z0-9]/","", $_GET['c']);
          $cat_slug = mysqli_real_escape_string($dbc, $cat_slug);
          $q = "SELECT id FROM categories WHERE slug='$cat_slug'";
          $r = mysqli_query ($dbc, $q);
          $rows = mysqli_num_rows($r);
          if ($rows == 1) {
            $cat = $cat_slug;
          } else {
            // Redirect to the New Ad
            header("Location: new_ad.php");
            exit(); // Quit the script
          }
        }
      // If form submitted with errors
    } elseif (isset($_POST['ctgr'])){
      if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['ctgr'])) {$IP = get_ip_addr(); script_kiddy('sk_2', '_POST ctgr', $_POST['ctgr'], $IP);}
          $cat = preg_replace("/[^A-Za-z0-9]/","", $_POST['ctgr']);
          $_SESSION['cat'] = $cat;
      } elseif (!empty($_POST['username'])) {
        return;
      } else {
        // Redirect to the New Ad because something's really wrong
        header("Location: new_ad.php");
        exit(); // Quit the script
      }

  // Business listing?
  if ((isset($_GET['b'])) || (isset($_GET['p']))) {
    $new_ad_biz_listing = 'biz';
    $_SESSION['new_ad_biz_listing'] = $new_ad_biz_listing;
  } elseif (isset($_POST['b'])) {
    if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['b'])) {$IP = get_ip_addr(); script_kiddy('sk_3', '_POST b', $_POST['b'], $IP);}
    $new_ad_biz_listing = preg_replace("/[^A-Za-z0-9]/","", $_POST['b']); // Being extra safe here, preg_replace may not be necessary
    $_SESSION['new_ad_biz_listing'] = $new_ad_biz_listing;
  } elseif ((isset($_SESSION['new_ad_biz_listing'])) && ($_SESSION['new_ad_biz_listing'] == 'biz')) {
    $new_ad_biz_listing = $_SESSION['new_ad_biz_listing'];
  } else {
    $new_ad_content_bizn = NULL;
    $_SESSION['new_ad_content_bizn'] = $new_ad_content_bizn;
    $new_ad_biz_listing = 'non';
    $_SESSION['new_ad_biz_listing'] = $new_ad_biz_listing;
  }

  // Podcast listing?
  if (isset($_GET['p'])) {
    $new_ad_pod_listing = 'pod';
    $_SESSION['new_ad_pod_listing'] = $new_ad_pod_listing;
  } elseif (isset($_POST['p'])) {
    if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['p'])) {$IP = get_ip_addr(); script_kiddy('sk_49', '_POST p', $_POST['p'], $IP);}
    $new_ad_pod_listing = preg_replace("/[^A-Za-z0-9]/","", $_POST['p']); // Being extra safe here, preg_replace may not be necessary
    $_SESSION['new_ad_pod_listing'] = $new_ad_pod_listing;
  } elseif ((isset($_SESSION['new_ad_pod_listing'])) && ($_SESSION['new_ad_pod_listing'] == 'pod')) {
    $new_ad_pod_listing = $_SESSION['new_ad_pod_listing'];
  } else {
    $new_ad_content_pdcst = NULL;
    $_SESSION['new_ad_content_pdcst'] = $new_ad_content_pdcst;
    $new_ad_pod_listing = 'non';
    $_SESSION['new_ad_pod_listing'] = $new_ad_pod_listing;
  }

// Set the New Ad variables
require_once ('./includes/ad_values_set.inc.php');

// Check for New Ad form submission
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['adform'])))  {
 if ((preg_match ('/[^a-zA-Z0-9]$/i', $_POST['adform'])) || ($_POST['adform'] != 'submitted')) {$IP = get_ip_addr(); script_kiddy('sk_4', '_POST adform', $_POST['adform'], $IP);}

  // Define banned words
	include ('includes/bannedwords.inc.php');

  // Check Tags
    // Error message
    $err_tags = 'At least one tag, comma-separated, only letters and numbers';

  if (isset($_POST['taglist'])) {
    // Basic regex check (removes most non-alphanumerics, but not @^ from glitches, and leaves commas)
    $nospecialTagList = preg_replace("/[^a-zA-Z0-9,]/", '', $_POST['taglist']);

    if (preg_match ('/^[a-zA-Z0-9 ,-_]{0,180}$/i', $nospecialTagList)) {
      if (!preg_match_all ("#\b($bannedwords)\b#i", $nospecialTagList)) {
        $nospacesTagList = str_replace(' ', '', $nospecialTagList); // Remove everything but commas
        $nospacesTagList = rtrim($nospacesTagList, ','); // Remove any trailing commas
        $lowercaseTagList = strtolower($nospacesTagList);
        $arrayTagList = array();
        $arrayTagIDs = array();
        $arrayTagList = explode(',', $lowercaseTagList);

        foreach($arrayTagList as $tagText){
          // Do a full regex check on each individual tag (so we can remove 100% of non-alphanumetics)
          //$tagText = preg_replace("/\W|_/", '', $tagText); // Simple, but removes non-ASCII alnum, better answer here: https://stackoverflow.com/a/46941504/10343144
          $tagText = preg_replace("/[\W_]+/u", '', $tagText);
          if ($tagText == '') { continue; } // Skip this tag if the regex filtered everything
          $tagText = mysqli_real_escape_string($dbc, $tagText);

          // See whether the tag already exists
          $q = "SELECT id, merged, merged_into_id FROM tags WHERE tag='$tagText'";
          $r = mysqli_query ($dbc, $q);
          $row = mysqli_fetch_array($r, MYSQLI_NUM);
          $tagID = "$row[0]";
          $tag_merged = "$row[1]";
          $tag_merged_into_id = "$row[2]";
          // Get the number of rows returned
          $rows = mysqli_num_rows($r);
          if ($rows == 0) {
            // Add the tag to the table if it doesn't exist
            $qi = "INSERT INTO tags (tag) VALUES ('$tagText')";
            $ri = mysqli_query ($dbc, $qi);
            if (mysqli_affected_rows($dbc) == 1) { // If there was no problem
              // Get the new tag's ID
              $q = "SELECT id FROM tags WHERE tag='$tagText'";
              $r = mysqli_query ($dbc, $q);
              $row = mysqli_fetch_array($r, MYSQLI_NUM);
              $tagID = "$row[0]";
            }
            // Get the merged-into tag info
          } elseif ($tag_merged == "merged") {
            $tagID = $tag_merged_into_id;
          }

        $arrayTagIDs[] = $tagID;

      } // End for loop of tag IDs

      // Remove any duplicates
      $arrayTagIDs = array_unique($arrayTagIDs);

      // Get the tag for each tag id
      foreach($arrayTagIDs as $tagID) {
        // Get the tag from its ID
        $q = "SELECT tag FROM tags WHERE id='$tagID'";
        $r = mysqli_query ($dbc, $q);
        $row = mysqli_fetch_array($r, MYSQLI_NUM);
        $tag = "$row[0]";

        $arrayTags[] = $tag;

      } // End for loop of each tag to go into the list

      // Check to see if regex filters allowed any tag entries
      if (!isset($arrayTags)) {
        $reg_errors['taglist'] = $err_tags;
        unset($_POST['taglist']);
        $new_ad_tagIDs = '';
        $_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
        $new_ad_tagList = '';
        $_SESSION['new_ad_tagList'] = $new_ad_tagList;
        $new_ad_tagArray = '';
        $_SESSION['new_ad_tagArray'] = $new_ad_tagArray;
      } else { // Regex allowed tags to get through, good

        // Compile all tag IDs into one list
          $new_ad_tagList = implode(", ", $arrayTags);
          $_SESSION['new_ad_tagList'] = $new_ad_tagList;
          $new_ad_tagArray = $arrayTags;
          $_SESSION['new_ad_tagArray'] = $new_ad_tagArray;

        // Compile all tags into one list
          $new_ad_tagIDs = implode(", ", $arrayTagIDs);
          $_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
        }

      } else { // Banned words
        $reg_errors['taglist'] = $bannedMessage;
        unset($_POST['taglist']);
        $new_ad_tagIDs = '';
        $_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
        $new_ad_tagList = '';
        $_SESSION['new_ad_tagList'] = $new_ad_tagList;
        $new_ad_tagArray = '';
        $_SESSION['new_ad_tagArray'] = $new_ad_tagArray;
      }
  } else {
    $reg_errors['taglist'] = $err_tags;
    unset($_POST['taglist']);
    $new_ad_tagIDs = '';
    $_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
    $new_ad_tagList = '';
    $_SESSION['new_ad_tagList'] = $new_ad_tagList;
    $new_ad_tagArray = '';
    $_SESSION['new_ad_tagArray'] = $new_ad_tagArray;
  }
} else {
      $reg_errors['taglist'] = $err_tags;
}

  // Check Nickname
  if (preg_match ('/^[A-Z0-9 \'\/.&,:%-]{3,80}$/i', $_POST['nick'])) {
    if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['nick'])) {
      $new_ad_nickname = $_POST['nick'];
			$_SESSION['new_ad_nickname'] = $new_ad_nickname;
    } else {
      $reg_errors['nick'] = 'Nickname: '.$bannedMessage;
    }
  } else {
    $reg_errors['nick'] = 'Nickname: Only letters and numbers and - \' / . , & : %';
  }

	// Check Heading
	if (preg_match ('/^[A-Z0-9 \'\/&,:%-]{3,80}$/i', $_POST['hdng'])) {
    if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['hdng'])) {
      $new_ad_heading = $_POST['hdng'];
      $_SESSION['new_ad_heading'] = $new_ad_heading;
    } else {
      $reg_errors['hdng'] = 'Heading: '.$bannedMessage;
    }
  } else {
		$reg_errors['hdng'] = 'Heading: Only letters and numbers and - \' / , & : %';
	}

  // Check Description
  if (preg_match ('/^[A-Z0-9 \'\/&,:%-]{3,80}$/i', $_POST['dscr'])) {
    if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['dscr'])) {
      $new_ad_description = $_POST['dscr'];
			$_SESSION['new_ad_description'] = $new_ad_description;
    } else {
      $reg_errors['dscr'] = 'Description: '.$bannedMessage;
    }
  } else {
    $reg_errors['dscr'] = 'Description: Only letters and numbers and - \' / , & : %';
  }

  // Check Info
  if (preg_match ('/^[A-Z0-9 \'\/&,:%-]{3,80}$/i', $_POST['info'])) {
    if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['info'])) {
      $new_ad_info = $_POST['info'];
			$_SESSION['new_ad_info'] = $new_ad_info;
    } else {
      $reg_errors['info'] = 'Info: '.$bannedMessage;
    }
  } else {
    $reg_errors['info'] = 'Info: Only letters and numbers and - \' / , & : %';
  }

  // Check Pricing
  if (preg_match ('/^[A-Z0-9 \/.&,$:%-]{3,80}$/i', $_POST['pyrt'])) {
    if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['pyrt'])) {
      $new_ad_pricing = $_POST['pyrt'];
			$_SESSION['new_ad_pricing'] = $new_ad_pricing;
    } else {
      $reg_errors['pyrt'] = 'Pricing: '.$bannedMessage;
    }
  } else {
    $reg_errors['pyrt'] = 'Pricing: Only letters and numbers and - / . , $ & : %';
  }

  // Check Contact URL
  $regex_url = '_^(?:(?:https|http)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS';
  if ((preg_match($regex_url, $_POST['cntc'])) && (strlen($_POST['cntc']) > 11) && (strlen($_POST['cntc']) < 520) ) {
    if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['cntc'])) {
      $new_ad_contactURL = $_POST['cntc'];
      $_SESSION['new_ad_contactURL'] = $new_ad_contactURL;
    } else {
      $reg_errors['cntc'] = 'Contact URL: '.$bannedMessage;
    }
  } else {
    $reg_errors['cntc'] = 'Enter a valid URL, beginning with http:// or https://';
  }

  // Check Business Listing
  if (isset($_POST['bizn'])) {
    if (preg_match ('/^[A-Z0-9 \'\/.&,:-]{3,80}$/i', $_POST['bizn'])) {
      if (!preg_match_all ("#\b($bannedwords)\b#i", $_POST['bizn'])) {
        $new_ad_content_bizn = $_POST['bizn'];
        $_SESSION['new_ad_content_bizn'] = $new_ad_content_bizn;
      } else {
        $reg_errors['bizn'] = 'Business Listing: '.$bannedMessage;
      }
    } else {
      $reg_errors['bizn'] = 'Business Listing: Only letters and numbers and - \' / . , : &';
    }
  }

  // Check Podcast Listing
  if (isset($_POST['pdcst'])) {
    if (preg_match ('/^[\r\nA-Z0-9_ \'\/.@%?,:!;–—-]{100,1000}$/i', $_POST['pdcst'])) { // '\r\n' allows line breaks
      if (!preg_match_all("#\b($bannedwords)\b#i", $_POST['pdcst'])) {
        if (str_word_count($_POST['pdcst']) <= 55) {
          $regex_replace = "/[^0-9a-zA-Z_ \'\/.@%?,:!;–—-]/";
      		$result = preg_replace($regex_replace,"", $_POST['pdcst']);
          $result = preg_replace('/[\r\n]/', ' ', $result);// Convert line breaks to spaces before spaces
          $result = preg_replace('/\s\s+/', ' ', $result); // Remove white space
      		$result = preg_replace('/([A-Z].[a-z]+)-([A-Z].[a-z]+)/','$1–$2',$result); // Proper noun range to en-dash
      		$result = preg_replace('/([0-9]$)+-+([0-9])/','$1–$2',$result); // number range to en-dash
      		$result = str_replace(' -- ',' – ',$result); // to en-dash
      		$result = str_replace(' --','—',$result); // to em-dash
      		$result = str_replace('-- ','—',$result); // to em-dash
      		$result = str_replace('---','—',$result); // to em-dash
      		$result = str_replace('--','—',$result); // to em-dash
      		$result = strip_tags($result); // Remove any HTML tags

          $new_ad_content_pdcst = $result;
          $_SESSION['new_ad_content_pdcst'] = $new_ad_content_pdcst;
        } else {
          $reg_errors['pdcst'] = 'Maximum 50 words.';
        }
      } else {
        $reg_errors['pdcst'] = 'Podcast Listing: '.$bannedMessage;
      }
    } else {
      $reg_errors['pdcst'] = 'Podcast Listing: 50 word max, 100-1,000 characters. Only letters, numbers, dashes, and \' / . , : ; ? ! % @<br />Currency must use market terms like "USD" or "AUD" or "Canadian dollars" etc.';
    }
  }

  // Check following ad start date
  if ((isset($_POST['after_ad_date_to_start'])) && (filter_var($_POST['after_ad_date_to_start'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
    $after_ad_date_to_start = $_POST['after_ad_date_to_start'];
    $_SESSION['after_ad_date_to_start'] = $after_ad_date_to_start;
  } else {
    $after_ad_date_to_start = 'right_now';
    $_SESSION['after_ad_date_to_start'] = $after_ad_date_to_start;
  }

  // Category
  if ($_POST['ctgr']) {
    if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['ctgr'])) {$IP = get_ip_addr(); script_kiddy('sk_5', '_POST ctgr', $_POST['ctgr'], $IP);}
    $cat = preg_replace("/[^A-Za-z0-9]/","", $_POST['ctgr']);
    $_SESSION['cat'] = $cat;
  }

  // Subcategory Submission
  if ($_POST['subcat']) {
    if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['subcat'])) {$IP = get_ip_addr(); script_kiddy('sk_6', '_POST subcat', $_POST['subcat'], $IP);}
    $new_ad_subcat = preg_replace("/[^A-Za-z0-9]/","", $_POST['subcat']);
    $_SESSION['new_ad_subcat'] = $new_ad_subcat;
  }

  // Role
  if ($_POST['role']) {
    if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['role'])) {$IP = get_ip_addr(); script_kiddy('sk_7', '_POST role', $_POST['role'], $IP);}
    $roleID = preg_replace("/[^A-Za-z0-9]/","", $_POST['role']);
    $_SESSION['roleID'] = $roleID;
  }

  // Weeks long
  if ($_POST['weekslong']) { // new ads
    if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['weekslong'])) {$IP = get_ip_addr(); script_kiddy('sk_8', '_POST weekslong', $_POST['weekslong'], $IP);}
    $new_ad_weekslong = preg_replace("/[^A-Za-z0-9]/","", $_POST['weekslong']);
    $_SESSION['new_ad_weekslong'] = $new_ad_weekslong;
  } elseif (isset($_SESSION['new_ad_weekslong'])) { // Rerun ads
    $new_ad_weekslong = $_SESSION['new_ad_weekslong'];
  } else {
    $new_ad_weekslong = NULL;
  }

  // Role
  $roleID = mysqli_real_escape_string ($dbc, $roleID);
  $q = "SELECT role FROM roles WHERE id='$roleID'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $roleName = "$row[0]"; // $_POST['subcat']
  $_SESSION['roleName'] = $roleName;

  // SubCat ID & pretty name
  $cat = mysqli_real_escape_string ($dbc, $cat);
  $new_ad_subcat = mysqli_real_escape_string ($dbc, $new_ad_subcat);
  $q = "SELECT id, subcat FROM sub_$cat WHERE slug='$new_ad_subcat'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $subcatID = "$row[0]";
  $subcatName = "$row[1]";
  $_SESSION['subcatID'] = $subcatID;
  $_SESSION['subcatName'] = $subcatName;

  // Dup checks
    // Heading Dup
    $new_ad_heading_check = mysqli_real_escape_string ($dbc, $new_ad_heading);
    if (isset($allowed_dup)) {
      $q = "SELECT id FROM ads WHERE (date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_hdng='$new_ad_heading_check' AND category_id='$catID' AND subcat_id='$subcatID') AND NOT (id='$allowed_dup' OR rerun_id='$allowed_dup')";
    } else {
      $q = "SELECT id FROM ads WHERE date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_hdng='$new_ad_heading_check' AND category_id='$catID' AND subcat_id='$subcatID'";
    }
    $row = mysqli_query($dbc, $q);
      if (mysqli_num_rows($row) > 0) {
        $reg_errors['hdng'] = "<br /><b>That heading is already in use for this ad category.</b> Change the heading or change the categories.";
      }
    // Contact URL Dup
    $new_ad_contactURL_check = mysqli_real_escape_string ($dbc, $new_ad_contactURL);
    if (isset($allowed_dup)) {
      $q = "SELECT id FROM ads WHERE (date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_cntc='$new_ad_contactURL_check' AND category_id='$catID' AND subcat_id='$subcatID') AND NOT (id='$allowed_dup' OR rerun_id='$allowed_dup')";
    } else {
      $q = "SELECT id FROM ads WHERE date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_cntc='$new_ad_contactURL_check' AND category_id='$catID' AND subcat_id='$subcatID'";
    }
    $row = mysqli_query($dbc, $q);
      if (mysqli_num_rows($row) > 0) {
        $reg_errors['cntc'] = "<br /><b>That URL is already in use for this ad category.</b> <i>You have some options!</i><br /><br />You may:<ol><li>1. Send visitors somewhere else, even to a page with 85% identical content,</li><li>2. Try a different category or subcategory for your ad,</li><li>3. Run an ad for a webstore and run separate ads that each link to a unique corresponding webstore product category page.</li></ol><i>(Careful, don't play around with this rule. A duplicate URL workaround, such as forwarding multiple ads to the same destination or information, could result in account removal, ad removal, and fee forfeiture. Our goal is to avoid being 'spammy'. This doesn't need to be a problem for you and we want to help your needs, so you can get more clarity in our Terms of Service.)</i>";
      }

    // Business listing Dup
    if ($new_ad_biz_listing == 'biz') {
      $new_ad_content_bizn_check = mysqli_real_escape_string ($dbc, $new_ad_content_bizn);
      if (isset($allowed_dup)) {
        $q = "SELECT id FROM ads WHERE (date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_bizn='$new_ad_content_bizn_check' AND category_id='$catID' AND subcat_id='$subcatID') AND NOT (id='$allowed_dup' OR rerun_id='$allowed_dup')";
      } else {
        $q = "SELECT id FROM ads WHERE date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_bizn='$new_ad_content_bizn_check' AND category_id='$catID' AND subcat_id='$subcatID'";
      }
      $row = mysqli_query($dbc, $q);
        if (mysqli_num_rows($row) > 0) {
          $reg_errors['bizn'] = "<b>You or someone else already has that business name listed for this category.</b> A brand, trademark, or DBA can only be used once per ad category. \"Killing\" an old ad running a new ad <i>WILL NOT</i> free up the name! <i>You have some options!</i><br /><br />Consider:<ol><li>1. If you want to run more than one ad for your business, but, say, for a different product category in your online store, you may use this format to make your \"business name\" unique: <i>\"My Legal ABC Business Name: Product Category\"</i>.</li><li>2. If this is your business and you need to change information, your business ad can be modified once every 8 weeks.</li><li>3. If you want more than one business listing, you must have more than one business/trade name to list. This is about law and intellectual property rights.</li></ol>";
        }
      }



  	if (!empty($reg_errors)) { // If reg_errors exist

      $_SESSION['validAd'] = false;

    } else { // If entries pass regex...

      // Price
      if ($new_ad_weekslong != NULL) { // Sometimes it's NULL, such as for edits, mods, or specials
        $wkly_price = $_POST['wkly_price'];
        $base_price = (isset($_POST['base_price'])) ? $_POST['base_price'] : 0;
        $adCorePrice = (($new_ad_pod_listing == 'pod') && ($base_price != 0)) ? ($base_price + abs($new_ad_weekslong*$wkly_price)) : abs($new_ad_weekslong*$wkly_price);

          // Check if decimals were removed
          if (strpos($adCorePrice, ".") == false) { $adCorePrice = $adCorePrice.'.00'; }
    		$adPrice = $adCorePrice;
        $_SESSION['adPrice'] = $adPrice;
      } else {
        $adPrice = NULL;
        $_SESSION['adPrice'] = NULL;
      }

  		// Set the New Ad as valid (if we're not editing it)
  		$_SESSION['validAd'] = true;

    } // End errors
} // End submitted form
