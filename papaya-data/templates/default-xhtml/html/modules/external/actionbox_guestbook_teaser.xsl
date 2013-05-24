<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <xsl:template match="gbteaser">
    <xsl:if test="text and text != ''">
      <p><xsl:value-of select="text" /></p>
      <xsl:if test="count(entries/entry) &gt; 0">
        <xsl:if test="captions/entries and captions/entries != ''">
          <h3><xsl:value-of select="captions/entries" /></h3>
        </xsl:if>
      </xsl:if>
    </xsl:if>

    <xsl:if test="count(entries/entry) &gt; 0">
      <xsl:for-each select="entries/entry">
        <div class="header">
          <strong><xsl:value-of select="@author" /></strong><br />
          <xsl:text> </xsl:text><xsl:value-of select="captions/at" /><xsl:text> </xsl:text>
          <xsl:call-template name="format-date-time">
            <xsl:with-param name="dateTime" select="@created" />
          </xsl:call-template>
        </div>
        <p><xsl:value-of select="." disable-output-escaping="yes"/></p>
      </xsl:for-each>

      <xsl:if test="show-more-link">
        <a class="showMoreLink" href="{show-more-link}"><xsl:value-of select="captions/show-more" /></a>
      </xsl:if>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>