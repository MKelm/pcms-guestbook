<?php
/**
* Guestbook box moudle

* @copyright 2008 by Martin Kelm
* @link http://www.idxsolutions.de/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package guestbook
* @author Martin Kelm <kelm@idxsolutions.de>
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Guestbook box module
*
* @package module_guestbook
* @author Martin Kelm <kelm@idxsolutions.de>
*/
class actionbox_guestbook_teaser extends base_actionbox {

  /**
  * Parameter prefix name
  * @var string $paramName
  */
  var $paramName = 'gb';

  /**
  * Output object for guestbook content / box modules
  * @var object $gbObject output_guestbook
  */
  var $outputObj = NULL;

  /**
  * Edit fields
  * @var string
  */
  var $editFields = array(
    'book' => array('Guestbook', 'isNum', FALSE, 'function', 'getGbCombo',
      '', 0),
    'entries_amount' => array('Entries amount', 'isNum', TRUE,
      'input', 4, '', 3),
    'pageid_gb' => array('Guestbook page id', 'isNum', TRUE, 'pageid',
      10, '', 0),

    'Text',
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'combo',
      array(0 => 'Yes', 1 => 'No', '', 1),
      'Apply linebreaks from input to the HTML output.'),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 5),

    'Messages',
    'msg_no_data' => array('No data', 'isNoHTML', TRUE, 'input', 200, '',
      'No data found.'),

    'Captions',
    'cpt_entries' => array('Entries', 'isNoHTML', FALSE, 'input', 200, '',
      'Entries'),
    'cpt_at' => array('At', 'isNoHTML', TRUE, 'input', 200, '',
      'at'),
    'cpt_show_more' => array('Show more', 'isNoHTML', TRUE, 'input', 200, '',
      'Show more'),
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

    $result .= sprintf('<text>%s</text>',
      $this->getXHTMLString(@$this->data['text'],
        !((bool)@$this->data['nl2br'])));

    $result .= sprintf('<captions>'.LF.
                       '<entries>%s</entries>'.LF.
                       '<at>%s</at>'.LF.
                       '<show-more>%s</show-more>'.LF.
                       '</captions>'.LF,
      $this->data['cpt_entries'],
      $this->data['cpt_at'],
      $this->data['cpt_show_more']
    );

    if ($this->data['pageid_gb'] > 0) {
      $showMoreLink = $this->getWebLink($this->data['pageid_gb'], NULL, NULL);
      $result .= sprintf('<show-more-link>%s</show-more-link>'.LF,
        papaya_strings::escapeHTMLChars($showMoreLink));
    }

    if ($this->outputObj->countEntries($this->data['book']) > 0) {
      $result .= $this->outputObj->getEntriesXML(
        $this->data['book'],
        $this->data['entries_amount'],
        0
      );
    } else {
      $this->outputObj->getMessageXML('error', $this->data['msg_no_data']);
    }

    return '<gbteaser>'.LF.
           $result.LF.
           '</gbteaser>'.LF;
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
