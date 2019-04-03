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

class Zend_View_Helper_SpaceToEntity extends Zend_View_Helper_Abstract
{
  public function spaceToEntity($value, $force = false)
  {
    if ($force)
    {
      return str_replace(' ', '&nbsp;', $value);
    }

    preg_match_all('#\h{2,}#m', $value, $matches, PREG_PATTERN_ORDER);
    if (isset($matches[0]))
    {
      $matches = $matches[0];
      sort($matches);
      foreach ($matches as $val)
      {
        $value = str_replace($val, str_repeat('&nbsp;',  strlen($val)), $value);
      }
    }

    return $value;
  }
}