<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
  
  <xsl:template name="content-dialog">
    <xsl:param name="dialog" />
    
    <form class="mail">
      <xsl:copy-of select="$dialog/@action" />
      <xsl:copy-of select="$dialog/@method" />
      <fieldset>
        <xsl:copy-of select="$dialog/input[@type='hidden']" />
        <xsl:for-each select="$dialog/field">
          <div class="field">
            <label for="{@id}"><xsl:value-of select="@caption" /></label>
            <xsl:choose>
              <xsl:when test="textarea">
                <textarea class="text">
                  <xsl:copy-of select="textarea/@*" />
                  <xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
                </textarea>
              </xsl:when>
              <xsl:when test="input">
                <input class="text">
                  <xsl:copy-of select="input/@*" />
                  <xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
                </input>
              </xsl:when>
            </xsl:choose>
          </div>
        </xsl:for-each>
      </fieldset>
      <fieldset class="button">
        <button style="float: {$dialog/button/@align}">
          <xsl:copy-of select="$dialog/button/@type" />
          <xsl:value-of select="$dialog/button/text()" />
        </button>
        <xsl:call-template name="float-fix" />
      </fieldset>
    </form>
  </xsl:template>
  
</xsl:stylesheet>
