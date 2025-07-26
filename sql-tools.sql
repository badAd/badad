ALTER TABLE `badadlistdb`.`devkeys`
ADD `use_custom_callback` BOOLEAN DEFAULT 0;


  $r = mysqli_query ($dbc, $q);
  if ($r === false) {} // Simple check for failure without requiring "affected rows"
  if (!$r) // Another simple check for failure
