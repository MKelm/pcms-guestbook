<?php
/**
* A guestbook box.
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
* Base class for page modules.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* A guestbook box.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookBox extends base_actionbox {
  
  /**
  * Parameter prefix name
  * @var string $paramName
  */
  public $paramName = 'gb';

  /**
  * Set cacheable status of this module
  * @var boolean $cacheable
  */
  public $cacheable = FALSE;
  
  /**
  * Edit fields
  * @var string
  */
  public $editFields = array(
    'book' => array(
      'Guestbook', 'isNum', FALSE, 'function', 'getDialogFieldSelectBooks', NULL, 0
    ),
    'entries_amount' => array(
      'Entries amount', 'isNum', TRUE, 'input', 4, NULL, 3
    ),
    'pageid_gb' => array(
      'Guestbook page id', 'isNum', TRUE, 'pageid', 10, NULL, 0
    ),

    'Text',
    'nl2br' => array(
      'Automatic linebreak', 'isNum', FALSE, 'combo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.',
      0
    ),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 5, NULL, ''),

    'Captions',
    'cpt_entries' => array(
      'Entries', 'isNoHTML', FALSE, 'input', 200, NULL, 'Entries'
    ),
    'cpt_show_more' => array(
      'Show more', 'isNoHTML', TRUE, 'input', 200, NULL, 'Show more'
    )
  );
  
  /**
  * The content object to be used
  * 
  * @var GuestbookBoxContent
  */
  private $_content = NULL;
  
  /**
  * The ui dialog field select books object to be used
  * 
  * @var GuestbookUiDialogFieldSelectBooks
  */
  protected $_uiDialogFieldSelectBooks = NULL;
  
  /**
  * Get (and, if necessary, initialize) the GuestbookBoxContent object 
  * 
  * @return GuestbookBoxContent $content
  */
  public function content(GuestbookBoxContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } elseif (is_null($this->_content)) {
      include_once(dirname(__FILE__).'/Box/Content.php');
      $this->_content = new GuestbookBoxContent();
      $this->_content->parameterGroup($this->paramName);
    }
    return $this->_content;
  }
  
  /**
  * Get (and, if necessary, initialize) the GuestbookUiDialogFieldSelectBooks object 
  * 
  * @return GuestbookUiDialogFieldSelectBooks $dialogField
  */
  public function uiDialogFieldSelectBooks(GuestbookUiDialogFieldSelectBooks $dialogField = NULL) {
    if (isset($dialogField)) {
      $this->_uiDialogFieldSelectBooks = $dialogField;
    } elseif (is_null($this->_uiDialogFieldSelectBooks)) {
      include_once(dirname(__FILE__).'/Ui/Dialog/Field/Select/Books.php');
      $this->_uiDialogFieldSelectBooks = new GuestbookUiDialogFieldSelectBooks();
    }
    return $this->_uiDialogFieldSelectBooks;
  }
  
  /**
  * Get the box XML output
  *
  * @return string XML
  */
  public function getParsedData() {
    $content = $this->content();
    $this->setDefaultData();
    $this->initializeParams();
    $content->owner($this);
    $content->data($this->data);
    return $content->getXml();
  }
  
  /**
  * Get a dialog field to select a book.
  * 
  * @param string $parameterName
  * @param array $properties
  * @$param array $data
  * @return string
  */
  public function getDialogFieldSelectBooks($parameterName, $properties, $data) {
    $uiDialogFieldSelectBooks = $this->uiDialogFieldSelectBooks();
    $uiDialogFieldSelectBooks->parameterGroup = $this->paramName;
    $uiDialogFieldSelectBooks->parameterName = $parameterName;
    $uiDialogFieldSelectBooks->currentValue = $data['id'];
    return $uiDialogFieldSelectBooks->getXml();
  }
}
