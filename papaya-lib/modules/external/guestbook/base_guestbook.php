<?php
/**
* Guestbook base class

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
* database class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Guestbook base class
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com>
* @author Martin Kelm <kelm@idxsolutions.de>
*/
class base_guestbook extends base_db {

  /**
  * Parameter prefix name
  * @var string
  */
  var $paramName = 'gb';

  /**
  * Parameters
  * @var array
  */
  var $params = NULL;

  /**
  * Entries
  * @var array
  */
  var $entries = NULL;
  /**
  * Entry
  * @var array
  */
  var $entry = NULL;

  var $books = NULL;
  var $book = NULL;

  var $allowedTags = array('strong', 'b', 'i', 'tt');
  var $langId = 1;

  /**
  * Constructor

  * @param refrence $parentObj parent object
  */
  function __construct(&$parentObj = NULL) {

    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;

    $this->tableEntries = PAPAYA_DB_TABLEPREFIX.'_guestbookentries';
    $this->tableBooks = PAPAYA_DB_TABLEPREFIX.'_guestbooks';
  }

  /**
  * PHP4 Wrapper
  */
  function base_guestbook(&$parentObj = NULL) {
    $this->__construct($parentObj);
  }

  /**
  * Count entries of a specified book.
  *
  * @param integer $bookId
  * @return integer amount of entries
  */
  function countEntries($bookId) {
    $sql = "SELECT COUNT(*) AS c
              FROM %s
             WHERE guestbook_id = '%s'";
    $params = array($this->tableEntries, $bookId);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($count = $res->fetchField()) {
        return (int)$count;
      }
    }

    return 0;
  }

  /**
   * Store guestbooks in $this->books
   *
   * @var $this->books
   * @return boolean
   */
  function loadBooks() {
    unset($this->books);

    $sql = "SELECT gb_id, title
              FROM %s";
    $params = array($this->tableBooks);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->books[$row['gb_id']] = $row;
      }

      return TRUE;
    }
    return FALSE;
  }


  /**
   * Delivers entries linked to a guestbook
   *
   * @param int $gbId
   * @param int $offset
   * @return boolean
   */
  function loadEntries($gbId, $limit = NULL, $offset = NULL) {
    unset($this->entries);

    if ($gbId > 0) {
      $limitCond = '';
      if ($limit !== NULL && $offset !== NULL) {
        $limitCond = sprintf(" LIMIT %d, %d", $offset, $limit);
      }

      $sql = "SELECT entry_id, entry_created, entry_text,
                     entry_ip, author, email
                FROM %s
               WHERE guestbook_id = '%s'
               ORDER BY entry_created DESC $limitCond";

      $params = array($this->tableEntries, $gbId);

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->entries[$row['entry_id']] = $row;
        }
        return TRUE;
      }
      return FALSE;
    }
  }


  /**
   * Loads one Guestbook with all parameters (title, id) and
   * stores it in $this->book
   *
   * @var $this->book
   * @param int $gb_id
   * @return boolean
   */
  function loadBook($gbId) {
    unset($this->book);

    if ($gbId >= 0) {
      $sql = "SELECT gb_id, title
                FROM %s
               WHERE gb_id = '%d'";
      $params = array($this->tableBooks, $gbId);

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->book = $row;
        }

        return TRUE;
      }
    }
    return FALSE;
  }


  /**
   * Load one entry according to its ID
   *
   * @param int $entry_id
   * @return boolean
   */
  function loadEntry($entryId) {
    unset($this->entry);

    if ($entryId >= 0) {
      $sql = "SELECT entry_id, entry_text, author, email,
                     entry_created
                FROM %s
               WHERE entry_id = '%d'";
      $params = array($this->tableEntries, $entryId);

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->entry = $row;
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Spam protection
   *
   * @author Martin Kelm <kelm@idxsolutions.de>
   * @param string $text
   * @return boolean
   * @version 19.05.2007
   */
  function checkSpam($block) {
    $checkTime = time() - $block;
    $sql = "SELECT COUNT(entry_id) AS entries
                FROM %s
               WHERE entry_created > %d
                AND entry_ip = '%s'";
    $params = array($this->tableEntries, (int)$checkTime,
      $_SERVER['REMOTE_ADDR']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['entries'] > 0) {
          return FALSE;
        }
      }
    }
    $sql = "SELECT COUNT(entry_id) AS entries
                FROM %s
               WHERE email = '%s'
                AND entry_text = '%s'";
    $params = array($this->tableEntries, @$this->params['email'],
      @$this->params['text']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['entries'] > 0) {
          return FALSE;
        }
      }
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_spamfilter.php');
    $filter = &base_spamfilter::getInstance();
    $probability = $filter->check($this->params['text'], $this->langId);
    $filter->log($this->params['text'], $this->langId, 'Guestbook Entry Text');
    if ($probability['spam'] &&
        defined('PAPAYA_SPAM_BLOCK') && PAPAYA_SPAM_BLOCK) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
   * Save a new entry with its guestbook_id in the db
   *
   * @author Martin Kelm <kelm@idxsolutions.de>
   * @param int $bookId Id of the corresponding guestbook
   * @return boolean
   * @version 19.05.2007
   */
  function createEntry($bookId) {
    $data = array(
      'author' => $this->params['name'],
      'email'  => $this->params['email'],
      'entry_text'   => $this->params['text'],
      'entry_created' => time(),
      'entry_ip'      => $_SERVER['REMOTE_ADDR'],
      'guestbook_id'  => $bookId
    );
    if ($this->databaseInsertRecord($this->tableEntries, 'entry_id', $data)) {
      return TRUE;
    }
    return FALSE;
  }


  /**
  * Send a mail to the administrator if a new gb entry has been added.
  *
  * @author Martin Kelm <kelm@idxsolutions.de>
  * @version new email object version
  *
  * @param string $mailTo Recipient E-Mail address
  * @param string $mailTo Recipient's name
  * @param string $mailSubject Subject
  * @param string $mailText Text
  * @param string $mailFromName Sender's name
  * @param string $mailFromMail Sender's E-Mail address
  * @return boolean Sent or not
  */
  function sendAdminMail($mailTo, $mailToName, $mailSubject, $mailText,
                         $mailFromName, $mailFromMail) {
    $content = array();
    $content['LINK'] = 'http://'.$_SERVER['HTTP_HOST'].
      $this->getBasePath().$this->getBaseLink();
    $content['MODERATOR'] = $mailToName;

    include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $email = new email();
    $email->setSender($mailFromMail, $mailFromName);
    $email->addAddress($mailTo, $mailToName);
    $email->setSubject($mailSubject);
    $email->setBody($mailText, $content);
    if ($email->send()) {
      return TRUE;
    }

    return FALSE;
  }

}
?>
