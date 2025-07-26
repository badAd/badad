<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="3.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
                xmlns:content="http://purl.org/rss/1.0/modules/content/"
                xmlns:atom="http://www.w3.org/2005/Atom"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:badad="http://badad.one/rss/1.0/">

  <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
  <xsl:template match="/">

    <html xmlns="http://www.w3.org/1999/xhtml">

      <head>
        <title><xsl:value-of select="/rss/channel/title"/> RSS Feed</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>

        <style type="text/css">

          body {
            font-family: sans-serif;
            background-color: #000;
            color: #eee;
          }

          a {
            color: #198eac;
          }

          div.head {
            padding: 1px 20px 10px 20px;
            margin: 5px 10px 10px 10px;
            background-color: #333;
          }

          div.head img.feedimage {
            float: right;
            margin: 10px 0px 10px 10px;
          }

          div.head img.badadcredit {
            margin: 5px 0px 5px;
          }

          div.head img.social {
            margin: 5px 0px 5px;
          }

          div.item {
            background-color: #333;
            margin: 5px 10px 10px 10px;
            padding: 1px 20px 10px 20px;
          }

          div.item div.date {
            font-size: 10pt;
            color: #bbb;
          }

          div.item div.description {
            font-size: 13pt;
          }
          div.item div.description hr {
            border: 1px solid #333;
          }

          div.item div.content {
            font-size: 11pt;
          }

        </style>

      </head>

      <body>

		<!-- Head of rendered page -->
        <div class="head">

          <!-- RSS image -->
          <xsl:if test="/rss/channel/image">
            <a class="head-logo">
              <xsl:attribute name="href">
                <xsl:value-of select="/rss/channel/link"/>
              </xsl:attribute>
              <img class="feedimage">
                <xsl:attribute name="src">
                  <xsl:value-of select="/rss/channel/image/url"/>
                </xsl:attribute>
                <xsl:attribute name="title">
                  <xsl:value-of select="/rss/channel/title"/>
                </xsl:attribute>
              </img>
            </a>
          </xsl:if>

          <!-- RSS title -->
            <h1><xsl:value-of select="/rss/channel/title"/></h1>
            <p><xsl:value-of select="/rss/channel/description"/></p>

          <!-- Link & description -->
          <p>

            <!-- Applies if iTunes podcast image present -->
            <!--
            <xsl:if test="/rss/channel/itunes:image">
              Search in iTunes!
              <br/>
            </xsl:if>
            -->

            <a class="top" target="_blank" title="Homepage">
              <xsl:attribute name="href">
                <xsl:value-of select="/rss/channel/link"/>
              </xsl:attribute>
              <img class="social" src="homepage.png" width="75"/>
              <!-- Homepage: <xsl:value-of select="/rss/channel/description"/><xsl:text disable-output-escaping="yes">&#160;&#8594;</xsl:text> -->
            </a>

            <!-- Stitcher link? -->
            <xsl:if test="/rss/channel/badad:stitcherURL">
              &#160;&#160;
              <a class="top" target="_blank" title="Stitcher">
                <xsl:attribute name="href">
                  <xsl:value-of select="/rss/channel/badad:stitcherURL/@url"/>
                </xsl:attribute>
                <img class="social" src="stitcher.png" width="75"/>
                <!-- <xsl:text disable-output-escaping="yes">Stitcher &#160;&#8594;</xsl:text> -->
              </a>
            </xsl:if>

            <!-- Spotify link? -->
            <xsl:if test="/rss/channel/badad:spotifyURL">
              &#160;&#160;
              <a class="top" target="_blank" title="Spotify">
                <xsl:attribute name="href">
                  <xsl:value-of select="/rss/channel/badad:spotifyURL/@url"/>
                </xsl:attribute>
                <img class="social" src="spotify.png" width="75"/>
                <!-- <xsl:text disable-output-escaping="yes">Spotify &#160;&#8594;</xsl:text> -->
              </a>
            </xsl:if>

            <!-- Apple link? -->
            <xsl:if test="/rss/channel/badad:appleURL">
              &#160;&#160;
              <a class="top" target="_blank" title="Apple podcasts">
                <xsl:attribute name="href">
                  <xsl:value-of select="/rss/channel/badad:appleURL/@url"/>
                </xsl:attribute>
                <img class="social" src="applepodcast.png" width="75"/>
                <!-- <xsl:text disable-output-escaping="yes">Apple &#160;&#8594;</xsl:text> -->
              </a>
            </xsl:if>

          </p>

          <!-- badAd credit -->
          <br/><p><small><i>Proudly aggregated by</i></small>
          <br/><a href="https://badad.one"><img class="badadcredit" src="badAd.png" width="250"/></a>
          <br/><small>Thanks to the amazing sponsors at <a href="https://badad.one">badAd.one</a>!<br/>Text &amp; podcast ads, short &amp; lovely, you can advertise too!</small></p>

        </div>

        <!-- Applies if Atom feed elements are present -->
        <xsl:if test="/rss/channel/atom:link[@rel='alternate']">
          <div>

            <xsl:for-each select="/rss/channel/atom:link[@rel='alternate']">
              <a target="_blank">
                <xsl:attribute name="class">
                  <xsl:value-of select="@icon"/>
                </xsl:attribute>
                <xsl:attribute name="href">
                  <xsl:value-of select="@href"/>
                </xsl:attribute>
                <xsl:value-of select="@title"/>
              </a>
            </xsl:for-each>

          </div>
        </xsl:if>

        <!-- Iterate each feed item -->
        <xsl:for-each select="/rss/channel/item">
          <div class="item">

            <!-- Title with link -->
            <h2 class="item-title">
              <a target="_blank">
                <xsl:attribute name="href">
                  <xsl:value-of select="link"/>
                </xsl:attribute>
                <xsl:value-of select="title"/>
              </a>
            </h2>

            <!-- Date -->
            <div class="date">
              <span><i><xsl:value-of select="pubDate" /></i></span>
              <xsl:if test="itunes:duration">
                <xsl:if test="itunes:duration!='empty'">
                  <xsl:text disable-output-escaping="yes"> &#x2022; </xsl:text>
                  <span><xsl:value-of select="itunes:duration" /></span>
                </xsl:if>
              </xsl:if>
            </div>
            <br/>

            <!-- Duration is also an iTunes-only RSS element, not normal in many other podcast feeds -->
            <xsl:if test="itunes:duration">

              <!-- Audio - one for each mp3 mime type-->
              <xsl:if test="enclosure[@type='audio/mpeg']">
                <audio controls="true" preload="none">
                  <xsl:attribute name="src">
                    <xsl:value-of select="enclosure[@type='audio/mpeg']/@url"/>
                  </xsl:attribute>
                </audio>
              </xsl:if>
              <xsl:if test="enclosure[@type='audio/mpeg3']">
                <audio controls="true" preload="none">
                  <xsl:attribute name="src">
                    <xsl:value-of select="enclosure[@type='audio/mpeg3']/@url"/>
                  </xsl:attribute>
                </audio>
              </xsl:if>
              <xsl:if test="enclosure[@type='audio/x-mpeg']">
                <audio controls="true" preload="none">
                  <xsl:attribute name="src">
                    <xsl:value-of select="enclosure[@type='audio/x-mpeg']/@url"/>
                  </xsl:attribute>
                </audio>
              </xsl:if>
              <xsl:if test="enclosure[@type='audio/x-mpeg-3']">
                <audio controls="true" preload="none">
                  <xsl:attribute name="src">
                    <xsl:value-of select="enclosure[@type='audio/x-mpeg-3']/@url"/>
                  </xsl:attribute>
                </audio>
              </xsl:if>

              <!-- Video -->
              <xsl:if test="enclosure[@type='video/mp4']">
                <video controls="true" preload="none">
                  <xsl:attribute name="src">
                    <xsl:value-of select="enclosure[@type='video/mp4']/@url"/>
                  </xsl:attribute>
                </video>
              </xsl:if>

            </xsl:if>

            <!-- Description -->
            <br/><br/>
            <div class="description">
              <span><xsl:value-of disable-output-escaping="yes" select="description" /></span>
              <br/>
              <hr />
            </div>

            <!-- Content -->
            <!-- DEV NO, this is dup content
            <div class="content">
              <span><xsl:value-of select="content:encoded" /></span>
            </div>
            -->

          </div>
        </xsl:for-each>

      </body>

    </html>

  </xsl:template>
</xsl:stylesheet>
