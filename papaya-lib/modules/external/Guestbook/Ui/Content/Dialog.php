<?php
/**
* A guestbook content dialog.
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
* A guestbook content dialog.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookUiContentDialog 
  extends PapayaUiControlCommandDialogDatabaseRecord {
  
  /**
  * Current book id
  * @var integer
  */
  protected $_bookId = NULL;
  
  /**
  * Current content language id
  * @var integer
  */
  protected $_languageId = NULL;
  
  /**
  * Captions for dialog
  * @var array
  */
  protected $_captions = NULL;
  
  /**
  * Message to show on invalid input(s)
  * @var string
  */
  protected $_invalidInputMessage = NULL;
  
  /**
  * Current error message.
  * @var string
  */
  protected $_errorMessage = NULL;
  
  /**
  * Get/set book id
  * @var integer $bookId
  */
  public function bookId($bookId = NULL) {
    if (isset($bookId)) {
      PapayaUtilConstraints::assertInteger($bookId);
      $this->_bookId = $bookId;
    }
    return $this->_bookId;
  }
  
  /**
  * Get/set language id
  * @var integer $languageId
  */
  public function languageId($languageId = NULL) {
    if (isset($languageId)) {
      PapayaUtilConstraints::assertInteger($languageId);
      $this->_languageId = $languageId;
    }
    return $this->_languageId;
  }
  
  /**
  * Get/set captions for dialog
  * @var array $captions
  */
  public function captions($captions = NULL) {
    if (isset($captions)) {
      PapayaUtilConstraints::assertArray($captions);
      $this->_captions = $captions;
    }
    return $this->_captions;
  }
  
  /**
  * Get/set invalid input message
  * @var string $invalidInputMessage
  */
  public function invalidInputMessage($invalidInputMessage = NULL) {
    if (isset($invalidInputMessage)) {
      PapayaUtilConstraints::assertString($invalidInputMessage);
      $this->_invalidInputMessage = $invalidInputMessage;
    }
    return $this->_invalidInputMessage;
  }
  
  /**
  * Get/set error message
  * @var string $errorMessage
  */
  public function errorMessage($errorMessage = NULL) {
    if (isset($errorMessage)) {
      PapayaUtilConstraints::assertString($errorMessage);
      $this->_errorMessage = $errorMessage;
    }
    return $this->_errorMessage;
  }
  
  /**
  * Create dialog and add fields for the dynamic values defined by the current theme values page
  *
  * @see PapayaUiControlCommandDialog::createDialog()
  * @return PapayaUiDialog
  */
  public function createDialog() {
    $buttonCaption = $this->_captions['button'];

    $dialog = new PapayaUiDialogDatabaseSave($this->record());
    $dialog->callbacks()->onBeforeSave = array($this, 'callbackBeforeSaveRecord');
    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      array(
        'page' => $this->parameters()->get('page', '')
      )
    );
    $dialog->caption = NULL;
    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      $this->_captions['author'], 
      'author', 
      200, 
      '', 
      new PapayaFilterText(PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS)
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogEntryAuthor');
    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      $this->_captions['email'], 'email', 200, '', new PapayaFilterEmail()
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogEntryEmail');
    include_once(dirname(__FILE__).'/../../Filter/Entry/Xml.php');
    $dialog->fields[] = $field = new PapayaUiDialogFieldTextarea(
      $this->_captions['text'], 
      'text', 
      5,
      '', 
      new GuestbookFilterEntryXml($this->languageId())
    );
    $field->setMandatory(TRUE);
    $field->setId('dialogEntryText');
    $dialog->buttons[] = new PapayaUiDialogButtonSubmit($buttonCaption);
    
    $this->callbacks()->onExecuteSuccessful = array($this, 'callbackSaveValues');
    $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
    return $dialog;
  }
  
  /**
  * Callback before save record in PapayaUiDialogDatabaseSave
  * 
  * @param object $context
  * @param object $record
  */
  public function callbackBeforeSaveRecord($context, $record) {
    $record->assign(
      array(
        'language_id' => $this->languageId(),
        'book_id' => $this->bookId(),
        'created' => time(),
        'ip' => $_SERVER['REMOTE_ADDR']
      )
    );
    return TRUE;
  }
  
  /**
  * Save data from dialog
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackSaveValues($context, $dialog) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_spamfilter.php');
    $filter = &base_spamfilter::getInstance();
    $filter->log(
      $dialog->data()->get('text'), 
      $this->languageId(), 
      'Guestbook Entry Text'
    );
  }
  
  /**
  * Show error message
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackShowError($context, $dialog) {
    $this->errorMessage(
      sprintf(
        $this->invalidInputMessage(),
        implode(', ', $dialog->errors()->getSourceCaptions())
      )
    );
  }
}
