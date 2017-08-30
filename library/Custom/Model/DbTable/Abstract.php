<?php
/*
Copyright © 2014 TestArena 

This file is part of TestArena.

TestArena is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

The full text of the GPL is in the LICENSE file.
*/
abstract class Custom_Model_DbTable_Abstract extends Zend_Db_Table_Abstract
{
  const TABLE_CONNECTOR = '$';
  /**
   * @param string $prefix Delimiter is the $ character.
   * @param array $names
   * @return array
   */
  protected function _createAlias($prefix, array $names)
  {
    $result = array();
    
    foreach ($names as $key => $name)
    {
      $key = is_numeric($key) ? $name : $key;
      $result[$prefix.self::TABLE_CONNECTOR.$key] = $name;
    }
    
    return $result;
  }
  
  /**
   * @param string $prefix Delimiter is the $ character.
   * @param array $names
   * @return array
   */
  protected function _createKeyAlias($prefix, array $names)
  {
    $result = array();
    
    foreach ($names as $key => $name)
    {
      $result[$prefix.self::TABLE_CONNECTOR.$key] = $name;
    }
    
    return $result;
  }
  
  protected function union(array $sqls)
  {
    foreach ($sqls as $i => $sql)
    {
      $sqls[$i] = '('.$sql.')';
    }
    
    return $this->select()->union($sqls);
  }
}