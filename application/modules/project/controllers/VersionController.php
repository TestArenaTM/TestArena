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
class Project_VersionController extends Custom_Controller_Action_Application_Project_Abstract
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
    return new Project_Form_VersionFilter(array('action' => $this->_projectUrl(array(), 'version_list')));
  }
    
  public function indexAction()
  {
    $this->_setCurrentBackUrl('version_list');
    $request = $this->_getRequestForFilter(Application_Model_FilterGroup::VERSIONS);
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $this->_filterAction($filterForm->getValues(), 'version');
      $versionMapper = new Project_Model_VersionMapper();
      list($list, $paginator) = $versionMapper->getAll($request);
    }
    else
    {
      $list = array();
      $paginator = null;
    }
    
    $filter = $this->_user->getFilter(Application_Model_FilterGroup::VERSIONS);
    
    if ($filter !== null)
    {
      $filterForm->prepareSavedValues($filter->getData());
    }
    
    $this->_setTranslateTitle();
    $this->view->versions = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->accessVersionManagement = $this->_checkAccess(Application_Model_RoleAction::VERSION_MANAGEMENT);
  }
    
  public function viewAction()
  {
    $version = $this->_getValidVersionForView();
    $this->_setTranslateTitle(array('name' => $version->getName()), 'headTitle');
    $this->view->version = $version;
    $this->view->accessVersionManagement = $this->_checkAccess(Application_Model_RoleAction::VERSION_MANAGEMENT);
    $this->view->backUrl = $this->_getBackUrl('version_list', $this->_projectUrl(array(), 'version_list'));
  }
  
  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    
    $versionMapper = new Project_Model_VersionMapper();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    $result = $versionMapper->getAllAjax($this->getRequest());
    
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
    
  private function _getAddVersionForm()
  {
    return new Project_Form_AddVersion(array(
      'action'    => $this->_projectUrl(array('projectId' => $this->_project->getId()), 'version_add_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
  }
  
  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::VERSION_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddVersionForm();
  }

  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::VERSION_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'version_list');
    }
    
    $form = $this->_getAddVersionForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $version = new Application_Model_Version($form->getValues());
    $version->setProjectObject($this->_project);
    $versionMapper = new Project_Model_VersionMapper();
    $t = new Custom_Translate();

    if ($versionMapper->add($version))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  private function _getEditVersionForm(Application_Model_Version $version)
  {
    $form = new Project_Form_EditVersion(array(
      'action'    => $this->_projectUrl(array('id' => $version->getId()), 'version_edit_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'id'        => $version->getId()
    ));

    return $form->populate($version->getExtraData('rowData'));
  }
  
  public function editAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::VERSION_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $version = $this->_getValidVersionForEdit();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditVersionForm($version);
    $this->view->version = $version;
  }

  public function editProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::VERSION_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'version_list');
    }
    
    $version = $this->_getValidVersionForEdit();
    $form = $this->_getEditVersionForm($version);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->version = $version;
      return $this->render('edit');
    }
    
    $t = new Custom_Translate();
    $versionMapper = new Project_Model_VersionMapper();
    $version->setProperties($form->getValues());
    $version->setProjectObject($this->_project);

    if ($versionMapper->save($version))
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
    $this->_checkAccess(Application_Model_RoleAction::VERSION_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $version = $this->_getValidVersion();
    $t = new Custom_Translate();
    $versionMapper = new Project_Model_VersionMapper();
    
    if ($versionMapper->delete($version))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect(array(), 'version_list');
  }
  
  private function _getValidVersion()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $version = new Application_Model_Version($idValidator->getFilteredValues());
    $version->setProjectObject($this->_project);
    return $version;
  }
  
  private function _getValidVersionForEdit()
  {
    $version = $this->_getValidVersion();
    $versionMapper = new Project_Model_VersionMapper();
    $rowData = $versionMapper->getForEdit($version);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    return $version->setExtraData('rowData', $rowData);
  }
  
  private function _getValidVersionForView()
  {
    $version = $this->_getValidVersion();
    $versionMapper = new Project_Model_VersionMapper();
    $rowData = $versionMapper->getForView($version);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    return $version;
  }
}