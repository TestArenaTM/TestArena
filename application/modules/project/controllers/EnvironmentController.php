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

class Project_EnvironmentController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
 
    if (!$this->getRequest()->isXmlHttpRequest())
    {
      if ($this->_project === null)
      {
        throw new Custom_404Exception();
      }
      
      if (!in_array($this->getRequest()->getActionName(), array('index', 'view')))
      {
        $this->_project->checkFinished();
        $this->_project->checkSuspended();
      }
    }
  }
  
  private function _getFilterForm()
  {
    return new Project_Form_EnvironmentFilter(array('action' => $this->_projectUrl(array(), 'environment_list')));
  }
    
  public function indexAction()
  {
    $this->_setCurrentBackUrl('environment_list');
    $request = $this->_getRequestForFilter(Application_Model_FilterGroup::ENVIRONMENTS);
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $this->_filterAction($filterForm->getValues(), 'environment');
      $environmentMapper = new Project_Model_EnvironmentMapper();
      list($list, $paginator) = $environmentMapper->getAll($request);
    }
    else
    {
      $list = array();
      $paginator = null;
    }
    
    $filter = $this->_user->getFilter(Application_Model_FilterGroup::ENVIRONMENTS);
    
    if ($filter !== null)
    {
      $filterForm->prepareSavedValues($filter->getData());
    }
    
    $this->_setTranslateTitle();
    $this->view->environments = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->accessEnvironmentManagement = $this->_checkAccess(Application_Model_RoleAction::ENVIRONMENT_MANAGEMENT);
  }
    
  public function viewAction()
  {
    $environment = $this->_getValidEnvironmentForView();
    $this->_setTranslateTitle(array('name' => $environment->getName()), 'headTitle');
    $this->view->environment = $environment;
    $this->view->accessEnvironmentManagement = $this->_checkAccess(Application_Model_RoleAction::ENVIRONMENT_MANAGEMENT);
    $this->view->backUrl = $this->_getBackUrl('environment_list', $this->_projectUrl(array(), 'environment_list'));
  }
  
  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    $result = $environmentMapper->getAllAjax($this->getRequest());
    
    if (count($result) > 0)
    {
      foreach($result as $key => $item)
      {
        $result[$key]['name'] = $htmlSpecialCharsFilter->filter($item['name']);
      }
    }

    echo json_encode($result);
    exit;
  }
    
  private function _getAddEnvironmentForm()
  {
    return new Project_Form_AddEnvironment(array(
      'action'    => $this->_projectUrl(array('projectId' => $this->_project->getId()), 'environment_add_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
  }
  
  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::ENVIRONMENT_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddEnvironmentForm();
  }

  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::ENVIRONMENT_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'environment_list');
    }
    
    $form = $this->_getAddEnvironmentForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $environment = new Application_Model_Environment($form->getValues());
    $environment->setProjectObject($this->_project);
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $t = new Custom_Translate();

    if ($environmentMapper->add($environment))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  private function _getEditEnvironmentForm(Application_Model_Environment $environment)
  {
    $form = new Project_Form_EditEnvironment(array(
      'action'    => $this->_projectUrl(array('id' => $environment->getId()), 'environment_edit_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'id'        => $environment->getId()
    ));

    return $form->populate($environment->getExtraData('rowData'));
  }
  
  public function editAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::ENVIRONMENT_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $environment = $this->_getValidEnvironmentForEdit();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditEnvironmentForm($environment);
    $this->view->environment = $environment;
  }

  public function editProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::ENVIRONMENT_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'environment_list');
    }
    
    $environment = $this->_getValidEnvironmentForEdit();
    $form = $this->_getEditEnvironmentForm($environment);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->environment = $environment;
      return $this->render('edit');
    }
    
    $t = new Custom_Translate();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $environment->setProperties($form->getValues());
    $environment->setProjectObject($this->_project);

    if ($environmentMapper->save($environment))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  public function deleteAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::ENVIRONMENT_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $environment = $this->_getValidEnvironment();
    $t = new Custom_Translate();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    
    if ($environmentMapper->delete($environment))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect(array(), 'environment_list');
  }
  
  private function _getValidEnvironment()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $environment = new Application_Model_Environment($idValidator->getFilteredValues());
    $environment->setProjectObject($this->_project);
    return $environment;
  }
  
  private function _getValidEnvironmentForEdit()
  {
    $environment = $this->_getValidEnvironment();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $rowData = $environmentMapper->getForEdit($environment);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    return $environment->setExtraData('rowData', $rowData);
  }
  
  private function _getValidEnvironmentForView()
  {
    $environment = $this->_getValidEnvironment();
    $environmentMapper = new Project_Model_EnvironmentMapper();

    if ($environmentMapper->getForView($environment) === false)
    {
      throw new Custom_404Exception();
    }
    
    return $environment;
  }
}