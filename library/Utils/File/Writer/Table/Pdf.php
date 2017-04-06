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

class Utils_File_Writer_Table_Pdf extends Utils_File_Writer_Table_Abstract
{
  private $_delimiter;
  private $_data;
  private $_rows;
  private $_currentRow;
  
  public function __construct($fileName, array $columns, $delimiter = ';')
  {
    parent::__construct($fileName, $columns);
    $this->_delimiter = $delimiter;
    $this->_data = new PHPExcel();
    $this->_rows = $this->_data->setActiveSheetIndex();
    $this->_currentRow = 2;
    
    foreach ($this->getColumns() as $i => $name)
    {
      $this->_rows->setCellValueByColumnAndRow($i, 1, $name);
    }
  }
  
  public function write(array $row)
  {
    foreach ($row as $key => $value)
    {
      if (($columnIndex = $this->getColumnOrder($key)) !== false)
      {
        $this->_rows->setCellValueByColumnAndRow($columnIndex, $this->_currentRow, $value);
      }
    }
    
    $this->_currentRow++;
  }
  
  public function close()
  {
    $margins = new PHPExcel_Worksheet_PageMargins();
    $margins->setBottom(0)->setTop(0)->setLeft(0)->setRight(0);
    $this->_rows->setPageMargins($margins);
    $writer = new PHPExcel_Writer_PDF($this->_data);
    $writer->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    $writer->save($this->getFileName());
  }
}