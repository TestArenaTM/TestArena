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
class Utils_File_Ini
{
  static public function write($fileName, $data, $hasSections = false)
  { 
    $content = '';
    
    if ($hasSections)
    { 
      foreach ($data as $sectionName => $section)
      { 
        $content .= '['.$sectionName."]\n";
        
        foreach ($section as $name => $value)
        {
          if (is_array($value))
          {
            foreach ($value as $v)
            {
              $content .= $name.'[] = "'.$v."\"\n"; 
            }
          } 
          elseif($value == '')
          {
            $content .= $name." = \n";
          }
          else
          {
            $content .= $name.' = "'.$value."\"\n"; 
          }
        } 
      } 
    } 
    else
    {
      foreach ($data as $name => $value)
      {
        if (is_array($value)) 
        {
          foreach ($value as $v)
          {
            $content .= $name.'[] = "'.$v."\"\n"; 
          }
        } 
        elseif ($value == '')
        {
          $content .= $name." = \n"; 
        }
        else 
        {
          $content .= $name.' = "'.$value."\"\n"; 
        }
      } 
    } 

    if (file_put_contents($fileName, $content) === false)
    {
      return false;
    }
    
    return true; 
  }
  
  static public function read($fileName, $hasSections = true)
  {
    return parse_ini_file($fileName, $hasSections);
  }
}