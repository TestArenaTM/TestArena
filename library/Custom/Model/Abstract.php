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
abstract class Custom_Model_Abstract
{
  const EMPTY_DATE = '0000-00-00';
  
  static public function getPrefixedProperties($prefix, array $names)
  {
    $result = array();
    
    foreach ($names as $name)
    {
      $result[] = $prefix.'_'.$name;
    }
    
    return $result;
  }
  
  static public function getPrefixedPropertiesWhitAlias($prefix, $aliasPrefix, array $names)
  {
    $result = array();
    
    foreach ($names as $name)
    {
      $result[$aliasPrefix.'_'.$name] = $prefix.'_'.$name;
    }
    
    return $result;
  }
  
  public function __set($name, $value)
  {
    throw new Exception('Set exception!');
  }
  
  public function __get($name)
  {
    throw new Exception('Get exception!');
  }
}