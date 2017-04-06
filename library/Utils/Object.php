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
class Utils_Object
{
  static public function cast($object, $className)
  {
    if(!is_object($object))
    {
      return false;
    }
    
    if(!class_exists($className))
    {
      return false;
    }
    
    $serializedParts = explode(':', serialize($object));
    $serializedParts[1] = strlen($className);
    $serializedParts[2] = '"'.$className.'"';
    return unserialize(implode(':', $serializedParts));
  }
}