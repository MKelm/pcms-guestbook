<?php
/**
* A administration dialog to change a book entry.
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
* A administration dialog to change a book entry.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookAdministrationEditorChangesEntryChange 
  extends PapayaUiControlCommandDialogDatabaseRecord {
  
  /**
  * Create dialog and add fields for the dynamic values defined by the current theme values page
  *
  * @see PapayaUiControlCommandDialog::createDialog()
  * @return PapayaUiDialog
  */
  public function createDialog() {
    $entryId = $this->parameters()->get('entry_id', 0);
    $dialogCaption = 'Change guestbook entry';
    $buttonCaption = 'Save';
    if ($entryId > 0) {
      if ($this->record()->load($entryId)) {
      } else {
        $entryId = 0;
      }
    }
    
    $dialog = new PapayaUiDialogDatabaseSave($this->record());
    $dialog->papaya($this->papaya());
    $dialog->caption = new PapayaUiStringTranslated($dialogCaption);
    if ($entryId > 0) {
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => $this->parameters()->get('cmd', ''),
          'entry_id' => $entryId,
          'book_id' => $this->parameters()->get('book_id', '')
        )
      );
      $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
        new PapayaUiStringTranslated('Author'), 
        'author', 
        200, 
        '', 
        new PapayaFilterText(PapayaFilterText::ALLOW_SPACES|PapayaFilterText::ALLOW_DIGITS)
      );
      $field->setMandatory(TRUE);
      $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
        new PapayaUiStringTranslated('E-Mail'), 
        'email', 200, '', new PapayaFilterEmail()
      );
      $field->setMandatory(TRUE);
      include_once(dirname(__FILE__).'/../../../../Filter/Entry/Xml.php');
      $dialog->fields[] = $field = new PapayaUiDialogFieldTextarea(
        new PapayaUiStringTranslated('Text'), 'text', 5, '', new GuestbookFilterEntryXml()
      );
      $field->setMandatory(TRUE);
      
      $dialog->buttons[] = new PapayaUiDialogButtonSubmit(
        new PapayaUiStringTranslated($buttonCaption)
      );
      
      $this->callbacks()->onExecuteSuccessful = array($this, 'callbackSaveValues');
      $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
    } else {
      $dialog->fields[] = new PapayaUiDialogFieldMessage(
        PapayaMessage::TYPE_INFO, 'Guestbook entry not found.'
      );
    }
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
        'Guestbook entry saved.'
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
