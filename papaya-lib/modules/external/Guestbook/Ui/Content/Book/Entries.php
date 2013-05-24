<?php
/**
* A list of book entries.
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
* A list of book entries.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookUiContentBookEntries extends PapayaUiControlCollection {

  /**
  * Only {@see PapayaModuleGuestbookUiContentBookEntry} objects are allowed in this list
  *
  * @var string
  */
  protected $_itemClass = 'GuestbookUiContentBookEntry';

  /**
  * If a tag name is provided, an additional element will be added in
  * {@see PapayaUiControlCollection::appendTo()) that will wrapp the items.
  * @var string
  */
  protected $_tagName = 'entries';
  
  /**
  * Entries caption
  * 
  * @var string
  */
  private $_caption = '';
  
  /**
  * Set/get caption
  * 
  * @param string $caption
  * @return string
  */
  public function caption($caption = NULL) {
    if (isset($caption)) {
      PapayaUtilConstraints::assertString($caption);
      $this->_caption = $caption;
    }
    return $this->_caption;
  }
  
  /**
  * Append book output to parent element.
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement|NULL parent the elements where appended to,
  *    NULL if no items are appended.
  */
  public function appendTo(PapayaXmlElement $parent) {
    if (count($this->_items) > 0) {
      $parent = $parent->appendElement(
        $this->_tagName,
        array('caption' => $this->caption())
      );
      foreach ($this->_items as $item) {
        $item->appendTo($parent);
      }
      return $parent;
    }
    return NULL;
  }
}
