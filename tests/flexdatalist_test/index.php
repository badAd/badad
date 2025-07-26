<?php

echo '<!DOCTYPE html>
<html>
<head>
<title>Flexdatalist Test</title>
<link href="jquery.flexdatalist.css" rel="stylesheet" type="text/css" />
</head>
<body>';

if (isset($_POST['fdl'])) {
	$fdl = $_POST['fdl'];
} else {
	$fdl = "";
}

echo "Enter any of these: one, two, three, four, five<br />Your list: $fdl";

echo '<form action="index.php" method="post" accept-charset="utf-8">
<datalist id="fdl">
	<option value="one">one</option>
	<option value="two">two</option>
	<option value="three">three</option>
	<option value="four">four</option>
	<option value="five">five</option>
</datalist>
<input class="flexdatalist"
  list="fdl"
	data-value-property="value"
  data-searchContain="true"
	data-selection-required="true"
  data-min-length="1"
  data-toggle-selected="true"
  multiple="multiple"
	type="text"
	name="fdl"
	value="'.$fdl.'" />
<br/ ><br />
<input type="submit" value="Submit" />
</form>';


// Flexdatalist
// Thanks https://github.com/sergiodlopes/jquery-flexdatalist
echo "
<script src=\"jquery-1.8.3.min.js\"></script>
<script src=\"jquery.flexdatalist.min.js\"></script>
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

echo '</body>
</html>';
?>
