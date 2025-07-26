<?php
echo "<h1>404: $_SERVER['REQUEST_URI'] does not exist.</h1>";
if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
$refuri = parse_url($_SERVER['HTTP_REFERER']); // use the parse_url() function to create an array containing information about the domain
	if($refuri['host'] == "pacificdailyads.com"){
	//the link was on your site
	echo '<p>Our mistake, sorry. If you\'re a registered user, please <a href="https://pacificdailyads.com/feedback.php">let us know</a> what you clicked to get here.</p>';
	} else {
	//the link was on another site. $refuri['host'] will return what that site is
	echo "Tell someone over at $refuri['host'] that they have a dead link to this site.";
	}
} else {
	//the visitor typed gibberish into the address bar
	echo "Stop typing jibberish.";
}
?>
