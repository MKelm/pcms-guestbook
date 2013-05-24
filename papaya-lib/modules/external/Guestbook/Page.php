<?php
/**
* A guestbook page.
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
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* A guestbook page.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookPage extends base_content {
  
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
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, NULL, ''),
    'book' => array(
      'Guestbook', 'isNum', FALSE, 'function', 'getDialogFieldSelectBooks', NULL, 0
    ),
    'entries_per_page' => array(
      'Entries per page', 'isNum', TRUE, 'input', 4, NULL, 10
    ),

    'Text',
    'nl2br' => array(
      'Automatic linebreak', 'isNum', FALSE, 'combo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.',
      0
    ),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, NULL, ''),

    'Messages',
    'msg_input_error' => array(
      'Input error', 'isNoHTML', TRUE, 'input', 200, NULL,
      'Invalid input. Please check the field(s) "%s".'
    ),

    'Dialog captions',
    'cpt_name' => array(
      'Name', 'isNoHTML', TRUE, 'input', 200, NULL, 'Name'
    ),
    'cpt_email' => array(
      'E-Mail', 'isNoHTML', TRUE, 'input', 200, NULL, 'E-Mail'
    ),
    'cpt_text' => array(
      'Text', 'isNoHTML', TRUE, 'input', 200, NULL, 'Text'
    ),
    'cpt_submit' => array(
      'Add', 'isNoHTML', TRUE, 'input', 200, NULL, 'Add'
    ),

    'Captions',
    'cpt_entries' => array(
      'Entries', 'isNoHTML', TRUE, 'input', 200, NULL, 'Entries'
    )
  );
  
  /**
  * The content object to be used
  * 
  * @var GuestbookPageContent
  */
  protected $_content = NULL;
  
  /**
  * The ui dialog field select books object to be used
  * 
  * @var GuestbookUiDialogFieldSelectBooks
  */
  protected $_uiDialogFieldSelectBooks = NULL;
  
  /**
  * Get (and, if necessary, initialize) the GuestbookPageContent object 
  * 
  * @return GuestbookPageContent $content
  */
  public function content(GuestbookPageContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } elseif (is_null($this->_content)) {
      include_once(dirname(__FILE__).'/Page/Content.php');
      $this->_content = new GuestbookPageContent();
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
