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

class Utils_File_Reader_Table_Xls extends Utils_File_Reader_Table_Abstract
{
  private $_reader;
  private $_rows = null;
  private $_currentRow = 0;

  public function __construct($fileName)
  {
    parent::__construct($fileName);
    $this->_reader = new PHPExcel_Reader_Excel5();
    $data = $this->_reader->load($fileName);
    $this->_rows = $data->setActiveSheetIndex();
    $this->_currentRow = 2;
    $this->_columns = array();
    

    while ($this->_rows->cellExistsByColumnAndRow($this->_columnCount, 1))
    {
      $this->_columns[$this->_columnCount] = $this->_rows->getCellByColumnAndRow($this->_columnCount, 1)->getValue();
      $this->_columnCount++;
    }
  }
  
  public function read()
  {
    if ($this->_currentRow > $this->_rows->getHighestRow())
    {
      return false;
    }
    
    $row = array();
    
    for ($i = 0; $i < $this->_columnCount; $i++)
    {
      $row[$this->_columns[$i]] = $this->_rows->getCellByColumnAndRow($i, $this->_currentRow)->getValue();
    }
    
    $this->_currentRow++;
    return $row;
  }
}