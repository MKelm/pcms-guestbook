<?php
/**
* A guestbook administration module.
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
* Base class for administration modules.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* A guestbook administration module.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class edmodule_administration extends base_module {

  /**
  * Permissions
  * @var array
  */
  public $permissions = array(1 => 'Manage');

  /**
  * Execute module
  *
  * @access public
  */
  public function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      $path = dirname(__FILE__);
      include_once($path.'/Administration/Page.php');
      $administration = new GuestbookAdministrationPage(
        $this->layout
      );
      $administration->execute();
    }
  }
}
?>
