<?xml version="1.0"?> 
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:import href="./base/base.xsl" />
<xsl:import href="./lang/language.xsl" />
<xsl:import href="./lang/de.xsl"/>
<xsl:import href="./lang/en.xsl"/>

<xsl:import href="./page_general.xsl" />
<xsl:import href="./content/topic.xsl" />
<xsl:import href="./content/guestbook.xsl" />

<xsl:output method="xml" encoding="utf-8" standalone="yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" indent="yes" omit-xml-declaration="yes" /> 

<xsl:param name="PAGE_THEME_PATH" />
<xsl:param name="PAGE_WEB_PATH" />
<xsl:param name="PAGE_TITLE" />

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

<xsl:template name="cssfiles">
</xsl:template>

</xsl:stylesheet>
