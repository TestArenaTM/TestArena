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
class Utils_File_Reader_Table_Xml extends Utils_File_Reader_Table_Abstract
{
  const TABLE_TAG_NAME  = 'table';
  const COLUMNS_TAG_NAME = 'columns';
  const COLUMN_TAG_NAME = 'column';
  const ROWS_TAG_NAME    = 'rows';
  const ROW_TAG_NAME    = 'row';
  
  private $_reader;
  private $_isTable = false;
  private $_isRows = false;
  
  public function __construct($fileName)
  {
    parent::__construct($fileName);
    $this->_reader = new XMLReader();
    $this->_reader->open($fileName);

    $isColumns = false;
    
    while ($this->_reader->read())
    {
      if ($this->_reader->nodeType == XMLReader::ELEMENT)
      {
        if (!$this->_isTable && $this->_reader->name == self::TABLE_TAG_NAME)
        {
          $this->_isTable = true;
          $isColumns = false;
        }
        else
        {
          if (!$isColumns)
          {
            if ($this->_reader->name == self::COLUMNS_TAG_NAME)
            {
              $this->_columns = array();
              $isColumns = true;
            }
            elseif ($this->_reader->name == self::ROWS_TAG_NAME)
            {
              $this->_isRows = true;
              break;
            }
          }
          elseif ($this->_reader->name == self::COLUMN_TAG_NAME)
          {
            $this->_columns[$this->_columnCount++] = $this->_reader->readString();
          }
        }
      }
      elseif ($this->_reader->nodeType == XMLReader::END_ELEMENT && $this->_reader->name == self::COLUMNS_TAG_NAME)
      {
        $isColumns = false;
      }
    }
  }
  
  public function read()
  {
    $row = array();
    $isRow = false;

    while ($this->_isTable && $this->_isRows && $this->_reader->read())
    {
      if ($this->_reader->nodeType == XMLReader::ELEMENT)
      {
        if (!$isRow && $this->_reader->name == self::ROW_TAG_NAME)
        {
          $isRow = true;
        }
        elseif ($isRow)
        {
          if ($this->hasColumn($this->_reader->name))
          {
            $row[$this->_reader->name] = $this->_reader->readString();
          }
        }
      }
      elseif ($this->_reader->nodeType == XMLReader::END_ELEMENT)
      {
        if ($isRow && $this->_reader->name == self::ROW_TAG_NAME)
        {
          return $row;
        }
        elseif ($this->_isRows && $this->_reader->name == self::ROWS_TAG_NAME)
        {
          $this->_isRows = false;
        }
        elseif ($this->_isTable && $this->_reader->name == self::TABLE_TAG_NAME)
        {
          $this->_istable = false;
        }
      }
    }

    return false;
  }
}