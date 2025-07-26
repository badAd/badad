<?php

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Show New Ad options
if ((!isset($_GET['c']) && empty($_POST)) || ((!empty($_POST)) && (!empty($_POST['username'])))) {
  // Clear any old SESSION form values
  include ('./includes/ad_values_unset.inc.php');

// Business listing features
echo '<p><i><b>*Business Listing includes:</b> Business directory listing, business name, access to online stats, ad duration up to 1 year, and ability to modify every 8 weeks.
<br />**New Podcast ads include Business Listing. We will record a 30 second ad from 50 words you provide. In the future, you may renew the same ad at a lower price, which also includes business listing.</i></p>';

// Categories
echo '
<div class="new_ad_menu_cat">
<h3>Start with an ad category...</h3><br />
<table class="new_ad_menu">
<tbody>
<tr><th>Category:</th><th>Normal Price:</th><th>Business Listing Price:*</th><th>New Podcast Ad Price:**</th><th>Podcast Ad Renewal Price:</th></tr>';

  $q = "SELECT slug, category, price, bizn_price, pdcst_price, pdcst_renew FROM categories";
  $r = mysqli_query($dbc, $q);
  while ($row = mysqli_fetch_array($r)) {
    $catSlug = $row[0];
    $catName = $row[1];
    $catPrice = $row[2];
    $biznPrice = $row[3];
    $pdcstPrice = $row[4];
    $pdcstRenew = $row[5];

    // Build price numbers
    if (strpos($catPrice, ".") == false) { $catPrice = $catPrice.'.00'; }
    if (strpos($biznPrice, ".") == false) { $biznPrice = $biznPrice.'.00'; }
    if (strpos($pdcstPrice, ".") == false) { $pdcstPrice = $pdcstPrice.'.00'; }
    if (strpos($pdcstRenew, ".") == false) { $pdcstRenew = $pdcstRenew.'.00'; }
    $podcstFirstWeek = number_format(((($pdcstPrice * 100) + ($pdcstRenew * 100)) / 100), 2, '.', '');

echo "
<tr>
  <td class=\"new_ad_menu\"><b class=\"category_name\">$catName</b></td>
  <td class=\"new_ad_menu\"><a class=\"cat_start\"title=\"Start an ad for: $catName\" href=\"new_ad.php?c=$catSlug\">\$$catPrice /week</a></td>
  <td class=\"new_ad_menu\"><a class=\"cat_start\"title=\"Start an ad for: $catName\" href=\"new_ad.php?c=$catSlug&b\">\$$biznPrice /week</a></td>
  <td class=\"new_ad_menu\"><a class=\"cat_start\"title=\"Start an ad for: $catName\" href=\"new_ad.php?c=$catSlug&p\">\$$podcstFirstWeek first week</a></td>
  <td class=\"new_ad_menu\">\$$pdcstRenew /week</td>
</tr>
";

  }
echo '
</tbody>
</table>
</div>';

// New and non-logged-in users see login and registration forms
if (!isset($_SESSION['user_id'])) {

	 // REFERRAL VERSION
	 // Log in form (Referrals excluded)
   /* Omitting this from the test below because registration is being removed from this step: (!isset($_POST['registerform'])) && */
	 if ((!isset($_SESSION['refUserID'])) && (!isset($_SESSION['rSlug']))) { // Don't show this or run the error checks if the register form was submitted
     echo "<br /><hr /><br /><p><i>Optional: If you have an account, you may login now or later. Returning customers have more options for Business Listing on the next page...</i></p>";
		 echo "<h5 class=\"loginNotice\">Login (returning users)</h>";
		 $lformaction = 'new_ad.php'; // This must be set for login_form.inc.php to work
		 require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
 } elseif ((isset($_SESSION['refUserID'])) && (isset($_SESSION['rSlug']))) {
	 	echo "<p class=\"note_blue\">Referred user credit applies once per account. The credit will automatically be granted at checkout.</p><br />";
 } // REFERRED

	/*REFERRAL VERSION REPLACES THIS
	 // Log in form
	 if (!isset($_POST['registerform'])) { // Don't show this or run the error checks if the register form was submitted
		 echo "<h5 class=\"loginNotice\">Login (returning users)</h>";
		 $lformaction = 'new_ad.php'; // This must be set for login_form.inc.php to work
		 require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
	}
	REFERRAL COMMENTS THIS*/

  /* Not at this point, registration should be only for serious customers
	 // Registration form
	 if (!isset($_POST['loginform'])) { // Don't show this or run the error checks if the login form was submitted
		 echo "<h5 class=\"loginNotice\">Signup (new users)</h5>";
		 $rformaction = 'new_ad.php'; // This must be set for register.inc.php to work
		 require ('./includes/register.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
 	}
  */
}

/* Simple login check here replaced by a fuller registration-referral option (just above)
  // Login form
  if (!isset($userid)) {
  echo '<br /><br /><p>Returning users can login to see just a few more options.</p>';
  $lformaction = "new_ad.php"; // This must be set for login_form.inc.php to work
  require ('./includes/login_form.inc.php'); // This must be a separate file, not a function, so the error checks in login_check.inc.php will work
  }
Replaced by registration-referral option */


// Subcategories
echo '<br /><br /><h3>Or, choose from all subcategories now...</h3><br />
<div class="new_ad_menu_sub">
<table class="new_ad_menu">
<tbody>';

  $q = "SELECT slug, category, price, bizn_price, pdcst_price, pdcst_renew FROM categories";
  $r = mysqli_query($dbc, $q);
  while ($row = mysqli_fetch_array($r)) {
    $catSlug = $row[0];
    $catName = $row[1];
    $catPrice = $row[2];
    $biznPrice = $row[3];
    $pdcstPrice = $row[4];
    $pdcstRenew = $row[5];
    // Build price numbers
    if (strpos($catPrice, ".") == false) { $catPrice = $catPrice.'.00'; }
    if (strpos($biznPrice, ".") == false) { $biznPrice = $biznPrice.'.00'; }
    if (strpos($pdcstPrice, ".") == false) { $pdcstPrice = $pdcstPrice.'.00'; }
    if (strpos($pdcstRenew, ".") == false) { $pdcstRenew = $pdcstRenew.'.00'; }
    $podcstFirstWeek = number_format(((($pdcstPrice * 100) + ($pdcstRenew * 100)) / 100), 2, '.', '');

echo "
<tr>
  <th class=\"new_ad_menu\"><b class=\"category_name\">$catName</b></th><th>Normal Price:</th><th>Business Listing Price:*</th><th>New Podcast Ad Price:**</th><th>Podcast Ad Renewal Price:</th>
</tr>
";

    $catSlug = mysqli_real_escape_string ($dbc, $catSlug);
    $qs = "SELECT subcat, slug FROM sub_$catSlug";
    $srow = mysqli_query($dbc, $qs);
    while ($subRow = mysqli_fetch_array($srow)) {
      $subcatName = $subRow[0];
      $subcatSlug = $subRow[1];
      echo "
      <tr>
        <td class=\"new_ad_menu\"><span class=\"subcategory_name\">$subcatName</span></td>
        <td class=\"new_ad_menu\"><a class=\"subcat_start\"title=\"Start an ad for: $catName\" href=\"new_ad.php?c=$catSlug&s=$subcatSlug\">\$$catPrice /week</a></td>
        <td class=\"new_ad_menu\"><a class=\"subcat_start\"title=\"Start an ad for: $catName\" href=\"new_ad.php?c=$catSlug&s=$subcatSlug&b\">\$$biznPrice /week</a></td>
        <td class=\"new_ad_menu\"><a class=\"subcat_start\"title=\"Start an ad for: $catName\" href=\"new_ad.php?c=$catSlug&s=$subcatSlug&p\">\$$podcstFirstWeek first week</a></td>
        <td class=\"new_ad_menu\">\$$pdcstRenew /week</td>
      </tr>
      ";
    }


  }
echo '
</tbody>
</table>
</div>';

} else {

// Build the New Ad form

// define create_new_ad_input()
require_once ('./includes/ad_functions.inc.php');

// Retrieve the Category ID, price & name
$cat = mysqli_real_escape_string ($dbc, $cat);
$q = "SELECT id, category, price, bizn_price, pdcst_price, pdcst_renew FROM categories WHERE slug='$cat'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$catID = "$row[0]"; // $_POST['ctgr']
$categoryName = "$row[1]";
$categoryPrice = "$row[2]";
$bizPerWeekPrice = "$row[3]";
$podNewAdPrice = "$row[4]";
$podRenewPrice = "$row[5]";

$_SESSION['catID'] = $catID;
$_SESSION['categoryName'] = $categoryName;
$_SESSION['categoryPrice'] = $categoryPrice;
$_SESSION['bizPerWeekPrice'] = $bizPerWeekPrice;
$_SESSION['podNewAdPrice'] = $podNewAdPrice;
$_SESSION['podRenewPrice'] = $podRenewPrice;
if (($new_ad_pod_listing == 'pod') || ($new_ad_biz_listing == 'biz')) {
 $adPricePerWeek = ($new_ad_pod_listing == 'pod') ? $podRenewPrice : $bizPerWeekPrice;
 $adPodcastPrice = ($new_ad_pod_listing == 'pod') ? $podNewAdPrice : 0;
 // Get some database info we will need later
 if (isset($_SESSION['user_id'])) {
   $userid = $_SESSION['user_id'];
   $qAllAds = "SELECT id, ad_nickname, date_expires FROM ads WHERE user_id='$userid' AND pub_status='live' AND date_expires > '$timeNow'";
   $rAllAds = mysqli_query ($dbc, $qAllAds);
 }
} elseif ($new_ad_biz_listing == 'non') {
  $adPricePerWeek = $categoryPrice;
}

// Freekey
if ((isset($_SESSION['purchase_key_id'])) && ((!isset($new_ad_pod_listing)) || ($new_ad_pod_listing != 'pod'))) {
  $adPricePerWeek = 0.00;
}

// Set the SESSION
$_SESSION['adPricePerWeek'] = $adPricePerWeek;
$_SESSION['adPodcastPrice'] = $adPodcastPrice;

// Cleanup prices
if (strpos($categoryPrice, ".") == false) { $categoryPrice = $categoryPrice.'.00'; }
if (strpos($bizPerWeekPrice, ".") == false) { $bizPerWeekPrice = $bizPerWeekPrice.'.00'; }
if (strpos($podNewAdPrice, ".") == false) { $podNewAdPrice = $podNewAdPrice.'.00'; }
if (strpos($podRenewPrice, ".") == false) { $podRenewPrice = $podRenewPrice.'.00'; }
$podcstFirstWeek = number_format(((($podNewAdPrice * 100) + ($podRenewPrice * 100)) / 100), 2, '.', '');

// Build the form with its options
echo '<div class="new_ad">';
//set_switch("&larr; Change category", "Start over to change the category", "new_ad.php", "o", "start-over", "set_gray");
echo '<br />
  <form action="new_ad.php" method="post" accept-charset="utf-8" class="newadform">
  <input type="hidden" name="adform" value="submitted" />
  <input type="hidden" name="ctgr" value="'.$cat.'" />
  <input type="hidden" name="b" value="'.$_SESSION['new_ad_biz_listing'].'" />
  <input type="hidden" name="p" value="'.$_SESSION['new_ad_pod_listing'].'" />';

  // Business or podcast listing?
  if ($new_ad_pod_listing == 'pod') {
    echo '<b class="category">Category & Rate:</b><br />
    <p class="category"><b class="category_name"><i>'.$categoryName.'</i></b> - <i class="bizn_yn">podcast & business listing</i> <a title="Start over to change category" href="new_ad.php">(change & start over)</a>
    <br />@ <b class="price">$'.$podcstFirstWeek.'</b> first week, extra weeks are $'.$podRenewPrice.' each';
  } elseif ($new_ad_biz_listing == 'biz') {
    echo '<b class="category">Category & Rate:</b><br />
    <p class="category"><b class="category_name"><i>'.$categoryName.'</i></b> - <i class="bizn_yn">business listing</i> <a title="Start over to change category" href="new_ad.php">(change & start over)</a>
    <br />@ <b class="price">$'.$bizPerWeekPrice.'</b> per week';

  } else {
    echo '<b class="category">Category & Rate:</b><br />
    <p class="category"><b class="category_name"><i>'.$categoryName.'</i></b> - <i class="bizn_yn">normal listing</i> <a title="Start over to change category" href="new_ad.php">(change & start over)</a>
    <br />@ <b class="price">$'.$categoryPrice.'</b> per week';
  }
  echo '</p>';

  // Run after current ad date?
  if ($new_ad_biz_listing == 'biz') {
    if (isset($userid)) {
      echo '
      <b class="datetostart">Wait to run this ad until another ad ends:</b> (optional)<br /><br />
      <select class="formselect" name="after_ad_date_to_start"> ';
      if ($after_ad_date_to_start) {
        echo '<option value="right_now">Run this ad immediately!</option>';
      } else {
        echo '<option disabled value="right_now" selected hidden>Choose an ad to chase...</option>
        <option value="right_now">Run this ad immediately!</option>';
      }
      // Each available date to chase
      while ($row = mysqli_fetch_array($rAllAds)) {
        $chase_adID = "$row[0]";
        $adNickname = "$row[1]";
        $adDateExpires = "$row[2]";

        if (($after_ad_date_to_start) && ($after_ad_date_to_start == $chase_adID)) {
          echo '<option value="'.$chase_adID.'" selected>'.$adNickname.' - (Ends: '.$adDateExpires.')</option>';
        } else {
          echo '<option value="'.$chase_adID.'">'.$adNickname.' - (Ends: '.$adDateExpires.')</option>';
        }
      }

      echo '</select><br /><br />';
    } else {
      echo '<p class="note_gray">For returning customers to see a few more "business listing" options, login before starting or login on the next page and come back. This does not affect new customers.</p>';
    }
  }

  // Subcategory message
  echo '<p class="note_gray"><i>Subcategories help three ways: 1. Advertising partners (like blogs with our ads) can choose which Subcategories (not Tags) to include on their websites. 2. Visitors can search badAd.one by Subcategory (or Tag). 3. Every ad must have a unique Heading, Contact URL, and Business Name (if applicable,) but, if you want to purchase the same ad more than once (for more exposure,) you are allowed to run an identical ad, <b>if the Subcategory is different</b>.</i></p>';
  echo '<p class="note_gray"><i>If you don\'t see a Subcategory you like, choose something close, then use a Tag (very bottom) for what you wanted.</i></p>';


  // Subcategory
    echo '<b class="subcategory">Subcategory:</b><br /><br />
    <select class="formselect" name="subcat" required>';
    if (isset($_POST['subcat'])) {
      // Script Kiddy Check
      if (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['subcat'])) {$IP = get_ip_addr(); script_kiddy('sk_1', '_POST subcat', $_POST['subcat'], $IP);}
      // Retain the $sc_value
      $sc_value = preg_replace("/[^A-Za-z0-9]/","", $_POST['subcat']);
      //$_SESSION['new_ad_subcat'] = $sc_value;
    } elseif (isset($_GET['s'])) {
      // Allow subcategory to be set
      $sc_value = preg_replace("/[^A-Za-z0-9]/","", $_GET['s']);
    } elseif (isset($_SESSION['new_ad_subcat'])) {
      $sc_value = $_SESSION['new_ad_subcat'];
    }

    // Make the non-option message the default, but only if the $sc_value wasn't POSTed or SESSIONed
    if (isset($sc_value)) {
      echo '<option disabled value="" hidden>Choose a Sub-Category</option>';
    } else { echo "value not set";
      echo '<option disabled value="" selected hidden>Choose a Sub-Category</option>';
    }
      // Get each of the subcategories from the database
      $cat = mysqli_real_escape_string ($dbc, $cat);
      $q = "SELECT * FROM sub_$cat ORDER BY subcat";
      $r = mysqli_query($dbc, $q);
      while (list($id, $subcat, $slug) = mysqli_fetch_array($r, MYSQLI_NUM)) {
        // Iterate each subcat, make the POSTed $sc_value the default if it exists
        if ((isset($sc_value)) && ($sc_value == $slug)) {
          echo '<option value="'.$slug.'" selected>'.$subcat.'</option>';
          $sc_value_selected = true;
        } else {
          echo '<option value="'.$slug.'">'.$subcat.'</option>';
        }
        // If no item was initiated, made sure we use the non-option placeholder
        if ((!isset($sc_value_selected)) || ($sc_value_selected !=true)) {
          echo '<option disabled value="" selected hidden>Choose a Sub-Category</option>';
        }
      }
    echo '</select>';

    // Role
    echo '<br /><br />
      <b class="role">Advertising as:</b><br /><br />
        <select class="formselect" name="role" required>';
    if ($roleID) {
      echo '<option disabled value="" hidden>Choose a role</option>';
  } else {
      echo '<option disabled value="" selected hidden>Choose a role</option>';
  }

    // Get roles
    $q = "SELECT id, role, description FROM roles";
    $r = mysqli_query ($dbc, $q);
    while ($row = mysqli_fetch_array($r)) {
      $roleIDoption = "$row[0]";
      $roleName = "$row[1]";
      $roleDescription = "$row[2]";

      if ($roleID == $roleIDoption) {
        echo '<option value="'.$roleIDoption.'" selected>'.$roleName.' - ('.$roleDescription.')</option>';
      } else {
        echo '<option value="'.$roleIDoption.'">'.$roleName.' - ('.$roleDescription.')</option>';
      }
    }
    echo '</select>';

    // Weeks long
  echo '<br /><br />
    <b class="weekslong">For:</b><br /><br />
    <select class="formselect" name="weekslong" required> ';
    // Start creating the "Weeks long" select option
  		// Add the value to the input if it is there
      // First, get the SESSIONed $wl_value, if it exists
			if (isset($_SESSION['new_ad_weekslong'])) {
				$wl_value = $_SESSION['new_ad_weekslong'];
      }
      // Make the non-option message the default, but only if the $wl_value wasn't POSTed or SESSIONed
      if (isset($wl_value)) {
				echo '<option disabled value="" hidden>How many weeks?</option>';
			} else {
				echo '<option disabled value="" selected hidden>How many weeks?</option>';
			}
      // "Weeks long" select option
      // Count to 8
      if ($new_ad_biz_listing == 'biz') {
        $countTo = 53;
      } else {
        $countTo = 8;
      }
      $count = 1;
      while ($count <= $countTo) {
        // Iterate each value as a select option
        if ((isset($wl_value)) && ($wl_value == $count)) {
          echo '<option value="'.$count.'" selected>'.$count.'</option>';
        } else {
          echo '<option value="'.$count.'">'.$count.'</option>';
        }

      $count++;
      }
    // End the select options
    echo '</select> week(s)<br /><br />';

    // Nickname
    echo '<b class="adnickname">Nickname:</b> <i class="note_gray">(for your reference)</i><br /><br />';
    create_new_ad_input('nick', 'text', 'Nickname', $reg_errors, $new_ad_nickname);

    // Podcast listing?
    if ($new_ad_pod_listing == 'pod') {
      echo '<br />
            <b class="adcontentform">Podcast ad manuscript:</b> Write something awesome!<br /><br />
            <span class="adcontentform">Avoid first person; say the name of your brand, product, or service instead of "We". This will be read as a statement in a longer context, something like...</span><br /><br />
            <i class="adcontentform"><span style="font-size: 80px; color: #888;">&#x201C;</span> Thanks for listening. This podcast is sponsored by ABC Company...</i><br />
            ';
      create_new_ad_input('pdcst', 'text', '[Example] Too busy to clean? ABC Some Product has something for you...', $reg_errors, $new_ad_content_pdcst);
      echo '<br /><br /><span id="wordCount">0</span> word(s), 50 max<br /><br />
      <small><i class="note_gray"><label for="podcast_checkbox"><input type="checkbox" name="podcast_checkbox" id="podcast_checkbox" value="I understand and agree" required> I understand and agree:</label><br />
      This podcast manuscript (above) will be read by one of our audio specialists for podcast appearances. It may also appear inside podcast feed episodes as text.<br />
      No copyright claims can be made for any content submitted on this page as a result of appearing in ads delivered through badAd.one or its affiliates, whether as podcast ads, text ads, or otherwise.<br />
      Currency symbols are banned. Podcasts are heard worlwide, so prices must indicate their currency. Allowed formats: 50 USD, 15 Austrailian dollars.<br />
      This podcast manuscript will need approval from our team, subjectively considering factors of: courtesy, bigotry, honesty, deception, quality, enthusiasm, grammar, etc.<br />
      We reserve the right to recommend changes, which will delay your ad until you approve our recommended changes, or to refuse your ad altogether without refund.<br />
      Of course, we want a good reputation, which will guide our review process.<br />
      Information below is for the text ad included with the podcast ad purchase and may also appear in podcast episodes titles, etc...</i></small><br />';
    } else {
      echo '<small><i class="note_gray"><label for="podcast_checkbox"><input type="checkbox" name="podcast_checkbox" id="podcast_checkbox" value="I understand and agree" required> I understand and agree:</label><br />
      No copyright claims can be made for any content submitted on this page as a result of appearing in ads delivered through badAd.one or its affiliates, whether as podcast ads, text ads, or otherwise.<br />';
    }

    // Text ad content
    echo '<br />
          <b class="adcontentform">Your text ad content:</b> <i class="note_gray">(Important stuff near the top, details toward the bottom)</i><br />';
    create_new_ad_input('hdng', 'text', '[ Heading: Catchy and Unique ]', $reg_errors, $new_ad_heading);
    create_new_ad_input('dscr', 'text', '[ Description: Stuff everyone must know ]', $reg_errors, $new_ad_description);
    create_new_ad_input('info', 'text', '[ Info: features, abilities, info, can-do ]', $reg_errors, $new_ad_info);
    create_new_ad_input('pyrt', 'text', '[ Pricing: best way to describe my pay-rate ]', $reg_errors, $new_ad_pricing);

    // Business or podcast listing?
    if ($new_ad_pod_listing == 'pod') {
      create_new_ad_input('bizn', 'textarea', '[ Legal Business/Trade Name ]', $reg_errors, $new_ad_content_bizn);
      echo '<input type="hidden" name="wkly_price" value="'.$podRenewPrice.'" />';
      echo '<input type="hidden" name="base_price" value="'.$podNewAdPrice.'" />';
    } elseif ($new_ad_biz_listing == 'biz') {
      create_new_ad_input('bizn', 'textarea', '[ Legal Business/Trade Name ]', $reg_errors, $new_ad_content_bizn);
      echo '<input type="hidden" name="wkly_price" value="'.$bizPerWeekPrice.'" />';
    } else {
      echo '<input type="hidden" name="wkly_price" value="'.$categoryPrice.'" />';
    }

    echo '<br />';

    echo '<label for="cntc" class="note_gray">Contact URL:</label><br />';
    create_new_ad_input('cntc', 'url', 'https://...', $reg_errors, $new_ad_contactURL);
    echo '<br />';

    echo '<label for="taglist" class="note_gray">Tags: (spaces will be removed, letters & numbers only, separate by commas)</label><br />';

    /* Remove the ta variables due to trouble with Flexdatalist
    $new_ad_tagIDs = '';
    $_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
    $new_ad_tagList = '';
    $_SESSION['new_ad_tagList'] = $new_ad_tagList;
    $new_ad_tagArray = '';
    $_SESSION['new_ad_tagArray'] = $new_ad_tagArray;
    */

    create_new_ad_input('taglist', 'text', 'Tags...', $reg_errors, $new_ad_tagList);

    // Tags datalist
    echo '<datalist id="tag_list">';
      // Get the "unique" tags from the database
      $q = "SELECT tag FROM tags WHERE merged='unique'";
      $r = mysqli_query ($dbc, $q);
      while ($row = mysqli_fetch_array($r)) {
        $tag = "$row[0]";
        echo "<option value=\"$tag\">$tag</option>";
    } // End tags datalist loop

    // Finish the data list and include the javascript it needs to work
    echo "
    </datalist>";

    // Finish the form
    echo '
    <br/ ><br />
		<input type="submit" name="submit_button" value="Next &rarr;" id="submit_button" class="formbutton" />
  </form>
</div>';

/* Commented-out Taglist until working more smoothly
// Taglist
//<script src=\"js/jquery-2.1.3.min.js\"></script>
echo "
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js\"></script>
<script src=\"js/taglist.js\"></script>
";
*/

/* Comment-out Flexdatalist until fixed
// The problem is that "selection required" only allows items in the datalist, but without it the "x" won't remove an item from the _POST value, but only appends
// Add the flexdatalist files to include
// Thanks https://github.com/sergiodlopes/jquery-flexdatalist
echo "
<script src=\"js/jquery-1.8.3.min.js\"></script>
<script src=\"js/jquery.flexdatalist.min.js\"></script>
<script>
$('.flexdatalist').flexdatalist({
  valueProperty: 'value',
  searchContain: true,
  selectionRequired: true,
  minLength: 1,
  focusFirstResult: true,
  valueProperty: 'iso2',
  multiple: true,
  toggleSelected: true,
  requestType: 'post'
});
</script>
";
*/

}
