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
abstract class Custom_Action_Helper_Abstract extends Zend_Controller_Action_Helper_Abstract
{
  protected $_view       = null;
  protected $_request    = null;
  protected $_controller = null;
  
  protected $_viewParams = null;
  
  public function start()
  {
    $this->_controller = $this->getActionController();
    $this->_request    = $this->_controller->getRequest();
    $this->_view       = $this->_controller->view;
    $this->_viewParams = new stdClass();
  }
}