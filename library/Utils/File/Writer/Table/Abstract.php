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
abstract class Utils_File_Writer_Table_Abstract
{
  private $_fileName;
  private $_columns = array();
  private $_columnsMap = array();
  private $_columnCount = 0;
  
  public function __construct($fileName, array $columns)
  {
    $this->_fileName = $fileName;
    $i = 0;
    
    foreach ($columns as $key => $name)
    {
      $this->_columns[$i] = $name;
      $this->_columnsMap[$key] = $i++;
      $this->_columnCount++;
    }
  }
  
  abstract public function write(array $row);
  abstract public function close();

  public function getColumnCount()
  {
    return $this->_columnCount;
  }
  
  public function getColumns()
  {
    return $this->_columns;
  }
  
  public function getFileName()
  {
    return $this->_fileName;
  }
  
  public function hasColumn($key)
  {
    return array_key_exists($key, $this->_columnsMap);
  }
  
  public function getColumnOrder($key)
  {
    return array_key_exists($key, $this->_columnsMap) ? $this->_columnsMap[$key] : false;
  }
}