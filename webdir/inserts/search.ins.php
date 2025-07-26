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

 // Set the session for a click
 $_SESSION['searchQuery'] = $searchQuery;

if (isset($_SESSION['subcat'])) {
  echo '<h4 class="ads">Results in: '.$_SESSION['subcat'].'</h4>';
} elseif (isset($_SESSION['category'])) {
  echo '<h4 class="ads">Results in: '.$_SESSION['category'].'</h4>';
} elseif (isset($_SESSION['tag'])) {
  echo '<h4 class="ads">Results in: #'.$_SESSION['tag'].'</h4>';
}

// Get the time
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// Set pagination variables:
$pageitems = 200;
$itemskip = $pageitems * ($paged - 1);

// Create the cross-column search query
$SQLcolumnSearch ="( pub_status='live' AND date_starts < '$timeNow' AND date_expires > '$timeNow' )";

// Category or Tags?
if (isset($_SESSION['tagID'])) {
  $tagID = $_SESSION['tagID'];
  $SQLcolumnSearch = $SQLcolumnSearch." AND ( tag_ids LIKE '{$tagID}' OR tag_ids LIKE '{$tagID}, %' OR tag_ids LIKE '%, {$tagID}' OR tag_ids LIKE '%, {$tagID}, %' )";
} elseif ((isset($_SESSION['subcatID'])) && (isset($_SESSION['catID']))) {
  $subcatID = $_SESSION['subcatID'];
  $catID = $_SESSION['catID'];
  $SQLcolumnSearch = $SQLcolumnSearch." AND ( category_id=$catID AND subcat_id=$subcatID )";
} elseif (isset($_SESSION['catID'])) {
  $catID = $_SESSION['catID'];
  $SQLcolumnSearch = $SQLcolumnSearch." AND ( category_id=$catID )";
}

// " AND id LIKE '0'" is a dummy test to allow the long list of "OR" tests, and open the order of operations
$SQLcolumnSearch =$SQLcolumnSearch." AND ( id LIKE '0'";

// Add each search word
if(strpos($searchQuery, " ") !== false) {

    $searchwordS = array();
    $searchwordS = explode(" ", $searchQuery);

    foreach($searchwordS as $searchword){
        $searchword = mysqli_real_escape_string($dbc, $searchword);
        $SQLcolumnSearch = $SQLcolumnSearch." OR LOWER(ad_content_hdng) LIKE LOWER('%$searchword%') OR LOWER(ad_content_dscr) LIKE LOWER('%$searchword%') OR LOWER(ad_content_info) LIKE LOWER('%$searchword%') OR LOWER(ad_content_bizn) LIKE LOWER('%$searchword%')";
    }

} else {

  $searchword = $searchQuery;
  $searchword = mysqli_real_escape_string($dbc, $searchword);
  $SQLcolumnSearch = $SQLcolumnSearch." OR LOWER(ad_content_hdng) LIKE LOWER('%$searchword%') OR LOWER(ad_content_dscr) LIKE LOWER('%$searchword%') OR LOWER(ad_content_info) LIKE LOWER('%$searchword%') OR LOWER(ad_content_bizn) LIKE LOWER('%$searchword%')";

}

// Finish the SQL serch query with order or operations
$SQLcolumnSearch = $SQLcolumnSearch." )";

	$q = "SELECT id FROM ads WHERE $SQLcolumnSearch $and_filter";
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
						echo "\" title=\"Page 1\" href=\"search.php?p=1&s=$searchQuery\">&laquo;</a>
					</td>
					<td>
						<a class=\"paginate";
            if ($paged == 1) {echo " disabled";}
           echo "\" title=\"Previous\" href=\"search.php?p=$prevpaged&s=$searchQuery\">&lsaquo;&nbsp;</a>
					</td>
					<td>
						<a class=\"paginate current\" title=\"Next\" href=\"search.php?p=$paged&s=$searchQuery\">Page $paged ($totalpages)</a>
					</td>
					<td>
						<a class=\"paginate";
            if ($paged == $totalpages) {echo " disabled";}
           echo "\" title=\"Next\" href=\"search.php?p=$nextpaged&s=$searchQuery\">&nbsp;&rsaquo;</a>
					</td>
					 <td>
						 <a class=\"paginate";
						 if ($paged == $totalpages) {echo " disabled";}
	 					echo "\" title=\"Last Page\" href=\"search.php?p=$totalpages&s=$searchQuery\">&raquo;</a>
					 </td>
		 		</tr>
			</table>
		</div>
	</div>";
}

// Display result query
$sq = "SELECT id, date_expires, category_id, subcat_id, role_id, tag_ids, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, week_search_count, epoch_wk_reset FROM ads WHERE $SQLcolumnSearch $and_filter ORDER BY week_search_count, epoch_wk_reset, id DESC LIMIT $itemskip,$pageitems";
$sr = mysqli_query($dbc, $sq);
if (mysqli_num_rows($sr) > 0) {
  while ($ad_item = mysqli_fetch_array($sr)) {
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
        //if (mysqli_affected_rows($dbc) != 1) {sql_error($q, 'dbc', "sqle_search");}
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
        //if (mysqli_affected_rows($dbc) != 1) {sql_error($q, 'dbc', "sqle_search");}
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
  		$List_ad_content_cntc_rd = "https://$adServeDomain/s/$List_serialno/ct.html";
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
  		// echo the category
  		echo '<br class=\"badad_ad\" /><a class="rendered_category" title="See other items in '.$catname.'" href="category.php?id='.$cat_id.'">'.$catname.'</a>: <a class="rendered_subcategory" title="See other items in '.$catname.': '.$subcatname.'" href="category.php?id='.$cat_id.'&s='.$subcat_id.'">'.$subcatname.'</a>';
  		// End the ad
  		echo '</p><br />';

      // Increment the week count
      $newWeekCount = $week_count +1;
      // Update the new count
      $q = "UPDATE ads SET week_search_count='$newWeekCount' WHERE id='$ad_id'";
      $r = mysqli_query ($dbc, $q);
      //if (mysqli_affected_rows($dbc) != 1) {sql_error($q, 'dbc', "sqle_search");}

      // Ad count
      $acq = "SELECT search_count FROM listads WHERE ad_id='$ad_id'";
  		$acr = mysqli_query ($srv_dbc, $acq);
  		$acrow = mysqli_fetch_array($acr, MYSQLI_NUM);
      $adcount = "$acrow[0]";
      // Increment the ad count

      $newCount = $adcount +1;
      // Update the new count
      $q = "UPDATE listads SET search_count='$newCount' WHERE ad_id='$ad_id'";
      $r = mysqli_query ($srv_dbc, $q);
      //if (mysqli_affected_rows($srv_dbc) != 1) {sql_error($q, 'srv_dbc', "sqle_search");}

      // Update the analytics
        // Tag, Category, or All
        if ((isset($_SESSION['tagID'])) && (isset($_SESSION['tag']))) {
          $subkey = "tag_ ".$_SESSION['tag'];
          $key_id = $_SESSION['tagID'];
        } elseif ((isset($_SESSION['catID'])) && (isset($_SESSION['subcatID'])) && (isset($_SESSION['category'])) && (isset($_SESSION['subcat']))) {
          $subkey = "cat_ ".$_SESSION['category'].": ".$_SESSION['subcat'];
          $subkey = mysqli_real_escape_string($srv_dbc, $subkey);
            // Global subcat ID for key_id
            $catID = $_SESSION['catID'];
            $subcatID = $_SESSION['subcatID'];
            $gcq = "SELECT id FROM global_subcat_ids WHERE cat_id=$catID AND subcat_id=$subcatID";
            $gcr = mysqli_query ($srv_dbc, $gcq);
            $gcrow = mysqli_fetch_array($gcr, MYSQLI_NUM);
          $key_id = $gcrow[0];
        } elseif ((isset($_SESSION['catID'])) && (isset($_SESSION['category']))) {
          $subkey = "cat_ ".$_SESSION['category'];
          $subkey = mysqli_real_escape_string($srv_dbc, $subkey);
          $key_id = $_SESSION['catID'];
        } else {
          $subkey = "plain_search";
          $key_id = "NULL";
        }

        $safeSearchQuery = mysqli_real_escape_string($srv_dbc, $searchQuery);

      if (($key_id == "NULL") && ($analytics_filter == "")) {
        $aq = "INSERT INTO seen_ad_analytics (ad_id, source, keytext, subkey, key_id, filter, time_date, time_epoch) VALUES ('$ad_id', 'search', '$safeSearchQuery', '$subkey', NULL, NULL, '$timeNow', '$timeNowEpoch')";
      } elseif (($key_id != "NULL") && ($analytics_filter == "")) {
        $aq = "INSERT INTO seen_ad_analytics (ad_id, source, keytext, subkey, key_id, filter, time_date, time_epoch) VALUES ('$ad_id', 'search', '$safeSearchQuery', '$subkey', '$key_id', NULL, '$timeNow', '$timeNowEpoch')";
      } elseif (($key_id == "NULL") && ($analytics_filter != "")) {
        $aq = "INSERT INTO seen_ad_analytics (ad_id, source, keytext, subkey, key_id, filter, time_date, time_epoch) VALUES ('$ad_id', 'search', '$safeSearchQuery', '$subkey', NULL, '$analytics_filter', '$timeNow', '$timeNowEpoch')";
      } elseif (($key_id != "NULL") && ($analytics_filter != "")) {
        $aq = "INSERT INTO seen_ad_analytics (ad_id, source, keytext, subkey, key_id, filter, time_date, time_epoch) VALUES ('$ad_id', 'search', '$safeSearchQuery', '$subkey', '$key_id', '$analytics_filter', '$timeNow', '$timeNowEpoch')";
      } else {
        $somethingiswrong = true;
      }
      if (!isset($somethingiswrong)) {
        $ar = mysqli_query ($srv_dbc, $aq);
        //if (mysqli_affected_rows($srv_dbc) != 1) {sql_error($q, 'srv_dbc', "sqle_search");}
      }
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
  						echo "\" title=\"Page 1\" href=\"search.php?p=1&s=$searchQuery\">&laquo;</a>
  					</td>
  					<td>
  						<a class=\"paginate";
              if ($paged == 1) {echo " disabled";}
             echo "\" title=\"Previous\" href=\"search.php?p=$prevpaged&s=$searchQuery\">&lsaquo;&nbsp;</a>
  					</td>
  					<td>
  						<a class=\"paginate current\" title=\"Next\" href=\"search.php?p=$paged&s=$searchQuery\">Page $paged ($totalpages)</a>
  					</td>
  					<td>
  						<a class=\"paginate";
              if ($paged == $totalpages) {echo " disabled";}
             echo "\" title=\"Next\" href=\"search.php?p=$nextpaged&s=$searchQuery\">&nbsp;&rsaquo;</a>
  					</td>
  					 <td>
  						 <a class=\"paginate";
  						 if ($paged == $totalpages) {echo " disabled";}
  	 					echo "\" title=\"Last Page\" href=\"search.php?p=$totalpages&s=$searchQuery\">&raquo;</a>
  					 </td>
  		 		</tr>
  			</table>
  		</div>
  	</div>";
  }

} else { // No pages available
  if ((isset($_SESSION['filter_b'])) && ($_SESSION['filter_b'] == true)) {
    echo '<h4 class="ads">There are currently no BUSINESS ads for this search query to list.</h4><h4 class="ads">You might <a href="new_ad.php">run an ad</a> for what you need&mdash;buying, selling, or both.</h4>';
  } else {
    echo '<h4 class="ads">There are currently no ads for this search query to list.</h4><h4 class="ads">You might <a href="new_ad.php">run an ad</a> for what you need&mdash;buying, selling, or both.</h4>';
  }
} // End empty check
