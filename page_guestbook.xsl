<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:import href="./base/base.xsl" />
<xsl:import href="./lang/language.xsl" />
<xsl:import href="./lang/de.xsl"/>
<xsl:import href="./lang/en.xsl"/>

<xsl:import href="./page_general.xsl" />
<xsl:import href="./content/topic.xsl" />

<xsl:output method="xml" encoding="utf-8" standalone="yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" indent="yes" omit-xml-declaration="yes" />

<xsl:param name="PAGE_THEME_PATH" />
<xsl:param name="PAGE_WEB_PATH" />
<xsl:param name="PAGE_TITLE" />

<xsl:template name="cssfiles">
</xsl:template>

<xsl:template name="content_area">
  <xsl:variable name="module" select="content/topic/@module"/>
  <xsl:choose>
    <xsl:when test="$module = 'content_guestbook'">
      <xsl:call-template name="content_topic" />
      <xsl:call-template name="content_guestbook"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="content_default"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="content_guestbook">
    <xsl:param name="content" select="/page/content/topic" />
    <h2>Einträge</h2>
    <xsl:for-each select="$content/entries/entry">
      <div class="entry">
        <div class="header">
          <strong><xsl:value-of select="@author" /></strong>
          <xsl:text> am </xsl:text>
          <xsl:call-template name="formatLongDate">
            <xsl:with-param name="isodate" select="@created" />
          </xsl:call-template>
        </div>
        <div class="text">
          <xsl:value-of select="." disable-output-escaping="yes"/>
        </div>
      </div>
    </xsl:for-each>
    <div id="bottomnav">
      <xsl:apply-templates select="$content/nav" />
    </div>
    <xsl:if test="$content/message[@type = 'error']">
      <div class="error">
        <xsl:value-of select="$content/message[@type = 'error']/text()" disable-output-escaping="yes" />
      </div>
    </xsl:if>
    <xsl:apply-templates select="$content/dialog" />
    <p class="note">Wichtig: Alle Felder müssen ausgefüllt sein.</p>
  </xsl:template>

  <xsl:template match="nav">
    <xsl:if test="item[@selected='selected']/preceding::item/@href">
      &#171;
      <a href="{item[@selected='selected']/preceding::item/@href}">vorherige Seite</a>
      &#183;
    </xsl:if>
    <xsl:for-each select="item">
      <xsl:variable name="nummer">
        <xsl:number level="single" count="item" from="./" format="1" />
      </xsl:variable>
      <xsl:choose>
        <xsl:when test="@selected">
          <xsl:value-of select="$nummer" />
        </xsl:when>
        <xsl:otherwise>
          <a href="{@href}" title="gehe zu Seite {$nummer}"><xsl:value-of select="$nummer" /></a>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="position() != last()">
      &#183;
      </xsl:if>
    </xsl:for-each>
    <xsl:if test="item[@selected='selected']/following::item/@href">
    &#183;  <a href="{item[@selected='selected']/following::item/@href}">nächste Seite</a>
      &#187;
    </xsl:if>
  </xsl:template>

  <xsl:template match="dialog">
    <form class="entry" method="post">
      <xsl:copy-of select="@action" />
      <fieldset>
        <xsl:copy-of select="input[@type='hidden']" />
        <xsl:for-each select="lines/line">
          <div class="line">
            <label for="{@fid}"><xsl:value-of select="@caption" disable-output-escaping="yes" /></label>
            <xsl:choose>
              <xsl:when test="textarea">
                <textarea class="text" id="{@fid}">
                  <xsl:copy-of select="textarea/@*[name() != 'fid' and name() != 'wrap']" />
                  <xsl:value-of select="textarea" disable-output-escaping="yes" />
                  <xsl:text> </xsl:text>
                </textarea>
              </xsl:when>
              <xsl:otherwise>
                <input class="text" id="{@fid}">
                  <xsl:copy-of select="input/@*[name() != 'fid']" />
                </input>
              </xsl:otherwise>
            </xsl:choose>
          </div>
        </xsl:for-each>
        <input type="submit" name="submit" value="Abschicken" />
      </fieldset>
    </form>
  </xsl:template>

</xsl:stylesheet>
