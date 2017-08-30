<?php
/*
Copyright © 2017 TestArena 

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
class Utils_File_Writer_Csv
{
  private $_delimiter;
  
  public function __construct($fileName, $delimiter = ';')
  {
    $this->_delimiter = $delimiter;
    $this->_handle = fopen($fileName, 'w');
  }
  
  public function write()
  {
    fputcsv($this->_handle, func_get_args(), $this->_delimiter);
  }
  
  public function writeRow(array $row)
  {
    fputcsv($this->_handle, $row, $this->_delimiter);
  }
  
  public function writeRows(array $rows)
  {
    foreach ($rows as $row)
    {
      fputcsv($this->_handle, $row, $this->_delimiter);
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