<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Users
if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

// Include the header file
$page_title = "Dev Help :: $siteTitle";
include ('./includes/header.html');

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
echo "<h3>Dev Help</h3><br />";

echo '<p><b><a href="help_dev_videos.php">Dev Videos</a></b></p>';

echo '<p>Curious? Explore or fork the official badAd WordPress plugin repo on <a target="_blank" href="https://github.com/badad/wordpress">github.com/badad/wordpress</a>.</p>';

// Basics
echo "<h4>Basics</h4>";
echo "<p>Our Dev API includes:
  <ul class=\"s\">
    <li class=\"s\">Test and live modes with corresponding keys (for you the developer)</li>
    <li class=\"s\">Secret and public keys (for you the developer)</li>
    <li class=\"s\">Two keys from a user (from our monetizing Partner who is also the user of your app)</li>
    <ul>
      <li>- The first \"Partner App Key\", used once then deleted, for only the initial handshake</li>
      <li>- The \"Call Key\", which our API will _POST to your callback URL, which the user never sees, which you then use to embed all of our ads in your app</li>
    </ul>
  </ul></p>";

// Advantage
echo "<h4>The advantage of using our Dev API</h4>";
echo "<p>When using our simple Embed Code, our monetizing Partner can control settings for embedded ads from the our user dashboard Partner Center. This may include:
  <ul class=\"s\">
    <li class=\"s\">Number of ads</li>
    <li class=\"s\">Horizantal or vertial alignment</li>
    <li class=\"s\">Where to place the ad (via where the Partner places our Embed Code)</li>
    <li class=\"s\">With NO option to hide our \"<b><u>badAd.one</u></b>\" credit above the embedded ads</li>
  </ul>
By using our Dev API, you can control all of these from within your own app.</p>";

// Function
echo "<h4>How it functions</h4>";
echo "<p>Our Dev API:
  <ul class=\"s\">
    <li class=\"s\">Uses simple _POST calls between your website and our API domain: <b>api.badad.one</b></li>
    <li class=\"s\">Requires your Public Key (test or live) <i>in a &lt;meta&gt; tag in the &lt;head&gt; of your callback URL page</i></li>
    <li class=\"s\">Requires your Secret Key (test or live) <i>anytime you make a call</i>, both for the initial handshake or embeds</li>
    <li class=\"s\">Requires</li>
    <ul>
      <li>- A callback URL, for only the initial handshake (which you can change)</li>
      <li>- A domain that must be part of your callback URL (which you CANNOT change)</li>
    </ul>
  </ul>
</p>";

// Call types
echo "<h4>Two types of calls</h4>";
echo "<p>Once connected, you can make two kinds of calls from our Dev API:
  <ul class=\"s\">
    <li class=\"s\">Embed ads in your app, which you make the settings for in each call</li>
    <li class=\"s\">Retrieve basic status and name information about our Partner's Project (such as to allow your user to see which projects are connected to your app from your user dashboard)</li>
  </ul></p>";

// Code and examples
echo "<h4>Code examples</h4>";
echo "<p><b><a href=\"badad_devapi_example.txz\" download=\"badad_devapi_example.txz\">Example code tarball</a></b></p>";
echo "<p>The code below is based on an example of the Dev API minimal PHP pages in <a href=\"badad_devapi_example.txz\" download=\"badad_devapi_example.txz\">this tarball</a>. PHP is not required for the Dev API. The example uses some HTML elements with CSS to make it less unbearable for normal users, such as making a demo for your oversight or PM, but this styling is not required. <i>Where any key string exists, whether our internal keys or your dev-related keys, we use a simple [A-Za-z0-9_] or [A-Za-z0-9] string in case you want to test RegEx checks, but these strings are not tailored any to RegEx length requirements, which may vary.</i></p>";

// Index of codes
echo '<h5 id="code_index">Index of code examples</h5>
<p>
<a href="#styling_embedded_ads"><b>Styling embedded ads</b></a><br />
<a href="#embed_ad_html">Embedded ad HTML (via Dev API or normal Embed Code)</a><br />
<a href="#embed_ad_css">Embedded ad CSS classes (via Dev API or normal Embed Code)</a><br />
<a href="#styling_partner_project_meta"><b>Styling Partner Project meta fetched through the Dev API</b></a><br />
<a href="#user_meta_html">User meta HTML (via Dev API)</a><br />
<a href="#user_meta_css">User meta CSS classes (via Dev API)</a><br />
<a href="#connecting_to_your_app"><b>Connecting our Partners to your app and embedding ads via our Dev API</b></a><br />
<a href="#handshake_connect">Handshake (Connect/Callback URL page)</a><br />
<a href="#fetch_meta">Fetch our Partner user meta to embed in your app</a><br />
<a href="#fetch_ads_embed">Fetch ads to embed in your app</a>
</p>';

// Styling ads

echo '<h5 id="styling_embedded_ads">Styling embedded ads</h5>';
echo "<p>All our ad HTML elements include simple and consistent CSS classes. Below is an empty stylesheet and an example of HTML from an ad embed with a two ads, one with a business name, one without. There can be up to ten ads included per embed. An embed may include only the badAd.one link without ads; the Dev API may include only ads without thebadAd.one link.</p>";
echo "<p>Whether you are a registered user at badAd.one or merely modifying your own website where your users may embed badAd.one ads, modifying these CSS classes implies and requires that you agree to our <a title=\"Terms & Conditions\" href=\"https://badad.one/Terms.htm\">Terms & Conditions</a>. This includes that, while you may hide the hr tag to hide the horizantal lines only, you will hide no other content of an ad embed and you must maintain the same font size for the entire ad embed, which must be easily readible size to the normal visitors of the website you are styling, based on a comparison to the font size and style of the website's main content.</p>";
echo '<p><a href="#code_index">&larr; back to index</a></p>';

// Embedded ad html
echo '<h6 id="embed_ad_html">Embedded ad HTML (<i>$response</i> via Dev API or normal Embed Code):</h6>
<pre><code class="devblock">
&lt;div class="badad_ad badad_container"&gt;&lt;hr class="badad_ad badad_ads_top"&gt;&lt;p class="badad_ad badad_link" style="text-align:center;"&gt;&lt;a class="badad_ad badad_link" rel="nofollow noreferrer noopener" href="https://badad.one/0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0/site.html"&gt;&lt;b&gt;badAd.one&lt;/b&gt;&lt;/a&gt;&lt;/p&gt;
  &lt;hr class="badad_ad badad_link_bottom"&gt;
  <span class="orange">
  &lt;!-- Vertical alignment div (choose one of these two divs) --&gt;
  &lt;div class="badad_ad badad_ads"&gt;
  </span>
  <span class="blue">
  &lt;!-- Horizantal alignment div (choose one of these two divs) --&gt;
  &lt;div class="badad_ad badad_ads" style="display:inline-block; text-align:center; display:flex; justify-content:center; align-items:center; width:100%;"&gt;
  </span>
    &lt;div class="badad_ad badad_ad_item"&gt;
      &lt;p class="badad_ad badad_ad_item" style="text-align:center;"&gt;
        &lt;span class="badad_ad badad_heading"&gt;&lt;strong class="badad_ad badad_heading"&gt;Learn Linux, Learn Computers&lt;/strong&gt;&lt;/span&gt;
        &lt;br class="badad_ad badad_heading"&gt;
        &lt;span class="badad_ad badad_description"&gt;Most-basic, must-have for everyone: MBAs to programming students&lt;/span&gt;
        &lt;br class="badad_ad badad_description"&gt;
        &lt;span class="badad_ad badad_info"&gt;&lt;em class="badad_ad badad_info"&gt;heavy on demonstration, fast explanation, learn by doing, easy lesson summaries&lt;/em&gt;&lt;/span&gt;
        &lt;br class="badad_ad badad_info"&gt;
        &lt;span class="badad_ad badad_payrate"&gt;Free for self-study, see site for online course&lt;/span&gt;
        &nbsp;
        &lt;span class="badad_ad badad_contact"&gt;&lt;a class="badad_ad badad_contact" rel="nofollow" href="https://ads.badad.one/0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0/0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0/ct.html"&gt;&lt;u class="badad_ad badad_contact"&gt;Contact&lt;/u&gt;&lt;/a&gt;&lt;/span&gt;
        &lt;br class="badad_ad badad_biz"&gt;
        &lt;strong class="badad_ad badad_biz"&gt;&lt;i class="badad_ad badad_biz"&gt;Ink Is A Verb&lt;/i&gt;&lt;/strong&gt;&lt;/p&gt;
        &lt;hr class="badad_ad badad_close_vert_ad"&gt;
      &lt;/div&gt;
      &lt;div class="badad_ad badad_ad_item"&gt;
        &lt;p class="badad_ad badad_ad_item" style="text-align:center;"&gt;
          &lt;span class="badad_ad badad_heading"&gt;&lt;strong class="badad_ad badad_heading"&gt;Learn Linux, Learn Computers&lt;/strong&gt;&lt;/span&gt;
          &lt;br class="badad_ad badad_heading"&gt;
          &lt;span class="badad_ad badad_description"&gt;Most-basic, must-have for everyone: MBAs to programming students&lt;/span&gt;
          &lt;br class="badad_ad badad_description"&gt;
          &lt;span class="badad_ad badad_info"&gt;&lt;em class="badad_ad badad_info"&gt;heavy on demonstration, fast explanation, learn by doing, easy lesson summaries&lt;/em&gt;&lt;/span&gt;
          &lt;br class="badad_ad badad_info"&gt;
          &lt;span class="badad_ad badad_payrate"&gt;Free for self-study, see site for online course&lt;/span&gt;
          &nbsp;
          &lt;span class="badad_ad badad_contact"&gt;&lt;a class="badad_ad badad_contact" rel="nofollow" href="https://ads.badad.one/0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0/0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0/ct.html"&gt;&lt;u class="badad_ad badad_contact"&gt;Contact&lt;/u&gt;&lt;/a&gt;&lt;/span&gt;
          &lt;hr class="badad_ad badad_close_vert_ad"&gt;
        &lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;
</code></pre>
<p><a href="#code_index">&larr; back to index</a></p>
';

// Empty CSS sheet for embedded ads
echo '<h6 id="embed_ad_css">Embedded ad CSS classes (via Dev API or normal Embed Code):</h6>
<pre><code class="devblock">
/* Container for everything */
div.badad_ad div.badad_container {

}

hr.badad_ad hr.badad_ads_top {

}

/* badAd.one link - optional via API */
p.badad_ad p.badad_link {

}

a.badad_ad a.badad_link {

}

hr.badad_ad hr.badad_link_bottom {

}

/* All ads */
div.badad_ad div.badad_ads {

}

/* Each ad */
div.badad_ad div.badad_ad_item {

}

p.badad_ad p.badad_ad_item {

}

/* Heading */
br.badad_ad br.badad_heading {

}


/* Description */
span.badad_ad span.badad_heading {

}

strong.badad_ad strong.badad_heading {

}

br.badad_ad br.badad_description {

}


/* Info */
span.badad_ad span.badad_info {

}

em.badad_ad em.badad_info {

}

br.badad_ad br.badad_info {

}

/* Only displays for business ads*/
strong.badad_ad strong.badad_biz {

}

i.badad_ad i.badad_biz {

}

br.badad_ad br.badad_biz {

}

/* Payrate */
span.badad_ad span.badad_payrate {

}

/* Contact */
span.badad_ad span.badad_contact {

}

a.badad_ad a.badad_contact {

}

u.badad_ad u.badad_contact {

}

/* For vertical setting only, not horizantal */
hr.badad_ad hr.badad_close_vert_ad {

}

/* For horizantal setting only, not vertical */
hr.badad_ad hr.badad_close_inline_row {

}
</code></pre>
<p><a href="#code_index">&larr; back to index</a></p>
';


// User meta
echo '<h5 id="styling_partner_project_meta">Styling Partner Project meta fetched through the Dev API</h5>';
echo "<p>Below is an empty stylesheet and an example of the HTML returned when fetching our Partner's Project meta information via our Dev API. One HTML example has a user-input description for a \"live\" Partner Project; the other only shows the ID for the Project because the user did not assign a description and the Project is also set to \"off\".</p>";
echo '<p><a href="#code_index">&larr; back to index</a></p>';

// User meta HTML
echo '<h6 id="user_meta_html">User meta HTML (<i>$response</i> via Dev API):</h6>
<pre><code class="devblock">
<span class="green">
&lt;!-- Described and live --&gt;
&lt;div class="badad_app_meta"&gt;
  &lt;div class="badad_app_description"&gt;User Description&lt;/div&gt;
  &lt;br class="badad_app"&gt;
  &lt;div class="badad_app_status"&gt;Status: &lt;b class="badad_app_status"&gt;&lt;span class="badAd_status_live"&gt;live&lt;/span&gt;&lt;/b&gt;&lt;/div&gt;
&lt;/div&gt;
</span>
<span class="red">
&lt;!-- Undescribed and off --&gt;
&lt;div class="badad_app_meta"&gt;
  &lt;div class="badad_app_description"&gt;(#55)&lt;/div&gt;
  &lt;br class="badad_app"&gt;
  &lt;div class="badad_app_status"&gt;Status: &lt;b class="badad_app_status"&gt;&lt;span class="badAd_status_off"&gt;off&lt;/span&gt;&lt;/b&gt;&lt;/div&gt;
&lt;/div&gt;
</span>
</code></pre>
<p><a href="#code_index">&larr; back to index</a></p>
';

// User meta HTML
echo '<h6 id="user_meta_css">User meta CSS classes (via Dev API):</h6>
<pre><code class="devblock">

div.badad_app_meta {

}

div.badad_app_description {

}

div.badad_app_status {

}

br.badad_app {

}

span.badAd_status_live {

}

span.badAd_status_off {

}
</code></pre>
<p><a href="#code_index">&larr; back to index</a></p>
';

// Handshake
echo '<h5 id="connecting_to_your_app">Connecting our Partners to your app and embedding ads via our Dev API</h5>';
echo "<p>You can connect your app to our Dev API by 1. redirecting your user to login at our website and sending a simple _POST form, which automatically goes back to your Callback URL or 2. having your user enter a Partner App Key in your website. Both examples are demononstrated in the code below.</p>";
echo "<p>The examples below are given in PHP, though PHP is not required; _POST is the only requirement for this Dev API. If you need to duplicate this example for any reason, the CSS stylesheet used in this example is available in <a href=\"badad_devapi_example.txz\" download=\"badad_devapi_example.txz\">this tarball</a>, which also includes files for the three examples shown here.</p>";
echo '<p><a href="#code_index">&larr; back to index</a></p>';

// Callback URL page
echo '<h6 id="handshake_connect">Handshake (Connect/Callback URL page)</h6>';

echo '<p>Make sure this is in the &lt;head&gt; of your Callback URL page:<br>(This is where we check for your pub key, regardless of whether using a custom callback URL)</p>';
echo '<pre><code class="devblock">
&lt;meta name="badad.api.dev.key" content="<span class="red">live_pub_XXXXXXXXX_my_developer_pub_key</span>" /&gt; (your developer key)
</code></pre>';

echo '<p>Send these via _POST to our user site (not our API domain) at <b>https://badad.one/connect_app.php</b>:<br /><i>(the handshake is the only time you will not make calls to <b>api.badad.one</b> because it connects to our user login area)</i></p>';
echo '<pre><code class="devblock">
$_POST[\'dev_key\'] (=<span class="red">live_sec_XXXXXXXXXX</span> - your developer key)
$_POST[\'partner_app_key\'] (=<span class="blue">app_key_XXXXXXXXXX</span> - optional Partner App Key, send from user-input form only if not using the login method)
<i>$_POST[\'custom_callback\'] (=<span class="orange">https://example.tld/other/place/to/redirect/after/handshake</span> - optional only if "Use custom callback" is checked for Dev App, must contain your "domain", may contain GET arguments if you want them)</i>
</code></pre>';

echo '<p>We will send these via _POST back to your callback URL: (or custom callback URL if set)</p>';
echo '<pre><code class="devblock">
$_POST[\'badad_connect_response\'] (=\'true\', nothing more, just a simple check)
$_POST[\'partner_call_key\'] (=<span class="purple">call_key_XXXXXXXXXX</span> - keep this for future embed calls, for this specific user)
$_POST[\'partner_app_key\'] (=<span class="blue">app_key_XXXXXXXXXX</span> - already deleted once you receive, this matches the Partner App Key you just sent us if you didn\'t use the login option)
</code></pre>';

echo '<p>This is part of an example:</p>';
echo '
<pre><code class="devblock">
  <span class="red">
  &lt;meta name="badad.api.dev.key" content="live_pub_XXXXXXXXX_my_developer_pub_key" /&gt;
  </span>
&lt;/head&gt;
&lt;body&gt;

&lt;?php

// Dev key
<span class="red">$my_developer_sec_key = \'live_sec_XXXXXXXXX_my_developer_sec_key\';</span> // The value must be your dev key

// Capture response
<span class="red">if ((isset($_POST[\'badad_connect_response\']))
&& (isset($_POST[\'partner_app_key\']))
&& (isset($_POST[\'partner_call_key\']))
&& (isset($_POST[\'partner_refcred\']))
&& (preg_match (\'/[a-zA-Z0-9_]$/i\', $_POST[\'partner_app_key\']))
&& (preg_match (\'/[a-zA-Z0-9_]$/i\', $_POST[\'partner_call_key\']))
&& (preg_match (\'/^call_key_(.*)/i\', $_POST[\'partner_call_key\']))
&& (preg_match (\'/[a-zA-Z0-9]$/i\', $_POST[\'partner_refcred\'])))</span> { // _POST all present and mild regex check

  $partner_app_key = <span class="blue">$_POST[\'partner_app_key\']</span>; // This is the key you just sent, the last time it will ever be used, deleted from our servers
  $partner_call_key = <span class="purple">$_POST[\'partner_call_key\']</span>; // Starts with: "call_key_", keep this in your database for future API calls with this connected partner

  echo "&lt;div class=\"connected\"&gt;
&lt;p&gt;&lt;b&gt;Connected!&lt;/b&gt;&lt;br /&gt;&lt;br /&gt;
key1: $partner_app_key&lt;br /&gt;
key2: $partner_call_key&lt;/p&gt;&lt;/div&gt;";
  exit();

}

// Forms to connect

<span class="blue">
// User app_key
echo \'
&lt;form id="connect_partner_app_id" class="connect_partner" action="https://badad.one/connect_app.php" method="post" accept-charset="utf-8"&gt;
&lt;p&gt;&lt;b&gt;Connect with a Partner App Key&lt;/b&gt;&lt;/p&gt;
</span><span class="red">
&lt;!-- DEV NEEDS THIS --&gt;
&lt;input type="hidden" name="dev_key" value="\'.$my_developer_sec_key.\'" /&gt;
</span><span class="blue">
&lt;label for="partner_app_key"&gt;Your Partner App Key:&lt;/label&gt;
&lt;br /&gt;&lt;br /&gt;

&lt;!-- DEV NEEDS THIS: name="partner_app_key" --&gt;
&lt;input type="text" name="partner_app_key" id="partner_app_key" size="32" required /&gt;

&lt;input type="submit" value="Connect" class="formbutton" /&gt;
&lt;br /&gt;
&lt;/form&gt;
\';</span>
<span class="green">
// User login
echo \'
&lt;form id="connect_partner_app_id" class="connect_partner" action="https://badad.one/connect_app.php" method="post" accept-charset="utf-8"&gt;
&lt;p&gt;&lt;b&gt;Connect by login&lt;/b&gt;&lt;/p&gt;
</span><span class="red">
&lt;!-- DEV NEEDS THIS --&gt;
&lt;input type="hidden" name="dev_key" value="\'.$my_developer_sec_key.\'" /&gt;
</span><span class="green">
&lt;input type="submit" value="Login to Connect..." class="formbutton" /&gt;
&lt;br /&gt;
&lt;/form&gt;
\';</span>
</code></pre>
<p><a href="#code_index">&larr; back to index</a></p>
';

// Fetch meta
echo '<h6 id="fetch_meta">Fetch our Partner user meta to embed in your app</h6>';

echo '<p>Send these via _POST to our user API at <b>https://api.badad.one/fetchmeta.php</b>:</p>';
echo '<pre><code class="devblock">
$_POST[\'dev_key\'] (=<span class="red">live_sec_XXXXXXXXXX</span> - your developer key)
$_POST[\'call_key\'] (=<span class="purple">call_key_XXXXXXXXXX</span> - we sent you this at the handshake, for this specific user)
</code></pre>';

echo '<p>This is part of an example:</p>';
echo '<p><i>The <b>$response</b> below can be styled using the classes defined earlier on this page in <a href="#styling_partner_project_meta">Styling Partner Project meta</a></i></p>
<pre><code class="devblock">
// Dev key
<span class="red">$my_developer_sec_key = \'live_sec_XXXXXXXXX_my_developer_sec_key\';</span>
<span class="purple">$partner_call_key = \'call_key_XXXXXXXXX_user_key_sent_from_badad\';</span> // You retrieved this from our _POST response at the handshake (above) and probably set this as a variable value queried from your own database

  $post = http_build_query(
    array(
      <span class="red">\'dev_key\' =&gt; $my_developer_sec_key,</span>
      <span class="purple">\'call_key\' =&gt; $partner_call_key</span>
    )
  );

  $optns = array(\'http\' =&gt;
    array(
      \'method\' =&gt; \'POST\',
      \'headder\' =&gt; \'Content-type: application/x-www-form-urlencoded\',
      \'content\' =&gt; $post
    )
  );

  $context = stream_context_create($optns);
  <span class="green">$response</span> = file_get_contents(\'https://api.badad.one/fetchmeta.php\', false, $context);
  echo <span class="green">$response</span>; // This $response is the HTML payload fetched from our Dev API
  exit();
</code></pre>
<p><a href="#code_index">&larr; back to index</a></p>
';

// Embed ads
echo '<h6 id="fetch_ads_embed">Fetch ads to embed in your app</h6>';

echo '<p>Send these via _POST to our user API at <b>https://api.badad.one/render.php</b>:</p>';
echo '<pre><code class="devblock">
$_POST[\'dev_key\'] (=<span class="red">live_sec_XXXXXXXXXX</span> - your developer key)
$_POST[\'call_key\'] (=<span class="purple">call_key_XXXXXXXXXX</span> - we sent you this at the handshake, for this specific user)

// Options (optional)
$_POST[\'num_ads\'] (optional, 1-10, default 1)
$_POST[\'show_badad_link\'] (optional, default false)
$_POST[\'inline_div\'] (optional, default false)
</code></pre>';

echo '<p>This is part of an example:</p>';
echo '<p><i>The <b>$response</b> below can be styled using the classes defined earlier on this page in <a href="#styling_embedded_ads">Styling embedded ads</a></i></p>
<pre><code class="devblock">
&lt;?php

// Dev key
<span class="red">$my_developer_sec_key = \'live_sec_XXXXXXXXX_my_developer_sec_key\';</span>
<span class="purple">$partner_call_key = \'call_key_XXXXXXXXX_user_key_sent_from_badad\';</span> // You retrieved this from our _POST response at the handshake (above) and probably set this as a variable value queried from your own database

  $post = http_build_query(
    array(
      <span class="orange">
      // Note these options
      \'num_ads\' =&gt; 4, // Optional, 1-10, default 1
      \'show_badad_link\' =&gt; true, // Optional, default false
      \'inline_div\' =&gt; true, // Optional, default false
      </span>
      <span class="red">\'dev_key\' =&gt; $my_developer_sec_key,</span>
      <span class="purple">\'call_key\' =&gt; $partner_call_key</span>
    )
  );

  $optns = array(\'http\' =&gt;
    array(
      \'method\' =&gt; \'POST\',
      \'headder\' =&gt; \'Content-type: application/x-www-form-urlencoded\',
      \'content\' =&gt; $post
    )
  );

  $context = stream_context_create($optns);
  <span class="green">$response</span> = file_get_contents(\'https://api.badad.one/render.php\', false, $context);
  echo <span class="green">$response</span>; // This $response is the HTML payload fetched from our Dev API
  exit();
</code></pre>
';


echo "<h4>Obtaining a partner_call_key for mobile apps, etc</h4>";

echo '
<p>Your app will not appear in the Partner Center, nor will you be able to use the API until after the initial handshake. You may perform the initial handshake to receive your partner_call_key using the same domain as when you created your "New Developer App for API". Simply use that domain to host a simple API page to perform the call and obtain your partner_call_key. (You can use the example app in the <b><a href="badad_devapi_example.txz" download="badad_devapi_example.txz">example code tarball</a></b>.) Then, use that partner_call_key in your mobile or other app as you like, regardless of what domain the app makes calls from. Of course, keep it secret and do not publish the partner_call_key to the web.</p>

<p><a href="#code_index">&larr; back to index</a></p>
';


// Developer Center
if (isset($userid)) {
  echo "<br /><hr /><br />";
  set_switch("Developer Center...", "Go to the Developer Center", "partner_dev.php", "partner_dev", $userid, "set_black");
}

// Include the HTML footer
include ('./includes/footer.html');
?>
