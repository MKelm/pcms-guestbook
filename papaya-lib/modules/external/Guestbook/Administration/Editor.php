<?php
/**
* Guestbook administration editor.
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
* Guestbook administration editor.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookAdministrationEditor 
  extends PapayaAdministrationPagePart {
  
  /**
  * Commands list object
  * @var PapayaUiControlCommandController
  */
  private $_commands = NULL;
  
  /**
  * Reference object to create urls
  * @var PapayaUiReference
  */
  private $_reference = NULL;
  
  /**
  * Listview for entries navigation
  * @var PapayaUiListview
  */
  private $_listview = NULL;
  
  /**
  * Paging object
  *
  * @var PapayaUiPagingCount
  */
  protected $_paging = NULL;
  
  /**
  * Book database record
  * @var GuestbookContentBook
  */
  protected $_book = NULL;
  
  /**
  * Book entry database record
  * @var GuestbookContentBookEntry
  */
  protected $_bookEntry = NULL;
  
  /**
  * Book entries database records
  * @var GuestbookContentBookEntries
  */
  protected $_bookEntries = NULL;
  
  /**
  * Book entries per page
  * @var integer
  */
  protected $_bookEntriesPerPage = 15;
  
  /**
  * Local images to use
  * @var array
  */
  private $_localImages = array();
  
  /**
  * Construct onject and set local images to use
  *
  * @var array $localImages
  */
  public function __construct(array $localImages) {
    $this->_localImages = $localImages;
  }
  
  /**
  * Append changes commands to parent xml element
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->append($this->commands());
    $parent->append($this->listview());
  }
  
  /**
  * Commands, actual actions
  *
  * @param PapayaUiControlCommandController $commands
  * @return PapayaUiControlCommandController
  */
  public function commands(PapayaUiControlCommandController $commands = NULL) {
    if (isset($commands)) {
      $this->_commands = $commands;
    } elseif (is_null($this->_commands)) {
      $this->_commands = new PapayaUiControlCommandController('cmd');
      $this->_commands->owner($this);
      
      include_once(dirname(__FILE__).'/Editor/Changes/Book/Change.php');
      $this->_commands['add_book'] =
        $command = new GuestbookAdministrationEditorChangesBookChange($this->book());
      $this->_commands['change_book'] =
        $command = new GuestbookAdministrationEditorChangesBookChange($this->book());
      include_once(dirname(__FILE__).'/Editor/Changes/Book/Remove.php');
      $this->_commands['remove_book'] =
        $command = new GuestbookAdministrationEditorChangesBookRemove($this->book());
      include_once(dirname(__FILE__).'/Editor/Changes/Entry/Change.php');
      $this->_commands['edit_entry'] =
        $command = new GuestbookAdministrationEditorChangesEntryChange($this->bookEntry());
      include_once(dirname(__FILE__).'/Editor/Changes/Entry/Remove.php');
      $this->_commands['remove_entry'] =
        $command = new GuestbookAdministrationEditorChangesEntryRemove($this->bookEntry());
    }
    return $this->_commands;
  }
  
  /**
  * Getter/Setter for the books navigation listview
  *
  * @param PapayaUiListview $listview
  */
  public function listview(PapayaUiListview $listview = NULL) {
    if (isset($listview)) {
      $this->_listview = $listview;
    } elseif (NULL === $this->_listview) {
      $this->_listview = new PapayaUiListview();
      $this->_listview->papaya($this->papaya());
      $this->_listview->parameterGroup($this->parameterGroup());
      $this->_listview->parameters($this->parameters());
      $this->_listview->reference(clone $this->reference());
      $this->_listview->caption = new PapayaUiStringTranslated('Entries');
      $this->_listview->builder(
        $builder = new PapayaUiListviewItemsBuilder(
          $this->createEntryList()
        )
      );
      $this->_listview->builder()->callbacks()->onCreateItem = array($this, 'callbackCreateItem');
      $this->_listview->builder()->callbacks()->onCreateItem->context = $builder;
      $this->_listview->toolbars()->bottomRight->elements[] = $this->paging();
    }
    return $this->_listview;
  }
  
  /**
  * Get the entry list for the listview
  * 
  * @return PapayaIteratorTreeItems
  */
  private function createEntryList() {
    $entryIterator = new PapayaIteratorTreeItems($this->bookEntries());
    return $entryIterator;
  }
  
  /**
  * Callback to create the items
  *
  * @param PapayaUiListviewItemsBuilder $builder
  * @param PapayaUiListviewItems $items
  * @param mixed $element
  * @param mixed $index
  */
  public function callbackCreateItem($builder, $items, $element, $index) {
    $items[] = $item = new PapayaUiListviewItem(
      'items-page', 
      sprintf(
        '%s (E-Mail: %s, IP: %s)', 
        $element['author'],
        $element['email'],
        $element['ip']
      )
    );
    $item->papaya($this->papaya());
    $item->reference->setParameters(
      array(
        'cmd' => 'edit_entry',
        'entry_id' => $element['id']
      ),
      $this->parameterGroup()
    );
    $item->selected = 
      $this->parameters()->get('entry_id', '') == $element['id'];
      
    $subitems = new PapayaUiListviewSubitems($item);
    include_once(dirname(__FILE__).'/../Ui/Listview/Subitem/Date/Time.php');
    $subitems[] = new GuestbookUiListviewSubitemDateTime(
      (int)$element['created']
    );
    $subitems[] = new PapayaUiListviewSubitemImage(
      'actions-page-delete', 
      '', 
      array('cmd' => 'remove_entry', 'entry_id' => $element['id'])
    );
    $item->subitems($subitems);
      
    return $item;
  }
  
  /**
  * Paging object
  * 
  * @param PapayaUiToolbarPaging $paging
  */
  public function paging(PapayaUiToolbarPaging $paging) {
    if (isset($paging)) {
      $this->_paging = $paging;
    } elseif (is_null($this->_paging)) {
      $this->_paging = new PapayaUiToolbarPaging(
        array($this->_parameterGroup, 'page'),
        (int)$this->bookEntries()->absCount()
      );
      $this->_paging->papaya($this->papaya());
      $this->_paging->reference(clone $this->reference());
      $this->_paging->itemsPerPage = $this->_bookEntriesPerPage;
    }
    return $this->_paging;
  }
  
  /**
  * The basic reference object used by the subobjects to create urls.
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
      $this->_reference->setApplication($this->getApplication());
      $this->_reference->setParameters(
        array(
          'book_id' => $this->parameters()->get('book_id', 0),
          'entry_id' => $this->parameters()->get('entry_id', 0),
          'page' => $this->parameters()->get('page', 0)
        ),
        $this->parameterGroup()
      );
    }
    return $this->_reference;
  }
  
  /**
  * Access to the book
  *
  * @param GuestbookContentBook $book
  * @return GuestbookContentBook
  */
  public function book(GuestbookContentBook $book = NULL) {
    if (isset($book)) {
      $this->_book = $book;
    } elseif (is_null($this->_book)) {
      include_once(dirname(__FILE__).'/../Content/Book.php');
      $this->_book = new GuestbookContentBook();
      $this->_book->papaya($this->papaya());
    }
    return $this->_book;
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
      include_once(dirname(__FILE__).'/../Content/Book/Entries.php');
      $this->_bookEntries = new GuestbookContentBookEntries();
      $this->_bookEntries->papaya($this->papaya());
      $currentPage = $this->parameters()->get('page', 0);
      include_once(
        $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '/').
        'system/base_language_select.php'
      );
      $languageSelect = &base_language_select::getInstance();
      $currentLanguageId = $languageSelect->currentLanguageId;
      $this->_bookEntries->load(
        array(
          'book_id' => $this->parameters()->get('book_id', 0),
          'language_id' => $currentLanguageId
        ),
        $this->_bookEntriesPerPage,
        ($currentPage > 1) ? ($currentPage - 1) * $this->_bookEntriesPerPage : 0
      );
    }
    return $this->_bookEntries;
  }
}
