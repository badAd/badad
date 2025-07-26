<?php

// This updates the subcate_ids table with current IDs for all categories and their subcategories

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

echo "Updating global category IDs...<br />";

// Loop through each category
$qc = 'SELECT id, slug FROM categories';
$crow = mysqli_query($dbc, $qc);
while ($catRow = mysqli_fetch_array($crow)) {
  $catID = $catRow[0];
  $catSlug = $catRow[1];

  $qs = "SELECT id FROM sub_$catSlug";
  $srow = mysqli_query($dbc, $qs);
  while ($subRow = mysqli_fetch_array($srow)) {
    $subcatID = $subRow[0];

    // See if entry exists in global_subcat_ids
    $q = "SELECT id FROM global_subcat_ids WHERE cat_id='$catID' AND subcat_id='$subcatID'";
    $r = mysqli_query ($srv_dbc, $q);
    $rows = mysqli_num_rows($r);
    if ($rows == 0) { // If it doesn't exist

      $q = "INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('$catID', '$subcatID')";
      echo $q.";<br />";
    //  $r = mysqli_query ($srv_dbc, $q);
    //  if (mysqli_affected_rows($srv_dbc) == 1) { // If it ran well
    //    echo "Added: cat_$catID subcat_$subcatID<br />";
    //  }
    } // If it doesn't exist
  } // Subcategory loop
} // Category loop

?>
