<?php
/**
* A date time subitem.
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
* A date time subitem.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookUiListviewSubitemDateTime extends PapayaUiListviewSubitemDate {
  
  /**
  * Create subitem object
  *
  * @param integer $timestamp
  */
  public function __construct($timestamp) {
    PapayaUtilConstraints::assertInteger($timestamp);
    $this->_timestamp = $timestamp;
    $this->_options = self::SHOW_DATE|self::SHOW_TIME;
  }
}
