<?php

// This fetches and updates the content and meta information for a podcast
// It is included in feed_refresh.php and the cron task loop
// $feed_pid must be declared

//In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
// $feed_pid = 240;
// $refresh_action = true;

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
  exit;
}

// Time
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);
$timeZone = date("O");

// From string_functions.inc, on a different server
function longString($length = 10) {
  // if (preg_match ('/[a-zA-Z0-9]$/i', $_GET['string']))
    $chrs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chrsL = strlen($chrs);
    $renderedString = '';
    for ($i = 0; $i < $length; $i++) {
        $renderedString .= $chrs[rand(0, $chrsL - 1)];
    }
    return $renderedString;
}

// File size function // Thanks https://stackoverflow.com/questions/2602612/
if (!function_exists('episode_length')) { // No duplicate functions
  function episode_length($url) {
    // Assume failure.
    $result = -1;

    $curl = curl_init( $url );

    // Issue a HEAD request and follow any redirects.
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($curl, CURLOPT_USERAGENT, get_user_agent_string());

    // Run the check
    $data = curl_exec($curl);
    $size = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($curl);

    // Return results
    return $size;
  } // End episode_length() function
}

// Remote file time duration via ffmpeg (may only work for local files)
if (!function_exists('ff_time')) { // No duplicate functions
  function ff_time($filepath) {

    if (preg_match('/[^?#]+\.(?:m4a|mp3|mov|m4v|mp4)/', strtolower($filepath))) {

      // ffmpeg time in seconds
      $ffmpegout = shell_exec("ffprobe -i '$filepath' -show_entries format=duration -v quiet -of csv='p=0' -sexagesimal | cut -f1 -d '.'");

      // Return
      return $ffmpegout;
    }
  } // End ff_time() function
}

// Time duration function Thanks https://stackoverflow.com/a/48187008/10343144
if (!function_exists('validTime')) { // No duplicate functions
  function validTime($time, $format='H:i:s') {
    $d = DateTime::createFromFormat("Y-m-d $format", "2017-12-01 $time");
    return $d && $d->format($format) == $time;
  } // End validTime() function
}

// Current time of SQL server
$q = "SELECT CURRENT_TIMESTAMP";
$r = mysqli_query ($agg_dbc, $q);
while ($row = mysqli_fetch_array($r)) { $curr_time_sql = $row[0]; }
$curr_time_php = strtotime($curr_time_sql);

// Get the feed info
$q = "SELECT source, global_subcat_ids, project_id FROM feeds WHERE project_id='$feed_pid'";
$r = mysqli_query ($agg_dbc, $q);
if (mysqli_num_rows($r) == 1) {
  $row = mysqli_fetch_array($r);
  $f_source = "$row[0]";
  $global_subcat_ids = "$row[1]";
  $project_id = "$row[2]";
} else {
  header("Location: https://$siteDomain");
  exit;
}

// Fetch the feed
$rss = simplexml_load_file($f_source,'SimpleXMLElement', LIBXML_NOCDATA);

$loopcount = 1;

foreach ($rss->channel->item as $item) {

  // badAd entry //

  // Select the ad by list_wk_count & category, etc
  $q = "SELECT pod_ad_id, ad_id, duration, enclosure_aud_length, rerun_pod_ad_id
  FROM podcastads WHERE INSTR('$global_subcat_ids', global_subcat_id) AND epoch_starts < '$timeNowEpoch' AND pub_status='live'
  ORDER BY list_wk_count, epoch_wk_reset, pod_ad_id DESC LIMIT 1";
  $r = mysqli_query($agg_dbc, $q);
  if (mysqli_num_rows($r) == 1) {
    $pod_ad_item = mysqli_fetch_array($r);
  	$pod_ad_id = "$pod_ad_item[0]";
    $ad_id = "$pod_ad_item[1]";
    $pod_ad_duration = "$pod_ad_item[2]";
    $pod_ad_enclosure_aud_length = "$pod_ad_item[3]";
    $pod_ad_rerun_pod_ad_id = "$pod_ad_item[4]"; // Are we using this?
  } else {
    include ('pod_ad_defaults.inc.php');
    $pod_ad_id = $ba_default_pod_ad_id;
    $ad_id = $ba_default_ad_id;
    $pod_ad_duration = $ba_default_aud_duration;
    $pod_ad_enclosure_aud_length = $ba_default_aud_length;
    $pod_ad_rerun_pod_ad_id = 0; // Are we using this?
  }

  // Fetch the text ad info and create
  // - <title>
  // - <description>
  // - "Contact" link for <item><link>
  $q = "SELECT ad_content_hdng, ad_content_dscr, serialno
  FROM listads WHERE ad_id='$ad_id'";
  $r = mysqli_query($srv_dbc, $q);
  if (mysqli_num_rows($r) == 1) {
    $pod_ad_item = mysqli_fetch_array($r);
    $txt_ad_content_hdng = "$pod_ad_item[0]";
    $txt_ad_content_dscr = "$pod_ad_item[1]";
    $txt_serialno = "$pod_ad_item[2]";
  } else {
    include ('pod_ad_defaults.inc.php');
    $txt_ad_content_hdng = $ba_default_txt_hdng;
    $txt_ad_content_dscr = $ba_default_txt_dscr;
    $txt_serialno = $ba_default_txt_serialno;
  }
  $q = "SELECT badadref_no
  FROM partnersites WHERE id='$project_id'";
  $r = mysqli_query($srv_dbc, $q);
  if (mysqli_num_rows($r) == 1) {
    $pod_ad_item = mysqli_fetch_array($r);
    $txt_badadref_no = "$pod_ad_item[0]";
  } else {
    sql_error("$q", 'srv_dbc', "sqle_185");
  }

  $pod_ad_title = $txt_ad_content_hdng;
  $pod_ad_description = $txt_ad_content_dscr;
  $pod_ad_link = "https://$adServeDomain/$txt_badadref_no/$txt_serialno/ct.html";

  // Generate a 64-alnum key for this entry
  $new_serial_key = longString(72);
  // Dup check
  $q = "SELECT serial_key FROM episode_ad_keys WHERE BINARY serial_key='$new_serial_key'"; // "BINARY" makes sure case and characters are exact
  $row = mysqli_query ($agg_dbc, $q);

  while (mysqli_num_rows($row) != 0) {
    $new_serial_key = longString(72);
    // Check again
    $q = "SELECT serial_key FROM episode_ad_keys WHERE BINARY serial_key='$new_serial_key'"; // "BINARY" makes sure case and characters are exact
    $row = mysqli_query ($agg_dbc, $q);
    if (mysqli_num_rows($row) == 0) {
      break;
    }
  }

  // Audio enclosure url
  $pod_enclosure_aud = "https://$podcastServeDomain/badad-$new_serial_key.mp3";

  // Podcast entry //
  $itunes = $item->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
  $content = $item->children('http://purl.org/rss/1.0/modules/content/');
  $atom = $item->children('http://www.w3.org/2005/Atom'); // For future use
  $dc = $item->children('http://purl.org/dc/elements/1.1/');

  // Parse every item we are ready to handle

  // Title
  $f_title = $item->title;
  $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|]/";
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
  // Required by iTunes, no item entry if empty
  if ((!isset($item->title)) || ($f_title == '')) { continue; }

  // Description
  $f_description = $item->description;
  $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|]/";
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
  // Make sure our variables are not empty
  $f_description = (isset($item->description)) ? $f_description : '';

  // Link
  $f_link = $item->link;
  $f_link = ((filter_var($f_link,FILTER_VALIDATE_URL)) && (strlen($f_link) <= 2048))
  ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $f_link),0,2048) : '';

  // iTunes Image
  $f_itunes_image = $itunes->image;
  $f_itunes_image = ((filter_var($f_itunes_image,FILTER_VALIDATE_URL)) && (strlen($f_itunes_image) <= 2048))
  ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $f_itunes_image),0,2048) : '';

  // iTunes Title (Optional)
  if ($f_itunes_title = $itunes->title) {
  $regex_replace = "/[^0-9a-zA-Z_ ©™®℠!@&#<>$%.,;+-=\/|]/";
  $f_itunes_title = preg_replace($regex_replace,"", $f_itunes_title);
  $f_itunes_title = html_entity_decode($f_itunes_title);
  $f_itunes_title = substr($f_itunes_title, 0, 255); // Limit to 255 characters for TINYTEXT datatype
  } else {
    $f_itunes_title = '';
  }

  // iTunes Episode Type
  $f_itunes_episodetype = (($itunes->episodeType == 'full') || ($itunes->episodeType == 'trailer') || ($itunes->episodeType == 'bonus')) ? $itunes->episodeType : 'full';

  // iTunes Episode
  $f_itunes_episode = (filter_var($itunes->episode, FILTER_VALIDATE_INT, array('min_range' => 1))) ? $itunes->episode : 0;

  // iTunes Season
  $f_itunes_season = (filter_var($itunes->season, FILTER_VALIDATE_INT, array('min_range' => 1))) ? $itunes->season : 0;

  // Duration
  if (validTime($itunes->duration)) { // Time to seconds
   $f_duration = $itunes->duration;
   sscanf($f_duration, "%d:%d:%d", $hours, $minutes, $seconds);
   $f_duration = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
  } elseif (filter_var($itunes->duration, FILTER_VALIDATE_INT, array('min_range' => 1))) {
   $f_duration = $itunes->duration;
  } else {
   $f_duration = '';
  }

  // WordPress with no enclsure, but iTunes-ready media
  if ((!isset($item->enclosure)) && (isset($content->encoded))) {
    $wpontent = $content->encoded;
    // We are looking for models like this:
    // [video width="1280" height="720" mp4="http://thecheesyreview.com/wp-content/uploads/2022/01/video-1280.mp4"][/video]
    // <a href="http://thecheesyreview.com/wp-content/uploads/2022/01/video-tall.mov">video-tall</a>
    // [audio mp3="https://podcast.jessesteele.com/wp-content/uploads/2022/03/1018_PW_2022-03-15-back-in-the-game-se-mod-elections-caught-ninja-mouse.mp3"][/audio]
    // <a href="https://podcast.jessesteele.com/wp-content/uploads/2022/03/1018_PW_2022-03-15-back-in-the-game-se-mod-elections-caught-ninja-mouse.mp3">Back in the game, SE mod elections, caught Ninja Mouse | Podcast Weekly</a>

    // WP audio tag
    if (strpos($wpontent, '[audio') !== false) {

    // WP video tag
    } elseif (strpos($wpontent, '[video') !== false) {

    // media inside href= attribute
    } elseif ((strpos($wpontent, '<a') !== false) && (strpos($wpontent, 'href=') !== false)) {

      $html = new DOMDocument;
      @$html->loadHTML($wpontent);
      $links = $html->getElementsByTagName('a');

      // Loop through every link until we find what we want
      foreach($links as $link){

        //Get the link in the href attribute.
        $linkhref = $link->getAttribute('href');
        $scheme = parse_url($linkhref)['scheme'];
        $host = parse_url($linkhref)['host'];
        $path = parse_url($linkhref)['path'];
        $extension = strtolower(pathinfo($linkhref, PATHINFO_EXTENSION));

        // Mime Type
        switch ($extension) {
          case 'm4a':
            if (!isset($enclosure_aud_length)) {
              $f_enclosure_aud = $scheme.'://'.$host.$path;
              $f_enclosure_aud_mime = 'audio/x-m4a';
              $enclosure_aud_length = ($length = episode_length($f_enclosure_aud)) ? $length : false;
              //$f_itunes_duration = ff_time($f_enclosure_aud);
            }
          break;

          case 'mp3':
            if (!isset($enclosure_aud_length)) {
              $f_enclosure_aud = $scheme.'://'.$host.$path;
              $f_enclosure_aud_mime = 'audio/mpeg';
              $enclosure_aud_length = ($length = episode_length($f_enclosure_aud)) ? $length : false;
              //$f_itunes_duration = ff_time($f_enclosure_aud);
            }
          break;

          case 'mov':
            if (!isset($enclosure_vid_length)) {
              $f_enclosure_vid = $scheme.'://'.$host.$path;
              $f_enclosure_vid_mime = 'video/quicktime';
              $enclosure_vid_length = ($length = episode_length($f_enclosure_vid)) ? $length : false;
              //$f_itunes_duration = ff_time($f_enclosure_vid);
            }
          break;

          case 'mp4':
            if (!isset($enclosure_vid_length)) {
              $f_enclosure_vid = $scheme.'://'.$host.$path;
              $f_enclosure_vid_mime = 'video/mp4';
              $enclosure_vid_length = ($length = episode_length($f_enclosure_vid)) ? $length : false;
              //$f_itunes_duration = ff_time($f_enclosure_vid);
            }
          break;

          case 'm4v':
            if (!isset($enclosure_vid_length)) {
              $f_enclosure_vid = $scheme.'://'.$host.$path;
              $f_enclosure_vid_mime = 'video/x-m4v';
              $enclosure_vid_length = ($length = episode_length($f_enclosure_vid)) ? $length : false;
              //$f_itunes_duration = ff_time($f_enclosure_vid);
            }
          break;

          case 'pdf':
            if (!isset($enclosure_doc_length)) {
              $f_enclosure_doc = $scheme.'://'.$host.$path;
              $f_enclosure_doc_mime = 'application/pdf';
              $enclosure_doc_length = ($length = episode_length($f_enclosure_aud)) ? $length : false;
              $f_itunes_duration = '';
            }
          break;

        } // case switch

      } // foreach $link

    } // media in <a> tags

    $f_itunes_duration = ''; // DEV remove once the ff_time() function is working

  } // WP

  // Enclosed media last, if doing then above WP shouldn't have run
  foreach ($item->enclosure as $enclosure) {
    switch ($enclosure['type']) {
      case 'audio/mpeg':
      case 'audio/mpeg3':
      case 'audio/x-mpeg':
      case 'audio/x-mpeg-3':
      case 'audio/ogg':
      case 'audio/x-wav':
      case 'audio/wav':
      case 'audio/x-flac':
      case 'audio/flac':
        $f_enclosure_aud_length = (filter_var($enclosure['length'], FILTER_VALIDATE_INT, array('min_range' => 1))) ? $enclosure['length'] : 0;
        $f_enclosure_aud_mime = $enclosure['type'];
        $f_enclosure_aud = $enclosure['url'];
        $f_enclosure_aud = ((filter_var($f_enclosure_aud,FILTER_VALIDATE_URL)) && (strlen($f_enclosure_aud) <= 2048))
        ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $f_enclosure_aud),0,2048) : '';
      break;

      case 'video/mp4':
      case 'video/ogg':
      case 'video/x-theora+ogg':
      case 'video/webm':
      case 'video/x-flv':
      case 'video/x-msvideo':
      case 'video/x-matroska':
      case 'video/quicktime':
        $f_enclosure_vid_length = (filter_var($enclosure['length'], FILTER_VALIDATE_INT, array('min_range' => 1))) ? $enclosure['length'] : 0;
        $f_enclosure_vid_mime = $enclosure['type'];
        $f_enclosure_vid = $enclosure['url'];
        $f_enclosure_vid = ((filter_var($f_enclosure_vid,FILTER_VALIDATE_URL)) && (strlen($f_enclosure_vid) <= 2048))
        ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $f_enclosure_vid),0,2048) : '';
      break;

      case 'application/pdf':
      case 'application/x-pdf':
      case 'text/plain':
      case 'text/html':
      case 'application/msword':
      case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
      case 'application/vnd.oasis.opendocument.text':
        $f_enclosure_doc_length = (filter_var($enclosure['length'], FILTER_VALIDATE_INT, array('min_range' => 1))) ? $enclosure['length'] : 0;
        $f_enclosure_doc_mime = $enclosure['type'];
        $f_enclosure_doc = $enclosure['url'];
        $f_enclosure_doc = ((filter_var($f_enclosure_doc,FILTER_VALIDATE_URL)) && (strlen($f_enclosure_doc) <= 2048))
        ? substr(preg_replace("/[^a-zA-Z0-9-_:\/.]/","", $f_enclosure_doc),0,2048) : '';
      break;
    } // switch
  } // foreach

  // GUID
  if ((isset($item->guid)) && ($item->guid != '')) {
    $f_guid = $item->guid;
  } else { // No GUID, so use the alnum from link instead
    $regex_replace = "/[^0-9a-zA-Z_-]/";
    $f_guid = preg_replace($regex_replace,"", $f_link);

    $q = "SELECT guid FROM items WHERE guid='$f_guid'";
    $row = mysqli_query ($srv_dbc, $q);
    if (mysqli_num_rows($row) != 0) { // if: has dup
      $add_num = 0;
      while (mysqli_num_rows($row) != 0) {
        $add_num = $add_num + 1;
        $new_f_guid = $f_guid.'-'.$add_num;
        // In case this gets longer than allowed characters
        $new_f_guid = ($add_num == 1) ? substr($new_f_guid, 0, 93) : $new_f_guid;
        $new_f_guid = ($add_num == 10) ? substr($new_f_guid, 0, 92) : $new_f_guid;
        $new_f_guid = ($add_num == 100) ? substr($new_f_guid, 0, 91) : $new_f_guid;
        $new_f_guid = ($add_num == 1000) ? substr($new_f_guid, 0, 90) : $new_f_guid;
        $new_f_guid = ($add_num == 10000) ? substr($new_f_guid, 0, 89) : $new_f_guid;
        $new_f_guid = ($add_num == 100000) ? substr($new_f_guid, 0, 88) : $new_f_guid;

        // Assign and update
        $f_guid = $new_f_guid;

        // Check again
        $q = "SELECT guid FROM items WHERE guid='$new_f_guid'";
        $row = mysqli_query ($srv_dbc, $q);
        if (mysqli_num_rows($row) == 0) {
          break;
        } // check again break
      } // while
    }

  }

  // iTunes Explicit
  if ($f_itunes_explicit = $itunes->explicit) {
    $f_itunes_explicit = (($f_itunes_explicit == 'false') || ($f_itunes_explicit == 'no')) ? 'false' : 'true';
  } else {
    $f_itunes_explicit = 'true';
  }

  // pubDate
  $f_pubdate = $item->pubDate;
  $f_pubdate_php = (isset($f_pubdate)) ? strtotime($f_pubdate) : strtotime("$timeNow $timeZone");
  $f_pubdate_sql = date("Y-m-d H:i:s", substr($f_pubdate_php, 0, 10));
  // Make sure our variables are not empty
  $f_pubdate_sql = (isset($f_pubdate_sql)) ? $f_pubdate_sql : $curr_time_sql;
  $ad_pubdate_php = (isset($f_pubdate_php)) ? $f_pubdate_php - 1 : $curr_time_php - 1;
  $ad_pubdate_sql = date("Y-m-d H:i:s", substr($ad_pubdate_php, 0, 10));

  // No empty enclosure fields
  $f_enclosure_aud = (isset($f_enclosure_aud)) ? $f_enclosure_aud : '0';
  $f_enclosure_vid = (isset($f_enclosure_vid)) ? $f_enclosure_vid : '0';
  $f_enclosure_doc = (isset($f_enclosure_doc)) ? $f_enclosure_doc : '0';
  $f_enclosure_img_length = (isset($f_enclosure_img_length)) ? $f_enclosure_img_length : '0';
  $f_enclosure_aud_length = (isset($f_enclosure_aud_length)) ? $f_enclosure_aud_length : '0';
  $f_enclosure_vid_length = (isset($f_enclosure_vid_length)) ? $f_enclosure_vid_length : '0';
  $f_enclosure_doc_length = (isset($f_enclosure_doc_length)) ? $f_enclosure_doc_length : '0';
  $f_enclosure_img_mime = (isset($f_enclosure_img_mime)) ? $f_enclosure_img_mime : '0';
  $f_enclosure_aud_mime = (isset($f_enclosure_aud_mime)) ? $f_enclosure_aud_mime : '0';
  $f_enclosure_vid_mime = (isset($f_enclosure_vid_mime)) ? $f_enclosure_vid_mime : '0';
  $f_enclosure_doc_mime = (isset($f_enclosure_doc_mime)) ? $f_enclosure_doc_mime : '0';
  // Required by iTunes, no item entry if no enclosures
  if (($f_enclosure_aud == '0') && ( $f_enclosure_vid == '0') && ($f_enclosure_doc == '0')
  && ($f_enclosure_img_length == '0') && ($f_enclosure_aud_length == '0') && ($f_enclosure_vid_length == '0') && ($f_enclosure_doc_length == '0')
  && ($f_enclosure_img_mime == '0') && ($f_enclosure_aud_mime == '0') && ($f_enclosure_vid_mime == '0') && ($f_enclosure_doc_mime == '0')) {
    continue;
  }

  // No empty other fields
  $f_description = (isset($f_description)) ? $f_description : '';
  $f_link = (isset($f_link)) ? $f_link : '';
  $f_itunes_image = (isset($f_itunes_image)) ? $f_itunes_image : '';
  $f_itunes_title = (isset($f_itunes_title)) ? $f_itunes_title : '';
  $f_itunes_episodetype = (isset($f_itunes_episodetype)) ? $f_itunes_episodetype : 'full';
  $f_itunes_episode = (isset($f_itunes_episode)) ? $f_itunes_episode : 0;
  $f_itunes_season = (isset($f_itunes_season)) ? $f_itunes_season : 0;
  $f_itunes_duration = (isset($f_itunes_duration)) ? $f_itunes_duration : 'empty';
  $f_guid = (isset($f_guid)) ? $f_guid : '';
  $f_itunes_explicit = (isset($f_itunes_explicit)) ? $f_itunes_explicit : 'true';
  $f_pubdate_sql = (isset($f_pubdate_sql)) ? $f_pubdate_sql : $curr_time_sql;

  // Prepare for database entry
  $f_title_esc = mysqli_real_escape_string($agg_dbc, $f_title);
  $f_description_esc = mysqli_real_escape_string($agg_dbc, $f_description);
  $f_link_esc = mysqli_real_escape_string($agg_dbc, $f_link);
  $f_itunes_image_esc = mysqli_real_escape_string($agg_dbc, $f_itunes_image);
  $f_itunes_title_esc = mysqli_real_escape_string($agg_dbc, $f_itunes_title);
  $f_itunes_episodetype_esc = mysqli_real_escape_string($agg_dbc, $f_itunes_episodetype);
  $f_itunes_episode_esc = mysqli_real_escape_string($agg_dbc, $f_itunes_episode);
  $f_itunes_season_esc = mysqli_real_escape_string($agg_dbc, $f_itunes_season);
  $f_itunes_duration_esc = mysqli_real_escape_string($agg_dbc, $f_itunes_duration);
  $f_enclosure_aud_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_aud);
  $f_enclosure_aud_length_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_aud_length);
  $f_enclosure_aud_mime_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_aud_mime);
  $f_enclosure_vid_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_vid);
  $f_enclosure_vid_length_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_vid_length);
  $f_enclosure_vid_mime_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_vid_mime);
  $f_enclosure_doc_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_doc);
  $f_enclosure_doc_length_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_doc_length);
  $f_enclosure_doc_mime_esc = mysqli_real_escape_string($agg_dbc, $f_enclosure_doc_mime);
  $f_guid_esc = mysqli_real_escape_string($agg_dbc, $f_guid);
  $f_itunes_explicit_esc = mysqli_real_escape_string($agg_dbc, $f_itunes_explicit);
  $f_pubdate_sql_esc = mysqli_real_escape_string($agg_dbc, $f_pubdate_sql);

  // Check for dups
  $q = "SELECT guid FROM items
    WHERE project_id='$feed_pid' AND
    (  (enclosure_aud='$f_enclosure_aud_esc' AND NOT enclosure_aud=0)
    OR (enclosure_vid='$f_enclosure_vid_esc' AND NOT enclosure_vid=0)
    OR (enclosure_doc='$f_enclosure_doc_esc' AND NOT enclosure_doc=0)
    OR (guid='$f_guid_esc' AND NOT guid='')
    OR (description='$f_description_esc' AND NOT description='')
    OR (title='$f_title_esc' AND NOT title=NULL) )";
  $r = mysqli_query ($agg_dbc, $q);
  if (mysqli_num_rows($r) == 0) {

    // Database episode entry
    $q = "INSERT INTO items (
      project_id,
      title,
      description,
      link,
      itunes_image,
      itunes_title,
      itunes_episodetype,
      itunes_episode,
      itunes_season,
      itunes_duration,
      enclosure_aud,
      enclosure_aud_length,
      enclosure_aud_mime,
      enclosure_vid,
      enclosure_vid_length,
      enclosure_vid_mime,
      enclosure_doc,
      enclosure_doc_length,
      enclosure_doc_mime,
      guid,
      itunes_explicit,
      pubdate
    ) VALUES (
      '$feed_pid',
      '$f_title_esc',
      '$f_description_esc',
      '$f_link_esc',
      '$f_itunes_image_esc',
      '$f_itunes_title_esc',
      '$f_itunes_episodetype_esc',
      '$f_itunes_episode_esc',
      '$f_itunes_season_esc',
      '$f_itunes_duration_esc',
      '$f_enclosure_aud_esc',
      '$f_enclosure_aud_length_esc',
      '$f_enclosure_aud_mime_esc',
      '$f_enclosure_vid_esc',
      '$f_enclosure_vid_length_esc',
      '$f_enclosure_vid_mime_esc',
      '$f_enclosure_doc_esc',
      '$f_enclosure_doc_length_esc',
      '$f_enclosure_doc_mime_esc',
      '$f_guid_esc',
      '$f_itunes_explicit_esc',
      '$f_pubdate_sql_esc'
    )";
    $r = mysqli_query ($agg_dbc, $q);
    if (mysqli_affected_rows($agg_dbc) != 1) {
      sql_error("$q", 'agg_dbc', "sqle_135");
    } else {
      // Refresh action? Display title
      echo ((isset($refresh_action)) && ($refresh_action == true)) ? '<pre>Pod: '.$f_title.'</pre>' : false;
    }

    // Database ad entry
    $q = "INSERT INTO items (
      project_id,
      pod_ad_id,
      title,
      description,
      link,
      itunes_duration,
      enclosure_aud,
      enclosure_aud_length,
      enclosure_aud_mime,
      guid,
      itunes_explicit,
      pubdate
    ) VALUES (
      '$feed_pid',
      '$pod_ad_id',
      '$pod_ad_title',
      '$pod_ad_description',
      '$pod_ad_link',
      '$pod_ad_duration',
      '$pod_enclosure_aud',
      '$pod_ad_enclosure_aud_length',
      'audio/mpeg',
      'badad-$new_serial_key',
      'false',
      '$ad_pubdate_sql'
    )";
    $r = mysqli_query ($agg_dbc, $q);
    if (mysqli_affected_rows($agg_dbc) != 1) {
      sql_error("$q", 'agg_dbc', "sqle_165");
    } else {
      // Database episode ad key entry
      $q = "INSERT INTO episode_ad_keys (
        serial_key,
        pod_ad_id,
        feed_pid
      ) VALUES (
        '$new_serial_key',
        '$pod_ad_id',
        '$feed_pid'
      )";
      $r = mysqli_query ($agg_dbc, $q);
      if (mysqli_affected_rows($agg_dbc) != 1) {
        sql_error("$q", 'agg_dbc', "sqle_166");
      } else {
        // Refresh action? Display title
        echo ((isset($refresh_action)) && ($refresh_action == true)) ? '<pre>Adv: '.$pod_ad_title.'</pre>' : false;
      }
    }

  // Dup found
  } else {
    // Refresh action? Display title as DUP
    echo ((isset($refresh_action)) && ($refresh_action == true)) ? '<pre>DUP IGNORED: '.$f_title.'</pre>' : false;

    continue;

  } // End database entry & dup check

  // Only syndicate last 72 items
  if ($loopcount > 71) { break; }
  $loopcount ++;

} // For each <item> loop



// DEV
// Move all items beyond 72 to archiveditems

?>
