<?php
/**
* Superclass for content.
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
* Superclass for content.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
abstract class GuestbookContent extends PapayaUiControlInteractive {
  
  /**
  * Owner object
  * @var GuestbookBox|GuestbookPage
  */
  protected $_owner = NULL;
  
  /**
  * Box configuration data
  * @var array
  */
  protected $_data = array();
  
  /**
  * Book entries database records
  * @var GuestbookContentBookEntries
  */
  protected $_bookEntries = NULL;
  
  /**
  * Ui content book control
  * @var GuestbookUiContentBook
  */
  protected $_uiContentBook = NULL;
  
  /**
  * Show ui content book paging
  * @var boolean
  */
  protected $_showUiContentBookPaging = FALSE;
  
  /**
  * Get/set owner object
  *
  * @param object $owner
  * @return object
  */
  public function owner($owner = NULL) {
    if (isset($owner)) {
      PapayaUtilConstraints::assertObject($owner);
      $this->_owner = $owner;
    }
    return $this->_owner;
  }

  /**
  * Get/set page configuration data
  *
  * @param array $data
  * @return array
  */
  public function data($data = NULL) {
    if (isset($data)) {
      PapayaUtilConstraints::assertArray($data);
      $this->_data = $data;
    }
    return $this->_data;
  }

  /**
  * Access to the book entries
  *
  * @param GuestbookContentBookEntries $bookEntries
  * @return GuestbookContentBookEntries
  */
  public function bookEntries(GuestbookContentBookEntries $bookEntries = NULL) {
    if (isset($bookEntries)) {
      $this->_bookEntries = $bookEntries;
    } elseif (is_null($this->_bookEntries)) {
      include_once(dirname(__FILE__).'/Content/Book/Entries.php');
      $this->_bookEntries = new GuestbookContentBookEntries();
      $this->_bookEntries->papaya($this->papaya());
    }
    return $this->_bookEntries;
  }
  
  /**
  * Access to the ui content book control
  *
  * @param GuestbookUiContentBook $uiContentBook
  * @return GuestbookUiContentBook
  */
  public function uiContentBook(GuestbookUiContentBook $uiContentBook = NULL) {
    if (isset($uiContentBook)) {
      $this->_uiContentBook = $uiContentBook;
    } elseif (is_null($this->_uiContentBook)) {
      include_once(dirname(__FILE__).'/Ui/Content/Book.php');
      $this->_uiContentBook = new GuestbookUiContentBook(
        $this->bookEntries(), $this->_showUiContentBookPaging
      );
      $this->_uiContentBook->papaya($this->papaya());
      $this->_uiContentBook->parameterGroup = $this->parameterGroup();
      if (isset($this->_data['entries_per_page'])) {
        $this->_uiContentBook->pagingItemsPerPage = 
          (int)$this->_data['entries_per_page'];
      }
    }
    return $this->_uiContentBook;
  }
}
