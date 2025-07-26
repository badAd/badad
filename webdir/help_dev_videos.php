<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Users
if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

// Include the header file
$page_title = "Dev Help Videos :: $siteTitle";
include ('./includes/header.html');

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
echo "<h3>Dev Help Videos</h3>";

echo '<p><a href="help_dev.php">&larr; back to Dev Help</a></p>';

echo '<p><b><a href="help_videos.php">Help Videos</a></b></p>';

echo '<hr /> <br />';

echo '
<p><b><a target="_blank" href="https://www.bitchute.com/video/NCfOBbCNxbLq/">Dev Help overview (1/7)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/NCfOBbCNxbLq/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/eFj8HywbJ6j3/">Codes and CSS (2/7)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/eFj8HywbJ6j3/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/bwafkTVdU3zl/">Connect Handshake (3/7)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/bwafkTVdU3zl/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/ca4v2CjzAys9/">Fetch meta and embed (4/7)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/ca4v2CjzAys9/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/o2KCXBpYQYyX/">Example Implementation (5/7)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/o2KCXBpYQYyX/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/xKYLtCOb28e9/">Partner connect demo (6/7)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/xKYLtCOb28e9/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/1hPQA11sJO4H/">Example live demo (7/7)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/1hPQA11sJO4H/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/wHKLAYdIFTws/">WordPress Dev Plugin Tour</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/wHKLAYdIFTws/"></iframe>
</p>

';


// Include the HTML footer
include ('./includes/footer.html');
?>
