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
class Application_Model_DefectUserPermission extends Custom_Model_UserPermission_Abstract
{
  static public $_defectRoleActions = array(
    Application_Model_RoleAction::DEFECT_ADD,
    Application_Model_RoleAction::DEFECT_ASSIGN_ALL,
    Application_Model_RoleAction::DEFECT_EDIT_ALL,
    Application_Model_RoleAction::DEFECT_DELETE_ALL,
    Application_Model_RoleAction::DEFECT_CHANGE_STATUS_ALL,
    Application_Model_RoleAction::DEFECT_EDIT_CREATED_BY_YOU,
    Application_Model_RoleAction::DEFECT_DELETE_CREATED_BY_YOU,
    Application_Model_RoleAction::DEFECT_CHANGE_STATUS_CREATED_BY_YOU,
    Application_Model_RoleAction::DEFECT_DELETE_ASSIGNED_TO_YOU,
    Application_Model_RoleAction::DEFECT_CHANGE_STATUS_ASSIGNED_TO_YOU,
    Application_Model_RoleAction::DEFECT_EDIT_ASSIGNED_TO_YOU
  );
  
  public function __construct(Application_Model_Defect $defect, Application_Model_User $user, array $userPermissions)
  {
    $this->_object = $defect;
    parent::__construct($user, $userPermissions);
  }
  
  public function isAddPermission()
  {
    return true === $this->_userPermissions[Application_Model_RoleAction::DEFECT_ADD];
  }
  
  public function isEditPermission()
  {
    if ($this->_checkAuthorPermission(Application_Model_RoleAction::DEFECT_EDIT_CREATED_BY_YOU)
          || $this->_checkAllPermission(Application_Model_RoleAction::DEFECT_EDIT_ALL)
          || $this->_checkAssignedToYouPermission(Application_Model_RoleAction::DEFECT_EDIT_ASSIGNED_TO_YOU))
    {
      return true;
    }
    
    return false;
  }
  
  public function isAssignPermission()
  {
    if ($this->_checkAllPermission(Application_Model_RoleAction::DEFECT_ASSIGN_ALL)
        || $this->_checkAllPermission(Application_Model_RoleAction::DEFECT_EDIT_ALL)
        || $this->_checkAuthorPermission(Application_Model_RoleAction::DEFECT_EDIT_CREATED_BY_YOU)
        || $this->_checkAssignedToYouPermission(Application_Model_RoleAction::DEFECT_EDIT_ASSIGNED_TO_YOU))
    {
      return true;
    }
    
    return false;
  }
  
  public function isChangeStatusPermission()
  {
    if ($this->_checkAuthorPermission(Application_Model_RoleAction::DEFECT_CHANGE_STATUS_ALL)
          || $this->_checkAllPermission(Application_Model_RoleAction::DEFECT_CHANGE_STATUS_ASSIGNED_TO_YOU)
          || $this->_checkAssignedToYouPermission(Application_Model_RoleAction::DEFECT_CHANGE_STATUS_CREATED_BY_YOU)
          || $this->_checkAllPermission(Application_Model_RoleAction::DEFECT_EDIT_ALL)
          || $this->_checkAuthorPermission(Application_Model_RoleAction::DEFECT_EDIT_CREATED_BY_YOU)
          || $this->_checkAssignedToYouPermission(Application_Model_RoleAction::DEFECT_EDIT_ASSIGNED_TO_YOU))
    {
      return true;
    }
    
    return false;
  }
  
  public function isDeletePermission()
  {
    if ($this->_checkAuthorPermission(Application_Model_RoleAction::DEFECT_DELETE_CREATED_BY_YOU)
          || $this->_checkAssigneePermission(Application_Model_RoleAction::DEFECT_DELETE_ASSIGNED_TO_YOU)
          || $this->_checkAllPermission(Application_Model_RoleAction::DEFECT_DELETE_ALL))
    {
      return true;
    }
    
    return false;
  }
}