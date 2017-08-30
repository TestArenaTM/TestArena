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
  private $_filter = null;
  
  protected $_project  = null;
  protected $_projects = array();
  protected $_projectsByPrefix = array();
  protected $_projectsForm;
  
  public function init()
  {
    parent::init();
    
    if ($this->checkUserSession())
    {
      $this->_setActiveProject();
      $this->_setRoleSettings();
      $this->_initUserFilters();
    }
  }
  
  protected function _setActiveProject()
  {
    //get user projects
    $projectMapper  = new Application_Model_ProjectMapper();
    $projectsRows = $projectMapper->getByUserId($this->_user);
    $projectNames = array();
    
    foreach ($projectsRows as $row)
    {
      $project = new Application_Model_Project($row);
      $this->_projects[$project->getId()] = $project;
      $this->_projectsByPrefix[$project->getPrefix()] = $project;
      $projectNames[$project->getId()] = $project->getName();
    }

    //set active project by form
    $request = $this->getRequest();
    $this->_projectsForm = new Application_Form_Project(array('projects' => $projectNames));
    
    if ($request->isPost() && $request->getPost('activeProject', null) !== null)
    {
      if ($this->_projectsForm->isValid($request->getPost()))
      {
        $this->_user->setDefaultProjectId($this->_projectsForm->getValue('activeProject'));
        $userMapper = new Application_Model_UserMapper();
        $userMapper->changeDefaultProjectId($this->_user);
      }
      
      $this->redirect();
    }

    //set active project by user default project id from db
    if ($this->_user->getDefaultProjectId() > 0)
    {
      if (array_key_exists($this->_user->getDefaultProjectId(), $this->_projects))
      {
        $this->_project = $this->_projects[$this->_user->getDefaultProjectId()];
        $this->_helper->layout->setLayout('project');
        $request->setParam('projectId', $this->_project->getId());
      }
      else
      {
        $this->_user->setDefaultProjectId();
        $userMapper = new Application_Model_UserMapper();
        $userMapper->changeDefaultProjectId($this->_user);
      }
      
      $this->_projectsForm->getElement('activeProject')->setValue($this->_user->getDefaultProjectId());
    }
    
    if ($this->_project !== null)
    {
      $this->_helper->layout->setLayout('project');
    }
  
    $this->view->projectsForm = $this->_projectsForm;
    $this->view->activeProject = $this->_project;
  }
  
  protected function _projectUrl($args, $routeName)
  {
    if (is_array($args) && $this->_project !== null)
    {
      $args['projectPrefix'] = $this->_project->getPrefix();
    }
    
    return $this->_url($args, $routeName);
  }
  
  public function projectRedirect($action = 'index', $controller = 'index', $module = 'default', $params = array(), $route = null, $reset = true)
  {
    if (is_array($action))
    {
      $action['projectPrefix'] = $this->_project->getPrefix();
    }
    
    return $this->redirect($action, $controller, $module, $params, $route, $reset);
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
  
  private function _initUserFilters()
  {
    $filterMapper = new Application_Model_FilterMapper();
    
    if ($this->_project === null)
    {
      $filterMapper->getForUser($this->_user);
    }
    else
    {
      $filterMapper->getForUserByProject($this->_user, $this->_project);
    }
  }
  
  protected function _getRequestForFilter($groupId)
  {
    $request = $this->getRequest();
    
    if ($request->getParam('skipSavedFilter', null) === null)
    {
      $this->_filter = $this->_user->getFilter($groupId);

      if ($this->_filter === null)
      {
        $this->_filter = new Application_Model_Filter();
        $this->_filter->setUserObject($this->_user);
        $this->_filter->setProjectObject($this->_project);
        $this->_filter->setGroup($groupId);
      }
      else
      {
        $this->_filter->prepareRequest($request);
      }
    }
    
    return $request;
  }
  
  protected function _filterAction(array $data, $multiSelectName = null)
  {
    if (array_key_exists('filterAction', $data) && is_numeric($data['filterAction']) && $data['filterAction'] > 0)
    {
      $action = $data['filterAction'];
      unset($data['filterAction']);
      $this->_clearMultiSelectIds($multiSelectName);

      if ($action == 2)
      {
        $currentData = $this->_filter->getData();

        if ($currentData === null)
        {
          $saveData = true;
        }
        else
        {
          $saveData = false;

          foreach ($data as $key => $value)
          {
            if (!array_key_exists($key, $currentData) || $value != $currentData[$key])
            {
              $saveData = true;
              break;
            }
          }
        }

        if ($saveData)
        {
          $this->_filter->setData($data);
          $filterMapper = new Application_Model_FilterMapper();
          $filterMapper->save($this->_filter);
        }
      }
    }
  }
}