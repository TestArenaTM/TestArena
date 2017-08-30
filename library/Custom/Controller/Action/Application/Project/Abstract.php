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
abstract class Custom_Controller_Action_Application_Project_Abstract extends Custom_Controller_Action_Application_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->_helper->layout->setLayout('project');    
    $this->checkUserSession(true, $this->getRequest()->isXmlHttpRequest());
    $this->_setActiveProjectByPrefix();
  }
  
  private function _setActiveProjectByPrefix()
  {
    $request = $this->getRequest();
    $projectPrefix = $request->getParam('projectPrefix', null);

    if ($projectPrefix === null || !array_key_exists($projectPrefix, $this->_projectsByPrefix))
    {
      throw new Custom_404Exception();
    }

    if ($this->_project === null || $this->_project->getId() != $this->_projectsByPrefix[$projectPrefix]->getId())
    {
      $this->_project = $this->_projectsByPrefix[$projectPrefix];

      $userMapper = new Application_Model_UserMapper();
      $this->_user->setDefaultProjectId($this->_project->getId());
      $userMapper->changeDefaultProjectId($this->_user);
      
      $this->_projectsForm->getElement('activeProject')->setValue($this->_project->getId());
      $request->setParam('projectId', $this->_project->getId());
      $this->view->activeProject = $this->_project;
    }
  }

  protected function _setRoleSettings()
  {
    if ($this->_isActiveProject())
    {
      $roleSettingsMapper = new Application_Model_RoleSettingMapper();
      $roleSettings       = $roleSettingsMapper->getUserRoleSettings($this->_user, $this->_project);
      $this->_user->setRoleSettings($roleSettings);
    }
  }
  
  protected function _checkAccess($roleActionId, $throwException = false)
  {
    $roleAcl = new Application_Model_RoleAcl();
    
    if (true === $roleAcl->isAccessAllowed($roleActionId, $this->_user))
    {
      return true;
    }
    else
    {
      if ($throwException)
      {
        $this->_throwTaskAccessDeniedException();
      }
      
      return false;
    }
  }
  
  protected function _checkMultipleAccess(array $roleActionIds)
  {
    $access = array();
    
    foreach ($roleActionIds as $id)
    {
      $access[$id] = $this->_checkAccess($id);
    }
    
    return $access;
  }
  
  protected function _isActiveProject()
  {
    return (null === $this->_project) ? false : true ;
  }
}