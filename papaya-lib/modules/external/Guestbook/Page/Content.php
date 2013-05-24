<?php
/**
* A guestbook page content.
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
* A guestbook page xml.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookPageContent extends GuestbookContent {

  /**
  * Book entry database record
  * @var GuestbookContentBookEntry
  */
  protected $_bookEntry = NULL;

  /**
  * Show ui content book paging
  * @var boolean
  */
  protected $_showUiContentBookPaging = TRUE;
  
  /**
  * Ui content dialog
  * @var GuestbookUiContentDialog
  */
  protected $_uiContentDialog = NULL;

  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {   
    $parent->appendElement(
      'title', array(), $this->_data['title']
    );
    
    $text = $parent->appendElement('text');
    $text->appendXml(
      $this->owner()->getXHTMLString(
        $this->_data['text'], !((bool)$this->_data['nl2br'])
      )
    );
    
    $this->uiContentDialog()->appendTo($parent);
    $errorMessage = $this->uiContentDialog()->errorMessage();
    if (!empty($errorMessage)) {
      $parent->appendElement(
        'message', array('type' => 'error'), $errorMessage
      );
    }
    
    $bookPage = $this->parameters()->get('page', 0);
    $this->bookEntries()->load(
      array(
        'book_id' => $this->_data['book'],
        'language_id' => $this->papaya()->request->languageId
      ),
      $this->_data['entries_per_page'],
      ($bookPage > 0) ? ($bookPage - 1) * $this->_data['entries_per_page'] : 0
    );
    $this->uiContentBook()->entries()->caption($this->_data['cpt_entries']);
    $this->uiContentBook()->appendTo($parent);
  }
  
  /**
  * Access to the ui content dialog control
  *
  * @param GuestbookUiContentDialog $uiContentDialog
  * @return GuestbookUiContentDialog
  */
  public function uiContentDialog(GuestbookUiContentDialog $uiContentDialog) {
    if (isset($uiContentDialog)) {
      $this->_uiContentDialog = $uiContentDialog;
    } elseif (is_null($this->_uiContentDialog)) {
      include_once(dirname(__FILE__).'/../Ui/Content/Dialog.php');
      $this->_uiContentDialog = new GuestbookUiContentDialog(
        $this->bookEntry()
      );
      $this->_uiContentDialog->parameterGroup($this->parameterGroup());
      $this->_uiContentDialog->languageId(
        $this->papaya()->request->languageId
      );
      $this->_uiContentDialog->bookId((int)$this->_data['book']);
      $this->_uiContentDialog->captions(
        array(
          'button' => $this->_data['cpt_submit'],
          'author' => $this->_data['cpt_name'],
          'email' => $this->_data['cpt_email'],
          'text' => $this->_data['cpt_text']
        )
      );
      $this->_uiContentDialog->invalidInputMessage(
        $this->_data['msg_input_error']
      );
    }
    return $this->_uiContentDialog;
  }
  
  /**
  * Access to the book entry
  *
  * @param GuestbookContentBookEntry $bookEntry
  * @return GuestbookContentBookEntry
  */
  public function bookEntry(GuestbookContentBookEntry $bookEntry = NULL) {
    if (isset($bookEntry)) {
      $this->_bookEntry = $bookEntry;
    } elseif (is_null($this->_bookEntry)) {
      include_once(dirname(__FILE__).'/../Content/Book/Entry.php');
      $this->_bookEntry = new GuestbookContentBookEntry();
      $this->_bookEntry->papaya($this->papaya());
    }
    return $this->_bookEntry;
  }
}
