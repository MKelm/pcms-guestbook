<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:import href="./base/base.xsl" />
<xsl:import href="./lang/language.xsl" />
<xsl:import href="./lang/de.xsl"/>
<xsl:import href="./lang/en.xsl"/>

<xsl:output method="xml" encoding="utf-8" standalone="no" indent="no" omit-xml-declaration="yes" />

  <xsl:param name="PAGE_THEME_PATH" />
  <xsl:param name="PAGE_WEB_PATH" />
  <xsl:param name="PAGE_TITLE" />

  <xsl:template match="gbteaser">

    <xsl:if test="text and text != ''">
      <p><xsl:value-of select="text" /></p>
    </xsl:if>

    <xsl:if test="captions/entries and captions/entries != ''">
      <h3><xsl:value-of select="captions/entries" /></h3>
    </xsl:if>

    <xsl:for-each select="entries/entry">
      <div class="entry">
        <div class="header">
          <strong><xsl:value-of select="@author" /></strong>
          <xsl:text> </xsl:text><xsl:value-of select="captions/at" /><xsl:text> </xsl:text>
          <xsl:call-template name="formatLongDate">
            <xsl:with-param name="isodate" select="@created" />
          </xsl:call-template>
        </div>
        <div class="text">
          <xsl:value-of select="." disable-output-escaping="yes"/>
        </div>
      </div>
    </xsl:for-each>

    <xsl:if test="message[@type = 'error']">
      <div class="error">
        <xsl:value-of select="message[@type = 'error']/text()" disable-output-escaping="yes" />
      </div>
    </xsl:if>

    <xsl:if test="count(entries/entry) &gt; 0 and show-more-link">
      <a class="showMoreLink" href="{show-more-link}"><xsl:value-of select="captions/show-more" /></a>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>

