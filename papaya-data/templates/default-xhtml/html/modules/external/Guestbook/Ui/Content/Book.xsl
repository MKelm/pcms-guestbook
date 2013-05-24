<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
  
  <xsl:template name="content-book">
    <xsl:param name="book" />
    <xsl:param name="entriesAsList" select="false()" />
    <xsl:param name="breakInEntryHeader" select="false()" />
    <xsl:param name="withEntryTextParagraph" select="false()" />
    <xsl:param name="showMoreLink" select="false()" />
    
    <xsl:if test="count($book/entries/entry) &gt; 0">
      <xsl:call-template name="content-book-entries">
        <xsl:with-param name="entries" select="$book/entries" />
        <xsl:with-param name="entriesAsList" select="$entriesAsList" />
        <xsl:with-param name="breakInEntryHeader" select="$breakInEntryHeader" />
        <xsl:with-param name="withEntryTextParagraph" select="$withEntryTextParagraph" />
      </xsl:call-template>
      
      <xsl:if test="$book/paging">
        <xsl:call-template name="content-book-paging">
          <xsl:with-param name="paging" select="$book/paging" />
        </xsl:call-template>
      </xsl:if>
      
      <xsl:if test="$showMoreLink">
        <a class="showMoreLink" href="{$showMoreLink}"><xsl:value-of select="$showMoreLink/@caption" /></a>
      </xsl:if>
    </xsl:if>
  </xsl:template>
  
  <xsl:template name="content-book-paging">
    <xsl:param name="paging" />
    
    <div class="paging">
      <xsl:for-each select="$paging/page">
        <xsl:if test="not(@type) and @number = 1">
          <xsl:text> [ </xsl:text>
        </xsl:if>
        <a href="{@href}">
          <xsl:choose>
            <xsl:when test="@type and @type = 'first'">
              <xsl:text>&#60;&#60;</xsl:text>
            </xsl:when>
            <xsl:when test="@type and @type = 'previous'">
              <xsl:text>&#60;</xsl:text>
            </xsl:when>
            <xsl:when test="@type and @type = 'next'">
              <xsl:text>&#62;</xsl:text>
            </xsl:when>
            <xsl:when test="@type and @type = 'last'">
              <xsl:text>&#62;&#62;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:choose>
                <xsl:when test="@selected">
                  <strong><xsl:value-of select="@number" /></strong>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="@number" />
                </xsl:otherwise>
              </xsl:choose>
            </xsl:otherwise>
          </xsl:choose>
        </a>
        <xsl:choose>
          <xsl:when test="not(@type) and @number &lt; $paging/@count">
            <xsl:text> </xsl:text>&#183;<xsl:text> </xsl:text>
          </xsl:when>
          <xsl:when test="@type and position() != last()">
            <xsl:text> </xsl:text>
          </xsl:when>
        </xsl:choose>
        <xsl:if test="not(@type) and @number = $paging/@count">
          <xsl:text> ] </xsl:text>
        </xsl:if>
      </xsl:for-each>
    </div>
  </xsl:template>
  
  <xsl:template name="content-book-entries">
    <xsl:param name="entries" />
    <xsl:param name="entriesAsList" select="false()" />
    <xsl:param name="breakInEntryHeader" select="false()" />
    <xsl:param name="withEntryTextParagraph" select="false()" />
    
    <xsl:if test="$entries/@caption and $entries/@caption != ''">
      <h2><xsl:value-of select="$entries/@caption" /></h2>
    </xsl:if>
    
    <xsl:choose>
      <xsl:when test="$entriesAsList">
        <ul>
          <xsl:for-each select="$entries/entry">
            <xsl:call-template name="content-book-entry">
              <xsl:with-param name="entriesAsList" select="$entriesAsList" />
              <xsl:with-param name="breakInEntryHeader" select="$breakInEntryHeader" />
              <xsl:with-param name="withEntryTextParagraph" select="$withEntryTextParagraph" />
            </xsl:call-template>
          </xsl:for-each>
        </ul>
      </xsl:when>
      <xsl:otherwise>
        <xsl:for-each select="$entries/entry">
          <xsl:call-template name="content-book-entry">
            <xsl:with-param name="breakInEntryHeader" select="$breakInEntryHeader" />
            <xsl:with-param name="withEntryTextParagraph" select="$withEntryTextParagraph" />
          </xsl:call-template>
        </xsl:for-each>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="content-book-entry">
    <xsl:param name="entriesAsList" select="false()" />
    <xsl:param name="breakInEntryHeader" select="false()" />
    <xsl:param name="withEntryTextParagraph" select="false()" />
    
    <xsl:choose>
      <xsl:when test="$entriesAsList">
        <li>
          <xsl:call-template name="content-book-entry-header">
            <xsl:with-param name="breakInEntryHeader" select="$breakInEntryHeader" />
          </xsl:call-template>
          <xsl:call-template name="content-book-entry-text">
            <xsl:with-param name="withEntryTextParagraph" select="$withEntryTextParagraph" />
          </xsl:call-template>
        </li>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="content-book-entry-header">
          <xsl:with-param name="breakInEntryHeader" select="$breakInEntryHeader" />
        </xsl:call-template>
        <xsl:call-template name="content-book-entry-text">
          <xsl:with-param name="withEntryTextParagraph" select="$withEntryTextParagraph" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="content-book-entry-header">
    <xsl:param name="breakInEntryHeader" select="false()" />
    
    <div class="header">
      <strong><xsl:value-of select="@author" /></strong>
      <xsl:choose>
        <xsl:when test="$breakInEntryHeader"><br /></xsl:when>
        <xsl:otherwise><xsl:text>, </xsl:text></xsl:otherwise>
      </xsl:choose>
      <em><xsl:call-template name="format-date-time">
        <xsl:with-param name="dateTime" select="@created" />
      </xsl:call-template></em>
    </div>
  </xsl:template>
  
  <xsl:template name="content-book-entry-text">
    <xsl:param name="withEntryTextParagraph" select="false()" />
    <div class="text">
      <xsl:choose>
        <xsl:when test="$withEntryTextParagraph">
          <p><xsl:copy-of select="node()" /></p>
        </xsl:when>
        <xsl:otherwise><xsl:copy-of select="node()" /></xsl:otherwise>
      </xsl:choose>
    </div>
  </xsl:template>

</xsl:stylesheet>
