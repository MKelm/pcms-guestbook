<?php
/**
* A guestbook cronjob to send moderator emails.
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
 * Basic cronjob class
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_cronjob.php');

/**
* A guestbook cronjob to send moderator emails.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookCronjobModeratorMail extends base_cronjob {

  /**
  * Modified?
  * @var boolean
  */
  public $modified = FALSE;

  /**
  * Edit Fields
  * @var array
  */
  public $editFields = array(
    'moderator_name' => array(
      'Moderator name', 'isNoHTML', TRUE, 'input', 50, NULL, 'Moderator'
    ),
    'moderator_email' => array(
      'Moderator email', 'isEMail', TRUE, 'input', 50, NULL, ''
    ),
    'mailfrom_name' => array(
      'From name', 'isNoHTML', TRUE, 'input', 50, NULL, 'Guestbook'
    ),
    'mailfrom_email' => array(
      'From email', 'isEMail', TRUE, 'input', 100, NULL, ''
    ),
    'entry_line' => array(
      'Entry line', 'isNoHTML', TRUE, 'input', 200, 
      'Placeholders Author, E-Mail, IP, Time, Book-ID, Language-ID', 
      "%s (E-Mail: %s, IP: %s) %s, Book %d, Language %d."
    ),
    'max_entry_age' => array(
      'Max. Entry Age', 'isNum', TRUE, 'input', 10, 'In hours', 24
    ),
    'backend_url' => array(
      'Backend URL', 'isHTTPX', TRUE, 'input', 200, NULL, ''
    ),
    
    'Message',
    'message_subject' => array(
      'Template Subject', 
      'isSomeText', 
      TRUE, 
      'input', 
      200,
      'Placeholder {%TIME%}',
      '[Guestbook] New entries @ {%TIME%}'),
    'message_body' => array(
      'Template Body', 
      'isSomeText', 
      TRUE, 
      'textarea',
      6,
      'Placeholders {%MODERATOR%}, {%ENTRIES%}, {%BACKENDURL%}',
      "Hello {%MODERATOR%},\nyou have new guestbook entries:\n{%ENTRIES%} \nGo to backend: {%BACKENDURL%}"
    )
  );

  /**
  * Book entries database records
  * @var PapayaModuleGuestbookContentBookEntries
  */
  private $_bookEntries = NULL;

  /**
   * Constructor
   *
   * @param object $owner
   * @param string $paramName
   */
  public function __construct(&$owner, $paramName = 'gb') {
    parent::__construct($owner, $paramName);
  }

  /**
   * Check execution parameters
   *
   * @return boolean Execution possible?
   */
  public function checkExecParams() {
    return TRUE;
  }

  /**
   * Basic execution
   *
   * @return integer 0
   */
  public function execute() {
    if (count($this->bookEntries()) > 0) {
      $entriesString = '';

      foreach ($this->bookEntries() as $entry) {
        $entriesString .= sprintf(
          $this->data['entry_line']."\n",
          $entry['author'],
          $entry['email'],
          $entry['ip'],
          date('Y-d-m H:i:s', $entry['created']),
          $entry['book_id'],
          $entry['language_id']
        );
      }

      include_once(
        $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '/').
        'system/sys_email.php'
      );
      $email = new email();
      $email->setSender(
        $this->data['mailfrom_name'], $this->data['mailfrom_email']
      );
      $email->addAddress(
        $this->data['moderator_email'], $this->data['moderator_name']
      );
      $time = date('Y-d-m H:i:s', time());
      $email->setSubject(
        $this->data['message_subject'], array('TIME' => $time)
      );
      $email->setBody(
        $this->data['message_body'],
        array(
          'MODERATOR' => $this->data['moderator_name'], 
          'ENTRIES' => $entriesString,
          'BACKENDURL' => $this->data['backend_url']
        )
      );
      $email->send();
    }
    return 0;
  }
  
  /**
  * Access to the book entries
  *
  * @param GuestbookContentBookEntries $bookEntries
  * @return GuestbookContentBookEntries
  */
  public function bookEntries(GuestbookContentBookEntries $bookEntries = NULL) {
    if (isset($bookEntries)) {
      $this->_bookEntries = $bookEntries;
    } elseif (is_null($this->_bookEntries)) {
      include_once(dirname(__FILE__).'/../../Content/Book/Entries.php');
      $this->_bookEntries = new GuestbookContentBookEntries();
      $this->_bookEntries->papaya($this->papaya());
      $this->_bookEntries->load(
        array(
          'min_created' => time() - ($this->data['max_entry_age'] * 3600)
        )
      );
    }
    return $this->_bookEntries;
  }
}
