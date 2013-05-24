<?php
/**
* Guestbook output class
*
* @copyright 2008-2010 by Martin Kelm
* @link http://www.idxsolutions.de/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package module_guestbook
* @author Martin Kelm <kelm@idxsolutions.de>
*/

/**
* Basic guestbook class
*/
require_once(dirname(__FILE__).'/base_guestbook.php');


/**
* Guestbook output class
*
* @package module_guestbook
* @author Martin Kelm <kelm@idxsolutions.de>
*/
class output_guestbook extends base_guestbook {

  /**
   * Override some class variables if a parent object is available.
   * The module can act like a part of the content module and use it's
   * enviroment.
   *
   * @param reference $parentObject parent content module
   */
  public function __construct(&$parentObj = NULL) {
    parent::__construct($parentObj);

    if (!empty($parentObj)) {
      $this->paramName = $parentObj->paramName;
      $this->baseLink = $parentObj->baseLink;
      $this->params = &$parentObj->params;
      $this->msgs = &$parentObj->msgs;
      if (isset($parentObj->parentObj) &&
          method_exists($parentObj->parentObj, 'getContentLanguage')) {
        $this->langId = $parentObj->parentObj->getContentLanguageId();
      }
    }
  }

  /**
   * Gets a combo selection of available guestbooks to show in the content
   * configuration panel.
   *
   * @param string $paramName Parameter name of the current content module
   * @param string $fieldName Name of the related edit field
   * @param string $data Previous selected value
   * @return string XML
   */
  public function getGuestbookCombo($paramName, $fieldName, $data) {
    $this->loadBooks();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($paramName),
      papaya_strings::escapeHTMLChars($fieldName)
    );

    if (isset($this->books) && is_array($this->books)) {
      foreach ($this->books as $book) {
        if (isset($book) && is_array($book)) {
          $selected = ($book['gb_id'] == $data['id']) ?
            ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%d" %s>%s</option>',
            (int)$book['gb_id'],
            $selected,
            papaya_strings::escapeHTMLChars($book['title'])
          );
        }
      }
    }
    $result .= '</select>'.LF;

    return $result;
  }

  /**
   * Get guestbook entries xml.
   *
   * @param int $bookId
   * @param int $maxEntries
   * @param int $offset optional
   * @return string $result XML
   */
  public function getEntriesXML($bookId, $maxEntries, $offset = 0) {
    $this->loadEntries($bookId, $maxEntries, $offset);
    $result = '';

    if (isset($this->entries) &&
        is_array($this->entries) && count($this->entries) > 0) {
      $result = '<entries>';

      while ($entry = each($this->entries)) {
        if (isset($entry[1]) && is_array($entry[1]) && count($entry[1]) > 0) {
          $entry = $entry[1];
          $date = date('Y-m-d H:i:s', $entry['entry_created']);
          $text = $this->removeEvilTags(
            $entry['entry_text'], '<strong></strong><b></b><i></i><a></a>'
          );
          $result .= sprintf(
            '<entry id="%d" author="%s" email="%s" created="%s">%s</entry>'.LF,
            $entry['entry_id'],
            papaya_strings::escapeHTMLChars($entry['author']),
            papaya_strings::escapeHTMLChars($entry['entry_created']),
            papaya_strings::escapeHTMLChars($date),
            $this->getXHTMLString($text, TRUE)
          );
        }
      }

      $result .= '</entries>';
    }

    return $result;
  }

  /**
  * Remove evil tags
  *
  * @param string $source
  * @return string
  */
  public function removeEvilTags($source) {
    return preg_replace_callback(
      '~<(/?(.*?))>|[<>]~i', array($this, 'escapeHTMLTags'), $source
    );
  }


  /**
  * Escape unknown tags
  *
  * @param string $tagSource
  * @return string
  */
  public function escapeHTMLTags($match) {
    if (isset($match[2]) && $match[2] != '' &&
        in_array($match[2], $this->allowedTags)) {
      $result = '<'.$match[1].'>';
    } else {
      $result = papaya_strings::escapeHTMLChars($match[0]);
    }
    return $result;
  }

  /**
   * Get message xml
   *
   * @param string $type message type
   * @param string $msg message text
   * @return string xml
   */
  public function getMessageXML($type, $msg) {
    $result = '';

    if (!empty($type) && !empty($msg)) {
      $result .= sprintf(
        '<message type="%s">%s</message>'.LF,
        papaya_strings::escapeHTMLChars($type),
        $this->getXHTMLString($msg)
      );
    }

    return $result;
  }
}
?>