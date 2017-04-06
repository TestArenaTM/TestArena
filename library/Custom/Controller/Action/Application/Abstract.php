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
abstract class Custom_Controller_Action_Application_Abstract extends Custom_Controller_Action_Abstract
{
  protected $_project  = null;
  protected $_projects = array();
  
  public function init()
  {
    parent::init();
    
    if ($this->checkUserSession())
    {
      $this->_setActiveProject();
      $this->_setRoleSettings();
    }
  }
  
  protected function _setActiveProject()
  {
    //get user projects
    $projectMapper  = new Application_Model_ProjectMapper();
    $projectsDbRows = $projectMapper->getByUserId($this->_user);
    $projectsNames  = array();
    
    foreach ($projectsDbRows as $row)
    {
      $this->_projects[$row['id']] = $row;
      $projectsNames[$row['id']]   = $row['name'];
    }
    
    $form = new Application_Form_Project(array('projects' => $projectsNames));
    
    $request = $this->getRequest();
    
    //set active project by form
    if ($request->isPost() && $request->getPost('activeProject', null) !== null)
    {
      if ($form->isValid($request->getPost()))
      {
        $this->_user->setDefaultProjectId($form->getValue('activeProject'));
        $userMapper = new Application_Model_UserMapper();
        $userMapper->changeDefaultProjectId($this->_user);
      }
      
      $this->redirect();
    }

    //set active project by user default project id from db
    if ($this->_user->getDefaultProjectId() > 0 && array_key_exists($this->_user->getDefaultProjectId(), $this->_projects))
    {
      $this->_project = new Application_Model_Project($this->_projects[$this->_user->getDefaultProjectId()]);
      
      $this->_helper->layout->setLayout('project');
      $form->getElement('activeProject')->setValue($this->_user->getDefaultProjectId());
    }
    
    $this->view->projectsForm = $form;
    $this->view->activeProject = $this->_project;
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
  
  protected function _throwTaskAccessDeniedException()
  {
    if ($this->getRequest()->isXmlHttpRequest())
    {
      $this->_throwTaskAccessDeniedExceptionAjax();
    }
    else
    {
      throw new Custom_AccessDeniedException();
    }
  }
  
  protected function _throwTaskAccessDeniedExceptionAjax()
  {
    $t = new Custom_Translate();

    $result['status'] = 'ERROR';
    $result['errors'][] = $t->translate('Dostęp zabroniony', null, 'default_error_error');

    echo Zend_Json::encode($result);
    exit();
  }
  
  protected function _throwTask500ExceptionAjax()
  {
    $t = new Custom_Translate();

    $result['status'] = 'ERROR';
    $result['errors'][] = $t->translate('Błąd aplikacji', null, 'default_error_error');

    echo Zend_Json::encode($result);
    exit();
  }
}