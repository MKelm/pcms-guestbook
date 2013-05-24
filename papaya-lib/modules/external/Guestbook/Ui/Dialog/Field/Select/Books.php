<?php
/**
* Gets a selection of available guestbooks to show in the content configuration panel.
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
* Gets a selection of available guestbooks to show in the content configuration panel.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookUiDialogFieldSelectBooks extends PapayaUiControlInteractive {
  
  /**
  * Current parameter name
  * @var string
  */
  protected $_parameterName = NULL;
  
  /**
  * Current value
  * @var string
  */
  protected $_currentValue = NULL;
  
  /**
  * Books database records
  * @var PapayaModuleGuestbookContentBooks
  */
  protected $_books = NULL;
  
  /**
  * Allows to declare dynamic properties with optional getter/setter methods. The read and write
  * options can be methods or properties. If no write option is provided the property is read only.
  *
  * array(
  *   'propertyName' => array('read', 'write')
  * )
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'parameterGroup' => array('parameterGroup', 'parameterGroup'),
    'parameterName' => array('parameterName', 'parameterName'),
    'currentValue' => array('currentValue', 'currentValue'),
  );
  
  /**
  * Get/set parameter group
  * @var string $parameterGroup
  */
  public function parameterGroup($parameterGroup = NULL) {
    if (isset($parameterGroup)) {
      PapayaUtilConstraints::assertString($parameterGroup);
      $this->_parameterGroup = $parameterGroup;
    } 
    return $this->_parameterGroup;
  }
  
  /**
  * Get/set parameter name
  * @var string $parameterName
  */
  public function parameterName($parameterName = NULL) {
    if (isset($parameterName)) {
      PapayaUtilConstraints::assertString($parameterName);
      $this->_parameterName = $parameterName;
    } 
    return $this->_parameterName;
  }
  
  /**
  * Get/set current value
  * @var string $currentValue
  */
  public function currentValue($currentValue = NULL) {
    if (isset($currentValue)) {
      PapayaUtilConstraints::assertString($currentValue);
      $this->_currentValue = $currentValue;
    } 
    return $this->_currentValue;
  }
  
  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $this->books()->load();
    $values = array();
    foreach ($this->books() as $book) {
      $values[$book['id']] = $book['title'];
    }
    
    $select = $parent->appendElement(
      'select',
      array(
        'name' => $this->parameterGroup.'['.$this->parameterName.']',
        'class' => 'dialogSelect dialogScale'
      )
    );

    if (!empty($values)) {
      foreach ($values as $id => $title) {
        $option = $select->appendElement(
          'option', 
          array('value' => $id),
          PapayaUtilStringXml::escape($title)
        );
        if ($id == $this->currentValue()) {
          $option->setAttribute('selected', 'selected');
        }
      }
    }
  }
  
  /**
  * Access to the books
  *
  * @param GuestbookContentBooks $books
  * @return GuestbookContentBooks
  */
  public function books(GuestbookContentBooks $books = NULL) {
    if (isset($books)) {
      $this->_books = $books;
    } elseif (is_null($this->_books)) {
      include_once(dirname(__FILE__).'/../../../../Content/Books.php');
      $this->_books = new GuestbookContentBooks();
      $this->_books->papaya($this->papaya());
    }
    return $this->_books;
  }
}
