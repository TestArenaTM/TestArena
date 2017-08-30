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
class MultiSelectController extends Custom_Controller_Action_Application_Abstract
{
  public function loadAjaxAction()
  {
    $this->checkUserSession(true, true);
    $name = $this->getRequest()->getPost('name');
    $items = array();
    $this->_initMultiSelectSession($name);
    $numberOfSelected = 0;
    
    foreach ($_SESSION['MultiSelect'][$name] as $id => $checked)
    {
      $items[] = array('id' => $id, 'checked' => $checked);
      $numberOfSelected += $checked ? 1 : 0;
    }
    
    echo json_encode(array(
      'status'           => 'OK',
      'items'            => $items,
      'numberOfSelected' => $numberOfSelected
    ));
    exit;
  }
  
  public function saveAjaxAction()
  {
    $this->checkUserSession(true, true);
    $request = $this->getRequest();
    $name = $request->getPost('name');
    $items = json_decode($request->getPost('items'));
    $this->_initMultiSelectSession($name);
    
    foreach ($items as $item)
    {
      $_SESSION['MultiSelect'][$name][$item->id] = $item->checked;
    }
    
    $numberOfSelected = 0;
    
    foreach ($_SESSION['MultiSelect'][$name] as $checked)
    {
      $numberOfSelected += $checked ? 1 : 0;
    }
    
    echo json_encode(array(
      'status'           => 'OK',
      'numberOfSelected' => $numberOfSelected
    ));
    exit;
  }
  
  public function clearAjaxAction()
  {
    $this->checkUserSession(true, true);
    $this->_clearMultiSelectIds($this->getRequest()->getPost('name'));
      
    echo json_encode(array(
      'status'           => 'OK',
      'numberOfSelected' => 0
    ));
    exit;
  }
}