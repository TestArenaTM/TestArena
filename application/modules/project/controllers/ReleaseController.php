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
class Project_ReleaseController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    
    if (!$this->getRequest()->isXmlHttpRequest())
    {
      $this->checkUserSession(true);
      
      if (!in_array($this->getRequest()->getActionName(), array('index', 'view')))
      {
        $this->_project->checkFinished();
        $this->_project->checkSuspended();
      }
    }
  }
  
  private function _getFilterForm()
  {
    return new Project_Form_ReleaseFilter(array('action' => $this->_url(array(), 'release_list')));
  }
    
  public function indexAction()
  {
    $request = $this->getRequest();
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $releaseMapper = new Project_Model_ReleaseMapper();
      list($list, $paginator) = $releaseMapper->getAll($request);
      $this->_setCurrentBackUrl('releaseDelete');
    }
    else
    {
      $list = array();
      $paginator = null;
    } 

    $this->_setTranslateTitle();
    $this->view->releases = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->accessReleaseAndPhaseManagement = $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT);
  }
  
  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    $releaseMapper = new Project_Model_ReleaseMapper();
    $result = $releaseMapper->getAllAjax($this->getRequest());    
    echo json_encode($result);
    exit;
  }
  
  public function listForForwardAjaxAction()
  {
    $this->checkUserSession(true, true);
    $releaseMapper = new Project_Model_ReleaseMapper();
    $result = $releaseMapper->getForForwardAjax($this->getRequest());    
    echo json_encode($result);
    exit;
  }
  
  public function listForPhaseAjaxAction()
  {
    $this->checkUserSession(true, true);
    $releaseMapper = new Project_Model_ReleaseMapper();
    $result = $releaseMapper->getForPhaseAjax($this->getRequest());    
    echo json_encode($result);
    exit;
  }
    
  public function viewAction()
  {
    $release = $this->_getValidReleaseForView();
    $this->_setTranslateTitle();
    $this->view->release = $release;
    $this->view->accessReleaseAndPhaseManagement = $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT);
  }
    
  private function _getAddReleaseForm()
  {
    return new Project_Form_AddRelease(array(
      'action'    => $this->_url(array('projectId' => $this->_project->getId()), 'release_add_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
  }
  
  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddReleaseForm();
  }

  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'release_list');
    }
    
    $form = $this->_getAddReleaseForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $release = new Application_Model_Release($form->getValues());
    $release->setProjectObject($this->_project);
    $releaseMapper = new Project_Model_ReleaseMapper();
    $t = new Custom_Translate();

    if ($releaseMapper->add($release))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getEditReleaseForm(Application_Model_Release $release)
  {
    $form = new Project_Form_EditRelease(array(
      'action'    => $this->_url(array(
        'projectId' => $this->_project->getId(), 
        'id'        => $release->getId()), 'release_edit_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'id'        => $release->getId()
    ));

    return $form->populate($release->getExtraData('rowData'));
  }
  
  public function editAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $release = $this->_getValidReleaseForEdit();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditReleaseForm($release);
    $this->view->release = $release;
  }

  public function editProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'release_list');
    }
    
    $release = $this->_getValidReleaseForEdit();
    $form = $this->_getEditReleaseForm($release);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->release = $release;
      return $this->render('edit');
    }
    
    $t = new Custom_Translate();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $release->setProperties($form->getValues());

    if ($releaseMapper->save($release))
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
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $release = $this->_getValidReleaseForView();
    
    if ($release->getExtraData('taskCount') > 0)
    {
      throw new Custom_404Exception();
    }

    $t = new Custom_Translate();
    $releaseMapper = new Project_Model_ReleaseMapper();
    
    if ($releaseMapper->delete($release))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect($this->_getBackUrl('releaseDelete', $this->_url(array(), 'release_list')));
  }
  
  private function _getCloneReleaseForm(Application_Model_Release $release)
  {
    $form = new Project_Form_CloneRelease(array(
      'action'    => $this->_url(array('id' => $release->getId()), 'release_clone_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'id'        => $release->getId()
    ));

    return $form->populate($release->getExtraData('rowData'));
  }
  
  public function cloneAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $release = $this->_getValidReleaseForClone();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getCloneReleaseForm($release);
    $this->view->release = $release;
  }
  
  public function cloneProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'release_list');
    }
    
    $release = $this->_getValidReleaseForClone();
    $form = $this->_getCloneReleaseForm($release);
    
    if (!$form->isValid($request->getPost()))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->release = $release;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      return $this->render('clone');
    }
    
    $t = new Custom_Translate();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $release->setProperties($form->getValues());
    $release->setExtraData('authUser', $this->_user);
    
    if ($releaseMapper->cloneRelease($release))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getValidRelease()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $release = new Application_Model_Release($idValidator->getFilteredValues());
    $release->setProjectObject($this->_project);
    return $release;
  }
  
  private function _getValidReleaseForEdit()
  {
    $release = $this->_getValidRelease();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $rowData = $releaseMapper->getForEdit($release);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    return $release->setExtraData('rowData', $rowData);
  }
  
  private function _getValidReleaseForClone()
  {
    $release = $this->_getValidRelease();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $rowData = $releaseMapper->getForEdit($release);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $rowData['startDate'] = $rowData['endDate'];
    unset($rowData['endDate']);
    $release->setEndDate(null);
    
    return $release->setExtraData('rowData', $rowData);
  }
  
  private function _getValidReleaseForView()
  {
    $release = $this->_getValidRelease();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $release = $releaseMapper->getForView($release);
    
    if ($release === false)
    {
      throw new Custom_404Exception();
    }
    
    return $release->setProjectObject($this->_project);
  }
}