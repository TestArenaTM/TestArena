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
      if ($this->_project === null)
      {
        throw new Custom_404Exception();
      }
      
      if (!in_array($this->getRequest()->getActionName(), array('index', 'view', 'report', 'report-process')))
      {
        $this->_project->checkFinished();
        $this->_project->checkSuspended();
      }
    }
  }
  
  private function _getFilterForm()
  {
    return new Project_Form_ReleaseFilter(array('action' => $this->_projectUrl(array(), 'release_list')));
  }
    
  public function indexAction()
  {
    $this->_setCurrentBackUrl('release_list');
    $this->_setCurrentBackUrl('release_activate');
    $request = $this->_getRequestForFilter(Application_Model_FilterGroup::RELEASES);
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $this->_filterAction($filterForm->getValues(), 'release');
      $releaseMapper = new Project_Model_ReleaseMapper();
      list($list, $paginator) = $releaseMapper->getAll($request);
    }
    else
    {
      $list = array();
      $paginator = null;
    } 
    
    $filter = $this->_user->getFilter(Application_Model_FilterGroup::RELEASES);
    
    if ($filter !== null)
    {
      $filterForm->prepareSavedValues($filter->getData());
    }

    $this->_filterAction($filterForm->getValues());
    $this->_setTranslateTitle();
    $this->view->releases = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->accessReleaseManagement = $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT); 
    $this->view->accessReportGenerate = $this->_checkAccess(Application_Model_RoleAction::REPORT_GENERATE); 
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
    
  public function viewAction()
  {
    $release = $this->_getValidReleaseForView();
    $this->_setCurrentBackUrl('release_activate');
    $this->_setTranslateTitle(array('name' => $release->getName()), 'headTitle');
    $this->view->release = $release;
    $this->view->accessReleaseManagement = $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT);
    $this->view->accessReportGenerate = $this->_checkAccess(Application_Model_RoleAction::REPORT_GENERATE); 
    $this->view->backUrl = $this->_getBackUrl('release_list', $this->_projectUrl(array(), 'release_list'));
  }
    
  private function _getAddReleaseForm()
  {
    return new Project_Form_AddRelease(array(
      'action'    => $this->_projectUrl(array('projectId' => $this->_project->getId()), 'release_add_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
  }
  
  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddReleaseForm();
  }

  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'release_list');
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
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  private function _getEditReleaseForm(Application_Model_Release $release)
  {
    $form = new Project_Form_EditRelease(array(
      'action'    => $this->_projectUrl(array(
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
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $release = $this->_getValidReleaseForEdit();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditReleaseForm($release);
    $this->view->release = $release;
  }

  public function editProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'release_list');
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
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  public function activateAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $release = $this->_getValidReleaseForView();

    $t = new Custom_Translate();
    $releaseMapper = new Project_Model_ReleaseMapper();
    
    if ($releaseMapper->activate($release))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('release_activate', $this->_projectUrl(array(), 'release_list')));
  }
  
  public function deactivateAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $release = $this->_getValidReleaseForView();

    $t = new Custom_Translate();
    $releaseMapper = new Project_Model_ReleaseMapper();
    
    if ($releaseMapper->deactivate($release))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('release_activate', $this->_projectUrl(array(), 'release_list')));
  }
  
  public function deleteAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
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

    return $this->projectRedirect($this->_getBackUrl('release_list', $this->_projectUrl(array(), 'release_list')));
  }
  
  private function _getReleaseReportForm(Application_Model_Release $release)
  {
    $form = new Project_Form_ReleaseReport(array(
      'action'    => $this->_projectUrl(array(
        'id'        => $release->getId()), 'release_report_process'),
      'method'    => 'post'
    ));

    return $form->populate(array(
      'type' => $release->getExtraData('type')
    ));
  }
  
  public function reportAction()
  {
    $release = $this->_getValidReleaseForReport();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getReleaseReportForm($release);
    $this->view->release = $release;
  }

  public function reportProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'release_list');
    }

    $release = $this->_getValidReleaseForReport();
    $form = $this->_getReleaseReportForm($release);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->release = $release;
      return $this->render('report');
    }
    
    $t = new Custom_Translate();
    $release->setProperties($form->getValues());
    $releaseMapper = new Project_Model_ReleaseMapper();
    $release->setExtraData('fileName', $t->translate('raport'));
    $release->setExtraData('fileDescription', $t->translate('Raport z wydania RELEASE.', array('release' => $release->getName())));
    
    if ($releaseMapper->createReport($release))
    {
      $t = new Custom_Translate();
      $session = new Zend_Session_Namespace('FileDownload');
      $session->layout = 'project';
      $this->_setBackUrl('file_dwonload', $form->getBackUrl());
      $this->projectRedirect(array('id' => $release->getExtraData('fileId')), 'file_download');
    }
    else
    {
      $t = new Custom_Translate();
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      $this->projectRedirect($form->getBackUrl());
    }
  }    
  
  /*private function _getCloneReleaseForm(Application_Model_Release $release)
  {
    $form = new Project_Form_CloneRelease(array(
      'action'    => $this->_projectUrl(array('id' => $release->getId()), 'release_clone_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'id'        => $release->getId()
    ));

    return $form->populate($release->getExtraData('rowData'));
  }
  
  public function cloneAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $release = $this->_getValidReleaseForClone();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getCloneReleaseForm($release);
    $this->view->release = $release;
  }*/
  
  /*public function cloneProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::RELEASE_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'release_list');
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
    
    $this->projectRedirect($form->getBackUrl());
  }*/
  
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
  
  /*private function _getValidReleaseForClone()
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
  }*/
  
  private function _getValidReleaseForView()
  {
    $release = $this->_getValidRelease();
    $releaseMapper = new Project_Model_ReleaseMapper();
    
    if ($releaseMapper->getForView($release) === false)
    {
      throw new Custom_404Exception();
    }
    
    return $release->setProjectObject($this->_project);
  }
  
  private function _getValidReleaseForReport()
  {
    $release = $this->_getValidRelease();
    $releaseMapper = new Project_Model_ReleaseMapper();
    
    if ($releaseMapper->getForView($release) === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkAccess(Application_Model_RoleAction::REPORT_GENERATE, true);
    
    return $release->setProjectObject($this->_project);
  }
}