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

class Utils_File_Writer_List_Csv extends Utils_File_Writer_List_Abstract
{
  private $_delimiter;
  private $_data;
  private $_rows;
  private $_currentRow = 1;
  
  public function __construct($fileName, $delimiter = ';')
  {
    parent::__construct($fileName);
    $this->_delimiter = $delimiter;
    $this->_data = new PHPExcel();
    $this->_rows = $this->_data->setActiveSheetIndex();
  }
  
  public function write($name, $value)
  {
    $this->_rows->setCellValueByColumnAndRow(0, $this->_currentRow, $name);
    $this->_rows->setCellValueByColumnAndRow(1, $this->_currentRow++, $value);
  }
  
  public function close()
  {
    $writer = new PHPExcel_Writer_CSV($this->_data);
    $writer->setDelimiter($this->_delimiter);
    $writer->save($this->getFileName());
  }
  
  public function getDelimiter()
  {
    return $this->_delimiter;
  }
}