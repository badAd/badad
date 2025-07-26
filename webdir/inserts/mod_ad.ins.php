<?php

// Start editing

// define create_new_ad_input()
require_once ('./includes/ad_functions.inc.php');

// Retrieve the Category ID, price & name
$cat = mysqli_real_escape_string ($dbc, $cat);
$q = "SELECT id, category, price, bizn_price FROM categories WHERE slug='$cat'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$catID = "$row[0]"; // $_POST['ctgr']
$categoryName = "$row[1]";
$categoryPrice = "$row[2]";
$bizPerWeekPrice = "$row[3]";

$_SESSION['catID'] = $catID;
$_SESSION['categoryName'] = $categoryName;
$_SESSION['categoryPrice'] = $categoryPrice;
$_SESSION['bizPerWeekPrice'] = $bizPerWeekPrice;
if ($new_ad_biz_listing == 'biz') {
 $adPricePerWeek = $bizPerWeekPrice;
} elseif ($new_ad_biz_listing == 'non') {
 $adPricePerWeek = $categoryPrice;
}
$_SESSION['adPricePerWeek'] = $adPricePerWeek;

// Cleanup prices
if (strpos($categoryPrice, ".") == false) { $categoryPrice = $categoryPrice.'.00'; }
if (strpos($bizPerWeekPrice, ".") == false) { $bizPerWeekPrice = $bizPerWeekPrice.'.00'; }

// Build the form with its options
echo '<div class="new_ad">';
//set_switch("&larr; Change category", "Start over to change the category", "new_ad.php", "o", "start-over", "set_gray");
echo '<br />
  <form action="mod_ad.php" method="post" accept-charset="utf-8" style="padding-left:100px">
  <input type="hidden" name="adform" value="submitted" />
  <input type="hidden" name="modified" value="modified" />
  <input type="hidden" name="ctgr" value="'.$cat.'" />
  <input type="hidden" name="b" value="'.$new_ad_biz_listing.'" />';

  // Scheduled to run
  echo '<b class="scheduled">Scheduled for:</b><br />';
  echo "<i>$new_ad_date_starts</i><br /><br />";

  // Business listing?
  if ($new_ad_biz_listing == 'biz') {
    echo '<b class="category">Category & Rate:</b><br />
    <p class="category"><b class="category_name"><i>'.$categoryName.'</i></b> - <i class="bizn_yn">business listing</i>
    <br />@ <b class="price">$'.$bizPerWeekPrice.'</b> per week total';
  } else {
    echo '<b class="category">Category & Rate:</b><br />
    <p class="category"><b class="category_name"><i>'.$categoryName.'</i></b> - <i class="bizn_yn">normal listing</i>
    <br />@ <b class="price">$'.$categoryPrice.'</b> per week';
  }
  echo '</p>';

  // Subcategory
    echo '<b class="subcategory">Subcategory:</b><br /><br />
    <select class="formselect" name="subcat" required>';
    if ((isset($_POST['subcat'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['subcat']))) {$IP = get_ip_addr(); script_kiddy('sk_13', '_POST subcat', $_POST['subcat'], $IP);}
    if (isset($_POST['subcat'])) {
      // Retain the $value
      $value = preg_replace("/[^A-Za-z0-9]/","", $_POST['subcat']);
      //$_SESSION['new_ad_subcat'] = $value;
    } elseif (isset($_SESSION['new_ad_subcat'])) {
      $value = $_SESSION['new_ad_subcat'];
    }

    // Make the non-option message the default, but only if the $value wasn't POSTed or SESSIONed
    if ($value) {
      echo '<option disabled value="" hidden>Choose a Sub-Category</option>';
    } else { echo "value not set";
      echo '<option disabled value="" selected hidden>Choose a Sub-Category</option>';
    }
      // Get each of the subcategories from the database
      $q = "SELECT * FROM sub_$cat ORDER BY subcat";
      $r = mysqli_query($dbc, $q);
      while (list($id, $subcat, $slug) = mysqli_fetch_array($r, MYSQLI_NUM)) {
        // Iterate each subcat, make the POSTed $value the default if it exists
        if ($value == $slug) {
          echo '<option value="'.$slug.'" selected>'.$subcat.'</option>';
        } else {
          echo '<option value="'.$slug.'">'.$subcat.'</option>';
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

    // Runs until
  echo '<br /><br />
    <b class="weekslong">Until: '.$new_ad_date_expires.'</b>';
    echo '<input type="hidden" name="weekslong" value="'.$new_ad_weekslong.'" />';

    // End the expiry date message
    echo '<br /><br /><label for="nick"><b class="adnickname">Nickname:</b> <i class="note_gray">(for your reference)</i><br /><br /></label>';
    create_new_ad_input('nick', 'text', 'Nickname this ad for your reference', $reg_errors, $new_ad_nickname);
    echo '<br />
    <b class="adcontentform">Your ad content:</b> <i class="note_gray">(For your changes...)</i>';
    create_new_ad_input('hdng', 'text', '[ Heading: Catchy and Unique ]', $reg_errors, $new_ad_heading);
    create_new_ad_input('dscr', 'text', '[ Description: Stuff everyone must know ]', $reg_errors, $new_ad_description);
    create_new_ad_input('info', 'text', '[ Info: features, abilities, info, can-do ]', $reg_errors, $new_ad_info);
    create_new_ad_input('pyrt', 'text', '[ Pricing: best way to describe my pay-rate ]', $reg_errors, $new_ad_pricing);

    // Business listing?
    if ($new_ad_biz_listing == 'biz') {
      create_new_ad_input('bizn', 'text', 'Legal Business/Trade Name', $reg_errors, $new_ad_content_bizn);
      echo '<input type="hidden" name="wkly_price" value="'.$bizPerWeekPrice.'" />';
    } else {
      echo '<input type="hidden" name="wkly_price" value="'.$categoryPrice.'" />';
    }

    echo '<label for="cntc" class="note_gray">Contact URL:</label><br />';
    create_new_ad_input('cntc', 'url', 'http://...', $reg_errors, $new_ad_contactURL);
    echo '<br />';

    echo '<label for="taglist" class="note_gray">Tags: (spaces will be removed, letters & numbers only, separate by commas)</label><br />';
    /* Comment-out Flexdatalist until fixed
      // Having trouble with this new form via Flexdatalist
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

    echo '<br /><br />
    <p><input type="checkbox" required /> <b>Ready!</b> Your changes will go into effect as soon as allowed: '.$new_ad_date_starts.'</p><p>Once you click "Save" your ad will be permanently set to rerun, but <i>you will be able to edit it before it goes live</i>.</p>
		<input type="submit" name="submit_button" value="Save" id="submit_button" class="formbutton" />
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
