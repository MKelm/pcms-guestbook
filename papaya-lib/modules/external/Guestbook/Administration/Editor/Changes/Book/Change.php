<?php
/**
* A administration dialog to change a book.
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
* A administration dialog to change a book.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookAdministrationEditorChangesBookChange 
  extends PapayaUiControlCommandDialogDatabaseRecord {
  
  /**
  * Create dialog and add fields for the dynamic values defined by the current theme values page
  *
  * @see PapayaUiControlCommandDialog::createDialog()
  * @return PapayaUiDialog
  */
  public function createDialog() {
    $bookId = $this->parameters()->get('book_id', 0);
    $dialogCaption = 'Add guestbook';
    $buttonCaption = 'Add';
    if ($bookId > 0) {
      if ($this->record()->load($bookId)) {
        $dialogCaption = 'Change guestbook';
        $buttonCaption = 'Save';
      } else {
        $bookId = 0;
      }
    }
    
    $dialog = new PapayaUiDialogDatabaseSave($this->record());
    $dialog->papaya($this->papaya());
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      array(
        'cmd' => $this->parameters()->get('cmd', ''),
        'book_id' => $bookId
      )
    );
    $dialog->caption = new PapayaUiStringTranslated($dialogCaption);
    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      new PapayaUiStringTranslated('Title'), 
      'title', 
      200, 
      '', 
      new PapayaFilterText(PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS)
    );
    $field->setMandatory(TRUE);

    $dialog->buttons[] = new PapayaUiDialogButtonSubmit(
      new PapayaUiStringTranslated($buttonCaption)
    );
    
    $this->callbacks()->onExecuteSuccessful = array($this, 'callbackSaveValues');
    $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
    return $dialog;
  }
  
  /**
  * Save data from dialog
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackSaveValues($context, $dialog) {
    $this->papaya()->messages->dispatch(
      new PapayaMessageDisplayTranslated(
        PapayaMessage::TYPE_INFO,
        'Guestbook saved.'
      )
    );
  }
  
  /**
  * Show error message
  *
  * @param object $context
  * @param PapayaUiDialog $dialog
  */
  public function callbackShowError($context, $dialog) {
    $this->papaya()->messages->dispatch(
      new PapayaMessageDisplayTranslated(
        PapayaMessage::TYPE_ERROR,
        'Invalid input. Please check the field(s) "%s".',
        array(implode(', ', $dialog->errors()->getSourceCaptions()))
      )
    );
  }
}
