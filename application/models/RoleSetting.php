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
class Application_Model_RoleSetting extends Custom_Model_Standard_Abstract
{
  private $_id         = null;
  private $_role       = null;
  private $_roleAction = null;
  
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getRole()
  {
    return $this->_role;
  }
  
  public function getRoleAction()
  {
    return $this->_roleAction;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }

  public function setRole(array $roleData)
  {
    $this->_role = new Application_Model_Role($roleData);
    return $this;
  }
  
  public function setRoleAction($id)
  {
    $this->_roleAction = new Application_Model_RoleAction($id);
    return $this;
  }
  // </editor-fold>
}