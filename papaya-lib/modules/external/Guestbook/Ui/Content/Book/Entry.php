<?php
/**
* A book entry item.
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
* A book entry item.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookUiContentBookEntry extends PapayaUiControlCollectionItem {

  /**
  * Entry id
  * 
  * @var integer
  */
  protected $_id = NULL;
  
  /**
  * Entry author
  * 
  * @var string
  */
  protected $_author = '';
  
  /**
  * Entry email
  * 
  * @var string
  */
  protected $_email = '';
  
  /**
  * Entry created date
  * 
  * @var string
  */
  protected $_created = '';
  
  /**
  * Entry text
  * 
  * @var string
  */
  protected $_text = '';

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'id' => array('_id', '_id'),
    'author' => array('_author', '_author'),
    'email' => array('_email', '_email'),
    'created' => array('_created', 'setCreated'),
    'text' => array('_text', '_text')
  );
  
  /**
  * Create object and store intialization values.
  *
  * @param string $image
  * @param string|PapayaUiString $caption
  * @param array $actionParameters
  */
  public function __construct($id, $author, $email, $created, $text) {
    $this->id = $id;
    $this->author = $author;
    $this->email = $email;
    $this->created = $created;
    $this->text = $text;
  }
  
  /**
  * Set a created date string.
  * 
  * @param integer $created
  */
  protected function setCreated($created) {
    $this->_created = date('Y-m-d H:i:s', $created);
  }
  
  /**
  * Return the collection for the item, overload for code completion and type check
  *
  * @param PapayaUiContentBookEntries $items
  * @return PapayaUiContentBookEntries
  */
  public function collection(PapayaUiContentBookEntries $entries = NULL) {
    return parent::collection($entries);
  }
  
  /**
  * Append entry item xml to parent xml element.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $entry = $parent->appendElement(
      'entry',
      array(
        'id' => (string)$this->_id,
        'author' => (string)$this->_author,
        'email' => (string)$this->_email,
        'created' => (string)$this->_created
      )
    );
    include_once(
      $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '/').
      'system/sys_base_object.php'
    );
    $entry->appendXml(
      base_object::getXHTMLString($this->_text, TRUE)
    );
  }
}
