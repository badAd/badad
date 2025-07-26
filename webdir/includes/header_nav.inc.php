<?php
// A settings page requires form functions
require_once ('./includes/form_functions.inc.php');

// Make sure we're not hiding all ad roles
if ((isset($_SESSION['filter_s'])) && ($_SESSION['filter_s'] == true) && (isset($_SESSION['filter_w'])) && ($_SESSION['filter_w'] == true) && (isset($_SESSION['filter_a'])) && ($_SESSION['filter_a'] == true)) {
  unset($_SESSION['filter_s']);
  unset($_SESSION['filter_w']);
  unset($_SESSION['filter_a']);
}

// Tag counts & Categories reflect filters
$and_filter = "";
if ((isset($_SESSION['filter_w'])) && ($_SESSION['filter_w'] == true)) { $and_filter = $and_filter." AND role_id NOT LIKE '1'"; }
if ((isset($_SESSION['filter_s'])) && ($_SESSION['filter_s'] == true)) { $and_filter = $and_filter." AND role_id NOT LIKE '2'"; }
if ((isset($_SESSION['filter_a'])) && ($_SESSION['filter_a'] == true)) { $and_filter = $and_filter." AND role_id NOT LIKE '3'"; }
if ((isset($_SESSION['filter_b'])) && ($_SESSION['filter_b'] == true)) { $and_filter = $and_filter." AND ad_biz_listing='biz'"; }

// Category, Tag, Search & Filter tools for ad pages
if ((isset($ad_page)) && ($ad_page == true)) {
  echo '
   <div id="catnav">
    <ul class="categories">';
  // Categories

  /* We don't need a Home button because the header has two
  // Home button
  echo '<li class="category_button"';

    // Add the class of a category page if it's the current page:
    if ($this_page = 'index.php') {echo ' selected';}

    // Complete the item:
    echo '"><form class="homebutton" action="index.php"><button class="nav" type="submit" class="cat_nav">Home</button></form></li>';
  */

  // Dynamically create header menu...
      // Array of labels and pages (without extensions)
      $category_list = array (

      /* How to list many:
        'Home' => 'index.php',
        'News Stacks' => 'https://$siteDomain'
      */


      );
      // Dynamically add categories to the header menu
      $q = 'SELECT id, category, slug FROM categories ORDER BY category';
      $r = mysqli_query($dbc, $q);
      while (list($id, $category, $slug) = mysqli_fetch_array($r, MYSQLI_NUM)) {
        $category_list[$slug] = $category;
      }

      // The page being viewed
      $this_page = basename($_SERVER['PHP_SELF']);

      // Create each menu button
      foreach ($category_list as $cat_menu_slug => $cat_menu_name) {

        // Start the item
        echo '<li class="category_button"';

          // Add the class of a category page if it's the current page:
          if (($this_page != 'index.php') && ($this_page == "category.php?id=$id")) {echo ' selected';}

          // Complete the item
          echo '"><button class="cat_nav" onclick="catBoxDrop_'.$cat_menu_slug.'()">'.$cat_menu_name.'
          </button></li>
          <script>
            function catBoxDrop_'.$cat_menu_slug.'() {';

              // Close all categories
              foreach ($category_list as $cat_menu_slug_close => $cat_menu_name_close) {
                echo 'document.getElementById("catDrop_'.$cat_menu_slug_close.'").style.display = "none";';
              }
              // Close tags if open
                echo 'document.getElementById("tagDrop").style.display = "none";';

              echo '

              var x_'.$cat_menu_slug.' = document.getElementById("catDrop_'.$cat_menu_slug.'");
              if (x_'.$cat_menu_slug.'.style.display === "none") {
                x_'.$cat_menu_slug.'.style.display = "block";';

                echo '
            } else {
                x_'.$cat_menu_slug.'.style.display = "none";
            }

          }

          </script>

        ';

      } // End of FOREACH button category loop

      // Partner buttons
      //echo '<ul>';
        // echo '<li class="partner nav"><button class="partner nav">Podcasts</button></li>';
        // echo '<li class="partner nav"><button class="partner nav">Blogs</button></li>';
      //echo '</ul>';

  // END MENU
  echo '</ul>';

  // END the category/partner bar
  echo '</div>';
// Search Bar
echo '
  <div id="topsearch">
    <ul class="filters">';

  // Tag button
  echo '<li class="category_button"';

    // Add the class of a category page if it's the current page:
    if ($this_page == 'tag.php') {echo ' selected';}

    // Complete the item
    echo '"><button class="tag_nav" onclick="tagBoxDrop()">#Tags
    </button></li>
    <script>
      function tagBoxDrop() {';

          // Close all categories
          foreach ($category_list as $cat_menu_slug_close => $cat_menu_name_close) {
            echo 'document.getElementById("catDrop_'.$cat_menu_slug_close.'").style.display = "none";';
          }
          // Close tags if open
            echo 'document.getElementById("tagDrop").style.display = "none";';

        echo '
        var x = document.getElementById("tagDrop");
        if (x.style.display === "none") {
          x.style.display = "block";
      } else {
          x.style.display = "none";
      }
    }
    </script>

  ';

  // Filters
  echo '
      <li class="filter label" title="Filters: S W A, Business">||</li>
      <li class="filter">';
      // Selling
      if ((isset($_SESSION['filter_s'])) && ($_SESSION['filter_s'] == true)) {
        set_switch(" S ", "Hiding 'Selling' ads, click to show", "filter_s_off.act.php", "filterback", $_SERVER['REQUEST_URI'], "role");
    } else {
        set_switch(" S ", "Showing 'Selling' ads, click to hide", "filter_s_on.act.php", "filterback", $_SERVER['REQUEST_URI'], "role role_selling");
    }
  echo '
      </li>
      <li class="filter">';
      // Want-ad
      if ((isset($_SESSION['filter_w'])) && ($_SESSION['filter_w'] == true)) {
        set_switch(" W ", "Hiding 'Want' ads, click to show", "filter_w_off.act.php", "filterback", $_SERVER['REQUEST_URI'], "role");
    } else {
        set_switch(" W ", "Showing 'Want' ads, click to hide", "filter_w_on.act.php", "filterback", $_SERVER['REQUEST_URI'], "role role_want");
    }
  echo '
      </li>
      <li class="filter">';
      // Agent
      if ((isset($_SESSION['filter_a'])) && ($_SESSION['filter_a'] == true)) {
        set_switch(" A ", "Hiding 'Agent' ads, click to show", "filter_a_off.act.php", "filterback", $_SERVER['REQUEST_URI'], "role");
    } else {
        set_switch(" A ", "Showing 'Agent' ads, click to hide", "filter_a_on.act.php", "filterback", $_SERVER['REQUEST_URI'], "role role_agent");
    }
  echo '
      </li>
      <li class="filter">';
      // Business Listing
      if ((isset($_SESSION['filter_b'])) && ($_SESSION['filter_b'] == true)) {
        set_switch(" Business Ads Only ", "Showing only Business ads, click to show all", "filter_b_off.act.php", "filterback", $_SERVER['REQUEST_URI'], "listing_business");
    } else {
        set_switch(" Normal & Business ", "Showing all ads, click to show only Business", "filter_b_on.act.php", "filterback", $_SERVER['REQUEST_URI'], "listing_all");
    }


  // Search
  echo '
      </li>
      <li class="search">
        <form class="searchbar" action="search.php">';

          if (isset($_SESSION['searchQuery'])) {
            echo '<input type="text" class="searchbar" id="search_string" placeholder="Search ads" name="s" value="'.$_SESSION['searchQuery'].'" />';
          } elseif (isset($_SESSION['subcat'])) {
            echo '<input type="text" class="searchbar" id="search_string" placeholder="Search '.$_SESSION['subcat'].'..." name="s" />';
          } elseif (isset($_SESSION['category'])) {
            echo '<input type="text" class="searchbar" id="search_string" placeholder="Search '.$_SESSION['category'].'..." name="s" />';
          } elseif (isset($_SESSION['tag'])) {
            echo '<input type="text" class="searchbar" id="search_string" placeholder="Search #'.$_SESSION['tag'].'..." name="s" />';
          } else {
            echo '<input type="text" class="searchbar" id="search_string" placeholder="Search ads" name="s" />';
          }
  echo '
       <button class="search_nav" type="submit">
         <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
           <ellipse stroke="#fff" stroke-width="2" ry="5" rx="5" id="svg_1" cy="7" cx="7" fill="none"/>
           <line stroke="#fff" stroke-width="2" id="svg_3" y2="15" x2="15" y1="10" x1="10" fill="none"/>
         </svg>
       </button>
      </form>
    </button></li></ul>
  </div></div>';

// Category dropdown
foreach ($category_list as $cat_menu_slug => $cat_menu_name) {
echo '
  <div class="outerCatDropContainer">
    <div id="catDrop_'.$cat_menu_slug.'" class="catDrop" style="display:none;">';

  // Category title
  echo '<div class="title">
          <h4>'.$cat_menu_name.'</h4>
        </div>';
  // x close button
  echo '
  <div class="closeButton">
  <br />
   <button onclick="closeBoxDrop()">X</button>
  </div>
  ';
// Dynamically generate the subcategory links
    echo '<ul>';
    $cq = "SELECT id FROM categories WHERE slug='$cat_menu_slug'";
    $cr = mysqli_query($dbc, $cq);
    $row = mysqli_fetch_array ($cr, MYSQLI_NUM);
    $catID = $row[0];
      $sq = "SELECT id, subcat FROM sub_$cat_menu_slug ORDER BY subcat";
      $sr = mysqli_query($dbc, $sq);
      while (list($subcatID, $subcat) = mysqli_fetch_array($sr, MYSQLI_NUM)) {
        $scq = "SELECT id FROM ads WHERE pub_status='live' AND subcat_id='$subcatID' AND category_id='$catID' $and_filter";
        $scrows = mysqli_query($dbc, $scq);
        $scat_count = mysqli_num_rows($scrows);
        if ($scat_count > 0) {
          echo '<li class="subcategory_sidebar"><a href="https://badad.one/category.php?id=' . $catID . '&s=' . $subcatID . '" title="' . $subcat . '">' . $subcat . '</a></li>';
        }
      }
    echo '</ul>';

  // Bottom x close button
  /* NOT HERE, too congested for such a short list
  echo '
  <div class="closeButton">
   <button onclick="closeBoxDrop()">X</button>
  </div>
  ';
  */

  // End the cat dropdown box
  echo '
      </div>
    </div>
  ';
  }

// Tags button dropdown
  echo '
    <div class="outerTagDropContainer">
      <div id="tagDrop" class="tagDrop" style="display:none;">';
  // Tags title
  echo '
            <div class="title">
          <h4>Tags</h4>
        </div>';
  // x close button
  echo '
  <div class="closeButton">
  <br />
   <button onclick="closeBoxDrop()">X</button>
  </div>
  ';

  // Tag list
  echo '
        <ul>';
    // Dynamically generate the tag links
    $q = "SELECT id, tag FROM tags WHERE merged='unique' ORDER BY tag";
    $r = mysqli_query($dbc, $q);
    while (list($tagID, $tag) = mysqli_fetch_array($r, MYSQLI_NUM)) {
      $tq = "SELECT id FROM ads WHERE pub_status='live' $and_filter AND (tag_ids LIKE '{$tagID},%' OR tag_ids LIKE '%, {$tagID},%' OR tag_ids LIKE '%, {$tagID}' OR tag_ids LIKE '{$tagID}')";
      $tag_count = mysqli_num_rows(mysqli_query($dbc, $tq));
      if ($tag_count > 0) {
        echo '<li class="tag_sidebar"><a href="https://badad.one/tag.php?t='.$tag.'" title="'.$tag.'">'.$tag.' ('.$tag_count.')</a></li>';
      }
    }
    echo '</ul>';

    // Bottom x close button
    echo '
    <div class="closeButton">
     <button onclick="closeBoxDrop()">X</button>
    </div>
    ';

  // end the tag dropdown box
  echo '
      </div>
    </div>
  ';


  // Script for cat/tag close button
  echo '
  <script>
    function closeBoxDrop() {';
      // Close all categories
      foreach ($category_list as $cat_menu_slug_close => $cat_menu_name_close) {
        echo 'document.getElementById("catDrop_'.$cat_menu_slug_close.'").style.display = "none";';
      }
      // Close tags if open
        echo 'document.getElementById("tagDrop").style.display = "none";';

  echo ' }
  </script>';

  // Finish the header navs
  echo '
  </div>
  <div class="main_page main_ads_page">
    <div class="outerMetaContainer">
      <div id="userMeta" class="userMeta main_ads_page" style="display:none;">
  ';
} else {
  // Finish the div#top_menu_nav
  echo '
  </div>
  <div class="main_page main_non_ads_page">
    <div class="outerMetaContainer">
      <div id="userMeta" class="userMeta main_non_ads_page" style="display:none;">
  ';
}

// User Meta
      if (isset($_SESSION['user_id'])) {

       // Get User's Name

       // User ID
       $userid = $_SESSION['user_id'];

       // User's name
       $q = "SELECT name FROM users WHERE id='$userid'";
       $r = mysqli_query ($dbc, $q);
       $row = mysqli_fetch_array($r, MYSQLI_NUM);
       $userName = "$row[0]";

       // User options
       echo '<div class="title">
             <h4>Your Account</h4>
           </div>
           <ul>
           <li><a href="https://'.$siteDomain.'/order_history.php" title="Ad Order History">Ad Order History</a></li>
           <li><a href="https://'.$siteDomain.'/partner.php" title="Partner Center">Partner Center</a></li>
           <li>&nbsp;</li>
           <li><a href="https://'.$siteDomain.'/account_info.php" title="Account Information">Account Information</a></li>
           <li>&nbsp;</li>
           <li><a href="https://'.$siteDomain.'/logout.php" title="Logout">Logout</a></li>
           </ul>
           ';

       // Show elevated options if active
       if (($_SESSION['user_is_admin']) || ($_SESSION['user_is_supervisor']) || ($_SESSION['user_is_publisher']) || ($_SESSION['user_is_editorvoice']) || ($_SESSION['user_is_voice']) || ($_SESSION['user_is_editor'])) {
         echo '<div class="title">
               <h4>Oversight</h4>
               </div>
               <ul>
               ';
       }
       // Show editor options if active
       if (isset($_SESSION['user_is_editor'])) {
         echo '<li><a href="https://'.$siteDomain.'/editor.php" title="Review ad manuscripts">Editing & Review</a>';
       }
       // Show vocie options if active
       if (isset($_SESSION['user_is_voice'])) {
         echo '<li><a href="https://'.$siteDomain.'/voice.php" title="Record &amp; upload voice ads">Voice Recording</a>';
       }
      // Show editorvoice options if active
      if (isset($_SESSION['user_is_editorvoice'])) {
        echo '<li><a href="https://'.$siteDomain.'/editor.php" title="Review ad manuscripts">Editing & Review</a>';
        echo '<li><a href="https://'.$siteDomain.'/voice.php" title="Record &amp; upload voice ads">Voice Recording</a>';
      }
      // Show publisher options if active
      if (isset($_SESSION['user_is_publisher'])) {
        echo '<li><a href="https://'.$siteDomain.'/editor.php" title="Review ad manuscripts">Editing & Review</a>';
        echo '<li><a href="https://'.$siteDomain.'/voice.php" title="Record &amp; upload voice ads">Voice Recording</a>';
        echo '<li><a href="https://'.$siteDomain.'/publisher.php" title="Review &amp; publish voice ads">Publishing</a>';
      }
      // Show supervisor options if active
      if (isset($_SESSION['user_is_supervisor'])) {
        echo '<li><a href="https://'.$siteDomain.'/editor.php" title="Review ad manuscripts">Editing & Review</a>';
        echo '<li><a href="https://'.$siteDomain.'/voice.php" title="Record &amp; upload voice ads">Voice Recording</a>';
        echo '<li><a href="https://'.$siteDomain.'/publisher.php" title="Review &amp; publish voice ads">Publishing</a>';
        echo '<li><a href="https://'.$siteDomain.'/supervisor.php" title="Supervise users &amp; activity">Supervision</a>';
      }
      // Show admin options if active
      if (isset($_SESSION['user_is_admin'])) {
        echo '<li><a href="https://'.$siteDomain.'/editor.php" title="Review ad manuscripts">Editing & Review</a>';
        echo '<li><a href="https://'.$siteDomain.'/voice.php" title="Record &amp; upload voice ads">Voice Recording</a>';
        echo '<li><a href="https://'.$siteDomain.'/publisher.php" title="Review &amp; publish voice ads">Publishing</a>';
        echo '<li><a href="https://'.$siteDomain.'/supervisor.php" title="Supervise users &amp; activity">Supervision</a>';
        echo '<li><a href="https://'.$siteDomain.'/administor.php" title="Admin control center">Administration</a>';
      }
      // Close elevated options if active
      if (($_SESSION['user_is_admin']) || ($_SESSION['user_is_supervisor']) || ($_SESSION['user_is_editorvoice']) || ($_SESSION['user_is_voice']) || ($_SESSION['user_is_editor'])) {
        echo '</ul>';
      }

       // Bottom x close Meta button
       echo '
       <div class="closeButton">
        <button onclick="metaBoxDrop()">X</button>
       </div>
       ';

     } else {
       // Set the variable
       if (!isset($login_form_action)) { $login_form_action = "login.php"; }
        // Not logged in
         $lformaction = $login_form_action;
         include ('includes/login_form.inc.php');

         echo '
        <div style="text-align:right;">
         <button onclick="metaBoxDrop()">X close</button>
        </div>';
      }

echo '
    </div>
  </div>
';

// Login notices
// Only if there are no critical notices
if ((!isset($_SESSION['sent_need_accept_new_tc'])) && (!isset($_SESSION['sent_partner_need_accept_new_tc'])) && (!isset($_SESSION['sent_need_see_security_notice']))) {
    // Notices and warnings
  if ((isset($_SESSION['just_registered'])) && (isset($_SESSION['reguser_name']))) {
    $userName = $_SESSION['reguser_name'];
    echo '<div id="login_notice_wrapper"><div class="back_green" id="login_notice">Thank you for registering, '.$userName.'! Remember to <b><a class="note_white" title="Click to send a confirmation link to your email address" href="https://badad.one/confirm_email.php">confirm your email address ('.$userEmail.')</a></b>.</div></div>';
    unset ($_SESSION['just_registered']);
  } elseif ((isset($registr_errors['recaptcha'])) || (isset($registr_errors['name'])) || (isset($registr_errors['project'])) || (isset($registr_errors['username'])) || (isset($registr_errors['email2'])) || (isset($registr_errors['email1'])) || (isset($registr_errors['pass2'])) || (isset($registr_errors['pass1']))) {
    echo '<div id="login_notice_wrapper"><div class="back_red" id="login_notice">Registration error, check your credentials.</div></div>';
  } elseif ((!isset($_SESSION['repair_info'])) && ((isset($login_errors['username'])) || (isset($login_errors['pass'])) || (isset($login_errors['login'])))) {
  	echo '<div id="login_notice_wrapper"><div class="back_red" id="login_notice">Login error, check your credentials.</div></div>';
  } elseif ((!isset($_SESSION['repair_info'])) && ((isset($tfa_errors['email_code'])) || (isset($tfa_errors['sms_code'])) || (isset($tfa_errors['google_auth'])))) {
  	echo '<div id="login_notice_wrapper"><div class="back_red" id="login_notice">Wrong code, double check.</div></div>';

  // TFA
  } elseif (isset($_SESSION['tfa_email_link'])) {
  	echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">Login link sent to your email, check your inbox!</div></div>';
  } elseif (isset($_SESSION['tfa_email_code'])) {
  	echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">Login code sent to your email, check your inbox!</div></div>';
  } elseif (isset($_SESSION['tfa_sms_code'])) {
  	echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">Login code sent to your mobile phone!</div></div>';
  } elseif (isset($_SESSION['tfa_google_auth'])) {
  	echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">Double checking with your Google Authenticator!</div></div>';

  // Need critical action
  } elseif ((isset($_SESSION['need_accept_new_tc'])) && ($_SESSION['need_accept_new_tc'] == true)) {
    $_SESSION['sent_need_accept_new_tc'] = true;
    unset($_SESSION['need_accept_new_tc']);
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<script type="text/javascript">window.location = "https://badad.one/notice_user_tc.php";</script>';
    exit(); // Quit the script
  } elseif ((isset($_SESSION['need_see_security_notice'])) && ($_SESSION['need_see_security_notice'] == true)) {
    $_SESSION['sent_need_see_security_notice'] = true;
    unset($_SESSION['need_see_security_notice']);
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<script type="text/javascript">window.location = "https://badad.one/notice_security.php";</script>';
    exit(); // Quit the script
  } elseif ((isset($_SESSION['partner_need_accept_new_tc'])) && ($_SESSION['partner_need_accept_new_tc'] == true)) {
    $_SESSION['sent_partner_need_accept_new_tc'] = true;
    unset($_SESSION['partner_need_accept_new_tc']);
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<script type="text/javascript">window.location = "https://badad.one/notice_partner_tc.php";</script>';
    exit(); // Quit the script

  // Need action
  } elseif ((isset($_SESSION['no_status'])) && (!isset($_POST['accountform']))) {
  	echo '<div id="login_notice_wrapper"><div class="back_yellow" id="login_notice">Your email needs to be confirmed in your <a title="Account Information" href="https://badad.one/account_info.php">Account Information</a> before you can place any new orders.</div></div>';
  } elseif ((isset($_SESSION['email_unconfirmed'])) && (isset($_SESSION['user_email']))) {
    // Get User's Email
    $userEmail = $_SESSION['user_email'];
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<div id="login_notice_wrapper"><div class="back_yellow" id="login_notice">Remember to <b><a title="Click to send a confirmation link to your email address" href="https://badad.one/confirm_email.php">confirm your email</a></b> ('.$userEmail.'). Wrong address? Make changes in <a title="Account Information" href="https://badad.one/account_info.php">Account Information</a>.</div></div>';
  } elseif (isset($_SESSION['user_must_verify'])) {
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">You made some recent changes. Please secure your account by confirming in <b><a class="note_white" title="Account Information" href="https://badad.one/account_info.php">Account Information</a></b>.</div></div>';
  } elseif (isset($_SESSION['partner_dev_must_verify'])) {
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">You made changes in the <b>Developer Center</b>. Please look everything over, then confirm in the <b><a class="note_white" title="Partner Center" href="https://badad.one/partner.php">Partner Center</a></b>.</div></div>';
  } elseif (isset($_SESSION['partner_must_verify'])) {
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">You made changes in the <b><a class="note_white" title="Partner Center" href="https://badad.one/partner.php">Partner Center</a></b>. Please secure your account by confirming.</div></div>';
  } elseif ((isset($_SESSION['need_lookover_account_info'])) && ($_SESSION['need_lookover_account_info'] == true)) {
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">We need to make sure everything in your <b><a class="note_white" title="Account Information" href="https://badad.one/account_info.php">Account Information</a></b> is correct.</div></div>';
  } elseif ((isset($_SESSION['partner_need_see_new_categories'])) && ($_SESSION['partner_need_see_new_categories'] == true)) {
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);} // No login welcome if action needed
    echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">We updated our categories, double check: <b>Categories > choose</b> in the <b><a class="note_white" title="Partner Center" href="https://badad.one/partner.php">Partner Center</a></b>.</div></div>';

  // Login/logout
  } elseif (isset($_SESSION['logged_out'])) {
  	echo '<div id="login_notice_wrapper"><div class="back_blue" id="login_notice">Logged out, hope to see you back soon!</div></div>';
  	unset ($_SESSION['logged_out']);
  } elseif (((isset($_SESSION['login_success'])) && (isset($_SESSION['user_id'])) && (isset($_SESSION['user_name']))) && ((!isset($_SESSION['user_must_verify'])) && (!isset($_SESSION['partner_must_verify'])) && (!isset($_SESSION['partner_dev_must_verify'])))) {
     // Get User's Name
     $userid = $_SESSION['user_id'];
     // User's name
     $userName = $_SESSION['user_name'];
  	echo '<div id="login_notice_wrapper"><div class="back_green" id="login_notice">Logged in, welcome back '.$userName.'!</div></div>';
    if (isset($_SESSION['login_success'])) {unset($_SESSION['login_success']);}
  }
}
