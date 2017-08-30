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
abstract class Custom_Model_UserPermission_Abstract
{
  protected $_userPermissions = array();
  protected $_user            = null;
  protected $_object          = null;
  
  public function __construct(Application_Model_User $user, array $userPermissions)
  {
    if (!isset($this->_object))
    {
      throw new LogicException(get_class($this) . ' must have a $_object');
    }
    
    $this->_user            = $user;
    $this->_userPermissions = $userPermissions;
  }
  
  protected function _checkAuthorPermission($roleActionId)
  {
    if ($this->_object->getAuthorId() == $this->_user->getId()
        && true === $this->_userPermissions[$roleActionId])
    {
      return true;
    }
    
    return false;
  }
  
  protected function _checkAllPermission($roleActionId)
  {
    if (true === $this->_userPermissions[$roleActionId])
    {
      return true;
    }
    
    return false;
  }
  
  protected function _checkAssignedToYouPermission($roleActionId)
  {
    if ($this->_object->getAssignerId() == $this->_user->getId()
        && true === $this->_userPermissions[$roleActionId])
    {
      return true;
    }
    
    return false;
  }
  
  protected function _checkAssigneePermission($roleActionId)
  {
    if ($this->_object->getAssigneeId() == $this->_user->getId()
        && true === $this->_userPermissions[$roleActionId])
    {
      return true;
    }
    
    return false;
  }
  
  protected function _checkAssignerPermission($roleActionId)
  {
    if ($this->_object->getAssignerId() == $this->_user->getId()
        && true === $this->_userPermissions[$roleActionId])
    {
      return true;
    }
    
    return false;
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