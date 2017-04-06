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
class Utils_File_Reader_List_Xml extends Utils_File_Reader_List_Abstract
{
  const ITEMS_TAG_NAME  = 'items';
  const ITEM_TAG_NAME = 'item';
  
  private $_reader;
  private $_isItems = false;
  
  public function __construct($fileName)
  {
    parent::__construct($fileName);
    $this->_reader = new XMLReader();
    $this->_reader->open($fileName);
    
    while ($this->_reader->read())
    {
      if ($this->_reader->nodeType == XMLReader::ELEMENT && $this->_reader->name == self::ITEMS_TAG_NAME)
      {
        $this->_isItems = true;
        break;
      }
    }
  }
  
  public function read()
  {
    while ($this->_isItems && $this->_reader->read())
    {
      if ($this->_reader->nodeType == XMLReader::ELEMENT && $this->_reader->name == self::ITEM_TAG_NAME)
      {
        $this->_name = $this->_reader->getAttribute('name');
        $this->_value = $this->_reader->getAttribute('value');
        return true;
      }
      elseif ($this->_reader->nodeType == XMLReader::END_ELEMENT && $this->_reader->name == self::ITEMS_TAG_NAME)
      {
        $this->_isItems = false;
      }
    }
    
    $this->_name = null;
    $this->_value = null; 
    
    return false;
  }
}