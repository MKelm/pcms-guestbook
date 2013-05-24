<?php
/**
* A administration dialog to remove a book.
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
* A administration dialog to remove book.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookAdministrationEditorChangesBookRemove 
  extends PapayaUiControlCommandDialogDatabaseRecord {
  
  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see PapayaUiControlCommandDialog::createDialog()
   * @return PapayaUiDialog
   */
  public function createDialog() {
    $bookId = $this->parameters()->get('book_id', 0);
    if ($bookId > 0) {
      $loaded = $this->record()->load($bookId);
    } else {
      $loaded = FALSE;
    }
    $dialog = new PapayaUiDialogDatabaseDelete($this->record());
    $dialog->papaya($this->papaya());
    $dialog->caption = new PapayaUiStringTranslated('Delete guestbook');
    if ($loaded) {
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => $this->parameters()->get('cmd', ''),
          'book_id' => $bookId
        )
      );
      $dialog->fields[] = new PapayaUiDialogFieldInformation(
        new PapayaUiStringTranslated('Delete guestbook?'),
        'places-trash'
      );
      $dialog->buttons[] = new PapayaUiDialogButtonSubmit(
        new PapayaUiStringTranslated('Delete')
      );
      $this->callbacks()->onExecuteSuccessful = array($this, 'callbackDeleted');
    } else {
      $dialog->fields[] = new PapayaUiDialogFieldMessage(
        PapayaMessage::TYPE_INFO, 'Guestbook not found.'
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
        'Guestbook deleted.'
      )
    );
  }
}
