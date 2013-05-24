<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <!-- import content book templates -->
  <xsl:import href="./Ui/Content/Book.xsl" />

  <xsl:template match="box">
    <xsl:if test="text and text != ''">
      <xsl:copy-of select="text/node()" />
    </xsl:if>
    
    <xsl:call-template name="content-book">
      <xsl:with-param name="book" select="book" />
      <xsl:with-param name="breakInEntryHeader" select="true()" />
      <xsl:with-param name="withEntryTextParagraph" select="true()" />
      <xsl:with-param name="showMoreLink" select="show-more-link" />
    </xsl:call-template>
  </xsl:template>

</xsl:stylesheet>
