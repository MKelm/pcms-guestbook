<?php
/**
* Guestbook content moudle

* @copyright 2007-2008 by Alexander Nichau, Martin Kelm
* @link http://www.idxsolutions.de/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com>
* @author Martin Kelm <kelm@idxsolutions.de>
*/

/**
* Basic class Page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Guestbook content module
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com>
* @author Martin Kelm <kelm@idxsolutions.de>
*/
class content_guestbook extends base_content {

  /**
  * Parameter prefix name
  * @var string $paramName
  */
  var $paramName = 'gb';

  /**
  * Set cacheable status of this module
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Output object for guestbook content / box modules
  * @var object $gbObject output_guestbook
  */
  var $outputObj = NULL;

  /**
   * Dialog form to enter a new entry.
   * var object $entryDialog base_dialog
   */
  var $entryDialog = NULL;

  /**
  * Set dialog captions if the dialog is available
  * var array $dialogCaptions
  */
  var $dialogCaptions = array();

  /**
  * Edit fields
  * @var string
  */
  var $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, ''),
    'book' => array('Guestbook', 'isNum', FALSE, 'function', 'getGbCombo',
      '', 0),
    'entries_per_page' => array('Entries per page', 'isNum', TRUE,
      'input', 4, '', 10),
    'block_seconds' => array('Seconds to block ip', 'isNum', TRUE,
      'input', 4, '', 30),
    'max_text_length' => array('Maximal length of text', 'isNum', TRUE,
      'input', 4, '', 255),
    'show_edit_form' => array('Show edit formular', 'isNum', TRUE, 'combo',
      array(0 => 'No', 1 => 'Yes'), '', 1),

    'Text',
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'combo',
      array(0 => 'Yes', 1 => 'No', '', 1),
      'Apply linebreaks from input to the HTML output.'),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10),

    'Emails',
    'admin_sendmails' => array('Send moderator emails', 'isNum', TRUE,
      'combo', array(1 => 'Yes', 0 => 'No'), '', 1),
    'admin_name' => array('Moderator name', 'isNoHTML', TRUE,
      'input', 50, '', 'Moderator name'),
    'admin_email' => array('Moderator email', 'isEMail', TRUE,
      'input', 50, '', 'webmaster@domain.tld'),
    'mailfrom_name' => array('From name', 'isNoHTML', TRUE,
      'input', 50, '', 'Guestbook'),
    'mailfrom_email' => array('From email', 'isEMail', TRUE,
      'input', 100, '', 'admin@domain.tld'),
    'mailsubject' => array('Email subject', 'isNoHTML', TRUE,
      'input', 200, '', 'New entry in guestbook'),
    'mailtext' => array('Email text', 'isSomeText', TRUE,
      'textarea', 3, '{%LINK%}, {%MODERATOR%}', '{%LINK%}'),

    'Messages',
    'msg_no_data' => array('No data', 'isNoHTML', TRUE, 'input', 200, '',
      'No data found.'),
    'msg_input_error' => array('Input error', 'isNoHTML', TRUE,
      'input', 200, '', 'Please check your input.'),
    'msg_spam_protection' => array('Spam protection', 'isNoHTML', TRUE,
      'input', 200, '',
      'Spam detected, please wait or change your message.'),

    'Form captions',
    'cpt_title' => array('Title', 'isNoHTML', TRUE, 'input', 200, '',
      'Title'),
    'cpt_name' => array('Name', 'isNoHTML', TRUE, 'input', 200, '',
      'Name'),
    'cpt_email' => array('E-Mail', 'isNoHTML', TRUE, 'input', 200, '',
      'E-mail'),
    'cpt_text' => array('Text', 'isNoHTML', TRUE, 'input', 200, '',
      'Text'),
    'cpt_submit' => array('Submit', 'isNoHTML', TRUE, 'input', 200, '',
      'Submit'),

    'Captions',
    'cpt_entries' => array('Entries', 'isNoHTML', TRUE, 'input', 200, '',
      'Entries'),
    'cpt_at' => array('At', 'isNoHTML', TRUE, 'input', 200, '',
      'at'),
    'cpt_previous' => array('Previous page', 'isNoHTML', TRUE, 'input', 200, '',
      'at'),
    'cpt_next' => array('Next page', 'isNoHTML', TRUE, 'input', 200, '',
      'at'),
    'cpt_note' => array('Formular note', 'isNoHTML', TRUE, 'input', 200, '',
      'You have to fill out all fields!'),
  );

  /**
  * Initialize output object to use specific output methods and base methods.
  *
  * @author Martin Kelm <kelm@idxsolutions.de>
  */
  function initializeOutputObject() {
    if (empty($this->outputObj) || !is_object($this->outputObj)) {
      include_once(dirname(__FILE__).'/output_guestbook.php');
      $this->outputObj = &new output_guestbook($this);
    }
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->initializeParams();
    $this->initializeOutputObject();

    // Set default data is a new method in papaya 5 since april 2008.
    if (method_exists($this, 'setDefaultData')) {
      $this->setDefaultData();
    }

    $result = sprintf('<title>%s</title>'.LF,
      $this->getXHTMLString(@$this->data['title']));
    $result .= sprintf('<text>%s</text>',
      $this->getXHTMLString(@$this->data['text'],
        !((bool)@$this->data['nl2br'])));

    $result .= $this->getCaptionsXML();

    if ($this->data['show_edit_form'] == 1) {
      $this->dialogCaptions = $this->getFormCaptions();
      // Parameters form captions and load parameters of field values
      $this->initFormDialog(TRUE);
    }

    switch($this->params['action']) {
    case 'insert':
      $result .= $this->addNewEntry();
      $result .= $this->getDefaultOutputXML();
      break;

    default:
      $result .= $this->getDefaultOutputXML();
    }

    return $result;
  }

  /**
  * Get captions xml
  *
  * @return string xml
  */
  function getCaptionsXML() {
    return sprintf('<captions>'.LF.
                   '<entries>%s</entries>'.LF.
                   '<at>%s</at>'.LF.
                   '<previous>%s</previous>'.LF.
                   '<next>%s</next>'.LF.
                   '<note>%s</note>'.LF.
                   '</captions>'.LF,
      $this->data['cpt_entries'],
      $this->data['cpt_at'],
      $this->data['cpt_previous'],
      $this->data['cpt_next'],
      $this->data['cpt_note']
    );
  }

  /**
  * This method gets the default xml set.
  *
  * @param array $formCaptions contains an empty array if the formular is not available
  * @return string xml
  */
  function getDefaultOutputXML() {
    $result = $this->outputObj->getEntriesXML((int)$this->data['book'],
      (int)$this->data['entries_per_page'],
      (int)$this->params['start']);
    $result .= $this->getXMLNavigation((int)$this->data['book'],
      (int)$this->data['entries_per_page'],
      (int)$this->params['start']);

    if ($this->data['show_edit_form'] == 1) {
      $result .= $this->getFormXML(TRUE);
    }

    return $result;
  }

  /**
  * This method set aggregates formular captions in one array to use in the
  * dialog method later.
  *
  * @author Martin Kelm <kelm@idxsolutions.de>
  * @return array
  */
  function getFormCaptions() {
    return array(
      'title'  => $this->data['cpt_title'],
      'name'   => $this->data['cpt_name'],
      'email'  => $this->data['cpt_email'],
      'text'   => $this->data['cpt_text'],
      'submit' => $this->data['cpt_submit']
    );
  }

  /**
   * Initializes dialog
   *
   * @author Martin Kelm <kelm@idxsolutions.de>
   *
   * @param array $captions predefined field / formular captions
   * @param boolean $loadParams load formular params set before
   * @return boolean
   */
  function initFormDialog($loadParams = TRUE) {
    if (!isset($this->entryDialog) || !is_object($this->entryDialog)) {
      $captions = &$this->dialogCaptions;

      $hidden = array(
        'action' => 'insert'
      );
      $fields = array(
        'name' => array($captions['name'], 'isSomeText', TRUE, 'input', 200),
        'email' => array($captions['email'], 'isEmail', TRUE, 'input', 200),
        'text' => array($captions['text'], 'isSomeText', TRUE, 'textarea')
      );

      include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
      $this->surferObj = &base_surfer::getInstance();

      if ($this->surferObj->isValid !== FALSE) {
        $name = (isset($this->params['name'])) ? $this->params['name'] : '';

        if (isset($this->surferObj->surfer['surfer_givenname']) &&
            !empty($this->surferObj->surfer['surfer_givenname']) &&
            isset($this->surferObj->surfer['surfer_surname']) &&
            !empty($this->surferObj->surfer['surfer_surname'])) {
          $name = $this->surferObj->surfer['surfer_givenname'].' '.
            $this->surferObj->surfer['surfer_surname'];
        }

        $data = array(
          'name' => $name,
          'email' => $this->surferObj->surfer['surfer_email']
        );
      }

      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $this->entryDialog = new base_dialog($this, $this->paramName, $fields,
        $data, $hidden);
      $this->entryDialog->msgs = &$this->msgs;
      $this->entryDialog->baseLink = $this->baseLink;
      $this->entryDialog->dialogTitle =
        papaya_strings::escapeHTMLChars($captions['title']);
      $this->entryDialog->dialogDoubleButtons = FALSE;
      if ($loadParams) {
        $this->entryDialog->loadParams();
      }
    }
    if (isset($this->entryDialog) &&
        is_object($this->entryDialog)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets entry form xml
   *
   * @author Martin Kelm <kelm@idxsolutions.de>
   *
   * @return mixed boolean or string dialog xml
   */
  function getFormXML($loadParams = TRUE) {
    $this->initFormDialog($loadParams);
    if (isset($this->entryDialog) && is_object($this->entryDialog)) {
      return $this->entryDialog->getDialogXML();
    }
    return FALSE;
  }

  /**
   * Delivers a previous/next Navigation and links to every list page
   *
   * @author Alexander Nichau <alexander@nichau.com>
   * @author Martin Kelm <kelm@idxsolutions.de>
   *
   * @param integer $bookId
   * @param integer $max
   * @param integer $offset Starting point
   * @return string $result XML
   */
  function getXMLNavigation($bookId, $max, $offset) {
    $count = $this->outputObj->countEntries($bookId);
    $result = LF.'<nav>'.LF;
    $loops = ceil($count / $max);
    for ($i = 0; $i < $loops; $i++) {
      $selected = $i*$max == $offset ? ' selected="selected"' : '';
      $result .= sprintf('<item href="%s"%s />'.LF,
        $this->getLink(array('start' => $max*$i)), $selected);
    }
    $result .= '</nav>'.LF;

    return $result;
  }

  /**
  * This method checks the dialog, creates and entry and returns an
  * message if an erro has been occurred.
  *
  * @return string XML
  */
  function addNewEntry() {
    $result = '';
    $errorMsg = '';

    if ($this->data['show_edit_form'] == 1
        && isset($this->entryDialog)
        && is_object($this->entryDialog)) {

      if (strlen($this->entryDialog->data['text']) <=
            (int)$this->data['max_text_length']) {

        if ($this->entryDialog->checkDialogInput()) {
          if ($this->outputObj->checkSpam($this->data['block_seconds'])) {
            if ($this->outputObj->createEntry($this->data['book']) !== FALSE) {

              unset($this->entryDialog);
              $this->initFormDialog(FALSE);

              if ($this->data['admin_sendmails'] === 1) {
                $this->outputObj->sendAdminMail(
                  (string)$this->data['admin_email'],
                  (string)$this->data['admin_name'],
                  (string)$this->data['mailsubject'],
                  (string)$this->data['mailtext'],
                  (string)$this->data['mailfrom_name'],
                  (string)$this->data['mailfrom_email']
                );
              }
            }

          } else {
            $errorMsg = $this->data['msg_spam_protection'];
          }

        } else {
          $errorMsg = $this->data['msg_input_error'];
        }

      } else {
        $errorMsg = $this->data['msg_input_error'];
      }

    } else {
      $errorMsg = $this->data['msg_no_data'];
    }

    $result .= $this->outputObj->getMessageXML('error', $errorMsg);

    return $result;
  }

  /**
   * Get the guestbooks in a combo-box for the admin-interface
   *
   * @param String $name
   * @param String $field
   * @param array $data
   * @return array
   */
  function getGbCombo($name, $field, $data) {
    $this->initializeOutputObject();
    return $this->outputObj->getGuestbookCombo($this->paramName, $name,
      $this->decodeData($data));
  }

  /**
  * Decode data ugly plain data
  *
  * @param string $str
  * @access public
  * @return array
  */
  function decodeData($str) {
    $currentData = explode(';', $str);
    $result = array('id' => @trim($currentData[0]));
    return $result;
  }

}
?>
