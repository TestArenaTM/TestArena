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
class Utils_File_Writer_List_Xml extends Utils_File_Writer_List_Abstract
{
  const ITEMS_TAG_NAME = 'items';
  const ITEM_TAG_NAME  = 'item';
  
  private $_writer;
  
  public function __construct($fileName)
  {
    parent::__construct($fileName);
    $this->_writer = new XMLWriter();
    $this->_writer->openMemory();
    $this->_writer->setIndent(true);
    $this->_writer->setIndentString('  ');
    
    $this->_writer->startDocument('1.0', 'UTF-8');
    $this->_writer->startElement(self::ITEMS_TAG_NAME);
  }

  public function write($name, $value)
  {
    $this->_writer->startElement(self::ITEM_TAG_NAME);
    $this->_writer->writeAttribute('name', $name);
    $this->_writer->writeAttribute('value', $value);
    $this->_writer->endElement();
  }
  
  public function close()
  {
    $this->_writer->endElement();
    $this->_writer->endDocument();
    file_put_contents($this->getFileName(), $this->_writer->outputMemory());
  }
}