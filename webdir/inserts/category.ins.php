<?php

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Set role & business filters
$and_filter = "";
$analytics_filter = "";
if ((isset($_SESSION['filter_w'])) && ($_SESSION['filter_w'] == true)) { $and_filter = $and_filter." AND role_id NOT LIKE '1'"; $analytics_filter = $analytics_filter."w,"; }
if ((isset($_SESSION['filter_s'])) && ($_SESSION['filter_s'] == true)) { $and_filter = $and_filter." AND role_id NOT LIKE '2'"; $analytics_filter = $analytics_filter."s,"; }
if ((isset($_SESSION['filter_a'])) && ($_SESSION['filter_a'] == true)) { $and_filter = $and_filter." AND role_id NOT LIKE '3'"; $analytics_filter = $analytics_filter."a,"; }
if ((isset($_SESSION['filter_b'])) && ($_SESSION['filter_b'] == true)) { $and_filter = $and_filter." AND ad_biz_listing='biz'"; $analytics_filter = $analytics_filter."b,"; }

// Get the category title if needed
if ((!isset($category)) || (!isset($catslug))) {
	$q = "SELECT category, slug FROM categories WHERE id='$catID'";
	$r = mysqli_query($dbc, $q);
	if (mysqli_num_rows($r) != 1) { // Problem!
		echo $category;
		echo '<p class="error">This page has been accessed in error.</p>';
		include ('./includes/footer.html');
		exit();
	}
	// Fetch the category title
	$row = mysqli_fetch_array($r, MYSQLI_NUM);
	$category = "$row[0]";
	$catslug = "$row[1]";
}

// Get the time
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// Create the date search condition
$SQLdateCondition ="( pub_status='live' AND date_starts < '$timeNow' AND date_expires > '$timeNow' )";

// Set pagination variables:
$pageitems = 200;
$itemskip = $pageitems * ($paged - 1);

// Subcategory?
if (isset($_GET['s'])) {
			// Valid the Subcategory ID
			if (filter_var($_GET['s'], FILTER_VALIDATE_INT, array('min_range' => 1))) {
				$subcatID = preg_replace("/[^A-Za-z0-9]/","", $_GET['s']);
			} else {
				header("Location: index.php");
				exit(); // Quit the script
			}
		$sq = "SELECT subcat FROM sub_$catslug WHERE id='$subcatID'";
		$sr = mysqli_query($dbc, $sq);
		$srow = mysqli_fetch_array($sr, MYSQLI_NUM);
		$subcat = "$srow[0]";

	// Prepare a subcategory query
		$subcatYN = true;
		$cq = "SELECT id, date_expires, category_id, subcat_id, role_id, tag_ids, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, week_cat_count, epoch_wk_reset FROM ads WHERE category_id=$catID AND subcat_id=$subcatID AND $SQLdateCondition $and_filter ORDER BY week_cat_count, epoch_wk_reset, id DESC LIMIT $itemskip,$pageitems";
		// Global subcat ID for key_id
		$gcq = "SELECT id FROM global_subcat_ids WHERE cat_id=$catID AND subcat_id=$subcatID";
		$gcr = mysqli_query ($srv_dbc, $gcq);
		$gcrow = mysqli_fetch_array($gcr, MYSQLI_NUM);
		$key_id = $gcrow[0];
	} else {
		// Prepare a no-subcategory query
		$subcatYN = false;
		$cq = "SELECT id, date_expires, category_id, subcat_id, role_id, tag_ids, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, week_cat_count, epoch_wk_reset FROM ads WHERE category_id=$catID AND $SQLdateCondition $and_filter ORDER BY week_cat_count, epoch_wk_reset, id DESC LIMIT $itemskip,$pageitems";
		// Cetegory ID for key_id
		$key_id = $catID;
	}


// Get the ads associated with this category
$row = mysqli_query($dbc, $cq);

$q = "SELECT id FROM ads WHERE $SQLdateCondition $and_filter";
$r = mysqli_query($dbc, $q);
$totalrows = mysqli_num_rows($r);

$totalpages = floor($totalrows / $pageitems);
$remainder = $totalrows % $pageitems;
if ($remainder > 0) {
	$totalpages = $totalpages + 1;
}
if ($paged > $totalpages) {
	$totalpages = 1;
}
$nextpaged = $paged + 1;
$prevpaged = $paged - 1;

// Pagination row
if ($totalpages > 1) {
	echo "
	<div class=\"paginate_nav_container\">
		<div class=\"paginate_nav\">
			<table>
				<tr>
					<td>
						<a class=\"paginate";
						if ($paged == 1) {echo " disabled";}
						echo "\" title=\"Page 1\" href=\"index.php?p=1\">&laquo;</a>
					</td>
					<td>
						<a class=\"paginate\" title=\"Previous\" href=\"index.php?p=$prevpaged\">&lsaquo;&nbsp;</a>
					</td>
					<td>
						<a class=\"paginate current\" title=\"Next\" href=\"index.php?p=$paged\">Page $paged</a>
					</td>
					<td>
						<a class=\"paginate\" title=\"Next\" href=\"index.php?p=$nextpaged\">&nbsp;&rsaquo;</a>
					</td>
					 <td>
						 <a class=\"paginate";
						 if ($paged == $totalpages) {echo " disabled";}
	 					echo "\" title=\"Last Page\" href=\"index.php?p=$totalpages\">&raquo;</a>
					 </td>
		 		</tr>
			</table>
		</div>
	</div>";
}

// Ads in the category
if (mysqli_num_rows($row) > 0) {
	while ($ad_item = mysqli_fetch_array($row)) {
			// Assign variables
			$ad_id = $ad_item[0];
			$date_expires = $ad_item[1];
			$cat_id = $ad_item[2];
			$subcat_id = $ad_item[3];
			$role_id = $ad_item[4];
			$tag_ids = $ad_item[5];
			$ad_hdng = $ad_item[6];
			$ad_dscr = $ad_item[7];
			$ad_info = $ad_item[8];
			$ad_pyrt = $ad_item[9];
			$ad_cntc = $ad_item[10];
			$ad_bizn = $ad_item[11];
			$ad_biz_listing = $ad_item[12];
			$week_count = $ad_item[13];
			$epoch_wk_reset = $ad_item[14];

			// Kill expired ads
	    $ad_epoch_dead = strtotime($date_expires);
	  	if ($timeNowEpoch >= $ad_epoch_dead) {
	  		// Update the status
	  		$q = "UPDATE ads SET pub_status='expired' WHERE id='$ad_id'";
	  		$r = mysqli_query ($dbc, $q);
				//if (mysqli_affected_rows($dbc) != 1) {sql_error($q, 'dbc', "sqle_category");}
	  		continue;
	  	}

			// Reset old week counts
			if ($timeNowEpoch >= $epoch_wk_reset) {
				$week_count = 0;
				// Loop until it is in the future
				$resetEpoch = ($epoch_wk_reset + 604800);
				while ($timeNowEpoch >= $resetEpoch) {
					$resetEpoch = ($resetEpoch + 604800);
				}
				// Update the status
				$q = "UPDATE ads SET epoch_wk_reset='$resetEpoch', week_view_count='0', week_cat_count='0', week_tag_count='0', week_search_count='0' WHERE id='$ad_id'";
				$r = mysqli_query ($dbc, $q);
				//if (mysqli_affected_rows($dbc) != 1) {sql_error($q, 'dbc', "sqle_category");}
			}

			// Get the listad role
			$rlaq = "SELECT initial, role, slug, description FROM roles WHERE id='$role_id'";
			$rlar = mysqli_query ($dbc, $rlaq);
			$rlarow = mysqli_fetch_array($rlar, MYSQLI_NUM);
			$role_i = "$rlarow[0]";
      $role_Name = "$rlarow[1]";
      $role_slug = "$rlarow[2]";
			$role_description = "$rlarow[3]";
			$role_link = "<a class=\"role role_$role_slug\" title =\"$role_Name: $role_description\">$role_i</a>";

			// Get the listad serial number
			$laq = "SELECT serialno FROM listads WHERE ad_id='$ad_id'";
			$lar = mysqli_query ($srv_dbc, $laq);
			$larow = mysqli_fetch_array($lar, MYSQLI_NUM);
			$List_serialno = "$larow[0]";

			// Prep the link
			$List_ad_content_cntc_rd = "https://$adServeDomain/c/$List_serialno/ct.html";
			// Render the ad
			$renderedAd = "<b class=\"badad_ad\">$ad_hdng</b><br class=\"badad_ad\" /><span class=\"badad_ad descr\">$ad_dscr</span><br class=\"badad_ad\" /><em class=\"badad_ad\">$ad_info</em><br class=\"badad_ad\" /><span class=\"badad_ad price\">$ad_pyrt</span> <a class=\"badad_ad\" rel=\"nofollow\" href=\"$List_ad_content_cntc_rd\"><u class=\"badad_ad\">Contact</u></a> $role_link";
			// Business listing?
			if ($ad_biz_listing == 'biz') {
				$renderedAd = $renderedAd."<br class=\"badad_ad\" /><b class=\"badad_ad badad_ad_content_bizn\">|<i class=\"badad_ad badad_ad_content_bizn\"> $ad_bizn </i>|</b>";
			}

			// Get the category from its ID
			$cq = "SELECT category, slug FROM categories WHERE id='$cat_id'";
			$cr = mysqli_query ($dbc, $cq);
			$crow = mysqli_fetch_array($cr, MYSQLI_NUM);
			$catname = "$crow[0]";
			$catslug = "$crow[1]";

			// Get the subcategory from its ID
			$scq = "SELECT subcat, slug FROM sub_$catslug WHERE id='$subcat_id'";
			$scr = mysqli_query ($dbc, $scq);
			$scrow = mysqli_fetch_array($scr, MYSQLI_NUM);
			$subcatname = "$scrow[0]";
			$subcatslug = "$crow[1]";

			// tags
			$arrayTagIDs = explode(',', $tag_ids);
			$tag_row = '';
			foreach($arrayTagIDs as $tagID){
				// Get the tag from its ID
				$tq = "SELECT tag FROM tags WHERE id='$tagID'";
				$tr = mysqli_query ($dbc, $tq);
				$trow = mysqli_fetch_array($tr, MYSQLI_NUM);
				$tag = "$trow[0]";
				$tag_row .= '<b class="rendered_tag" ><a class="rendered_tag" title="See other items with the '.$tag.' tag" href="tag.php?t='.$tag.'">#'.$tag.'</a></b>, ';
			}

			// echo the ad content
			echo '<p style="text-align: center;">'."$renderedAd<br class=\"badad_ad\" />";

			// echo the tag
			echo $tag_row;
			// echo any subcategory
			if ($subcatYN == true) {
				echo '<br class=\"badad_ad\" /><a class="rendered_subcategory" title="See other items in '.$catname.': '.$subcatname.'" href="category.php?id='.$cat_id.'&s='.$subcat_id.'">'.$subcatname.'</a></p>';
			} else {
				$subcat = 'none';
			}
			// End the ad
			echo '</p><br />';

		// Increment the week count
    $newWeekCount = $week_count +1;
    // Update the new count
    $q = "UPDATE ads SET week_cat_count='$newWeekCount' WHERE id='$ad_id'";
    $r = mysqli_query ($dbc, $q);
    //if (mysqli_affected_rows($dbc) != 1) {sql_error($q, 'dbc', "sqle_category");}

		// Ad count
    $acq = "SELECT cat_count FROM listads WHERE ad_id='$ad_id'";
		$acr = mysqli_query ($srv_dbc, $acq);
		$acrow = mysqli_fetch_array($acr, MYSQLI_NUM);
		$adcount = "$acrow[0]";
    // Increment the ad count
    $newCount = $adcount +1;
    // Update the new count
    $q = "UPDATE listads SET cat_count='$newCount' WHERE ad_id='$ad_id'";
    $r = mysqli_query ($srv_dbc, $q);
		//if (mysqli_affected_rows($srv_dbc) != 1) {sql_error($q, 'srv_dbc', "sqle_category");}

		// Update the analytics
		$safeCatname = mysqli_real_escape_string($srv_dbc, $catname);
		$safeSubcat = mysqli_real_escape_string($srv_dbc, $subcat);
		$aq = "INSERT INTO seen_ad_analytics (ad_id, source, keytext, subkey, key_id, filter, time_date, time_epoch) VALUES ('$ad_id', 'cat', '$safeCatname', '$safeSubcat', $key_id, '$analytics_filter', '$timeNow', '$timeNowEpoch')";
		$ar = mysqli_query ($srv_dbc, $aq);
		//if (mysqli_affected_rows($srv_dbc) != 1) {sql_error($q, 'srv_dbc', "sqle_category");}

	}

	// Pagination row
	if ($totalpages > 1) {
		echo "
		<div class=\"paginate_nav_container\">
			<div class=\"paginate_nav\">
				<table>
					<tr>
						<td>
							<a class=\"paginate";
							if ($paged == 1) {echo " disabled";}
							echo "\" title=\"Page 1\" href=\"index.php?p=1\">&laquo;</a>
						</td>
						<td>
							<a class=\"paginate\" title=\"Previous\" href=\"index.php?p=$prevpaged\">&lsaquo;&nbsp;</a>
						</td>
						<td>
							<a class=\"paginate current\" title=\"Next\" href=\"index.php?p=$paged\">Page $paged</a>
						</td>
						<td>
							<a class=\"paginate\" title=\"Next\" href=\"index.php?p=$nextpaged\">&nbsp;&rsaquo;</a>
						</td>
						 <td>
							 <a class=\"paginate";
							 if ($paged == $totalpages) {echo " disabled";}
		 					echo "\" title=\"Last Page\" href=\"index.php?p=$totalpages\">&raquo;</a>
						 </td>
			 		</tr>
				</table>
			</div>
		</div>";
	}

} else { // Empty category
	if ((isset($_SESSION['filter_b'])) && ($_SESSION['filter_b'] == true)) {
		echo '<h4 class="ads">There are currently no BUSINESS ads in this category to list.</h4><h4 class="ads">You might <a href="new_ad.php">run an ad</a> for what you need&mdash;buying, selling, or both.</h4>';
	} else {
		echo '<h4 class="ads">There are currently no ads in this category to list.</h4><h4 class="ads">You might <a href="new_ad.php">run an ad</a> for what you need&mdash;buying, selling, or both.</h4>';
	}
}
