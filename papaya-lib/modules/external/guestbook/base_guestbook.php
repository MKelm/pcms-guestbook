<?php
/**
* Edit module guestbook 
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com>
* @author Martin Kelm <martinkelm@idxsolutions.de>
*/

/**
* database class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');


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
  * @param string $paramName Name des Parameterarrays
  */
  function __construct(&$contentObj = NULL) {
    if ($contentObj) {
    	$this->paramName = $contentObj->paramName;
    	$this->baseLink = $contentObj->baseLink;
			$this->params = &$contentObj->params;
			$this->captions = &$contentObj->captions;
			$this->msgs = &$contentObj->msgs;
			if (isset($contentObj->parentObj)) {
				$this->langId = $contentObj->parentObj->getContentLanguageId();
			}
    }
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->databaseURI = PAPAYA_DB_URI;
    $this->tableEntries = PAPAYA_DB_TABLEPREFIX.'_guestbookentries';
    $this->tableBooks = PAPAYA_DB_TABLEPREFIX.'_guestbooks';
  }
  
  function base_guestbook(&$contentObj = NULL) {
    $this->__construct($contentObj);
  }
  
  /**
   * Generates content from db through helper functions
   *
   * @param int $bookId
   * @param int $maxEntries
   * @param int $offset optional
   * @param base_dialog $dialog
   * @return String $result XML set
   */
  function getOutput($bookId, $maxEntries, $offset = 0) {
    $sql = "SELECT entry_id, author, email, entry_created, entry_text 
			        FROM %s
			       WHERE guestbook_id = '%d'
			       ORDER BY entry_created DESC";
    $params = array($this->tableEntries, $bookId);
    if ($res = $this->databaseQueryFmt($sql, $params, $maxEntries, $offset)) {
      $result = '<entries>';
      while ($row = $res->fetchRow()) {
				$date = papaya_strings::escapeHTMLChars(date('Y-m-d H:i:s', $row[3]));
				$text = '<![CDATA['.$this->getXHTMLString($this->removeEvilTags($row[4], 
				'<strong></strong><b></b><i></i><a>'), FALSE).']]>';
				$text = nl2br($text);
        $result .= sprintf('	<entry id="%s" author="%s" email="%s" created="%s">%s</entry>', 
        (int)$row[0], papaya_strings::escapeHTMLChars($row[1]), 
        papaya_strings::escapeHTMLChars($row[2]), 
        papaya_strings::escapeHTMLChars($date), $text) .LF;
      }
      $result .= '</entries>';
      $result .= $this->getFormXML();
      $result .= $this->getXMLNavigation($bookId, $maxEntries, $offset);
    }
    
    return $result;
  }
  
  /**
   * Initializes dialog
   *
   * @return boolean
   * @version: Martin Kelm, 19.05.2007
   */
  function initFormDialog($loadParams = TRUE) {  	
  	if (!isset($this->entryDialog) || !is_object($this->entryDialog)) {
			$hidden = array(
				'action' => 'insert'
			);
			$fields = array(
				'name' => array($this->captions['name'], 'isSomeText', TRUE, 'input', 200),
				'email' => array($this->captions['email'], 'isEmail', TRUE, 'input', 200),
				'text' => array($this->captions['text'], 'isSomeText', TRUE, 'textarea')
			);
			$data = array(
				'name' => @$this->params['name'],
				'email' => @$this->params['email'],
				'text' => @$this->params['text']
			);
			include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
			$this->entryDialog = new base_dialog($this, $this->paramName, $fields, 
				$data, $hidden);
			$this->entryDialog->msgs = &$this->msgs;
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
   * @return mixed boolean or string dialog xml
   * @version: Martin Kelm, 19.05.2007
   */
  function getFormXML() {      
  	if (isset($this->entryDialog) && 
  	    is_object($this->entryDialog)) {  	
			$this->entryDialog->baseLink = $this->baseLink;
			$this->entryDialog->dialogTitle = 
			  papaya_strings::escapeHTMLChars($this->captions['title']);
			$this->entryDialog->dialogDoubleButtons = FALSE;
			return $this->entryDialog->getDialogXML();
	  }
	  return FALSE;
  }
	
	
	/**
  * Remove evil tags (copied from module forum)
	*
  *	@author Thomas Weinert
  * @param string $source
  * @access public
  * @return string
  */
  function removeEvilTags($source) {
    return preg_replace_callback('~<(/?(.*?))>|[<>]~i', array($this, 
      'escapeHTMLTags'), $source);
  }
	
	
	/**
  * escape unknown tags (copied from module forum)
  *
  * @param string $tagSource
  * @access public
  * @return string
  */
  function escapeHTMLTags($match) {
    if (isset($match[2]) && $match[2] != '' && 
        in_array($match[2], $this->allowedTags)) {
      $result = '<'.$match[1].'>';
    } else {
      $result = papaya_strings::escapeHTMLChars($match[0]);
    }
    return $result;
  }
  
  /**
   * Delivers a previous/next Navigation and links to every list page
   *
   * @param int $offset Starting point
   * @return String $result XML-Set
   */
  function getXMLNavigation($bookId, $max, $offset) {
    $sql = "SELECT COUNT(*) AS c 
              FROM %s 
             WHERE guestbook_id = '%s'";
    $params = array($this->tableEntries, $bookId);
    
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $row = $res->fetchRow();
      $count = $row[0];
    }
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
   * Save a new entry with its guestbook_id in the db
   *
   * @param int $bookId Id of the corresponding guestbook
   * @return boolean
   * @version: Martin Kelm, 19.05.2007
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
    	unset($this->params);
    	unset($this->entryDialog);
    	$this->initFormDialog(FALSE);
      return TRUE;
    }
    return FALSE;
  }
  
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

  /**
   * Spam protection
   * 
   * @param string $text 
   * @return boolean
   * @version: Martin Kelm, 19.05.2007
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
        $this->books[(int)$row['gb_id']] = $row;
      }

      return TRUE;
    }
    return FALSE;
  }
  
  
  /**
   * Delivers entries linked to a guestbook
   *
   * @param int $gb_id
   * @param int $offset
   * @return boolean
   */
  function loadEntries($gb_id, $offset = 0) {
    unset($this->entries);
    if (isset($gb_id)) {
      $sql = "SELECT entry_id, entry_created, entry_text, 
                     entry_ip, author, email 
                FROM %s 
               WHERE guestbook_id = '%s' 
               ORDER BY entry_created DESC ";
      $params = array($this->tableEntries, (int)$gb_id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->entries[(int)$row['entry_id']] = $row;
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
  function loadBook($gb_id) {
    unset($this->book);
    if ($gb_id >= 0) {
      $sql = "SELECT gb_id, title 
                FROM %s 
               WHERE gb_id = '%d'";
      $params = array($this->tableBooks, $gb_id);
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
  function loadEntry($entry_id) {
    unset($this->entry);
    if ($entry_id >= 0) {
      $sql = "SELECT entry_id, entry_text, author, email, 
                     entry_created 
                FROM %s 
               WHERE entry_id = '%d'";
      $params = array($this->tableEntries, $entry_id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->entry = $row;
        }
        return TRUE;
      }
    }
    return FALSE;
  }
  
  
  function getGuestbookCombo($paramName, $fieldName, $data) {
    $this->loadBooks();
    $result = sprintf('<select name="%s[%s]" class="dialogSelect dialogScale">'.LF, 
      $paramName, $fieldName);

    if (isset($this->books) && is_array($this->books)) {
      foreach ($this->books as $book) {
        if (isset($book) && is_array($book)) {
          $selected = ($book['gb_id'] == $data['id']) ? 
            ' selected="selected"' : '';
          $result .= sprintf('<option value="%d" %s>%s</option>', 
            (int)$book['gb_id'], $selected, 
            papaya_strings::escapeHTMLChars($book['title']));
        }
      }
    }
    $result .= '</select>'.LF;

    return $result;
  }

}
?>
