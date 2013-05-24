<?php
/**
* A guestbook administration page.
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
* A guestbook administration page.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookAdministrationPage extends PapayaAdministrationPage {
  
  /**
  * Parameter group name
  * @var string
  */
  protected $_parameterGroup = 'gb';
  
  /**
  * Local images to use in page parts
  * @var array
  */
  private $_localImages = array();
  
  /**
  * Construct onject and set local images to use in page parts
  *
  * @var object $layout
  */
  public function __construct($layout) {
    parent::__construct($layout);
    $moduleImagePath = 'module:f1b18c4b71fb8e7a60f2a54e35f1b701/';
    $this->_localImages = array(
      'book' => $moduleImagePath.'gbook.gif',
      'book_open' => $moduleImagePath.'gbook_open.gif',
      'add_book' => $moduleImagePath.'gbook_add.gif',
      'edit_book' => $moduleImagePath.'gbook_edit.gif',
      'remove_book' => $moduleImagePath.'gbook_remove.gif'
    );
  }
  
  /**
  * Create navigation
  */
  protected function createNavigation() {
    include_once(dirname(__FILE__).'/Navigation.php');
    return new GuestbookAdministrationNavigation(
      $this->_localImages
    );
  }
  
  /**
  * Create content
  */
  protected function createContent() {
    include_once(dirname(__FILE__).'/Editor.php');
    return new GuestbookAdministrationEditor(
      $this->_localImages
    );
  }
  
}
