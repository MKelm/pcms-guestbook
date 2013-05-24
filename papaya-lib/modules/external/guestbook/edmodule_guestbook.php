<?php
/**
* Edit module guestbook
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com>
*/

/**
* Basic class modules
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');


class edmodule_guestbook extends base_module {
  /**
  * Permissions
  * @var array
  */
  var $permissions = array(1 => 'Manage');

  /**
  * Execute module
  *
  * @access public
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      $path = dirname(__FILE__);
      include_once($path.'/admin_guestbook.php');
      $gb = &new admin_guestbook;
      $gb->module = &$this;
      $gb->images = &$this->images;
      $gb->msgs = &$this->msgs;
      $gb->layout = &$this->layout;
      $gb->authUser = &$this->authUser;
      $gb->initialize();
      $gb->execute();
      $gb->getXML();
    }
  }
}

?>
