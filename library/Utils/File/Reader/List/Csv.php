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

class Utils_File_Reader_List_Csv extends Utils_File_Reader_List_Abstract
{
  private $_reader;
  private $_rows = null;
  private $_currentRow = 0;

  public function __construct($fileName, $delimiter = ';')
  {
    parent::__construct($fileName);
    $this->_reader = new PHPExcel_Reader_CSV();
    $this->_reader->setDelimiter($delimiter);
    $data = $this->_reader->load($fileName);
    $this->_rows = $data->setActiveSheetIndex();
    $this->_rowCount = $this->_rows->getHighestRow();
    $this->_currentRow = 2;
  }
  
  public function read()
  {
    if ($this->_currentRow > $this->_rows->getHighestRow())
    {
      $this->_name = null;
      $this->_value = null; 
    
      return false;
    }
    
    $this->_name = $this->_rows->getCellByColumnAndRow(0, $this->_currentRow);
    $this->_value = $this->_rows->getCellByColumnAndRow(1, $this->_currentRow);
    $this->_currentRow++;
    return true;
  }
  
  public function getDelimiter()
  {
    return $this->_reader->getDelimiter();
  }
}