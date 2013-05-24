<?php
/**
* Guestbook content moudle
*
* @copyright 2007-2010 by Alexander Nichau, Martin Kelm
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
* @author Alexander Nichau <alexander@nichau.com> (original 2007)
* @author Martin Kelm <kelm@idxsolutions.de> (updates 2007-2010)
*/

/**
* Basic class Page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Guestbook content module
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com> (original 2007)
* @author Martin Kelm <kelm@idxsolutions.de> (updates 2007-2010)
*/
class content_guestbook extends base_content {

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
  * Output object for guestbook content / box modules
  * @var object $gbObject output_guestbook
  */
  public $outputObj = NULL;

  /**
   * Dialog form to enter a new entry.
   * var object $entryDialog base_dialog
   */
  public $entryDialog = NULL;

  /**
  * Set dialog captions if the dialog is available
  * @var array $dialogCaptions
  */
  public $dialogCaptions = array();

  /**
  * Edit fields
  * @var string
  */
  public $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, NULL, ''),
    'book' => array(
      'Guestbook', 'isNum', FALSE, 'function', 'getGbCombo', '', 0
    ),
    'entries_per_page' => array(
      'Entries per page', 'isNum', TRUE, 'input', 4, NULL, 10
    ),
    'block_seconds' => array(
      'Seconds to block ip', 'isNum', TRUE, 'input', 4, NULL, 30
    ),
    'max_text_length' => array(
      'Maximal length of text', 'isNum', TRUE, 'input', 4, NULL, 255
    ),
    'show_edit_form' => array(
      'Show edit formular', 'isNum', TRUE, 'combo',
      array(0 => 'No', 1 => 'Yes'), NULL, 1
    ),

    'Text',
    'nl2br' => array(
      'Automatic linebreak', 'isNum', FALSE, 'combo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.',
      0
    ),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, NULL, ''),

    'Emails',
    'admin_sendmails' => array(
      'Send moderator emails', 'isNum', TRUE, 'combo',
      array(1 => 'Yes', 0 => 'No'), NULL, 0
    ),
    'admin_name' => array(
      'Moderator name', 'isNoHTML', TRUE, 'input', 50, NULL, 'Moderator name'
    ),
    'admin_email' => array(
      'Moderator email', 'isEMail', TRUE, 'input', 50, NULL, ''
    ),
    'mailfrom_name' => array(
      'From name', 'isNoHTML', TRUE, 'input', 50, NULL, 'Guestbook'
    ),
    'mailfrom_email' => array(
      'From email', 'isEMail', TRUE, 'input', 100, NULL, ''
    ),
    'mailsubject' => array(
      'Email subject', 'isNoHTML', TRUE, 'input', 200, NULL,
      'New entry in guestbook'
    ),
    'mailtext' => array(
      'Email text', 'isSomeText', TRUE, 'textarea', 3,
      '{%LINK%}, {%MODERATOR%}', '{%LINK%}', ''
    ),

    'Messages',
    'msg_no_data' => array(
      'No data', 'isNoHTML', TRUE, 'input', 200, NULL, 'No data found.'
    ),
    'msg_input_error' => array(
      'Input error', 'isNoHTML', TRUE, 'input', 200, NULL,
      'Please check your input.'
    ),
    'msg_spam_protection' => array(
      'Spam protection', 'isNoHTML', TRUE, 'input', 200, NULL,
      'Spam detected, please wait or change your message.'
    ),

    'Form captions',
    'cpt_title' => array(
      'Title', 'isNoHTML', TRUE, 'input', 200, NULL, 'Title'
    ),
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
      'Submit', 'isNoHTML', TRUE, 'input', 200, NULL, 'Submit'
    ),

    'Captions',
    'cpt_entries' => array(
      'Entries', 'isNoHTML', TRUE, 'input', 200, NULL, 'Entries'
    ),
    'cpt_at' => array(
      'At', 'isNoHTML', TRUE, 'input', 200, NULL, 'at'
    ),
    'cpt_previous' => array(
      'Previous page', 'isSomeText', TRUE, 'input', 200, NULL, '<'
    ),
    'cpt_next' => array(
      'Next page', 'isSomeText', TRUE, 'input', 200, NULL, '>'
    )
  );

  /**
  * Initialize output object to use specific output methods and base methods.
  */
  private function _initializeOutputObject() {
    if (empty($this->outputObj) || !is_object($this->outputObj)) {
      include_once(dirname(__FILE__).'/output_guestbook.php');
      $this->outputObj = &new output_guestbook($this);
    }
  }

  /**
  * Get parsed data
  *
  * @return string
  */
  public function getParsedData() {
    $this->setDefaultData();
    $this->_initializeOutputObject();

    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString(
        $this->data['text'], !((bool)$this->data['nl2br'])
      )
    );

    $result .= $this->_getCaptionsXML();

    if ($this->data['show_edit_form'] == 1) {
      $this->dialogCaptions = $this->_getFormCaptions();
      // Parameters form captions and load parameters of field values
      unset($this->entryDialog);
      $this->_initFormDialog(TRUE);
    }

    $action = isset($this->params['action']) ? $this->params['action'] : '';
    switch($action) {
    case 'insert':
      $result .= $this->_addNewEntry();
      $result .= $this->_getDefaultOutputXML();
      break;
    default:
      $result .= $this->_getDefaultOutputXML();
    }

    return $result;
  }

  /**
  * Get captions xml
  *
  * @return string xml
  */
  private function _getCaptionsXML() {
    return sprintf('<captions>'.LF.
                   '<entries>%s</entries>'.LF.
                   '<at>%s</at>'.LF.
                   '<previous>%s</previous>'.LF.
                   '<next>%s</next>'.LF.
                   '<submit>%s</submit>'.LF.
                   '</captions>'.LF,
      papaya_strings::escapeHTMLChars($this->data['cpt_entries']),
      papaya_strings::escapeHTMLChars($this->data['cpt_at']),
      papaya_strings::escapeHTMLChars($this->data['cpt_previous']),
      papaya_strings::escapeHTMLChars($this->data['cpt_next']),
      papaya_strings::escapeHTMLChars($this->data['cpt_submit'])
    );
  }

  /**
  * This method gets the default xml set.
  *
  * @param array $formCaptions contains an empty array if the formular is not available
  * @return string xml
  */
  private function _getDefaultOutputXML() {
    $start = isset($this->params['start']) ? (int)$this->params['start'] : 0;
    $result = $this->outputObj->getEntriesXML(
      (int)$this->data['book'],
      (int)$this->data['entries_per_page'],
      $start
    );
    $result .= $this->_getXMLNavigation(
      (int)$this->data['book'],
      (int)$this->data['entries_per_page'],
      $start
    );

    if ($this->data['show_edit_form'] == 1) {
      $result .= $this->_getFormXML(TRUE);
    }

    return $result;
  }

  /**
  * This method set aggregates formular captions in one array to use in the
  * dialog method later.
  *
  * @return array
  */
  private function _getFormCaptions() {
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
   * @param array $captions predefined field / formular captions
   * @param boolean $loadParams load formular params set before
   * @return boolean
   */
  private function _initFormDialog($loadParams = TRUE) {
    if (!isset($this->entryDialog) || !is_object($this->entryDialog)) {
      $captions = &$this->dialogCaptions;

      $hidden = array(
        'action' => 'insert'
      );
      $fields = array(
        'name' => array(
          $captions['name'], 'isSomeText', TRUE, 'input', 200, NULL, ''
        ),
        'email' => array(
          $captions['email'], 'isEmail', TRUE, 'input', 200, NULL, ''
        ),
        'text' => array(
          $captions['text'], 'isSomeText', TRUE, 'textarea', NULL, ''
        )
      );

      include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
      $this->surferObj = &base_surfer::getInstance();

      if ($this->surferObj->isValid !== FALSE) {
        $name = isset($this->params['name']) ? $this->params['name'] : '';

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
      } else {
        $data = array();
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
    if (isset($this->entryDialog) && is_object($this->entryDialog)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets entry form xml
   *
   * @return mixed boolean or string dialog xml
   */
  private function _getFormXML($loadParams = TRUE) {
    $this->_initFormDialog($loadParams);
    if (isset($this->entryDialog) && is_object($this->entryDialog)) {
      return $this->entryDialog->getDialogXML();
    }
    return FALSE;
  }

  /**
   * Delivers a previous/next Navigation and links to every list page
   *
   * @param integer $bookId
   * @param integer $max
   * @param integer $offset Starting point
   * @return string $result XML
   */
  private function _getXMLNavigation($bookId, $max, $offset) {
    $count = $this->outputObj->countEntries($bookId);
    $result = LF.'<nav>'.LF;
    $loops = ceil($count / $max);
    for ($i = 0; $i < $loops; $i++) {
      $selected = $i*$max == $offset ? ' selected="selected"' : '';
      $result .= sprintf(
        '<item href="%s"%s />'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('start' => $max*$i))
        ),
        $selected
      );
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
  private function _addNewEntry() {
    $result = '';
    $errorMsg = '';

    if ($this->data['show_edit_form'] == 1 &&
        isset($this->entryDialog) && is_object($this->entryDialog)) {

      if (!empty($this->params['text']) && strlen($this->params['text']) <=
            (int)$this->data['max_text_length']) {

        if ($this->entryDialog->checkDialogInput()) {
          $data = &$this->entryDialog->data;
          $noSpam = $this->outputObj->checkSpam(
            $this->data['block_seconds'], $data['email'], $data['text']
          );
          if ($noSpam !== FALSE) {
            $created = $this->outputObj->createEntry(
              $this->data['book'], $data['name'], $data['email'], $data['text']
            );
            if ($created !== FALSE) {

              unset($this->entryDialog);
              $this->_initFormDialog(FALSE);

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
  public function getGbCombo($name, $field, $data) {
    $this->_initializeOutputObject();
    return $this->outputObj->getGuestbookCombo(
      $this->paramName, $name, $this->_decodeData($data)
    );
  }

  /**
  * Decode data ugly plain data
  *
  * @param string $str
  * @access public
  * @return array
  */
  private function _decodeData($str) {
    $currentData = explode(';', $str);
    $result = array('id' => @trim($currentData[0]));
    return $result;
  }

}
?>