<?php
/**
* Guestbook admin module
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
* Guestbook base functionality
*/
require_once(dirname(__FILE__).'/base_guestbook.php');

/**
* Guestbook admin module
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com> (original 2007)
* @author Martin Kelm <kelm@idxsolutions.de> (updates 2007-2010)
*/
class admin_guestbook extends base_guestbook {

  /**
  * Forum / books table
  * @var string $tableBoards
  */
  public $tableBooks = '';

  /**
  * Entry table
  * @var string $tableEntries
  */
  public $tableEntries = '';

  /**
  * Array with local images
  * @var array $localImages
  */
  public $localImages = NULL;
  
  /**
  * Entry page list limit.
  * @var integer $entriesPerPage
  */
  public $entriesPerPage = 15;

  /*
  * Initialize - Load parameters and session variable
  */
  public function initialize() {
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('gb_id', array('cmd', 'offset'));

    $imagePath = 'module:'.$this->module->guid;
    $this->localImages = array(
      'gbook'        => $imagePath.'/gbook.gif',
      'gbook_add'    => $imagePath.'/gbook_add.gif',
      'gbook_edit'   => $imagePath.'/gbook_edit.gif',
      'gbook_open'   => $imagePath.'/gbook_open.gif',
      'gbook_remove' => $imagePath.'/gbook_remove.gif',
    );

    if (!isset($this->params['offset'])) {
      $this->params['offset'] = 0;
    }
    $this->loadBooks();
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Execute - basic function for handling parameters
  */
  public function execute() {
    $cmd = !empty($this->params['cmd']) ? $this->params['cmd'] : '';
    
    $this->params['offset'] = 
      is_numeric($this->params['offset']) == TRUE ? 
        $this->params['offset'] : 0;
    
    switch ($cmd) {
    case 'add_book' :
      if (isset($this->params['title']) && $this->params['save'] == 1) {
        if ($this->addGuestbook()) {
          $this->addMsg(
            MSG_INFO, sprintf($this->_gt('%s added.'), $this->_gt('Guestbook'))
          );
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!.'));
        }
      }
      break;
    case 'del_book' :
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete']) {
        if ($this->deleteGuestbook((int)$this->params['gb_id'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%s deleted.'), $this->_gt('Guestbook'))
          );
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!.'));
        }
      }
      break;
    case 'edit_book' :
      if (isset($this->params['save']) && $this->params['save'] == 1 &&
          isset($this->params['gb_id'])) {
        if ($this->saveGuestBook()) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%s modified.'), $this->_gt('Guestbook'))
          );
        } else {
          $this->addMsg(
            MSG_ERROR, $this->_gt('Database error! Changes not saved.')
          );
        }
        $this->loadBook($this->params['gb_id']);
      } elseif (isset($this->params['gb_id']) &&
                isset($this->books[(int)$this->params['gb_id']]) &&
                is_array($this->books[(int)$this->params['gb_id']])) {
        $this->loadBook($this->params['gb_id']);
      }
      break;
    case 'del_entry' :
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete']) {
        if (isset($this->params['entry_id']) &&
            $this->deleteEntry((int)$this->params['entry_id'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%s deleted.'), $this->_gt('Entry'))
          );
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!.'));
        }
      }
      break;
    case 'edit_entry' :
      if (isset($this->params['save']) && $this->params['save'] == 1 &&
          isset($this->params['entry_id'])) {
        if ($this->saveEntry()) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%s modified.'), $this->_gt('Entry'))
          );
        } else {
          $this->addMsg(
            MSG_ERROR, $this->_gt('Database error! Changes not saved.')
          );
        }
        $this->loadEntry($this->params['entry_id']);
      } elseif (isset($this->params['entry_id'])) {
        $this->loadEntry($this->params['entry_id']);
      }
      break;
    }
    $this->loadBooks();
  }

  /**
  * Return XML data
  * @return string
  */
  public function getXML() {
    if (is_object($this->layout)) {
      $this->getXMLButtons();
      $cmd = isset($this->params['cmd']) ? $this->params['cmd'] : '';

      switch ($cmd) {
      case 'del_book' :
        $this->getXMLDelGbForm();
        break;
      case 'del_entry' :
        $this->getXMLDelEntryForm();
        $this->getXMLEntryList();
        break;
      case 'edit_entry' :
        $this->getXMLEditEntryForm();
        $this->getXMLEntryList();
        break;
      case 'add_book' :
        $this->getXMLGbAddForm();
        break;
      case 'edit_book' :
        $this->getXMLGbEditForm();
        break;
      default :
        if (isset($this->params['gb_id']) && $this->params['gb_id'] > 0) {
          $this->getXMLEntryList();
        } else {
          $this->addMsg(MSG_INFO, sprintf($this->_gt('Please select a book.')));
        }
      }
      $this->getXMLBookList();
    }
  }

  /**
  * Get XML for buttons
  */
  public function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = &new base_btnbuilder;
    $toolbar->images = &$this->images;

    $toolbar->addButton(
      'Add Book',
      $this->getLink(array('cmd' => 'add_book')), $this->localImages['gbook_add'],
      '',
      isset($this->params['cmd']) && $this->params['cmd'] == 'add_book'
    );
    if (isset($this->params['gb_id'])) {
      $toolbar->addButton(
        'Edit Book',
        $this->getLink(
          array('cmd' => 'edit_book', 'gb_id' => $this->params['gb_id'])
        ),
        $this->localImages['gbook_edit'], '', 
        isset($this->params['cmd']) && $this->params['cmd'] == 'edit_book'
      );
    }
    if (isset($this->params['gb_id'])) {
      $toolbar->addButton(
        'Delete Book',
        $this->getLink(
          array('cmd' => 'del_book', 'gb_id' => $this->params['gb_id'])
        ),
        $this->localImages['gbook_remove'], '',
        isset($this->params['cmd']) && $this->params['cmd'] == 'del_book'
      );
    }
    if (isset($this->params['gb_id']) &&
        isset($this->params['entry_id'])) {
      $toolbar->addSeperator();	
      $toolbar->addButton(
        'Delete Entry',
        $this->getLink(
          array(
            'cmd' => 'del_entry',
            'gb_id' => $this->params['gb_id'],
            'entry_id' => $this->params['entry_id']
          )
        ),
        'actions-page-delete', '', 
        isset($this->params['cmd']) && $this->params['cmd'] == 'del_entry'
      );
    }

    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(
        sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str)
      );
    }
  }


  /**
  * Delete guestbook
  *
  * @param integer $id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  public function deleteGuestbook($id) {
    $deleted = $this->databaseDeleteRecord(
      $this->tableEntries, 'guestbook_id', $id
    );
    if ($deleted !== FALSE) {
      return FALSE !==
        $this->databaseDeleteRecord($this->tableBooks, 'gb_id', $id);
    }
    return FALSE;
  }

  /**
  * Save guestbook
  *
  * @access public
  * @return boolean
  */
  public function saveGuestbook() {
    $data = array(
      'title' => $this->params['title']
    );
    return FALSE !==
      $this->databaseUpdateRecord(
        $this->tableBooks, $data, 'gb_id', (int)$this->params['gb_id']
      );
  }


  /**
  * Add new guestbook
  *
  * @access public
  * @return boolean
  */
  public function addGuestbook() {
    $data = array(
      'title' => $this->params['title']
    );
    return FALSE !==
      $this->databaseInsertRecord($this->tableBooks, 'gb_id', $data);
  }
  
  /**
  * Save entry
  *
  * @access public
  * @return boolean
  */
  public function saveEntry() {
    $data = array(
      'entry_text' => $this->params['entry_text']
    );
    return FALSE !==
      $this->databaseUpdateRecord(
        $this->tableEntries, $data, 'entry_id', (int)$this->params['entry_id']
      );
  }


  /**
  * Delete entry
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  public function deleteEntry($id) {
    $this->loadEntry($id);
    if (isset($this->entry) && is_array($this->entry)) {
      $deleted = $this->databaseDeleteRecord(
        $this->tableEntries, 'entry_id', $id
      );
      if (FALSE !== $deleted) {
        return TRUE;
      }
    }
    return FALSE;
  }


  /**
  * Get XML for forum list
  *
  * @access public
  */
  public function getXMLBookList() {
    if (isset($this->books) && is_array($this->books)) {
      $result = sprintf(
        '<listview title="%s" width="200">'.LF, $this->_gt('Guestbooks')
      );
      $result .= '<items>'.LF;
      foreach ($this->books as $book) {
        if (isset($book) && is_array($book)) {
          $selected = (isset($this->params['gb_id']) &&
                       $this->params['gb_id'] == $book['gb_id']) ?
            ' selected="selected"' : '';

          $glyph = 'gbook';
          if (isset($this->params['gb_id']) &&
              $this->params['gb_id'] == $book['gb_id']) {
            $glyph = 'gbook_open';
          }

          $result .= sprintf('<listitem href="%s" title="%s" image="%s" %s/>'.LF,
          $this->getLink(array('gb_id' => (int)$book['gb_id'])),
          papaya_strings::escapeHTMLChars($book['title']),
          $this->localImages[$glyph], $selected);
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;echo $result;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML for delete Guestbook formular
  *
  * @access public
  */
  public function getXMLDelGbForm() {
    $this->loadBooks();
    if (isset($this->books[$this->params['gb_id']]) &&
        is_array($this->books[$this->params['gb_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_book',
        'gb_id' => $this->params['gb_id'],
        'confirm_delete' =>1,
      );
      $msg = sprintf($this->_gt('Delete guestbook "%s" (%s)?'),
      papaya_strings::escapeHTMLChars(
        $this->books[$this->params['gb_id']]['title']),
        (int)$this->params['gb_id']
      );
      $dialog = &new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }


  /**
  * Get XML for gb formular
  *
  * @access public
  */
  public function getXMLGbEditForm() {
    if (isset($this->books[$this->params['gb_id']]) &&
        is_array($this->books[$this->params['gb_id']])) {
      $this->initializeGbEditForm();
      $this->gbDialog->inputFieldSize = 'x-large';
      $this->gbDialog->baseLink = $this->baseLink;
      $this->gbDialog->dialogTitle =
        papaya_strings::escapeHTMLChars($this->_gt('Properties'));
      $this->gbDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->gbDialog->getDialogXML());
    }
  }
  
  /**
  * Get XML for gb add formular.
  * 
  * @access public
  */
  public function getXMLGbAddForm() {
    $this->initializeGbAddForm();
    $this->gbDialog->inputFieldSize = 'x-large';
    $this->gbDialog->baseLink = $this->baseLink;
    $this->gbDialog->dialogTitle =
      papaya_strings::escapeHTMLChars($this->_gt('Properties'));
    $this->gbDialog->dialogDoubleButtons = FALSE;
    $this->layout->add($this->gbDialog->getDialogXML());
  }


  /**
  * Initialize guestbook edit formular
  *
  * @access public
  */
  public function initializeGbEditForm() {
    if (!(isset($this->gbDialog) && is_object($this->gbDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->book;
      $hidden = array(
        'cmd' => 'edit_book', 'save' => 1, 'gb_id' => $data['gb_id']
      );
      $fields = array(
        'title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
      );
      $this->gbDialog = &new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->gbDialog->msgs = &$this->msgs;
      $this->gbDialog->loadParams();
    }
  }


  /**
  * Initialize guestbook add formular
  *
  * @access public
  */
  function initializeGbAddForm() {
    if (!(isset($this->gbDialog) && is_object($this->gbDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = array();
      $hidden = array('cmd'=>'add_book', 'save'=>1);
      $fields = array(
        'title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
      );
      $this->gbDialog = &new base_dialog($this, $this->paramName,
        $fields, $data, $hidden);
      $this->gbDialog->msgs = &$this->msgs;
      $this->gbDialog->loadParams();
    }
  }

  /**
  * Takes entries from the db and generates a xml view of these
  *
  * @access public
  * @return boolean
  */
  public function getXMLEntryList() {
    $this->loadEntries(
      $this->params['gb_id'], 
      $this->entriesPerPage, 
      $this->params['offset']
    );

    if (isset($this->params['gb_id']) &&
        isset($this->entries) && count($this->entries) > 0) {
      $result = sprintf('<listview width="100%%" title="%s">',
        $this->_gt('Entries'));
        
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
      $result .= papaya_paging_buttons::getPagingButtons(
        $this, NULL,
        $this->params['offset'], $this->entriesPerPage, $this->entriesAbsCount, 25
      );
        
      $result .= '<cols>';
      $result .= sprintf('<col>%s</col>', $this->_gt('Subject'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Created'));
      $result .= sprintf('<col>%s</col>', $this->_gt('Delete'));
      $result .= '</cols>';
      $result .= '<items>';

      foreach($this->entries as $entry) {
        $selected = (isset($this->params['entry_id']) &&
          $entry['entry_id'] == $this->params['entry_id']) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<listitem href="%s" title="%s" image="%s" %s>',
          $this->getLink(
            array(
              'cmd' => 'edit_entry',
              'entry_id' => $entry['entry_id'],
              'offset' => $this->params['offset']
            )
          ),
          papaya_strings::escapeHTMLChars(
            $entry['author'].' (Email: '.
            $entry['email'].', IP: '.
            $entry['entry_ip'].') '
          ),
          $this->images['items-page'], 
          $selected
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>',
          papaya_strings::escapeHTMLChars(
            date('Y-m-d H:i:s', $entry['entry_created'])
          )
        );
        
        $result .= sprintf(
          '<subitem><a href="%s"><glyph src="%s" hint="%s" /></a>'.
          '</subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => 'del_entry',
                'entry_id' => $entry['entry_id'],
                'offset' => $this->params['offset']
              )
            )
          ),
          $this->images['actions-page-delete'],
          $this->_gt('Delete Entry')
        );

        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);

      return TRUE;
    } elseif (isset($this->params['gb_id']) && 
              (!isset($this->entries) || count($this->entries) == 0)) {
      $this->addMsg(MSG_INFO, sprintf($this->_gt('No entries found.')));
	}
    return FALSE;
  }


  /**
  * Get XML for delete entry formular
  *
  * @access public
  */
  public function getXMLDelEntryForm() {
    $this->loadEntry($this->params['entry_id']);
    if (isset($this->entry) && is_array($this->entry)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_entry',
        'entry_id' => $this->entry['entry_id'],
        'offset' => $this->params['offset'],
        'confirm_delete' => 1
      );
      $msg = sprintf(
        $this->_gt('Delete entry (%s) from "%s"?'),
        (int)$this->entry['entry_id'],
        papaya_strings::escapeHTMLChars($this->entry['author'])
      );
      $dialog = &new base_msgdialog($this, $this->paramName, $hidden,
        $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }


  /**
  * Get XML for entry formular
  *
  * @access public
  */
  public function getXMLEditEntryForm() {
    if (isset($this->entry) && is_array($this->entry)) {
      
      if (!(isset($this->entryDialog) && is_object($this->entryDialog))) {
        include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
		$data = $this->entry;
		$data['entry_datetime'] = date('Y-m-d H:i', $data['entry_created']);
		
		$hidden = array(
		  'cmd' => 'edit_entry', 
		  'entry_id' => $data['entry_id'], 
		  'offset' => $this->params['offset'],
		  'save' => 1
		);
		$fields = array(
		  'author' => array('Author', 'isNoHTML', TRUE, 'disabled_input', 200),
		  'email' => array('E-Mail', 'isNoHTML', TRUE, 'disabled_input', 200),
		  'entry_datetime' => array('Time', 'isNoHTML', TRUE, 'disabled_input', 200),
		  'entry_text' => array('Text', 'isNoHTML', TRUE, 'textarea', 5)
		);
		$this->entryDialog = &new base_dialog(
		  $this, $this->paramName, $fields, $data, $hidden
		);
		$this->entryDialog->msgs = &$this->msgs;
		$this->entryDialog->loadParams();
	  }

      $this->entryDialog->inputFieldSize = 'x-large';
      $this->entryDialog->baseLink = $this->baseLink;
      $this->entryDialog->dialogTitle =
        papaya_strings::escapeHTMLChars($this->_gt('Properties'));
      $this->entryDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->entryDialog->getDialogXML());
    }
  }

}
?>
