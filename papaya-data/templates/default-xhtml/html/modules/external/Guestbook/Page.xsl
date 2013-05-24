<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <!-- import content book templates -->
  <xsl:import href="./Ui/Content/Book.xsl" />
  
  <!-- import content dialog templates -->
  <xsl:import href="./Ui/Content/Dialog.xsl" />

  <xsl:template name="content-area">
    <xsl:param name="pageContent" select="content/topic"/>
    <xsl:choose>
      <xsl:when test="$pageContent/@module = 'PapayaModuleGuestbookPage'">
        <xsl:call-template name="module-content-guestbook">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="module-content-default">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="module-content-guestbook">
    <xsl:param name="pageContent" />
    
    <h1><xsl:value-of select="$pageContent/title" /></h1>
    <xsl:if test="$pageContent/text/text() != '' or count($pageContent/text/*) &gt; 0">
      <div class="contentData">
        <xsl:apply-templates select="$pageContent/text/*|$pageContent/text/text()"/>
      </div>
    </xsl:if>
    
    <xsl:call-template name="content-book">
      <xsl:with-param name="book" select="$pageContent/book" />
      <xsl:with-param name="entriesAsList" select="true()" />
    </xsl:call-template>
    
    <xsl:if test="$pageContent/message[@type = 'error']/text()">
      <div class="error">
        <xsl:value-of select="$pageContent/message[@type = 'error']/text()" disable-output-escaping="yes" />
      </div>
    </xsl:if>
    
    <xsl:call-template name="content-dialog">
      <xsl:with-param name="dialog" select="$pageContent/dialog-box" />
    </xsl:call-template>
  </xsl:template>

</xsl:stylesheet>
