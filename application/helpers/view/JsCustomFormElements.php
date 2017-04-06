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
class Zend_View_Helper_JsCustomFormElements extends Zend_View_Helper_Abstract
{
  private $_kits = array(
    array(
      'module'      => 'user',
      'controller'  => 'login',
      'action'      => 'index'
    ),
    array(
      'module'      => 'user',
      'controller'  => 'login',
      'action'      => 'process'
    ),
    array(
      'module'      => 'user',
      'controller'  => 'register',
      'action'      => 'index'  
    ),
    array(
      'module'      => 'user',
      'controller'  => 'register',
      'action'      => 'process'  
    ),
  );
  
  public function jsCustomFormElements()
  {
    $front = Zend_Controller_Front::getInstance();
    $request = $front->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    $action = $request->getActionName();
    
    foreach ($this->_kits as $kit)
    {
      if ($module == $kit['module'] && $controller == $kit['controller'] && $action == $kit['action'])
      {
        return true;
      }
    }
    
    return false;
  }
}