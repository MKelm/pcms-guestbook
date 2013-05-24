<?php
/**
* Edit module guestbook
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
* Check library for string validation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');


class content_guestbook extends base_content {

  /**
  * Parameter prefix name
  * @var string
  */
  var $paramName = 'gb';

  /**
  * cacheable ?
  * @var boolean
  */
  var $cacheable = FALSE;

  var $captions = array();
	
  /**
  * Edit fields
  * @var string
  */
  var $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, ''),
	  'book' => array('Guestbook', 'isNum', FALSE, 'function', 'getGbCombo'),
	  'entries_per_page' => array('Entries per page', 'isNum', TRUE, 
      'input', 4, '', 10),
    'block_seconds' => array('Seconds to block ip', 'isNum', TRUE, 
      'input', 4, '', 30),
    'max_text_length' => array('Maximal length of text', 'isNum', TRUE, 
      'input', 4, '', 255),
    'Text',
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'combo', 
      array(0 => 'Yes', 1 => 'No'),
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
      'input', 100, '', 'admin@localhost'),
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
		'Captions', 
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
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->initializeParams();
    
    $this->captions['title'] = @$this->data['cpt_title'];
    $this->captions['name'] = @$this->data['cpt_name'];
    $this->captions['email'] = @$this->data['cpt_email'];
    $this->captions['text'] = @$this->data['cpt_text'];
    $this->captions['submit'] = @$this->data['cpt_submit'];
    
    include_once(dirname(__FILE__).'/base_guestbook.php');
    $gbObject = &new base_guestbook($this);
    $gbObject->initFormDialog();

    $result = sprintf('<title>%s</title>'.LF,
    $this->getXHTMLString(@$this->data['title']));
    $result .= sprintf('<text>%s</text>',
    $this->getXHTMLString(@$this->data['text'], 
      !((bool)@$this->data['nl2br']))); 
    
    $errorMsg = '';
    switch($this->params['action']) {
		case 'insert':		
		  if (isset($gbObject->entryDialog) && is_object($gbObject->entryDialog)) {
		  	if (strlen($gbObject->entryDialog->data['text']) <= 
		  	      (int)$this->data['max_text_length']) {
					if ($gbObject->entryDialog->checkDialogInput()) {
						if ($gbObject->checkSpam(@$this->data['block_seconds'])) {
							$gbObject->createEntry($this->data['book']);
							
							if ((int)@$this->data['admin_sendmails'] == 1) {
								$gbObject->sendAdminMail(
									(string)@$this->data['admin_email'], 
									(string)@$this->data['admin_name'],
									(string)@$this->data['mailsubject'],
									(string)@$this->data['mailtext'],
									(string)@$this->data['mailfrom_name'], 
									(string)@$this->data['mailfrom_email']
								);
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
		  	
		  if ($errorMsg) {
		  	$result .= '<message type="error">'.LF;
				$result .= $this->getXHTMLString($errorMsg);
				$result .= '</message>'.LF;
		  }
			$result .= $gbObject->getOutput((int)@$this->data['book'], 
			  (int)@$this->data['entries_per_page']);
			break;
		default:
			$result .= $gbObject->getOutput((int)@$this->data['book'], 
			  (int)@$this->data['entries_per_page'], $this->params['start']);
    }

    return $result;
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

  /**
   * Get the guestbooks in a combo-box for the admin-interface
   *
   * @param String $name
   * @param String $field
   * @param array $data
   * @return array
   */
  function getGbCombo($name, $field, $data) {
    include_once(dirname(__FILE__).'/base_guestbook.php');
    if (is_object($gbObject) == FALSE) {
      $gbObject = &new base_guestbook();
    }
    return $gbObject->getGuestbookCombo($this->paramName, $name, 
      $this->decodeData($data));
  }
  
}
?>
