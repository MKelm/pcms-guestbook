<?php
/**
* A guestbook box content.
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
* Basic xml class
*/
require_once(dirname(__FILE__).'/../Content.php');

/**
* A guestbook box content.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookBoxContent extends GuestbookContent {
  
  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {    
    $content = $parent->appendElement('box');
    
    $text = $content->appendElement('text');
    $text->appendXml(
      $this->_owner->getXHTMLString(
        $this->_data['text'], !((bool)$this->_data['nl2br'])
      )
    );

    $this->bookEntries()->load(
      array(
        'book_id' => $this->_data['book'],
        'language_id' => $this->papaya()->request->languageId
      ),
      $this->_data['entries_amount'],
      0
    );
    $this->uiContentBook()->entries()->caption($this->_data['cpt_entries']);
    $this->uiContentBook()->appendTo($content);
    
    if ($this->_data['pageid_gb'] > 0) {
      $showMoreLink = $this->owner()->getWebLink(
        $this->_data['pageid_gb'], NULL, NULL
      );
      $content->appendElement(
        'show-more-link',
        array('caption' => $this->_data['cpt_show_more']),
        PapayaUtilStringXml::escape($showMoreLink)
      );
    }
  }
}
