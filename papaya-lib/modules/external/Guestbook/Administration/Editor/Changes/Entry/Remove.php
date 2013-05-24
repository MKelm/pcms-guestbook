<?php
/**
* A administration dialog to remove a entry.
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
* A administration dialog to changeremovea entry.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookAdministrationEditorChangesEntryRemove 
  extends PapayaUiControlCommandDialogDatabaseRecord {
  
  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see PapayaUiControlCommandDialog::createDialog()
   * @return PapayaUiDialog
   */
  public function createDialog() {
    $entryId = $this->parameters()->get('entry_id', 0);
    if ($entryId > 0) {
      $loaded = $this->record()->load($entryId);
    } else {
      $loaded = FALSE;
    }
    $dialog = new PapayaUiDialogDatabaseDelete($this->record());
    $dialog->papaya($this->papaya());
    $dialog->caption = new PapayaUiStringTranslated('Delete guestbook entry');
    if ($loaded) {
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => $this->parameters()->get('cmd', ''),
          'entry_id' => $entryId,
          'book_id' => $this->parameters()->get('book_id', '')
        )
      );
      $dialog->fields[] = new PapayaUiDialogFieldInformation(
        new PapayaUiStringTranslated('Delete guestbook entry?'),
        'places-trash'
      );
      $dialog->buttons[] = new PapayaUiDialogButtonSubmit(new PapayaUiStringTranslated('Delete'));
      $this->callbacks()->onExecuteSuccessful = array($this, 'callbackDeleted');
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
  public function callbackDeleted($context, $dialog) {
    $this->papaya()->messages->dispatch(
      new PapayaMessageDisplayTranslated(
        PapayaMessage::TYPE_INFO,
        'Guestbook entry deleted.'
      )
    );
  }
}
