<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require (MYSQL);

// Users
if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

// Include the header file
$page_title = "Help :: $siteTitle";
include ('./includes/header.html');

// Referred?
include ('includes/referred.inc.php');

// Insert the page content
echo "<h3>Help</h3><br />";

echo '<p><b><a href="help_videos.php">Videos</a></b></p>';

echo '<p><b><a href="help_wordpress_videos.php">WordPress Help Videos</a></b></p>';

echo "<h4>About</h4>";
echo "<p>badAd.one is an advertising network platform that allows:</p>
  <ul class=\"c\">
    <li class=\"c\">Advertising</li>
    <li class=\"c\">Searching ads (at badAd.one, including non-registered users)</li>
    <li class=\"c\">Monetizing:</li>
    <ul>
      <li>- via website or blog using our simple Embed Code</li>
      <li>- via website, blog, or app using our <a title=\"Developer Help\" href=\"help_dev.php\">Developer API</a></li>
      <li>- via podcast with our Podcast tools, turn any WordPress blog or media-ready RSS feed into a podcast</li>
    </ul>
  </ul>
  <p>badAd.one, at launch, only uses text ads. We like this is for a few reasons. Text ads are more to-the-point, create less \"visual noise\" for app users and blog readers, and don't make websites as \"heavy\" in load times.</p>";

// Anonymity
echo "<h4>Anonymity: Our main quality</h4>";
echo "<p>Anonymity and privacy are a high priority for badad.one. We collect as little information on users as possible, which may make our services seem different from others at times. We do track how the \"unknown public\" interacts with our ads, but we do not track individual users as they interact with ads. All personal data we have on our users can be seen in Account Information, or other information we collect can be downloaded via raw XML for both ads and for monetizing Partner Projects.</p>";

// How ads work
echo "<h4>How ads work</h4>";
echo "<p>An ad contains simple information, tags, a category and subcategory, an optional business name or trademark, and an anonymous \"Contact\" link that redirects from our advertising network. (Visitors can't see where your ad points by merely \"hovering\" over the Contact link.) You can advertise not only to sell something, but also with a \"want ad\" (to buy or hire) or as an \"agent\" (who both buys and sells).</p>";

// Searching
echo "<h4>Searching ads</h4>";
echo "<p>Visitors to badAd.one can search and lookup all current advertisements on our network. There are various search filters available at the top of the website. No registration is required and no visitor, logged in or not, is tracked for searching ads. In a way, this is like a Yellow Pages section and it is free to search. We believe that anything worth advertising is worth looking for. For us, advertising is a two-way street; we help advertisers find their customers just as we help customers find what they're looking for.</p>";

// Cost
echo "<h4>How we charge and pay</h4>";
echo "<p>We don't charge advertisers for clicks or for views, only for the duration of an ad. We pay monetizing Partners for ad views and more for ad clicks. This payment is based on a \"share\" system of all Partners based on our revenue, not an absolute figure. We prefer this \"views-based\" advertising because it helps make us unique as an ad network and because that's how advertising worked for years in periodicals and billboards. Bloggers shouldn't welcome squatters under the guise of \"advertising\". As much as we love ads at badAd.one, a blog's content should be the most interesting to readers, not less interesting than the advertisements that pay the bills. By sharing space for an ad, the blogger should already get paid. When a visitor clicks on a blog's advertisement, that's when the advertiser should get paid, not just the blogger. We figure, if you're a blogger or developer loaning us real estate on your blogs and apps, we owe it to you for the space we take up, and we want to make that space as lovable as possible for your readers. Whether your a blogger or an advertiser, we want your audience to be glad that you chose to work with us.</p>";

// Beta
echo "<h4>Beta stage</h4>";
echo "<p>We are currently in our \"beta\" stage. Some things might not work, nothing is guaranteed. Of course, we hope everything works out great. By signing up to advertise or monetize with us early, you get a \"rank\" (by order of signup, NOT by who referred you) that will give you a little more weight in determining payout shares. Those who signup earlier will make more money per share as monetizing Partners. This is our way of thanking those who were with us from the beginning.</p>";

// Getting started
echo "<h4>Getting started</h4>";
echo "<p>The first step to advertising or monetizing is to buy your first ad. (This is required for registration to avoid spam.) It's not expensive and, in badAd's view of the world, everyone needs to sell or buy something sooner or later.</p>";

// Why
echo "<h4>Why</h4>";
echo "<p>We are all advertisers in one way or another, if not for the brands we wear on our clothes and cars. So, we all might as well say so. Buying your own ad helps you understand the business mindset of the brands you buy, from cheap to luxury. Corporate greed and \"consumerism\" wouldn't be so destructive if we all ran ads once in a while. We are on a mission to make people business-savvy by making everyone, including blog readers and app gamers, ad-literate.</p>";

echo "<h4>Monetizing</h4>";
echo "<p>Once you have purchased your first ad and confirmed your email address, you have the option to start monetizing as a Partner. This requires a simple acceptance of our Terms for Partners, which Developers must also accept, even if they do not wish to monetize. (But, we hope they want to anyway.) Normal monetizing Projects, which include an Embed Code, can be created from the Partner Center. We have a separate Developer Area that can only be accessed from our Partner Center, near the bottom of the page. A Partner who wishes to monetize an app or website through the Developer API, rather than the simple Embed Code, must create a \"Partner App Project Key\" from the Developer Center, which is colored blue, near the bottom of the Developer Center page. This will generate a one-time \"App Key\" that must be copied and pasted into the third party app.</p>";

// Dev Help
echo "<h4>Developer Help</h4>";
echo "<p>There is a separate help section for Developers implementing the <a title=\"Developer Help\" href=\"help_dev.php\">Developer API</a> in their software code.</p>";

// End literature
echo "<br /><hr /><br />";


// Include the HTML footer
include ('./includes/footer.html');
?>
