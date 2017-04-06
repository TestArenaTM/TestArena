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

class Utils_File_Writer_Table_Csv extends Utils_File_Writer_Table_Abstract
{
  private $_delimiter;
  private $_handle;
  
  public function __construct($fileName, array $columns, $delimiter = ';')
  {
    parent::__construct($fileName, $columns);
    $this->_delimiter = $delimiter;
    $this->_handle = fopen($fileName, 'w');
    fputcsv($this->_handle, $this->getColumns(), $this->_delimiter);
  }
  
  public function write(array $row)
  {
    $data = array_fill(0, count($row) - 1, null);
    
    foreach ($row as $key => $value)
    {
      if (($columnIndex = $this->getColumnOrder($key)) !== false)
      {
        $data[$columnIndex] = $value;
      }
    }

    fputcsv($this->_handle, $data, $this->_delimiter);
  }
  
  public function writeMany(array $rows)
  {
    foreach ($rows as $row)
    {
      if (is_array($row))
      {
        $this->write($row);
      }
    }
  }
  
  public function close()
  {
    fclose($this->_handle);
  }
  
  public function getDelimiter()
  {
    return $this->_delimiter;
  }
}