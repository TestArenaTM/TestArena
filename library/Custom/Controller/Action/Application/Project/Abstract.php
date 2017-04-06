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
    
    //if (!$this->getRequest()->isXmlHttpRequest())
    {
      $this->_setActiveProjectById();
    }
  }
  
  protected function _setActiveProjectById()
  {
    $idValidator = new Application_Model_Validator_ProjectId();
    
    if ($idValidator->isValid($this->_getAllParams()))
    {
      $projectId = $idValidator->getFilteredValue('projectId');
      
      if ($this->_project === null || $this->_project->getId() != $projectId)
      {
        if (!array_key_exists($projectId, $this->_projects))
        {
          $this->redirect(array(), 'index');
        }
        $this->_helper->layout->setLayout('project');
        
        $this->_project = new Application_Model_Project($this->_projects[$projectId]);
        
        $userMapper = new Application_Model_UserMapper();
        $this->_user->setDefaultProjectId($this->_project->getId());
        $userMapper->changeDefaultProjectId($this->_user);
        
        $this->view->projectsForm->getElement('activeProject')->setValue($this->_project->getId());
      }
      
      $this->view->activeProject = $this->_project;
    }
    
    if ($this->_project instanceof Application_Model_Project)
    {
      $this->getRequest()->setParam('projectId', $this->_project->getId());
    }
  }
}