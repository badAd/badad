<?php
//In case you want to show errors
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Set role & business filters

// Valid the Pagination
if ((isset($_GET['p'])) && (filter_var($_GET['p'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
	$paged = preg_replace("/[^A-Za-z0-9]/","", $_GET['p']);
} else {
	$paged = 1;
}

// Set pagination variables:
$pageitems = 200;
$itemskip = $pageitems * ($paged - 1);

	$q = "SELECT id FROM partnersites WHERE directory_listed='listed' AND type='podcast' AND domain='podcast'";
	$r = mysqli_query($srv_dbc, $q);
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
						echo "\" title=\"Page 1\" href=\"blogs.php?p=1&s=$searchQuery\">&laquo;</a>
					</td>
					<td>
						<a class=\"paginate\" title=\"Previous\" href=\"blogs.php?p=$prevpaged&s=$searchQuery\">&lsaquo;&nbsp;</a>
					</td>
					<td>
						<a class=\"paginate current\" title=\"Next\" href=\"blogs.php?p=$paged&s=$searchQuery\">Page $paged</a>
					</td>
					<td>
						<a class=\"paginate\" title=\"Next\" href=\"blogs.php?p=$nextpaged&s=$searchQuery\">&nbsp;&rsaquo;</a>
					</td>
					 <td>
						 <a class=\"paginate";
						 if ($paged == $totalpages) {echo " disabled";}
	 					echo "\" title=\"Last Page\" href=\"blogs.php?p=$totalpages&s=$searchQuery\">&raquo;</a>
					 </td>
		 		</tr>
			</table>
		</div>
	</div>";
}

// Message
echo '<p style="text-align: center;">These are podcasts proudly sponsored by badAd and its advertisers.</p>';

// Search box
echo '<div id="searchListedWrap"><form action="podcasts.php" method="get">
<input type="text" name="s" maxlength="255" size="42" value="'.$searchQuery.'">&nbsp;
<input type="submit" class="formbutton" value="Search">
</form></div><br /><br />';

// Table of listed items
echo '<table class="sitestable"><tbody>';
//echo '<tr><th align="right">Blog Name</th><th align="left">Domain</th></tr>'; // align="right" not working, maybe the header isn't needed anyway

// Add each search word
if(strpos($searchQuery, " ") !== false) {

    $searchwordS = array();
    $searchwordS = explode(" ", $searchQuery);
		$searchwordTotal = count($searchwordS);
		$searchwordCount = 1;
		$searchQuerySQL = mysqli_real_escape_string($srv_dbc, $searchQuery);
		$SQLcolumnSearch = "AND (LOWER(directory_name) LIKE LOWER('%$searchQuerySQL%') OR ";

    foreach($searchwordS as $searchword){
        $searchword = mysqli_real_escape_string($srv_dbc, $searchword);
        $SQLcolumnSearch .= "LOWER(directory_name) LIKE LOWER('%$searchword%')";
				$SQLcolumnSearch .= ($searchwordTotal > $searchwordCount) ? " OR " : "";
				$searchwordCount ++;
    }
		$SQLcolumnSearch .= ")";

} elseif ($searchQuery != "") {

  $searchword = $searchQuery;
  $searchword = mysqli_real_escape_string($srv_dbc, $searchword);
  $SQLcolumnSearch = "AND LOWER(directory_name) LIKE LOWER('%$searchword%')";

} else {
	$SQLcolumnSearch = "";
}

// Dynamically generate the ads
$q = "SELECT id, serial_no, directory_name FROM partnersites WHERE directory_listed='listed' AND type='podcast' AND domain='podcast' $SQLcolumnSearch ORDER BY directory_name DESC LIMIT $itemskip,$pageitems";
$row = mysqli_query($srv_dbc, $q);
	if (mysqli_num_rows($row) > 0) {
	while ($podcast_item = mysqli_fetch_array($row)) {
			// Assign variables
			$blog_id = $podcast_item[0];
			$podcast_slug = $podcast_item[1];
			$directory_name = $podcast_item[2];

			echo '<tr><td align="right"><big><a target = "_blank" title="'.$directory_url.'" href="https://'.$podcastServeDomain.'/'.$podcast_slug.'"><i>'.$directory_name.'</i></a></big></td><td align="left"><big><a target = "_blank" title="'.$directory_url.'" href="https://'.$podcastServeDomain.'/'.$podcast_slug.'">'.$podcastServeDomain.'/'.$podcast_slug.'</a></big></td></tr>';

	} // while each partner listing

	// Finish the table
	echo "</tbody>\n</table>";

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
							echo "\" title=\"Page 1\" href=\"blogs.php?p=1&s=$searchQuery\">&laquo;</a>
						</td>
						<td>
							<a class=\"paginate\" title=\"Previous\" href=\"blogs.php?p=$prevpaged&s=$searchQuery\">&lsaquo;&nbsp;</a>
						</td>
						<td>
							<a class=\"paginate current\" title=\"Next\" href=\"blogs.php?p=$paged&s=$searchQuery\">Page $paged</a>
						</td>
						<td>
							<a class=\"paginate\" title=\"Next\" href=\"blogs.php?p=$nextpaged&s=$searchQuery\">&nbsp;&rsaquo;</a>
						</td>
						<td>
							<a class=\"paginate";
							if ($paged == $totalpages) {echo " disabled";}
		 					echo "\" title=\"Last Page\" href=\"blogs.php?p=$totalpages&s=$searchQuery\">&raquo;</a>
						 </td>
			 		</tr>
				</table>
			</div>
		</div>";
	}

} elseif ($searchQuery != "") { // Empty
	echo '<h4 class="ads">There are currently no podcasts for that search.</h4>';
} else {
	echo '<h4 class="ads">There are currently no podcasts to list.</h4>';
}
