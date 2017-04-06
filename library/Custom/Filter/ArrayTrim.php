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
require_once 'Zend/Filter/Interface.php';

class Custom_Filter_ArrayTrim extends Zend_Filter_StringTrim
{
  public function filter($value)
  {
    return $this->_initFilter($value);
  }
  
  private function _initFilter(&$value)
  {
    if (is_array($value))
    {
      if (count($value) > 0)
      {
        foreach ($value as $key => $item)
        {
          if (is_array($item))
          {
            $this->_initFilter($item);
          }
          else
          {
            $value[$key] = $this->_trim($item);
          }
        }
        
        return $value;
      }
    }
    else
    {
      return $this->_trim($value);
    }
  }
  
  private function _trim($value)
  {
    if (null === $this->_charList)
    {
      return $this->_unicodeTrim((string) $value);
    }
    else
    {
      return $this->_unicodeTrim((string) $value, $this->_charList);
    }
  }
}

