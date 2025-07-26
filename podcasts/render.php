<?php

// The renders a podcast feed to create an XML feed file

//In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Configs
require_once ('./config.inc.php');
require_once (MYSQL);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// Time
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);
// Time Zone
// $q = "SELECT @@system_time_zone";
// $r = mysqli_query ($agg_dbc, $q);
// $row = mysqli_fetch_array($r);
// $timeZone = "$row[0]";
$timeZone = date("O");

// Validate the Feed slug
if ((isset($_GET['s'])) && ($_GET['s'] != '') && (preg_match('/[A-Za-z0-9\/-]{0,255}$/i', $_GET['s']))) {
  $podcast_slug = preg_replace("/[^A-Za-z0-9\/-]/","-", $_GET['s']); // Rejected to hyphen
  $podcast_slug = preg_replace('/-+/', '-', $podcast_slug); // Only one hyphen
  $podcast_slug = rtrim($podcast_slug, "-"); // No trailing hyphen
  function is_valid_podcast_slug($validating_slug) {
    return (preg_match ('/^[A-Za-z0-9\/-]{0,255}$/i', $validating_slug));
  }
  if (!is_valid_podcast_slug($podcast_slug)) {
    header("Location: https://$siteDomain");
    exit;
  } else {
    function feed_entities($string) {
      $result = htmlentities(html_entity_decode($string));
  		$result = str_replace('©','&#169;',$result); // &copy; for XML use
  		$result = str_replace('™','&#8482;',$result); // &trade; for XML use
  		$result = str_replace('®','&#174;',$result); // &reg; for XML use
  		$result = str_replace('&copy;','&#169;',$result); // &copy; for XML use
  		$result = str_replace('&trade;','&#8482;',$result); // &trade; for XML use
  		$result = str_replace('&reg;','&#174;',$result); // &reg; for XML use
      return $result;
    }

    $podcast_slug = strtolower($podcast_slug);
    // Iterate the meta info from the database
    $q = "SELECT project_id, slug, status, itunes_status, override_feed_settings, title, link, description, copyright, image_url, image_title, image_link, language, lastbuilddate,
      itunes_title, itunes_type, itunes_complete, itunes_image, itunes_author, itunes_summary, itunes_owner_name, itunes_owner_email, itunes_keywords, itunes_explicit, itunes_cat1, itunes_cat2, itunes_cat3, itunes_cat4, itunes_cat5,
    	ba_title, ba_link, ba_description, ba_copyright, ba_image_url, ba_image_title, ba_image_link, ba_language,
    	ba_itunes_title, ba_itunes_type, ba_itunes_complete, ba_itunes_image, ba_itunes_author, ba_itunes_summary, ba_itunes_owner_name, ba_itunes_owner_email, ba_itunes_keywords, ba_itunes_explicit,
    	ba_itunes_cat1, ba_itunes_cat2, ba_itunes_cat3, ba_itunes_cat4, ba_itunes_cat5, date_updated, feed_requested_count,
      stitcher_url, spotify_url, apple_url
      FROM feeds WHERE (BINARY slug='$podcast_slug' OR BINARY old_slug='$podcast_slug') AND status='live'";
    $r = mysqli_query ($agg_dbc, $q);
    if (mysqli_num_rows($r) == 1) {
      while ($row = mysqli_fetch_array($r)) {
        $f_project_id = "$row[0]";
    		$f_slug = "$row[1]";
    		$f_status = "$row[2]";
    		$f_itunes_status = "$row[3]";
    		$f_override_feed_settings = "$row[4]";
    		$f_title = "$row[5]";
    		$f_link = "$row[6]";
    		$f_description = "$row[7]";
    		$f_copyright = "$row[8]";
    		$f_image_url = "$row[9]";
    		$f_image_title = "$row[10]";
    		$f_image_link = "$row[11]";
    		$f_language = "$row[12]";
    		$f_lastbuilddate = "$row[13]";
    		$f_itunes_title = "$row[14]";
    		$f_itunes_type = "$row[15]";
    		$f_itunes_complete = "$row[16]";
    		$f_itunes_image = "$row[17]";
    		$f_itunes_author = "$row[18]";
    		$f_itunes_summary = "$row[19]";
    		$f_itunes_owner_name = "$row[20]";
    		$f_itunes_owner_email = "$row[21]";
    		$f_itunes_keywords = "$row[22]";
    		$f_itunes_explicit = "$row[23]";
    		$f_itunes_cat1 = "$row[24]";
    		$f_itunes_cat2 = "$row[25]";
    		$f_itunes_cat3 = "$row[26]";
    		$f_itunes_cat4 = "$row[27]";
    		$f_itunes_cat5 = "$row[28]";
        $f_ba_title = "$row[29]";
        $f_ba_link = ($row[30] == 'ba-empty') ? '' : "$row[30]";
        $f_ba_description = ($row[31] == 'ba-empty') ? '' :  "$row[31]";
        $f_ba_copyright = ($row[32] == 'ba-empty') ? '' :  "$row[32]";
        $f_ba_image_url = ($row[33] == 'ba-empty') ? '' :  "$row[33]";
        $f_ba_image_title = ($row[34] == 'ba-empty') ? '' :  "$row[34]";
        $f_ba_image_link = ($row[35] == 'ba-empty') ? '' :  "$row[35]";
    		$f_ba_language = "$row[36]";
        $f_ba_itunes_title = "$row[37]";
        $f_ba_itunes_type = "$row[38]";
        $f_ba_itunes_complete = "$row[39]";
        $f_ba_itunes_image = ($row[40] == 'ba-empty') ? '' :  "$row[40]";
        $f_ba_itunes_author = ($row[41] == 'ba-empty') ? '' :  "$row[41]";
        $f_ba_itunes_summary = ($row[42] == 'ba-empty') ? '' :  "$row[42]";
        $f_ba_itunes_owner_name = ($row[43] == 'ba-empty') ? '' :  "$row[43]";
        $f_ba_itunes_owner_email = ($row[44] == 'ba-empty') ? '' :  "$row[44]";
        $f_ba_itunes_keywords = ($row[45] == 'ba-empty') ? '' :  "$row[45]";
        $f_ba_itunes_explicit = "$row[46]";
        $f_ba_itunes_cat1 = ($row[47] == 'ba-empty') ? '' :  "$row[47]";
        $f_ba_itunes_cat2 = ($row[48] == 'ba-empty') ? '' :  "$row[48]";
        $f_ba_itunes_cat3 = ($row[49] == 'ba-empty') ? '' :  "$row[49]";
        $f_ba_itunes_cat4 = ($row[50] == 'ba-empty') ? '' :  "$row[50]";
        $f_ba_itunes_cat5 = ($row[51] == 'ba-empty') ? '' :  "$row[51]";
    		$f_date_updated = "$row[52]";
        $f_feed_requested_count = "$row[53]";
        $f_stitcher_url = "$row[54]";
        $f_spotify_url = "$row[55]";
        $f_apple_url = "$row[56]";
      } // Row

      // Update the request count
      $f_feed_requested_count ++;
      $q = "UPDATE feeds SET feed_requested_count='$f_feed_requested_count' WHERE project_id='$f_project_id'";
  		$r = mysqli_query ($agg_dbc, $q);
  		if (mysqli_affected_rows($agg_dbc) != 1) {
        sql_error("$q", 'agg_dbc', "sqle_167");
      }

      $aq = "INSERT INTO request_feed_analytics (project_id, time_date, time_epoch) VALUES ('$f_project_id', '$timeNow', '$timeNowEpoch')";
      $ar = mysqli_query ($agg_dbc, $aq);
      if (mysqli_affected_rows($agg_dbc) != 1) {
        sql_error("$q", 'agg_dbc', "sqle_149");
      }

    } else { // SQL podcast found
      header("Location: https://$siteDomain");
      exit;
    }

    // Sort out custom vs
    $r_custom = $f_override_feed_settings;
    $r_project_id = $f_project_id;
    $r_lastbuilddate = $f_lastbuilddate;
    $r_title = ($r_custom != 'yes') ? feed_entities($f_title) : feed_entities($f_ba_title);
    $r_link = ($r_custom != 'yes') ? feed_entities($f_link) : feed_entities($f_ba_link);
    $r_description = ($r_custom != 'yes') ? feed_entities($f_description) : feed_entities($f_ba_description);
    $r_copyright = ($r_custom != 'yes') ? feed_entities($f_copyright) : feed_entities($f_ba_copyright);
    $r_image_url = ($r_custom != 'yes') ? feed_entities($f_image_url) : feed_entities($f_ba_image_url);
    $r_image_title = ($r_custom != 'yes') ? feed_entities($f_image_title) : feed_entities($f_ba_image_title);
    $r_image_link = ($r_custom != 'yes') ? feed_entities($f_image_link) : feed_entities($f_ba_image_link);
    $r_language = ($r_custom != 'yes') ? feed_entities($f_language) : feed_entities($f_ba_language);
    $r_it_title = ($r_custom != 'yes') ? feed_entities($f_itunes_title) : feed_entities($f_ba_itunes_title);
    $r_it_type = ($r_custom != 'yes') ? feed_entities($f_itunes_type) : feed_entities($f_ba_itunes_type);
    $r_it_complete = ($r_custom != 'yes') ? feed_entities($f_itunes_complete) : feed_entities($f_ba_itunes_complete);
    $r_it_image = ($r_custom != 'yes') ? feed_entities($f_itunes_image) : feed_entities($f_ba_itunes_image);
    $r_it_author = ($r_custom != 'yes') ? feed_entities($f_itunes_author) : feed_entities($f_ba_itunes_author);
    //$r_it_summary = ($r_custom != 'yes') ? feed_entities($f_itunes_summary) : feed_entities($f_ba_itunes_summary);
    $r_it_owner = ($r_custom != 'yes') ? feed_entities($f_itunes_owner_name) : feed_entities($f_ba_itunes_owner_name);
    $r_it_email = ($r_custom != 'yes') ? feed_entities($f_itunes_owner_email) : feed_entities($f_ba_itunes_owner_email);
    $r_it_keywords = ($r_custom != 'yes') ? feed_entities($f_itunes_keywords) : feed_entities($f_ba_itunes_keywords);
    $r_it_explicit = ($r_custom != 'yes') ? feed_entities($f_itunes_explicit) : feed_entities($f_ba_itunes_explicit);
    $r_it_cat1 = ($r_custom != 'yes') ? feed_entities($f_itunes_cat1) : feed_entities($f_ba_itunes_cat1);
    $r_it_cat2 = ($r_custom != 'yes') ? feed_entities($f_itunes_cat2) : feed_entities($f_ba_itunes_cat2);
    $r_it_cat3 = ($r_custom != 'yes') ? feed_entities($f_itunes_cat3) : feed_entities($f_ba_itunes_cat3);
    $r_it_cat4 = ($r_custom != 'yes') ? feed_entities($f_itunes_cat4) : feed_entities($f_ba_itunes_cat4);
    $r_it_cat5 = ($r_custom != 'yes') ? feed_entities($f_itunes_cat5) : feed_entities($f_ba_itunes_cat5);

      // Render feed start
      // This is an XML document, say so first!
      header('Content-type: text/xml');

      // Header of feed
      $itunes_xmlns = "http://www.itunes.com/dtds/podcast-1.0.dtd"; // We use this often
      echo <<<EOF
      <?xml version="1.0" encoding="UTF-8"?>
      <?xml-stylesheet type="text/xsl" href="https://$podcastServeDomain/rss.xsl" ?>
      <rss version="2.0"
        xmlns:itunes="$itunes_xmlns"
        xmlns:media="http://search.yahoo.com/mrss/"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:atom="http://www.w3.org/2005/Atom"
      	xmlns:dc="http://purl.org/dc/elements/1.1/"
      	xmlns:badad="http://badad.one/rss/1.0/"
        >
      <channel>
      	<title><![CDATA[$r_title]]></title>
      	<link>$r_link</link>
        <atom:link href="$r_link" rel="self" type="application/rss+xml" />
      	<language>$r_language</language>
      	<image>
      		<url>$r_image_url</url>
      		<title><![CDATA[$r_image_title]]></title>
      		<link>$r_image_link</link>
      	</image>
      	<copyright><![CDATA[$r_copyright]]></copyright>
        <itunes:author><![CDATA[$r_it_author]]></itunes:author>
      	<description><![CDATA[$r_description]]></description>
      	<itunes:title>$r_it_title</itunes:title>
      	<itunes:type>$r_it_type</itunes:type>
        <itunes:owner>
          <itunes:name><![CDATA[$r_it_owner]]></itunes:name>
          <itunes:email>$r_it_email</itunes:email>
        </itunes:owner>
      	<itunes:image href="$r_it_image"/>
        <media:keywords>$r_it_keywords</media:keywords>
      	<itunes:keywords>$r_it_keywords</itunes:keywords>
        <itunes:explicit>$r_it_explicit</itunes:explicit>
      EOF;

      // Podcast links
      echo '';
      echo (($f_stitcher_url != NULL) && ($f_stitcher_url != '')) ? '<badad:stitcherURL url="'.$f_stitcher_url.'"/>' : false;
      echo (($f_spotify_url != NULL) && ($f_spotify_url != '')) ? '<badad:spotifyURL url="'.$f_spotify_url.'"/>' : false;
      echo (($f_apple_url != NULL) && ($f_apple_url != '')) ? '<badad:appleURL url="'.$f_apple_url.'"/>' : false;

      // Categories
      if (str_contains($r_it_cat1, '::')) {
        $cat1 = strtok($r_it_cat1, '::');
        $cat2 = preg_replace("/$cat1::/i", "", $r_it_cat1);
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$cat1.'/'.$cat2.'</media:category>
        <itunes:category text="'.$cat1.'">
          <itunes:category text="'.$cat2.'"/>
        </itunes:category>';
      } elseif (($r_it_cat1 != '') && ($r_it_cat1 != NULL)) {
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$r_it_cat1.'</media:category>
        <itunes:category text="'.$r_it_cat1.'"/>';
      }
      if (str_contains($r_it_cat2, '::')) {
        $cat1 = strtok($r_it_cat2, '::');
        $cat2 = preg_replace("/$cat1::/i", "", $r_it_cat2);
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$cat1.'/'.$cat2.'</media:category>
        <itunes:category text="'.$cat1.'">
          <itunes:category text="'.$cat2.'"/>
        </itunes:category>';
      } elseif (($r_it_cat2 != '') && ($r_it_cat2 != NULL)) {
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$r_it_cat2.'</media:category>
        <itunes:category text="'.$r_it_cat2.'"/>';
      }
      if (str_contains($r_it_cat3, '::')) {
        $cat1 = strtok($r_it_cat3, '::');
        $cat2 = preg_replace("/$cat1::/i", "", $r_it_cat3);
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$cat1.'/'.$cat2.'</media:category>
        <itunes:category text="'.$cat1.'">
          <itunes:category text="'.$cat2.'"/>
        </itunes:category>';
      } elseif (($r_it_cat3 != '') && ($r_it_cat3 != NULL)) {
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$r_it_cat3.'</media:category>
        <itunes:category text="'.$r_it_cat3.'"/>';
      }
      if (str_contains($r_it_cat4, '::')) {
        $cat1 = strtok($r_it_cat4, '::');
        $cat2 = preg_replace("/$cat1::/i", "", $r_it_cat4);
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$cat1.'/'.$cat2.'</media:category>
        <itunes:category text="'.$cat1.'">
          <itunes:category text="'.$cat2.'"/>
        </itunes:category>';
      } elseif (($r_it_cat4 != '') && ($r_it_cat4 != NULL)) {
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$r_it_cat4.'</media:category>
        <itunes:category text="'.$r_it_cat4.'"/>';
      }
      if (str_contains($r_it_cat5, '::')) {
        $cat1 = strtok($r_it_cat5, '::');
        $cat2 = preg_replace("/$cat1::/i", "", $r_it_cat5);
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$cat1.'/'.$cat2.'</media:category>
        <itunes:category text="'.$cat1.'">
          <itunes:category text="'.$cat2.'"/>
        </itunes:category>';
      } elseif (($r_it_cat5 != '') && ($r_it_cat5 != NULL)) {
        echo '
        <media:category scheme="'.$itunes_xmlns.'">'.$r_it_cat5.'</media:category>
        <itunes:category text="'.$r_it_cat5.'"/>';
      }
      echo '
      ';

      // Build date
      $r_timezone = date_default_timezone_get();
      $r_build = date("D, M j Y G:i:s", strtotime($r_lastbuilddate));
      echo <<<EOF
        <lastBuildDate>$r_build $timeZone</lastBuildDate>
      EOF;

      // Final iTunes tags (complete setting, tell subscribers to change should the slug get updated)
      echo <<<EOF
      <itunes:complete>$r_it_complete</itunes:complete>
      <itunes:new-feed-url>https://$podcastServeDomain/$podcast_slug</itunes:new-feed-url>
      EOF;

      // Atom elements
      $php_build = strtotime("$r_build $r_timezone");
      $atom_date = date(DATE_ATOM, $php_build);
      $regex_replace = "/[^0-9a-zA-Z_-]/";
      $atom_id = preg_replace($regex_replace,"", $podcastServeDomain.$podcast_slug);
      echo <<<EOF
      <updated>{$atom_date}</updated>
      <subtitle><![CDATA[$r_description]]></subtitle>
      <author>
        <name><![CDATA[$r_it_author]]></name>
      </author>
      <id>$atom_id</id>
      <rights>$r_copyright</rights>
      EOF;

      // Finish head
      echo '
      ';

      // Loop & render each feed item
      $q = "SELECT
       title, description, pubdate, link, itunes_image, itunes_title, itunes_episodetype, itunes_episode, itunes_season, itunes_duration, guid, itunes_explicit,
       enclosure_aud, enclosure_vid, enclosure_doc, enclosure_aud_length, enclosure_vid_length, enclosure_doc_length, enclosure_aud_mime, enclosure_vid_mime, enclosure_doc_mime
       FROM items WHERE project_id='$r_project_id' ORDER BY pubdate DESC";
      $r = mysqli_query ($agg_dbc, $q);
      if (mysqli_num_rows($r) > 0) {
        while ($row = mysqli_fetch_array($r)) {
          $i_title = feed_entities("$row[0]");
          $i_description = feed_entities("$row[1]");
          $i_pubdate = feed_entities("$row[2]");
          $i_link = feed_entities("$row[3]");
          $i_itunes_image = feed_entities("$row[4]");
          $i_itunes_title = feed_entities("$row[5]");
          $i_itunes_episodetype = feed_entities("$row[6]");
          $i_itunes_episode = feed_entities("$row[7]");
          $i_itunes_season = feed_entities("$row[8]");
          $i_itunes_duration = feed_entities("$row[9]");
          $i_guid = feed_entities("$row[10]");
          $i_itunes_explicit = feed_entities("$row[11]");
          $i_enclosure_aud = feed_entities("$row[12]");
          $i_enclosure_vid = feed_entities("$row[13]");
          $i_enclosure_doc = feed_entities("$row[14]");
          $i_enclosure_aud_length = feed_entities("$row[15]");
          $i_enclosure_vid_length = feed_entities("$row[16]");
          $i_enclosure_doc_length = feed_entities("$row[17]");
          $i_enclosure_aud_mime = feed_entities("$row[18]");
          $i_enclosure_vid_mime = feed_entities("$row[19]");
          $i_enclosure_doc_mime = feed_entities("$row[20]");

          // Empty durations
          $i_itunes_duration = ($i_itunes_duration == 'empty') ? '' : $i_itunes_duration;
          // Date
          $i_date = date("D, M j Y G:i:s", strtotime($i_pubdate));
          // Is GUID a URL?
          $isPermaLink = (filter_var($i_guid,FILTER_VALIDATE_URL)) ? 'true' : 'false';

          // echo the <item>
          echo <<<EOF
          \n
          <item>
            <title>$i_title</title>
            <link>$i_link</link>
            <itunes:image href="$i_itunes_image" />
            <itunes:title>$i_itunes_title</itunes:title>
            <itunes:episodeType>$i_itunes_episodetype</itunes:episodeType>
            <itunes:episode>$i_itunes_episode</itunes:episode>
            <itunes:season>$i_itunes_season</itunes:season>
            <guid isPermaLink="$isPermaLink">$i_guid</guid>
            <pubDate>$i_date $timeZone</pubDate>
            <itunes:duration>$i_itunes_duration</itunes:duration>
            <author><![CDATA[$r_it_author]]></author>
            <description><![CDATA[$i_description]]></description>
            <content:encoded><![CDATA[$i_description]]></content:encoded>
            <itunes:author><![CDATA[$r_it_author]]></itunes:author>
            <itunes:explicit>$i_itunes_explicit</itunes:explicit>
          EOF;

          // Content variable
          $media_content = '';

        if ($i_enclosure_aud != 0) {

          echo <<<EOF
          \n  <enclosure url="$i_enclosure_aud" length="$i_enclosure_aud_length" type="$i_enclosure_aud_mime" />
          EOF;

          if ($i_enclosure_aud_mime == "audio/mpeg") {
            $media_content .=  '<p><audio controls><source src="'.$i_enclosure_aud.'" type="audio/mpeg"></audio></p>';
            echo <<<EOF
            \n  <itunes:duration>$i_itunes_duration</itunes:duration>
            EOF;
          } elseif ($i_enclosure_aud_mime == "audio/ogg") {
            $media_content .=  '<p><audio controls><source src="'.$i_enclosure_aud.'" type="audio/ogg"></audio></p>';
          } else {
            $media_content .=  '<p><a href="'.$i_enclosure_aud.'" target="_blank">Audio: '.$i_title.'</a></p>';
          }

        }

        if ($i_enclosure_vid != 0) {

          echo <<<EOF
          \n  <enclosure url="$i_enclosure_vid" length="$i_enclosure_vid_length" type="$i_enclosure_vid_mime" />
          EOF;

          if ($i_enclosure_vid_mime == "video/mp4") {

            $media_content .=  '<p><video width="450" controls><source src="'.$i_enclosure_vid.'" type="video/mp4"></video></p>';
            echo <<<EOF
            \n  <itunes:duration>$i_itunes_duration</itunes:duration>
            EOF;
          } elseif ($i_enclosure_vid_mime == "video/ogg") {
            $media_content .=  '<p><video width="450" controls><source src="'.$i_enclosure_vid.'" type="video/ogg"></video></p>';
          } else {
            $media_content .=  '<p><a href="'.$i_enclosure_vid.'" target="_blank">Video: '.$i_title.'</a></p>';
          }

        }

        if ($i_enclosure_doc != 0) {

          $media_content .=  '<p><a href="'.$i_enclosure_doc.'" target="_blank">Document: '.$i_title.'</a></p>';
          echo <<<EOF
          \n  <enclosure url="$i_enclosure_doc" length="$i_enclosure_doc_length" type="$i_enclosure_doc_mime" />
          EOF;

        }

          // DC & Content (for posts)
          echo <<<EOF
          \n
          <dc:creator><![CDATA[$r_it_author]]></dc:creator>
          <content:encoded><![CDATA[
            <p>$i_description</p>
            $media_content
          ]]></content:encoded>
          EOF;

          echo <<<EOF
          \n</item>
          EOF;

          // echo the Atom <entry>
          $php_i_build = strtotime("$i_date $r_timezone");
          $atom_updated = date(DATE_ATOM, $php_i_build);
          echo <<<EOF
          \n
          <entry>
            <title>$i_title</title>
            <link href="$i_link"/>
            <id>$i_guid</id>
            <updated>$atom_updated</updated>
            <summary><![CDATA[$i_description]]></summary>
          </entry>
          EOF;

        } // Each item/entry

      // SQL rows exist
      } else {
        echo <<<EOF
        \n
        <item>
          <title>That's all, folks!</title>
        </item>
        <entry>
          <title>That's all, folks!</title>
        </entry>
        EOF;
      }

      // Close feed
      echo <<<EOF
      \n
      </channel>
      </rss>
      EOF;

  } // Valid slug
} // Valid POST

?>
