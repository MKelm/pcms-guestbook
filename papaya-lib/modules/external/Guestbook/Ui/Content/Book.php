<?php
/**
* A content book ui control.
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
* A content book ui control.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookUiContentBook extends PapayaUiControl {
  
  /**
  * Object buffer for book entries.
  *
  * @var GuestbookUiContentBookEntries
  */
  protected $_entries = NULL;
  
  /**
  * List of database records
  *
  * @var GuestbookContentBookEntries
  */
  protected $_entriesData = NULL;
  
  /**
  * Parameter group name
  * @var string
  */
  protected $_parameterGroup = NULL;
  
  /**
  * Show paging output
  *
  * @var boolean
  */
  protected $_showPaging = NULL;
  
  /**
  * Paging object
  *
  * @var PapayaUiPagingCount
  */
  protected $_paging = NULL;
  
  /**
  * Paging items per page
  *
  * @var integer
  */
  protected $_pagingItemsPerPage = NULL;
  
  /**
  * Declared public properties, see property annotaiton of the class for documentation.
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'entries' => array('entries', 'entries'),
    'entriesData' => array('_entriesData', '_entriesData'),
    'parameterGroup' => array('_parameterGroup', '_parameterGroup'),
    'pagingItemsPerPage' => array('_pagingItemsPerPage', '_pagingItemsPerPage'),
    
  );
  
  /**
  * Construct object and set base properties
  * 
  * @param GuestbookContentBookEntries $entriesData
  * @param boolean $showPaging
  */
  public function __construct(GuestbookContentBookEntries $entriesData, $showPaging = FALSE) {
    PapayaUtilConstraints::assertBoolean($showPaging);
    $this->_entriesData = $entriesData;
    $this->_showPaging = $showPaging;
  }
  
  /**
  * Fill book with entries by database recors.
  */
  private function fillEntries() {
    if (count($this->_entriesData) > 0) {
      foreach ($this->_entriesData as $entryDataColumn) {
        include_once(dirname(__FILE__).'/Book/Entry.php');
        $this->entries[] = new GuestbookUiContentBookEntry(
          $entryDataColumn['id'],
          $entryDataColumn['author'],
          $entryDataColumn['email'],
          $entryDataColumn['created'],
          $entryDataColumn['text']
        );
      }
    }
  }

  /**
  * Append listview output to parent element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $book = $parent->appendElement('book');
    if ($this->_showPaging) {
      $this->paging()->appendTo($book);
    }
    $this->fillEntries();
    $this->entries()->appendTo($book);
  }
  
  /**
  * The list of listview items
  *
  * @param GuestbookUiContentBookEntries $entries
  */
  public function entries(GuestbookUiContentBookEntries $entries = NULL) {
    if (isset($entries)) {
      $this->_entries = $entries;
    } elseif (is_null($this->_entries)) {
      include_once(dirname(__FILE__).'/Book/Entries.php');
      $this->_entries = new GuestbookUiContentBookEntries($this);
      $this->_entries->papaya($this->papaya());
    }
    return $this->_entries;
  }
  
  /**
  * Paging object
  * 
  * @param PapayaUiPagingCount $paging
  */
  public function paging(PapayaUiPagingCount $paging) {
    if (isset($paging)) {
      $this->_paging = $paging;
    } elseif (is_null($this->_paging)) {
      $this->_paging = new PapayaUiPagingCount(
        $this->_parameterGroup.'[page]', 
        $this->papaya()->request->getParameter(
          $this->_parameterGroup.'[page]'
        ),
        $this->entriesData->absCount()
      );
      $this->_paging->papaya($this->papaya());
      $this->_paging->itemsPerPage = $this->_pagingItemsPerPage;
    }
    return $this->_paging;
  }
}
