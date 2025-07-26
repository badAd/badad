<?php

echo '<!DOCTYPE html>
<html>
<head>
<title>Taglist Test</title>
<link href="taglist.css" rel="stylesheet" type="text/css" />
</head>
<body>';

if (isset($_POST['tagList'])) {
	$tagList = $_POST['tagList'];
} else {
	$tagList = "";
}


echo '
<form action="index.php" method="post" accept-charset="utf-8">
<label for="authors">Type authors from favorite to least favorite</label>
<input type="text" list="names-list" id="authors" value="" size="50" name="authors" placeholder="Type author names">
<small>You can type how many you want.</small>
<datalist id="names-list">
  <option value="Albert Camus">
  <option value="Alexandre Dumas">
  <option value="C. S. Lewis">
  <option value="Charles Dickens">
  <option value="Dante Alighieri">
</datalist>
<br/ ><br />
<input type="submit" value="Submit" />
</form>
';


// Taglist
//<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js\"></script>
echo "
<script src=\"jquery-2.1.3.min.js\"></script>
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js\"></script>
<script src=\"taglist.js\"></script>
";

echo '</body>
</html>';
?>
