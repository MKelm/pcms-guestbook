<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

  <xsl:template name="content-area">
    <xsl:param name="pageContent" select="content/topic"/>
    <xsl:choose>
      <xsl:when test="$pageContent/@module = 'content_guestbook'">
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
        <xsl:apply-templates select="$pageContent/text" />
      </div>
      <h2><xsl:value-of select="$pageContent/captions/entries" /></h2>
    </xsl:if>


    <ul>
    <xsl:for-each select="$pageContent/entries/entry">
      <li>
        <div class="header">
          <strong><xsl:value-of select="@author" /></strong>
          <xsl:text> </xsl:text><xsl:value-of select="$pageContent/captions/at" /><xsl:text> </xsl:text>
          <xsl:call-template name="format-date-time">
            <xsl:with-param name="dateTime" select="@created" />
          </xsl:call-template>
        </div>
        <div class="text">
          <xsl:apply-templates select="." />
        </div>
      </li>
    </xsl:for-each>
    </ul>

    <div id="bottomnav">
      <xsl:apply-templates select="$pageContent/nav">
        <xsl:with-param name="pageContent" select="$pageContent" />
      </xsl:apply-templates>
    </div>

    <xsl:if test="$pageContent/message[@type = 'error']">
      <div class="error">
        <xsl:value-of select="$pageContent/message[@type = 'error']/text()" disable-output-escaping="yes" />
      </div>
    </xsl:if>

    <xsl:apply-templates select="$pageContent/dialog">
      <xsl:with-param name="pageContent" select="$pageContent" />
    </xsl:apply-templates>
  </xsl:template>

  <xsl:template match="nav">
    <xsl:param name="pageContent" />
    <xsl:if test="count(item) &gt; 1">
      <xsl:if test="item[@selected='selected']/preceding::item/@href">
        <a href="{item[@selected='selected']/preceding::item/@href}"><xsl:value-of select="$pageContent/captions/previous" /></a>
        <xsl:text> </xsl:text>
      </xsl:if>
      <xsl:text>[ </xsl:text>
      <xsl:for-each select="item">
        <xsl:choose>
          <xsl:when test="@selected">
            <strong><xsl:value-of select="position()" /></strong>
          </xsl:when>
          <xsl:otherwise>
            <a href="{@href}" title="gehe zu Seite {position()}"><xsl:value-of select="position()" /></a>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="position() != last()">
        &#183;
        </xsl:if>
      </xsl:for-each>
      <xsl:text> ]</xsl:text>
      <xsl:if test="item[@selected='selected']/following::item/@href">
        <xsl:text> </xsl:text>
        <a href="{item[@selected='selected']/following::item/@href}"><xsl:value-of select="$pageContent/captions/next" /></a>
      </xsl:if>
    </xsl:if>
  </xsl:template>

  <xsl:template match="dialog">
    <xsl:param name="pageContent" />
    <form class="mail" method="post">
      <xsl:copy-of select="@action" />
      <fieldset>
        <xsl:copy-of select="input[@type='hidden']" />
        <xsl:for-each select="lines/line">
          <div class="field">
            <label for="{@fid}"><xsl:value-of select="@caption" disable-output-escaping="yes" /></label>
            <xsl:choose>
              <xsl:when test="textarea">
                <textarea class="text" id="{@fid}">
                  <xsl:copy-of select="textarea/@*[name() = 'name']" />
                  <xsl:value-of select="textarea" disable-output-escaping="yes" />
                  <xsl:text> </xsl:text>
                </textarea>
              </xsl:when>
              <xsl:otherwise>
                <input class="text" id="{@fid}">
                  <xsl:copy-of select="input/@*[name() = 'type' or name() = 'name' or name() = 'maxlength']" />
                </input>
              </xsl:otherwise>
            </xsl:choose>
          </div>
        </xsl:for-each>
      </fieldset>
      <fieldset class="button">
        <button type="submit" name="submit"><xsl:value-of select="$pageContent/captions/submit" /></button>
      </fieldset>
    </form>
  </xsl:template>

</xsl:stylesheet>
