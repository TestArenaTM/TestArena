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
require_once(_LIBRARY_PATH.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');

class Utils_File_Reader_Table_Csv extends Utils_File_Reader_Table_Abstract
{
  private $_delimiter;
  private $_handle;

  public function __construct($fileName, $delimiter = ';')
  {
    parent::__construct($fileName);
    $this->_delimiter = $delimiter;
    $this->_handle = fopen($fileName, 'r');
    $this->_columns = fgetcsv($this->_handle, 0, $this->_delimiter);
    $this->_columnCount = count($this->_columns);
  }
  
  public function read()
  {
    $result = array();
    
    if ($this->getColumnCount())
    {
      $row = fgetcsv($this->_handle, 0, $this->_delimiter);

      if ($row === false)
      {
        return false;
      }
      
      foreach ($this->_columns as $index => $name)
      {
        $result[$name] = $row[$index];
      }
    }
    else
    {
      return false;
    }
    
    return $result;
  }
  
  public function readAll()
  {
    $rows = array();
    
    while (($row = $this->read()) !== false)
    {
      $rows[] = $row;
    }

    return $rows;
  }
  
  public function getDelimiter()
  {
    return $this->_delimiter;
  }
  
  public function close()
  {
    fclose($this->_handle);
  }
}