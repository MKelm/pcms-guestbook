<?php
/**
* A guestbook administration navigation.
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
* A guestbook administration navigation.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookAdministrationNavigation 
  extends PapayaAdministrationPagePart {
  
  /**
  * Listview for books navigation
  * @var PapayaUiListview
  */
  private $_listview = NULL;
  
  /**
  * Reference object to create urls
  * @var PapayaUiReference
  */
  private $_reference = NULL;
  
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
  * Append navigation to parent xml element
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $this->toolbar()->elements[] = $button = new PapayaUiToolbarButton();
    $button->reference(clone $this->reference());
    $button->caption = new PapayaUiStringTranslated('Add guestbook');
    $button->image = $this->_localImages['add_book'];
    $button->reference()->setParameters(
      array(
        'cmd' => 'add_book',
        'book_id' => 0
      ),
      $this->parameterGroup()
    );
    if (0 < ($bookId = $this->parameters()->get('book_id', 0))) {
      $this->toolbar()->elements[] = $button = new PapayaUiToolbarButton();
      $button->reference(clone $this->reference());
      $button->caption = new PapayaUiStringTranslated('Delete guestbook');
      $button->image = $this->_localImages['remove_book'];
      $button->reference()->setParameters(
        array(
          'cmd' => 'remove_book',
          'book_id' => $bookId
        ),
        $this->parameterGroup()
      );
    }
    if (0 < ($entryId = $this->parameters()->get('entry_id', 0))) {
      $this->toolbar()->elements[] = $button = new PapayaUiToolbarButton();
      $button->reference(clone $this->reference());
      $button->caption = new PapayaUiStringTranslated('Delete entry');
      $button->image = 'actions-page-delete';
      $button->reference()->setParameters(
        array(
          'cmd' => 'remove_entry',
          'entry_id' => $entryId,
          'page' => $this->parameters()->get('page', 0)
        ),
        $this->parameterGroup()
      );
    }
    
    $parent->append($this->listview());
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
      $this->_listview->caption = new PapayaUiStringTranslated('Guestbooks');
      $this->_listview->builder(
        $builder = new PapayaUiListviewItemsBuilder(
          $this->createBookList()
        )
      );
      $this->_listview->builder()->callbacks()->onCreateItem = array($this, 'callbackCreateItem');
      $this->_listview->builder()->callbacks()->onCreateItem->context = $builder;
    }
    return $this->_listview;
  }
  
  /**
  * Get the books list for the listview
  * 
  * @return PapayaIteratorTreeItems
  */
  private function createBookList() {
    include_once(dirname(__FILE__).'/../Content/Books.php');
    $books = new GuestbookContentBooks();
    $books->papaya($this->papaya());
    $books->load();
    $bookIterator = new PapayaIteratorTreeItems($books);
    return $bookIterator;
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
    $selected = $this->parameters()->get('book_id', '') == $element['id'];
    $items[] = $item = new PapayaUiListviewItem(
      $selected ? $this->_localImages['book_open'] : $this->_localImages['book'], 
      $element['title']
    );
    $item->papaya($this->papaya());
    $item->reference->setParameters(
      array(
        'cmd' => 'change_book',
        'book_id' => $element['id']
      ),
      $this->parameterGroup()
    );
    $item->selected = $selected;
    return $item;
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
          'book_id' => $this->parameters()->get('book_id', 0)
        ), 
        $this->parameterGroup()
      );
    }
    return $this->_reference;
  }
}
