<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Users
if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

// Include the header file
$page_title = "WordPress Help Videos :: $siteTitle";
include ('./includes/header.html');

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
echo "<h3>WordPress Help Videos</h3>";

echo '<p><a href="help.php">&larr; back to Help</a></p>';

echo '<p><b><a href="help_dev_videos.php">Dev Help Videos</a></b></p>';

echo '<hr /> <br />';

echo '
<p>First, you must have an account. <b><a target="_blank" href="https://www.bitchute.com/video/ROyed2cjJCg7/">Signup and get free ad credits</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/ROyed2cjJCg7/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/gW3C4CtlzrWw/">WordPress: Install plugin, add Dev keys & Connect your Partner Account</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/gW3C4CtlzrWw/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/BkIMAjWX4jii/">WordPress: Shortcodes</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/BkIMAjWX4jii/"></iframe>
</p>

<p><b><a target="_blank" href="https://www.bitchute.com/video/mZSpkFWnCbxo/">WordPress: Connect (short, if you already have Dev Keys)</a></b><br />
<iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/mZSpkFWnCbxo/"></iframe>
</p>
';


// Include the HTML footer
include ('./includes/footer.html');
?>
