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
class Utils_File_Writer_Table_Xml extends Utils_File_Writer_Table_Abstract
{
  const TABLE_TAG_NAME  = 'table';
  const COLUMNS_TAG_NAME = 'columns';
  const COLUMN_TAG_NAME = 'column';
  const ROWS_TAG_NAME    = 'rows';
  const ROW_TAG_NAME    = 'row';
  
  private $_writer;

  public function __construct($fileName, array $columns)
  {
    parent::__construct($fileName, $columns);
    $this->_writer = new XMLWriter();
    $this->_writer->openMemory();
    $this->_writer->setIndent(true);
    $this->_writer->setIndentString('  ');
    $this->_writer->startDocument('1.0', 'UTF-8');
    $this->_writer->startElement(self::TABLE_TAG_NAME);
    $this->_writer->startElement(self::COLUMNS_TAG_NAME);
    
    foreach ($this->getColumns() as $name)
    {
      $this->_writer->startElement(self::COLUMN_TAG_NAME);
      $this->_writer->text($name);
      $this->_writer->endElement();
    }
    
    $this->_writer->endElement();
    $this->_writer->startElement(self::ROWS_TAG_NAME);
  }
  
  public function write(array $row)
  {
    $this->_writer->startElement(self::ROW_TAG_NAME);
    
    foreach ($row as $key => $value)
    {
      if ($this->hasColumn($key))
      {
        $this->_writer->startElement($key);
        $this->_writer->text($value);
        $this->_writer->endElement();
      }
    }
    
    $this->_writer->endElement();
  }
  
  public function close()
  {
    $this->_writer->endElement();
    $this->_writer->endElement();
    $this->_writer->endDocument();
    file_put_contents($this->getFileName(), $this->_writer->outputMemory());
  }
}