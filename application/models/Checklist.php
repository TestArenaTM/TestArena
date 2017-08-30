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
class Application_Model_Checklist extends Application_Model_Test implements Custom_Interface_Test
{
  private $_items = array();
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getItems()
  {
    return $this->_items;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setItems($items)
  {
    if (!is_array($items))
    {
      $items = explode(',', $items);
    }
    
    if (count($items) > 0)
    {
      foreach($items as $item)
      {
        $this->addItem($item);
      }
    }
    
    return $this;
  }
  // </editor-fold>
  
  public function addItem($item)
  {
    if ($item instanceof Application_Model_ChecklistItem)
    {
      $this->_items[] = $item;
    }
    elseif(is_array($item) && count($item) > 0)
    {
      $this->_items[] = new Application_Model_ChecklistItem($item);
    }
    elseif(is_numeric($item))
    {
      $this->_items[] = new Application_Model_ChecklistItem(array('id' => $item));
    }
    
    return $this;
  }
}