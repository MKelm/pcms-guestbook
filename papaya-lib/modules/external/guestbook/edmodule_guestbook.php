<?php
/**
* Guestbook admin moudle

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
* Basic class modules
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Guestbook admin module
*
* @package module_guestbook
* @author Alexander Nichau <alexander@nichau.com>
* @author Martin Kelm <kelm@idxsolutions.de>
*/
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
