<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Users
if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

// Include the header file
$page_title = "Help Videos :: $siteTitle";
include ('./includes/header.html');

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
echo "<h3>Help Videos</h3>";

echo '<p><a href="help.php">&larr; back to Help</a></p>';

echo '<p><b><a href="help_dev_videos.php">Dev Help Videos</a></b></p>';

echo '<p><b><a href="help_wordpress_videos.php">WordPress Help Videos</a></b></p>';

echo '<hr /> <br />';

echo '
<p><b><a target="_blank" href="https://www.bitchute.com/video/ROyed2cjJCg7/">Signup and get free ad credits</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/ROyed2cjJCg7/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/hNne1sXAIBWf/">Monetize your blog</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/hNne1sXAIBWf/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/mNdP0GnEvAQ6/">Account security</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/mNdP0GnEvAQ6/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/2FItGjeG4U4W/">Analytics & Statistics</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/2FItGjeG4U4W/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/hR4QJCthzTYE/">Help section</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/hR4QJCthzTYE/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/xKYLtCOb28e9/">Dev Partner connect demo</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/xKYLtCOb28e9/"></iframe>
</p>
';


// Include the HTML footer
include ('./includes/footer.html');
?>
