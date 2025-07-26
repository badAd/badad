<?php

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Set a variable for the userid
$userid = $_SESSION['user_id'];

// $siteID must be set
// This stores all categories in the _POST array, so _POST must not be used to reach this insert!

// See if user owns the site
$q = "SELECT user_id, nickname, type FROM partnersites WHERE id='$siteID'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$site_user_id = "$row[0]";
$site_nickname = "$row[1]";
$site_type = "$row[2]";
if ($site_user_id != $userid) {
	header("Location: partner.php");
	exit(); // Quit the script
}

// If $_POSTed to update database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Auto-add new cats
	if ((isset($_POST['auto_add_new_cat'])) && ($_POST['auto_add_new_cat'] != 'auto_add_all_true')) {$IP = get_ip_addr(); script_kiddy('sk_15', '_POST auto_add_new_cat', $_POST['auto_add_new_cat'], $IP);}
	if (isset($_POST['auto_add_new_cat'])) {
		$auto_add_new_cat = true;
		$auto_add_new_cat_set = 'true';
	} else {
		$auto_add_new_cat = false;
		$auto_add_new_cat_set = 'false';
	}
	unset($_POST['auto_add_new_cat']);

	// Global Subcats
  $arrayGlobalSubIDs = array();
  // Just take the _POST array and make it the array of global_subcat_ids
  $arrayGlobalSubIDs = $_POST;
  $update_site_cats_List = implode(", ", $arrayGlobalSubIDs);

  // Update the database
	$update_site_cats_List = mysqli_real_escape_string ($srv_dbc, $update_site_cats_List);
  $q = "UPDATE partnersites SET global_subcat_ids='$update_site_cats_List', auto_add_new_cat=$auto_add_new_cat_set WHERE id='$siteID'";
	// Podcast project? Also update the feeds table
	if ($site_type == ' podcast') {
		$update_site_cats_List = mysqli_real_escape_string ($srv_dbc, $update_site_cats_List);
	  $qp = "UPDATE feeds SET global_subcat_ids='$update_site_cats_List', auto_add_new_cat=$auto_add_new_cat_set WHERE project_id='$siteID'";
		if (!$rp = mysqli_query ($agg_dbc, $qp)) {
			sql_error($qp, 'agg_dbc', "sqle_138");
		}
	} // Podcast update also
  if (!$r = mysqli_query ($srv_dbc, $q)) {
		sql_error($q, 'srv_dbc', "sqle_81");
	} elseif (mysqli_affected_rows($srv_dbc) == 1) {
    echo "<p class=\"note_green\">Updated successfully!</p>";
	} else {
		echo "<p class=\"note_gray\">No changes.</p>";
	}
}

// Breadcrumb
echo "<p class=\"note_gray\">&larr; Return to the <a title=\"Partner Center\" href=\"partner.php\">Partner center</a>?</p>";

// Heading
echo "<h3>Subcategories for partner site '<b><u>$sitedomain</u>";
if ($site_nickname != NULL) {echo " <i>$site_nickname</i>";}
echo "</b>' (ID #$siteID)</h3><p>Choose which subcategories of ads you want to allow for advertisement on this site.</p>";

// Build the subcategory settings form for this site project

// Get site project information
$q = "SELECT global_subcat_ids FROM partnersites WHERE id='$siteID'";
$r = mysqli_query ($srv_dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$site_cats_List = $row[0];

// Compile the global_subcat_ids into an array
$arrayGlobalSubIDs = array();
$arrayGlobalSubIDs = explode(',', $site_cats_List);

// Start the form
echo "<form action=\"partner_site_subcats.php?s=$siteID\" method=\"post\">";
echo '<input type="submit" value="Save" class="formbutton" /><br /><br />';

// Auto-add new categories
echo '<label><input type="checkbox" name="auto_add_new_cat" value="auto_add_all_true"';
if ($auto_add_new_cat == true) {echo "checked ";}
echo '/> <b>All new categories always:</b> If badAd.one makes new Categories, automatically included those in my selections here.<br /><i class="note_gray">(This will <b>not</b> override your selections; deselected categories will remain deselected.)</i></label><br /><br />';

// Select all
echo '<label><input type="checkbox" onclick="toggle(this);" /> <b>Select all</b></label><br /><br />';

// Loop through each category
$qc = 'SELECT id, slug, category FROM categories';
$crow = mysqli_query($dbc, $qc);
while ($catRow = mysqli_fetch_array($crow)) {
  $catID = $catRow[0];
  $catSlug = $catRow[1];
  $catName = $catRow[2];

  echo "<b>$catName</b><br />";

  $qs = "SELECT id, subcat FROM sub_$catSlug";
  $srow = mysqli_query($dbc, $qs);
  while ($subRow = mysqli_fetch_array($srow)) {
    $subcatID = $subRow[0];
    $subcatName = $subRow[1];

    // Get global_subcat_id
    $q = "SELECT id FROM global_subcat_ids WHERE cat_id='$catID' AND subcat_id='$subcatID'";
    $r = mysqli_query ($srv_dbc, $q);
    $row = mysqli_fetch_array ($r, MYSQLI_NUM);
    $global_subcat_ID = $row[0];


    echo "<label><input type=\"checkbox\" name=\"$global_subcat_ID\" value=\"$global_subcat_ID\" ";

    if (in_array("$global_subcat_ID", $arrayGlobalSubIDs)) {
      echo "checked ";
    }

    echo "/> $subcatName</label><br />";

  } // Subcategory loop
	echo "<br />";
} // Category loop

// End the form
echo '<input type="submit" value="Save" class="formbutton" />
    </form>';


// Select all JavaScript
echo '
<script>
function toggle(source) {
    var checkboxes = document.querySelectorAll(\'input[type="checkbox"]\');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
    }
}
</script>';
