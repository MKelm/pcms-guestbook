<?php
/**
* This object loads the defined book entries.
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
* This object loads the defined book entries.
*
* @package Papaya-Modules
* @subpackage External-Guestbook
*/
class GuestbookContentBookEntries extends PapayaDatabaseRecords {
	
  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
	  'id' => 'entry_id',
    'language_id' => 'entry_lngid',
    'book_id' => 'guestbook_id',
    'created' => 'entry_created',
    'text' => 'entry_text',
    'ip' => 'entry_ip',
    'author' => 'author',
    'email' => 'email'
  );
  
  /**
  * An array of order by properties and directions.
  *
  * @var array(string=>integer)|NULL
  */
  protected $_orderByProperties = array(
    'created' => PapayaDatabaseInterfaceOrder::DESCENDING
  );

  /**
  * Table containing books
  *
  * @var string
  */
  protected $_tableName = 'guestbookentries';

  /**
  * An array of properties, used to compile the identifer
  *
  * @var array(string)
  */
  protected $_identifierProperties = array('id', 'language_id');
  
  /**
  * Loading entries using a min_created timestamp to get a range of entries
  *
  * @param scalar|array $filter
  * @param string $prefix
  * @return string
  */
  protected function _compileCondition($filter, $prefix = " WHERE ") {
    return parent::_compileCondition($filter, $prefix);
    if (isset($filter['min_created'])) {
      $conditions .= empty($conditions) ? $prefix : ' AND ';
      $conditions .= sprintf(
        " entry_created > '%d'", (int)$filter['min_created']
      );
    }
  }
}
