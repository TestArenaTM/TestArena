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
class Project_PhaseController extends Custom_Controller_Action_Application_Project_Abstract
{
  private $_release = null;
  
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
      
      if ($this->_project->getExtraData('releaseCount') == 0)
      {
        throw new Custom_404Exception();
      }
    }
  }
  
  private function _getFilterForm()
  {
    $releaseMapper = new Project_Model_ReleaseMapper();
    return new Project_Form_PhaseFilter(array(
      'action'      => $this->_url(array(), 'phase_list'),
      'releaseList' => $releaseMapper->getForFilterAsOptions($this->_project)
    ));
  }
    
  public function indexAction()
  {
    $request = $this->getRequest();
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $this->_setCurrentBackUrl('phaseDelete');
      $phaseMapper = new Project_Model_PhaseMapper();
      list($list, $paginator) = $phaseMapper->getAll($request);    
    }
    else
    {
      $list = array();
      $paginator = null;
    }    
    
    $this->_setTranslateTitle();
    $this->view->phases = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->accessReleaseAndPhaseManagement = $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT);
  }
    
  public function viewAction()
  {
    $phase = $this->_getValidPhaseForView();
    $this->_setTranslateTitle();
    $this->view->phase = $phase;
    $this->view->accessReleaseAndPhaseManagement = $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT);
  }
    
  private function _getAddPhaseForm()
  {
    $request = $this->getRequest();
    $id = $request->getParam('releaseId', null);
    
    $options = array(
      'action' => $this->_url(array('releaseId' => $id), 'phase_add_process'),
      'method' => 'post'
    );

    if (!$id)
    {
      $id = $request->getPost('releaseId', null);
    }
    
    if ($id > 0)
    {
      $release = new Application_Model_Release();
      $release->setId($id);
      $releaseMapper = new Project_Model_ReleaseMapper();

      if ($releaseMapper->getForPhase($release))
      {
        $options['minDate']     = $release->getStartDate();
        $options['maxDate']     = $release->getEndDate();
        $options['releaseId']   = $release->getId();
        $options['releaseName'] = $release->getName();
        $this->_release = $release;
      }
    }

    $this->view->release = $this->_release;
    return new Project_Form_AddPhase($options);
  }
  
  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddPhaseForm();
  }

  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'phase_list');
    }
    
    $form = $this->_getAddPhaseForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $phase = new Application_Model_Phase($form->getValues());
    $phase->setRelease('id', $form->getValue('releaseId'));
    $phaseMapper = new Project_Model_PhaseMapper();
    $t = new Custom_Translate();

    if ($phaseMapper->add($phase))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getEditPhaseForm(Application_Model_Phase $phase)
  {
    $options = array(
      'action'    => $this->_url(array('id' => $phase->getId()), 'phase_edit_process'),
      'method'    => 'post',
      'minDate'   => $phase->getRelease()->getStartDate(),
      'maxDate'   => $phase->getRelease()->getEndDate(),
      'releaseId' => $phase->getRelease()->getId(),
      'id'        => $phase->getId()
    );
    
    $this->_release = $phase->getRelease();
    $id = $this->getRequest()->getParam('releaseId', null);
    
    if ($id !== null)
    {
      $release = new Application_Model_Release();
      $release->setId($id);
      $releaseMapper = new Project_Model_ReleaseMapper();

      if ($releaseMapper->getForPhase($release))
      {
        $options['minDate']   = $release->getStartDate();
        $options['maxDate']   = $release->getEndDate();
        $options['releaseId'] = $release->getId();
        $this->_release = $release;
      }
    }
      
    $this->view->release = $this->_release;
    
    $form = new Project_Form_EditPhase($options);    
    return $form->populate($phase->getExtraData('rowData'));
  }
  
  public function editAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $phase = $this->_getValidPhaseForEdit();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditPhaseForm($phase);
  }

  public function editProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_AND_PHASE_MANAGEMENT, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'phase_list');
    }
    
    $phase = $this->_getValidPhaseForEdit();
    $form = $this->_getEditPhaseForm($phase);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit');
    }
    
    $t = new Custom_Translate();
    $phaseMapper = new Project_Model_PhaseMapper();
    $phase->setProperties($form->getValues());
    $phase->setReleaseObject($this->_release);

    if ($phaseMapper->save($phase))
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
    
    $phase = $this->_getValidPhaseForView();
    
    if ($phase->getExtraData('taskCount') > 0)
    {
      throw new Custom_404Exception();
    }

    $t = new Custom_Translate();
    $phaseMapper = new Project_Model_PhaseMapper();
    
    if ($phaseMapper->delete($phase))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->_getBackUrl('phaseDelete', $this->_url(array(), 'phase_list')));
  }
  
  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    $phaseMapper = new Project_Model_PhaseMapper();
    $result = $phaseMapper->getAllAjax($this->getRequest());    
    echo json_encode($result);
    exit;
  }
  
  public function listForForwardAjaxAction()
  {
    $this->checkUserSession(true, true);
    $phaseMapper = new Project_Model_PhaseMapper();
    $result = $phaseMapper->getForForwardAjax($this->getRequest());    
    echo json_encode($result);
    exit;
  }
  
  public function listByReleaseAjaxAction()
  {
    $this->checkUserSession(true, true);
    $phaseMapper = new Project_Model_PhaseMapper();
    $result = $phaseMapper->getByReleaseAjax($this->getRequest());    
    echo json_encode($result);
    exit;
  }
  
  private function _getValidPhase()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    return new Application_Model_Phase($idValidator->getFilteredValues());
  }
  
  private function _getValidPhaseForEdit()
  {
    $phase = $this->_getValidPhase();
    $phaseMapper = new Project_Model_PhaseMapper();
    $rowData = $phaseMapper->getForEdit($phase);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $phase->setRelease('id', $rowData['releaseId']);
    $phase->setRelease('name', $rowData['releaseName']);
    $phase->setRelease('startDate', $rowData['releaseStartDate']);
    $phase->setRelease('endDate', $rowData['releaseEndDate']);
    return $phase->setExtraData('rowData', $rowData);
  }
  
  private function _getValidPhaseForView()
  {
    $phase = $this->_getValidPhase();
    $phaseMapper = new Project_Model_PhaseMapper();
    $rowData = $phaseMapper->getForView($phase);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    return $phase;
  }
}