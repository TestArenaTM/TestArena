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
class Administration_RoleController extends Custom_Controller_Action_Administration_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
     
    if (!$this->_user->getAdministrator())
    {
      throw new Custom_AccessDeniedException();
    }
    
    $this->_helper->layout->setLayout('administration');
  }
  
  public function indexAction()
  {
    $request    = $this->getRequest();
    $filterForm = $this->_getFilterForm();
    $list       = array();
    $paginator  = null;
    
    if ($filterForm->isValid($request->getParams()))
    {
      $roleMapper = new Administration_Model_RoleMapper();
      list($list, $paginator) = $roleMapper->getAll($request);
    }
    
    $this->_setTranslateTitle();
    $this->view->roles = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
  }
  
  public function addAction()
  {
    $this->_setTranslateTitle();
    
    $roleActionMapper = new Administration_Model_RoleActionMapper();
    list($roleActions, $groupedRoleActions) = $roleActionMapper->getAllOrdered4RoleManagement();
    
    $projectMapper = new Administration_Model_ProjectMapper();
    $form          = $this->_getAddForm($roleActions);
    $projectId     = $this->_getParam('projectId', false);
    
    if ($projectId && $projectMapper->checkIfExists($projectId))
    {
      $this->view->prePopulatedProjects = $form->prepareJsonData($projectMapper->getForPopulateByIds(array($projectId), true));
    }
    
    $this->view->roleActions              = $groupedRoleActions;
    $this->view->form                     = $form;
    $this->view->defaultRoleTypesSettings = $this->_getDefaultRoleTypesSettingsJson();
    $this->view->defaultRoleTypes         = $this->_getDefaultRoleTypesJson();
  }
  
  public function addProcessAction()
  {
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'admin_role_add');
    }
    
    $roleActionMapper = new Administration_Model_RoleActionMapper();
    list($roleActions, $groupedRoleActions) = $roleActionMapper->getAllOrdered4RoleManagement();
    $form = $this->_getAddForm($roleActions);
    
    if (!$form->isValid(array_merge($request->getPost(), $request->getPost('roleSettings'))))
    {
      $projectMapper = new Administration_Model_ProjectMapper();
      $userMapper    = new Administration_Model_UserMapper();
      
      $this->_setTranslateTitle();
      $this->view->roleActions              = $groupedRoleActions;
      $this->view->form                     = $form;
      $this->view->prePopulatedProjects     = $form->prepareJsonData($projectMapper->getForPopulateByIds(explode(',',$form->getValue('projects')), true));
      $this->view->prePopulatedUsers        = $form->prepareJsonData($userMapper->getForPopulateByIds(explode(',', $form->getValue('users')), true));
      $this->view->defaultRoleTypesSettings = $this->_getDefaultRoleTypesSettingsJson();
      $this->view->defaultRoleTypes         = $this->_getDefaultRoleTypesJson();
      
      return $this->render('add'); 
    }
    
    $role       = new Application_Model_Role($form->getValues());
    $roleMapper = new Administration_Model_RoleMapper();
    
    $t = new Custom_Translate();
    
    if ($roleMapper->addRole($role))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function editAction()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      return $this->redirect(array(), 'admin_role_list');
    }
    
    $role       = new Application_Model_Role($idValidator->getFilteredValues());
    $roleMapper = new Administration_Model_RoleMapper();
    
    if (false === $roleMapper->getById($role) 
      || Application_Model_ProjectStatus::FINISHED == $role->getProject()->getStatusId())
    {
      throw new Custom_404Exception('Role not found!');
    }
    
    $roleActionMapper = new Administration_Model_RoleActionMapper();
    list($roleActions, $groupedRoleActions) = $roleActionMapper->getAllOrdered4RoleManagement();
    
    $form          = $this->_getEditForm($roleActions, $role);
    
    $roleArrayData = $role->getExtraData('roleData');
    
    $this->view->roleActions              = $groupedRoleActions;
    $this->view->prePopulatedProjects     = $form->prepareJsonRoleData($roleArrayData);
    $this->view->prePopulatedUsers        = $form->prepareJsonData($roleArrayData['users']);
    $this->view->form                     = $form;
    $this->view->role                     = $role;
    $this->view->defaultRoleTypesSettings = $this->_getDefaultRoleTypesSettingsJson();
    $this->view->defaultRoleTypes         = $this->_getDefaultRoleTypesJson();
  }
  
  public function editProcessAction()
  {
    $request = $this->getRequest();
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$request->isPost() || !$idValidator->isValid($request->getParams()))
    {
      return $this->redirect(array(), 'admin_role_list');
    }
    
    $role       = new Application_Model_Role($idValidator->getFilteredValues());
    $roleMapper = new Administration_Model_RoleMapper();
    
    if (false === $roleMapper->getById($role) 
      || Application_Model_ProjectStatus::FINISHED == $role->getProject()->getStatusId())
    {
      throw new Custom_404Exception('Role not found!');
    }
    
    $roleActionMapper = new Administration_Model_RoleActionMapper();
    list($roleActions, $groupedRoleActions) = $roleActionMapper->getAllOrdered4RoleManagement();
    $form        = $this->_getEditForm($roleActions, $role);
    
    if (!$form->isValid(array_merge($request->getPost(), $request->getPost('roleSettings'))))
    {
      $projectMapper = new Administration_Model_ProjectMapper();
      $userMapper    = new Administration_Model_UserMapper();
      
      $this->_setTranslateTitle();
      $this->view->roleActions              = $groupedRoleActions;
      $this->view->prePopulatedProjects     = $form->prepareJsonData($projectMapper->getForPopulateByIds(array('id'=>$form->getValue('projects')), true));
      $this->view->prePopulatedUsers        = $form->prepareJsonData($userMapper->getForPopulateByIds(explode(',', $form->getValue('users')), true));
      $this->view->form                     = $form;
      $this->view->role                     = $role;
      $this->view->defaultRoleTypesSettings = $this->_getDefaultRoleTypesSettingsJson();
      $this->view->defaultRoleTypes         = $this->_getDefaultRoleTypesJson();
      
      return $this->render('edit'); 
    }
    
    $role->clearUsers();
    $role->setProperties($form->getValues());
    
    $t = new Custom_Translate();
    
    if ($roleMapper->editRole($role))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function deleteAction()
  {
    $request = $this->getRequest();
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($request->getParams()))
    {
      return $this->redirect(array(), 'admin_role_list');
    }
    
    $role       = new Application_Model_Role($idValidator->getFilteredValues());
    $roleMapper = new Administration_Model_RoleMapper();
    
    if (false === $roleMapper->getById($role) 
      || Application_Model_ProjectStatus::FINISHED == $role->getProject()->getStatusId())
    {
      throw new Custom_404Exception('Role not found!');
    }
    
    $t = new Custom_Translate();
    
    if (count($role->getUsers()) > 0)
    {
      $this->_messageBox->set($t->translate('statusUsersAssigned'), Custom_MessageBox::TYPE_WARNING);
      return $this->redirect($request->getServer('HTTP_REFERER'));
    }
    
    if ($roleMapper->deleteRole($role))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($request->getServer('HTTP_REFERER'));
  }
  
  private function _getFilterForm()
  {
    $projectMapper = new Administration_Model_ProjectMapper();
    return new Administration_Form_RoleFilter(array(
      'action'      => $this->_url(array(), 'admin_role_list'),
      'projectList' => $projectMapper->getNotFinishedAllAsOptions()
    ));
  }
  
  private function _getAddForm(array $roleActions)
  {
    return new Administration_Form_AddRole( array(
      'action'            => $this->_url(array(), 'admin_role_add_process'),
      'method'            => 'post',
      'roleActions'       => $roleActions
    ));
  }
  
  private function _getEditForm(array $roleActions, Application_Model_Role $role)
  {
    $form = new Administration_Form_EditRole( array(
      'action'            => $this->_url(array('id' => $role->getId()), 'admin_role_edit_process'),
      'method'            => 'post',
      'roleActions'       => $roleActions,
      'roleId'            => $role->getId()
    ));
    
    $roleArrayData = $role->getExtraData('roleData');
    $roleArrayData['users'] = $form->prepareUsersDataForPopulate($roleArrayData['users']);
    
    $form->populate(array_merge($role->map($roleArrayData), $form->prepareRoleSettingCheckboxes($roleArrayData['roleSettings'])));
    
    return $form;
  }

  public function editUsersAjaxAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isXmlHttpRequest())
    {
      throw new Custom_AccessDeniedException();
    }
    
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$request->isPost() || !$idValidator->isValid($request->getParams()))
    {
      echo json_encode(array('status' => 'ERROR'));
      exit;
    }
    
    $role = new Application_Model_Role($idValidator->getFilteredValues());
    $form = new Administration_Form_EditUsersRole();
    $data = $request->getPost();

    if (!$form->checkToken($data['authtoken']))
    {
      $t = new Custom_Translate();
      echo json_encode(array(
        'status'    => 'ERROR',
        'errors'    => array('users' => array(), 'authtoken' => array($t->translate('notSame', null, 'error'))),
        'authtoken' => $form->generateNewToken()
      ));
      exit;
    }
    
    if (!$form->isValid($data))
    {
      $t = new Custom_Translate();
      $errors = array();

      foreach ($form->getErrors() as $name => $error)
      {
        $errors[$name] = array();
        
        foreach ($error as $message)
        {
         $errors[$name][] = $t->translate($message, null, 'error'); 
        }
      }
      
      echo json_encode(array(
        'status'    => 'ERROR',
        'errors'    => $errors,
        'authtoken' => $form->generateNewToken()
      ));
      exit;
    }
    
    $roleUserMapper = new Administration_Model_RoleUserMapper();
    $role->setUsers($form->getValue('users'));
    $status = 'ERROR';

    if ($roleUserMapper->saveAssignment($role))
    {
      $status = 'SUCCESS';
    }
    
    echo json_encode(array(
      'status'    => $status,
      'authtoken' => $form->generateNewToken()
    ));
    exit;
  }
  
  private function _getDefaultRoleTypesJson()
  {
    return json_encode(Application_Model_Role::$defaultRoleTypes);
  }
  
  private function _getDefaultRoleTypesSettingsJson()
  {
    $role = new Application_Model_Role();
    return json_encode($role->getDefaultRoleTypesSetting());
  }
}