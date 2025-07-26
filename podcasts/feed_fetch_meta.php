<?php

// This fetches and updates the content and meta information for a podcast
// It is included in feed_refresh.php and the cron task loop
// $feed_pid must be declared

//In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
// $feed_pid = 240;

// Configs
require_once ('./config.inc.php');
require_once (MYSQL);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Validate the Feed number
if ((isset($feed_pid))
&& (filter_var($feed_pid, FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $feed_pid = preg_replace("/[^0-9]/","", $feed_pid);

} else {
  header("Location: https://$siteDomain");
  exit();
}

// Time duration function Thanks https://stackoverflow.com/a/48187008/10343144
if (!function_exists('validTime')) { // No duplicate functions
  function validTime($time, $format='H:i:s') {
   $d = DateTime::createFromFormat("Y-m-d $format", "2017-12-01 $time");
   return $d && $d->format($format) == $time;
  }
}

// Current time of SQL server
$q = "SELECT CURRENT_TIMESTAMP";
$r = mysqli_query ($agg_dbc, $q);
while ($row = mysqli_fetch_array($r)) { $curr_time_sql = $row[0]; }
$curr_time_php = strtotime($curr_time_sql);

// Get the feed info
$q = "SELECT source, ba_title, ba_link, ba_description, ba_copyright, ba_image_url, ba_image_title, ba_image_link, ba_language, ba_itunes_title, ba_itunes_type, ba_itunes_complete, ba_itunes_image, ba_itunes_author, ba_itunes_summary, ba_itunes_owner_name, ba_itunes_owner_email, ba_itunes_keywords, ba_itunes_explicit, ba_itunes_cat1, ba_itunes_cat2, ba_itunes_cat3, ba_itunes_cat4, ba_itunes_cat5 FROM feeds WHERE project_id='$feed_pid'";
$r = mysqli_query ($agg_dbc, $q);
if (mysqli_num_rows($r) == 1) {
  while ($row = mysqli_fetch_array($r)) {
    $f_source = "$row[0]";
    $f_ba_title = "$row[1]";
    $f_ba_link = "$row[2]";
    $f_ba_description = "$row[3]";
    $f_ba_copyright = "$row[4]";
    $f_ba_image_url = "$row[5]";
    $f_ba_image_title = "$row[6]";
    $f_ba_image_link = "$row[7]";
    $f_ba_language = "$row[8]";
    $f_ba_itunes_title = "$row[9]";
    $f_ba_itunes_type = "$row[10]";
    $f_ba_itunes_complete = "$row[11]";
    $f_ba_itunes_image = "$row[12]";
    $f_ba_itunes_author = "$row[13]";
    $f_ba_itunes_summary = "$row[14]";
    $f_ba_itunes_owner_name = "$row[15]";
    $f_ba_itunes_owner_email = "$row[16]";
    $f_ba_itunes_keywords = "$row[17]";
    $f_ba_itunes_explicit = "$row[18]";
    $f_ba_itunes_cat1 = "$row[19]";
    $f_ba_itunes_cat2 = "$row[20]";
    $f_ba_itunes_cat3 = "$row[21]";
    $f_ba_itunes_cat4 = "$row[22]";
    $f_ba_itunes_cat5 = "$row[23]";
  }
} else {
  header("Location: https://$siteDomain");
  exit();
}

// Fetch the feed
$rss = simplexml_load_file($f_source,'SimpleXMLElement', LIBXML_NOCDATA);

// Process meta info (everything except <item>)

// Is this a valid feed?
if (!$rss->channel) {
  $feed_failed = true;
  header("Location: https://$siteDomain");
  exit();
}

// Declare some objects
$it = $rss->channel->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
$m = $rss->channel->children('http://search.yahoo.com/mrss/');
$c = $rss->channel;

// Title
if ($f_title = $c->title) {
$regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|—–]/";
$f_title = preg_replace($regex_replace,"", $f_title);
$f_title = html_entity_decode($f_title);
$f_title = preg_replace('/([A-Z].[a-z]+)-([A-Z].[a-z]+)/','$1–$2',$f_title); // Proper noun range to en-dash
$f_title = preg_replace('/([0-9]$)+-+([0-9])/','$1–$2',$f_title); // number range to en-dash
$f_title = str_replace(' -- ',' – ',$f_title); // to en-dash
$f_title = str_replace(' --','—',$f_title); // to em-dash
$f_title = str_replace('-- ','—',$f_title); // to em-dash
$f_title = str_replace('---','—',$f_title); // to em-dash
$f_title = str_replace('--','—',$f_title); // to em-dash
$f_title = strip_tags($f_title); // Remove any HTML tags
$f_title = substr($f_title, 0, 255); // Limit to 255 characters for TINYTEXT datatype
} else {
  $f_title = '';
  $feed_failed = true;
  $itunes_absent = true;
}

// Link
if ($f_link = $c->link) {
$f_link = (filter_var($f_link,FILTER_VALIDATE_URL)) ? $f_link : '';
} else {
  $f_link = '';
  $feed_failed = true;
  $itunes_partial = true;
}

// Description
if ($f_description = $c->description) {
  $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|—–]/";
  $f_description = preg_replace($regex_replace,"", $f_description);
  $f_description = html_entity_decode($f_description);
  $f_description = preg_replace('/([A-Z].[a-z]+)-([A-Z].[a-z]+)/','$1–$2',$f_description); // Proper noun range to en-dash
  $f_description = preg_replace('/([0-9]$)+-+([0-9])/','$1–$2',$f_description); // number range to en-dash
  $f_description = str_replace(' -- ',' – ',$f_description); // to en-dash
  $f_description = str_replace(' --','—',$f_description); // to em-dash
  $f_description = str_replace('-- ','—',$f_description); // to em-dash
  $f_description = str_replace('---','—',$f_description); // to em-dash
  $f_description = str_replace('--','—',$f_description); // to em-dash
  $f_description = strip_tags($f_description); // Remove any HTML tags
  $f_description = substr($f_description, 0, 65530); // Limit to 65,530 characters for TEXT datatype
} else {
  $f_description = '';
  $feed_failed = true;
  $itunes_absent = true;
}

// Image
if ($f_image_url = $c->image->url) {
  $f_image_url = (filter_var($f_image_url,FILTER_VALIDATE_URL)) ? $f_image_url : '';
  $f_image_title = $c->image->title;
  $regex_replace = "/[^0-9a-zA-Z_!&#-=\/|.; ]/";
  $f_image_title = preg_replace($regex_replace,"", $f_image_title);
  $f_image_link = $c->image->link;
  $f_image_link = (filter_var($f_image_link,FILTER_VALIDATE_URL)) ? $f_image_link : '';
} else {
  $f_image_url = '';
}

// Language
if ($f_language = $c->language) {
  $regex_replace = "/[^0-9a-zA-Z_-]/";
  $f_language = preg_replace($regex_replace,"", $f_language);
  $f_language = strtolower(substr($f_language, 0, 9)); // Limit to 9 characters, lowercase
  $f_language = (($f_language != NULL) && ($f_language != '')) ? $f_language : 'en-us';
  $f_language = substr($f_language, 0, 9); // Limit to 9 characters for TINYTEXT datatype
} else {
  $f_language = 'en-us';
}

// Copyright
if ($f_copyright = $c->copyright) {
  $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|—–]/";
  $f_copyright = preg_replace($regex_replace,"", $f_copyright);
} elseif ($f_copyright = $m->copyright) {
  $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|—–]/";
  $f_copyright = preg_replace($regex_replace,"", $f_copyright);
} else {
  $f_copyright = '';
}

// Dates
$q = "SELECT CURRENT_TIMESTAMP";
$row = mysqli_query ($agg_dbc, $q);
while ($r = mysqli_fetch_array($row)) { $curr_time_sql = $r[0]; }
$curr_time_php = strtotime($curr_time_sql); // Just in case we need the SQL time in PHP
// last Build Date
$f_lastbuilddate = $c->lastBuildDate;
$f_lastbuilddate_php = strtotime($f_lastbuilddate);
$f_lastbuilddate_sql = date("Y-m-d H:i:s", substr($f_lastbuilddate_php, 0, 10));
$f_lastbuilddate_sql = (isset($f_lastbuilddate_sql)) ? $f_lastbuilddate_sql : $curr_time_sql;

// iTunes Title (Optional)
if ($f_it_title = $it->title) {
$regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|—–]/";
$f_it_title = preg_replace($regex_replace,"", $f_it_title);
$f_it_title = html_entity_decode($f_it_title);
$f_it_title = substr($f_it_title, 0, 255); // Limit to 255 characters for TINYTEXT datatype
} else {
  $f_it_title = '';
}

// iTunes Type (Optional)
$f_it_type = ($it->type == 'serial') ? 'serial' : 'episodic';

// iTunes Complete (Optional)
$f_it_complete = ($it->complete == 'yes') ? 'yes' : 'not';

// iTunes Image URL
if (!empty($it->image->attributes()['href'])) {
  $f_it_image_url = $it->image->attributes()['href'];
  $f_it_image_url = (filter_var($f_it_image_url,FILTER_VALIDATE_URL)) ? $f_it_image_url : '';
} else {
  $f_it_image_url = '';
  $itunes_absent = true;
}

// iTunes Author
if ($f_it_author = $it->author) {
  $regex_replace = "/[^0-9a-zA-Z_!&#-=\/|.;©™®℠ ]/";
  $f_it_author = preg_replace($regex_replace,"", $f_it_author);
  $f_it_author = html_entity_decode($f_it_author);
  $f_it_author = substr($f_it_author, 0, 255); // Limit to 255 characters for TINYTEXT datatype
} else {
  $f_it_author = '';
  $itunes_partial = true;
}

// iTunes Summary
if ($f_it_summary = $it->summary) {
  $regex_replace = "/[^0-9a-zA-Z_!&#-=\/|—–.;©™®℠ ]/";
  $f_it_summary = preg_replace($regex_replace,"", $f_it_summary);
  $f_it_summary = html_entity_decode($f_it_summary);
  $f_it_summary = preg_replace('/([A-Z].[a-z]+)-([A-Z].[a-z]+)/','$1–$2',$f_it_summary); // Proper noun range to en-dash
  $f_it_summary = preg_replace('/([0-9]$)+-+([0-9])/','$1–$2',$f_it_summary); // number range to en-dash
  $f_it_summary = str_replace(' -- ',' – ',$f_it_summary); // to en-dash
  $f_it_summary = str_replace(' --','—',$f_it_summary); // to em-dash
  $f_it_summary = str_replace('-- ','—',$f_it_summary); // to em-dash
  $f_it_summary = str_replace('---','—',$f_it_summary); // to em-dash
  $f_it_summary = str_replace('--','—',$f_it_summary); // to em-dash
  $f_it_summary = strip_tags($f_it_summary); // Remove any HTML tags
  $f_it_summary = substr($f_it_summary, 0, 65530); // Limit to 65,530 characters for TEXT datatype
} else {
  $f_it_summary = '';
}

// iTunes Owner Name
if ($f_it_owner_name = $it->owner->name) {
  $regex_replace = "/[^0-9a-zA-Z_!&#-=\/|.;©™®℠ ]/";
  $f_it_owner_name = preg_replace($regex_replace,"", $f_it_owner_name);
  $f_it_owner_name = html_entity_decode($f_it_owner_name);
  $f_it_owner_name = substr($f_it_owner_name, 0, 255); // Limit to 255 characters for TINYTEXT datatype
} else {
  $f_it_owner_name = '';
  $itunes_partial = true;
}

// iTunes Owner Email
if ($f_it_owner_email = $it->owner->email) {
  $f_it_owner_email = (filter_var($f_it_owner_email,FILTER_VALIDATE_EMAIL)) ? $f_it_owner_email : '';
} else {
  $f_it_owner_email = '';
  $itunes_partial = true;
}

// iTunes Keywords
if ($f_it_keywords = $it->keywords) {
  $regex_replace = "/[^0-9a-zA-Z,_-]/";
  $f_it_keywords = preg_replace($regex_replace,"", $f_it_keywords);
  // Truncate after 12
  $keyword_items = explode(',', $result);
  $total = count($keyword_items);
  $count = 1;
  $result = '';
  foreach ($keyword_items as $word) {
    $word = preg_replace('/\s+/', '', $word);
    if (!preg_match("/[a-zA-Z]/i", $word)) { $count ++; continue; }
    $result .= $word.', ';
    $count ++;
    if (($count == 13) || ($count == $total + 1)) { break; }
  }
  $f_it_keywords = rtrim($result, ',');
} else {
  $f_it_keywords = '';
}

// iTunes Explicit
if ($f_it_explicit = $it->explicit) {
  $f_it_explicit = (($f_it_explicit == 'false') || ($f_it_explicit == 'no')) ? 'false' : 'true';
} else {
  $f_it_explicit = 'true';
}

// iTunes Categories
if ($it->category) {
  $itunes_cat = array();
  $ct = 0;
  foreach ($it->category as $cat) {
    if ($ct == 5) {break;}

    // Parent and child category
    if ((isset($cat->category->attributes()['text'])) && (isset($cat->attributes()['text']))) {

      $itunes_cat[$ct] = htmlspecialchars($cat->attributes()['text']).'::'.htmlspecialchars($cat->category->attributes()['text']);

    // Only parent cagegory
    } elseif (isset($cat->attributes()['text'])) {

      $itunes_cat[$ct] = htmlspecialchars($cat->attributes()['text']);

    // No category
    } else {

      $itunes_cat[$ct] = 'notset';

    }

    // SQL escape
    $itunes_cat_esc[$ct] = mysqli_real_escape_string($agg_dbc, $itunes_cat[$ct]);
    $ct++;

  }

} // iTunes category

// No empty categories for database
$cat_query  = ((isset($itunes_cat_esc[0])) && ($itunes_cat_esc[0] != 'notset')) ? "itunes_cat1='$itunes_cat_esc[1]'" : "itunes_cat1=''";
$cat_query .= ((isset($itunes_cat_esc[1])) && ($itunes_cat_esc[1] != 'notset')) ? ", itunes_cat2='$itunes_cat_esc[2]'" : ", itunes_cat2=''";
$cat_query .= ((isset($itunes_cat_esc[2])) && ($itunes_cat_esc[2] != 'notset')) ? ", itunes_cat3='$itunes_cat_esc[3]'" : ", itunes_cat3=''";
$cat_query .= ((isset($itunes_cat_esc[3])) && ($itunes_cat_esc[3] != 'notset')) ? ", itunes_cat4='$itunes_cat_esc[4]'" : ", itunes_cat4=''";
$cat_query .= ((isset($itunes_cat_esc[4])) && ($itunes_cat_esc[4] != 'notset')) ? ", itunes_cat5='$itunes_cat_esc[5]'" : ", itunes_cat5=''";

// Feed has no iTunes categories
if (((!isset($itunes_cat_esc[0])) || ($itunes_cat_esc[0] != 'notset'))
&&  ((!isset($itunes_cat_esc[1])) || ($itunes_cat_esc[1] != 'notset'))
&&  ((!isset($itunes_cat_esc[2])) || ($itunes_cat_esc[2] != 'notset'))
&&  ((!isset($itunes_cat_esc[3])) || ($itunes_cat_esc[3] != 'notset'))
&&  ((!isset($itunes_cat_esc[4])) || ($itunes_cat_esc[4] != 'notset'))) {
  $itunes_absent = true;
}
if ((($f_ba_itunes_cat1 == 'ba-empty') || ($f_ba_itunes_cat1 == ''))
&&  (($f_ba_itunes_cat2 == 'ba-empty') || ($f_ba_itunes_cat2 == ''))
&&  (($f_ba_itunes_cat3 == 'ba-empty') || ($f_ba_itunes_cat3 == ''))
&&  (($f_ba_itunes_cat4 == 'ba-empty') || ($f_ba_itunes_cat4 == ''))
&&  (($f_ba_itunes_cat5 == 'ba-empty') || ($f_ba_itunes_cat5 == ''))) {
  $ba_itunes_cat_empty = true;
}

// iTunes status
// Partial flags?
$f_itunes_status = ((isset($itunes_partial)) && ($itunes_partial == true)) ? 'partial' : 'ready';
// Absent flags?
$f_itunes_status = ((isset($itunes_absent)) && ($itunes_absent == true)) ? 'absent' : $f_itunes_status;
// Absent flags solved?
$f_itunes_status = (($f_itunes_status == 'absent')
                && ($f_ba_title != 'ba-empty')
                && ($f_ba_description != 'ba-empty')
                && ($f_ba_itunes_image != 'ba-empty')
                && ((!isset($ba_itunes_cat_empty)) || ($ba_itunes_cat_empty != true))) ?
                'custom' : $f_itunes_status;
// Partial flags solved?
if (($f_itunes_status == 'custom') &&
      (($f_ba_link == 'ba-empty')
    || ($f_ba_itunes_author == 'ba-empty')
    || ($f_ba_itunes_owner_name == 'ba-empty')
    || ($f_ba_itunes_owner_email == 'ba-empty'))) {
  $f_itunes_status = 'partial';
}

// Prepare for database
$f_title_esc = mysqli_real_escape_string($agg_dbc, $f_title);
$f_link_esc = mysqli_real_escape_string($agg_dbc, $f_link);
$f_description_esc = mysqli_real_escape_string($agg_dbc, $f_description);
$f_copyright_esc = mysqli_real_escape_string($agg_dbc, $f_copyright);
$f_image_url_esc = mysqli_real_escape_string($agg_dbc, $f_image_url);
$f_image_title_esc = mysqli_real_escape_string($agg_dbc, $f_image_title);
$f_image_link_esc = mysqli_real_escape_string($agg_dbc, $f_image_link);
$f_language_esc = mysqli_real_escape_string($agg_dbc, $f_language);
$f_it_title_esc = mysqli_real_escape_string($agg_dbc, $f_it_title);
$f_it_type_esc = mysqli_real_escape_string($agg_dbc, $f_it_type);
$f_it_complete_esc = mysqli_real_escape_string($agg_dbc, $f_it_complete);
$f_it_image_url_esc = mysqli_real_escape_string($agg_dbc, $f_it_image_url);
$f_it_author_esc = mysqli_real_escape_string($agg_dbc, $f_it_author);
$f_it_summary_esc = mysqli_real_escape_string($agg_dbc, $f_it_summary);
$f_it_owner_name_esc = mysqli_real_escape_string($agg_dbc, $f_it_owner_name);
$f_it_owner_email_esc = mysqli_real_escape_string($agg_dbc, $f_it_owner_email);
$f_it_keywords_esc = mysqli_real_escape_string($agg_dbc, $f_it_keywords);
$f_it_explicit_esc = mysqli_real_escape_string($agg_dbc, $f_it_explicit);
// badAd overrides
$f_ba_title_esc = (($f_title != '') && ($f_ba_title == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_title) : mysqli_real_escape_string($agg_dbc, $f_ba_title);
$f_ba_link_esc = (($f_link != '') && ($f_ba_link == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_link) : mysqli_real_escape_string($agg_dbc, $f_ba_link);
$f_ba_description_esc = (($f_description != '') && ($f_ba_description == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_description) : mysqli_real_escape_string($agg_dbc, $f_ba_description);
$f_ba_copyright_esc = (($f_copyright != '') && ($f_ba_copyright == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_copyright) : mysqli_real_escape_string($agg_dbc, $f_ba_copyright);
$f_ba_image_url_esc = (($f_image_url != '') && ($f_ba_image_url == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_image_url) : mysqli_real_escape_string($agg_dbc, $f_ba_image_url);
$f_ba_image_title_esc = (($f_image_title != '') && ($f_ba_image_title == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_image_title) : mysqli_real_escape_string($agg_dbc, $f_ba_image_title);
$f_ba_image_link_esc = (($f_image_link != '') && ($f_ba_image_link == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_image_link) : mysqli_real_escape_string($agg_dbc, $f_ba_image_link);
$f_ba_language_esc = mysqli_real_escape_string($agg_dbc, $f_ba_language);
$f_ba_itunes_title_esc = mysqli_real_escape_string($agg_dbc, $f_ba_itunes_title);
$f_ba_itunes_type_esc = mysqli_real_escape_string($agg_dbc, $f_ba_itunes_type);
$f_ba_itunes_complete_esc = mysqli_real_escape_string($agg_dbc, $f_ba_itunes_complete);
$f_ba_itunes_image_esc = (($f_it_image_url != '') && ($f_ba_itunes_image == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_it_image_url) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_image);
$f_ba_itunes_author_esc = (($f_it_author != '') && ($f_ba_itunes_author == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_it_author) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_author);
$f_ba_itunes_summary_esc = (($f_it_summary != '') && ($f_ba_itunes_summary == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_it_summary) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_summary);
$f_ba_itunes_owner_name_esc = (($f_it_owner_name != '') && ($f_ba_itunes_owner_name == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_it_owner_name) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_owner_name);
$f_ba_itunes_owner_email_esc = (($f_it_owner_email != '') && ($f_ba_itunes_owner_email == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_it_owner_email) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_owner_email);
$f_ba_itunes_keywords_esc = (($f_it_keywords != '') && ($f_ba_itunes_keywords == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_it_keywords) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_keywords);
$f_ba_itunes_explicit_esc = (($f_it_explicit != '') && ($f_ba_itunes_explicit == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $f_it_explicit) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_explicit);
$f_ba_itunes_cat1_esc = ((isset($itunes_cat_esc[1])) && ($itunes_cat_esc[1] != 'notset') && ($f_ba_itunes_cat1 == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $itunes_cat_esc[1]) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_cat1);
$f_ba_itunes_cat2_esc = ((isset($itunes_cat_esc[2])) && ($itunes_cat_esc[2] != 'notset') && ($f_ba_itunes_cat2 == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $itunes_cat_esc[2]) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_cat2);
$f_ba_itunes_cat3_esc = ((isset($itunes_cat_esc[3])) && ($itunes_cat_esc[3] != 'notset') && ($f_ba_itunes_cat3 == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $itunes_cat_esc[3]) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_cat3);
$f_ba_itunes_cat4_esc = ((isset($itunes_cat_esc[4])) && ($itunes_cat_esc[4] != 'notset') && ($f_ba_itunes_cat4 == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $itunes_cat_esc[4]) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_cat4);
$f_ba_itunes_cat5_esc = ((isset($itunes_cat_esc[5])) && ($itunes_cat_esc[5] != 'notset') && ($f_ba_itunes_cat5 == 'ba-empty')) ? mysqli_real_escape_string($agg_dbc, $itunes_cat_esc[5]) : mysqli_real_escape_string($agg_dbc, $f_ba_itunes_cat5);

// Database entry
$q = "UPDATE feeds SET
itunes_status='$f_itunes_status',
title='$f_title_esc',
link='$f_link_esc',
description='$f_description_esc',
copyright='$f_copyright_esc',
image_url='$f_image_url_esc',
image_title='$f_image_title_esc',
image_link='$f_image_link_esc',
language='$f_language_esc',
lastbuilddate='$f_lastbuilddate_sql',
itunes_image='$f_it_image_url_esc',
itunes_title='$f_it_title_esc',
itunes_type='$f_it_type_esc',
itunes_complete='$f_it_complete_esc',
itunes_author='$f_it_author_esc',
itunes_summary='$f_it_summary_esc',
itunes_owner_name='$f_it_owner_name_esc',
itunes_owner_email='$f_it_owner_email_esc',
itunes_keywords='$f_it_keywords_esc',
itunes_explicit='$f_it_explicit_esc',
$cat_query,
ba_title='$f_ba_title_esc',
ba_link='$f_ba_link_esc',
ba_description='$f_ba_description_esc',
ba_copyright='$f_ba_copyright_esc',
ba_image_url='$f_ba_image_url_esc',
ba_image_title='$f_ba_image_title_esc',
ba_image_link='$f_ba_image_link_esc',
ba_language='$f_ba_language_esc',
ba_itunes_title='$f_ba_itunes_title_esc',
ba_itunes_type='$f_ba_itunes_type_esc',
ba_itunes_complete='$f_ba_itunes_complete_esc',
ba_itunes_image='$f_ba_itunes_image_esc',
ba_itunes_author='$f_ba_itunes_author_esc',
ba_itunes_summary='$f_ba_itunes_summary_esc',
ba_itunes_owner_name='$f_ba_itunes_owner_name_esc',
ba_itunes_owner_email='$f_ba_itunes_owner_email_esc',
ba_itunes_keywords='$f_ba_itunes_keywords_esc',
ba_itunes_explicit='$f_ba_itunes_explicit_esc',
ba_itunes_cat1='$f_ba_itunes_cat1_esc',
ba_itunes_cat2='$f_ba_itunes_cat2_esc',
ba_itunes_cat3='$f_ba_itunes_cat3_esc',
ba_itunes_cat4='$f_ba_itunes_cat4_esc',
ba_itunes_cat5='$f_ba_itunes_cat5_esc'
WHERE project_id='$feed_pid'";

$r = mysqli_query ($agg_dbc, $q);
if ($r === false) { // Simple check for failure without requiring "affected rows"
  sql_error($q, 'agg_dbc', "sqle_129");
} else {
  $feed_fetch_meta_success = true;

  echo ((isset($refresh_action)) && ($refresh_action == true)) ? '<br><br><b><pre>'.$f_title.'</pre></b>' : false;

}

?>
