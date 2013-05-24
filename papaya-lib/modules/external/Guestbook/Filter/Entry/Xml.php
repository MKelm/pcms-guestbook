<?php
/**
* Filter class for entry xml strings.
*
* @copyright 2013 by Martin Kelm
* @link http://idx.shrt.ws
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/

/**
* Filter class for entry xml strings.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookFilterEntryXml extends PapayaFilterXml {
  
  /**
  * Current content language id
  * @var integer
  */
  protected $_languageId = NULL;
  
  /**
  * A list of allowed xml tags
  * @var array
  */
  protected $_allowedXmlTags = array('strong', 'b', 'em', 'i', 'tt');
  
  /**
  * Cache for block spam status to call spam filter once
  * @var boolean
  */
  protected $_blockSpam = NULL;
  
  /**
  * Construct object and set language id parameter
  * 
  * @var integer $languageId
  */
  public function __construct($languageId = 0) {
    PapayaUtilConstraints::assertInteger($languageId);
    $this->_languageId = $languageId;
  }
  
  /**
  * Check the value if it's a entry xml string, if not throw an exception.
  *
  * @throws PapayaFilterExceptionCharacterInvalid
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    $validated = parent::validate($value);
    
    if ($this->_languageId > 0 && $validated && is_null($this->_blockSpam)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_spamfilter.php');
      $filter = &base_spamfilter::getInstance();
      $probability = $filter->check($value, $this->_languageId);
      $this->_blockSpam = $probability['spam'] && 
        $this->papaya()->options->get('PAPAYA_SPAM_BLOCK', FALSE);
    }
    if ($this->_blockSpam) {
      include_once(dirname(__FILE__).'/../Exception/Spam/Value.php');
      throw new GuestbookFilterExceptionSpamValue();
    }
    
    return $validated;
  }
  
  /**
  * The filter function is used to read an input value if it is valid.
  *
  * @param string $value
  * @return string
  */
  public function filter($value) {
    $value = parent::filter($value);
    $value = $this->_removeEvilXmlTags($value);
    return $value;
  }
  
  /**
  * Remove evil xml tags
  *
  * @param string $source
  * @return string
  */
  protected function _removeEvilXmlTags($value) {
    return preg_replace_callback(
      '~<(/?(.*?))>|[<>]~i', array($this, 'escapeXmlTags'), $value
    );
  }
  
  /**
  * Escape unknown tags
  *
  * @param array $match
  * @return string
  */
  public function escapeXmlTags($match) {
    if (isset($match[2]) && $match[2] != '' &&
        in_array($match[2], $this->_allowedXmlTags)) {
      $result = '<'.$match[1].'>';
    } else {
      $result = PapayaUtilStringXml::escape($match[0]);
    }
    return $result;
  }
}
