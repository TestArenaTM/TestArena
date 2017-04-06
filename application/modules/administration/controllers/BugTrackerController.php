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
class Administration_BugTrackerController extends Custom_Controller_Action_Administration_Abstract
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
    $project = $this->_getValidProjectForView();
    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $this->_setTranslateTitle();
    $this->view->project = $project;
    $this->view->bugTrackers = $projectBugTrackerMapper->getAllByProject($project);
    
    if ($project->isFinished())
    {
      $this->render('index-project-finished');
    }
  }
  
  private function _getAddBugTrackerJiraForm(Application_Model_Project $project)
  {
    return new Administration_Form_AddBugTrackerJira(array(
      'action' => $this->_url(array('projectId' => $project->getId()), 'admin_bug_tracker_add_jira_process'),
      'method' => 'post',
    ));
  }
  
  public function addJiraAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddBugTrackerJiraForm($project);
  }

  public function addJiraProcessAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('projectId' => $project->getId()), 'admin_bug_tracker_add_jira');
    }
    
    $form = $this->_getAddBugTrackerJiraForm($project);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $project;
      return $this->render('add-jira');
    }
    
    $projectBugTracker = new Application_Model_ProjectBugTracker($form->getValues());
    $projectBugTracker->setProjectObject($project);
    $projectBugTracker->setBugTrackerJiraObject(new Application_Model_BugTrackerJira($form->getValues()));
    $projectBugTrackerMapper  = new Administration_Model_ProjectBugTrackerMapper();
    $t = new Custom_Translate();

    if ($projectBugTrackerMapper->addJira($projectBugTracker))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getAddBugTrackerMantisForm(Application_Model_Project $project)
  {
    return new Administration_Form_EditBugTrackerMantis(array(
      'action' => $this->_url(array('projectId' => $project->getId()), 'admin_bug_tracker_add_mantis_process'),
      'method' => 'post',
    ));
  }
  
  public function addMantisAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddBugTrackerMantisForm($project);
  }

  public function addMantisProcessAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('projectId' => $project->getId()), 'admin_bug_tracker_add_mantis');
    }
    
    $form = $this->_getAddBugTrackerMantisForm($project);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $project;
      return $this->render('add-mantis');
    }
    
    $projectBugTracker = new Application_Model_ProjectBugTracker($form->getValues());
    $projectBugTracker->setProjectObject($project);
    $projectBugTracker->setBugTrackerMantisObject(new Application_Model_BugTrackerMantis($form->getValues()));
    $projectBugTrackerMapper  = new Administration_Model_ProjectBugTrackerMapper();
    $t = new Custom_Translate();

    if ($projectBugTrackerMapper->addMantis($projectBugTracker))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getEditBugTrackerJiraForm(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $form = new Administration_Form_EditBugTrackerJira(array(
      'action'  => $this->_url(array('id' => $projectBugTracker->getId()), 'admin_bug_tracker_edit_jira_process'),
      'method'  => 'post'
    ));

    return $form->populate($projectBugTracker->getExtraData('rowData'));
  }
  
  public function editJiraAction()
  {
    $projectBugTracker = $this->_getValidProjectBugTrackerForEditJira();
    $this->_setTranslateTitle();
    $form = $this->_getEditBugTrackerJiraForm($projectBugTracker);
    $this->view->form = $form;
  }

  public function editJiraProcessAction()
  {
    $projectBugTracker = $this->_getValidProjectBugTrackerForEditJira();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $projectBugTracker->getId()), 'admin_bug_tracker_edit_jira');
    }
    
    $form = $this->_getEditBugTrackerJiraForm($projectBugTracker);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit-jira');
    }

    $t = new Custom_Translate();
    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $projectBugTracker->setProperties($form->getValues());
    $projectBugTracker->setBugTrackerJiraObject(new Application_Model_BugTrackerJira($form->getValues()));
    $projectBugTracker->setBugTrackerJira('id', $projectBugTracker->getBugTrackerId());

    if ($projectBugTrackerMapper->saveJira($projectBugTracker))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getEditBugTrackerMantisForm(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $form = new Administration_Form_EditBugTrackerMantis(array(
      'action'  => $this->_url(array('id' => $projectBugTracker->getId()), 'admin_bug_tracker_edit_mantis_process'),
      'method'  => 'post'
    ));

    return $form->populate($projectBugTracker->getExtraData('rowData'));
  }
  
  public function editMantisAction()
  {
    $projectBugTracker = $this->_getValidProjectBugTrackerForEditMantis();
    $this->_setTranslateTitle();
    $form = $this->_getEditBugTrackerMantisForm($projectBugTracker);
    $this->view->form = $form;
  }

  public function editMantisProcessAction()
  {
    $projectBugTracker = $this->_getValidProjectBugTrackerForEditMantis();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $projectBugTracker->getId()), 'admin_bug_tracker_edit_mantis');
    }
    
    $form = $this->_getEditBugTrackerMantisForm($projectBugTracker);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit-mantis');
    }

    $t = new Custom_Translate();
    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $projectBugTracker->setProperties($form->getValues());
    $projectBugTracker->setBugTrackerMantisObject(new Application_Model_BugTrackerMantis($form->getValues()));
    $projectBugTracker->setBugTrackerMantis('id', $projectBugTracker->getBugTrackerId());

    if ($projectBugTrackerMapper->saveMantis($projectBugTracker))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function activateAction()
  {
    $projectBugTracker = $this->_getValidProjectBugTrackerForView();
    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $t = new Custom_Translate();
    
    if ($projectBugTrackerMapper->activate($projectBugTracker))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  public function deleteAction()
  {
    $projectBugTracker = $this->_getValidProjectBugTrackerForView();
    
    if ($projectBugTracker->getProject()->isFinished())
    {
      throw new Custom_404Exception();
    }
    
    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $t = new Custom_Translate();
    
    if ($projectBugTrackerMapper->delete($projectBugTracker))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  private function _getValidProject()
  {
    $idValidator = new Application_Model_Validator_ProjectId();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      return $this->redirect(array(), 'admin_bug_tracker_list');
    }
    
    $project = new Application_Model_Project();
    return $project->setId($idValidator->getFilteredValue('projectId'));
  }
  
  private function _getValidProjectForView()
  {
    $project = $this->_getValidProject();

    $projectMapper = new Administration_Model_ProjectMapper();
    $project = $projectMapper->getForView($project);

    if ($project === false)
    {
      throw new Custom_404Exception();
    }
    
    return $project;
  }
  
  private function _getValidNotFinishedProject()
  {
    $project = $this->_getValidProject();

    $projectMapper = new Administration_Model_ProjectMapper();
    $rowData = $projectMapper->getForEdit($project);

    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $project->setExtraData('rowData', $rowData);
    $project->setDbProperties($rowData);

    if ($project->getStatusId() == Application_Model_ProjectStatus::FINISHED)
    {
      throw new Custom_AccessDeniedException();
    }
    
    return $project;
  }
  
  private function _getValidProjectBugTracker()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      return $this->redirect(array(), 'admin_bug_tracker_list');
    }
    
    return new Application_Model_ProjectBugTracker($idValidator->getFilteredValues());
  }
  
  private function _getValidProjectBugTrackerForView()
  {
    $projectBugTracker = $this->_getValidProjectBugTracker();

    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $projectBugTracker = $projectBugTrackerMapper->getForView($projectBugTracker);

    if ($projectBugTracker === false)
    {
      throw new Custom_404Exception();
    }
    
    return $projectBugTracker;
  }
  
  private function _getValidProjectBugTrackerForEditJira()
  {
    $projectBugTracker = $this->_getValidProjectBugTracker();

    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $rowData = $projectBugTrackerMapper->getForEditJira($projectBugTracker);

    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $projectBugTracker->setDbProperties($rowData);
    return $projectBugTracker->setExtraData('rowData', $rowData);
  }
  
  private function _getValidProjectBugTrackerForEditMantis()
  {
    $projectBugTracker = $this->_getValidProjectBugTracker();

    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $rowData = $projectBugTrackerMapper->getForEditMantis($projectBugTracker);

    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $projectBugTracker->setDbProperties($rowData);
    return $projectBugTracker->setExtraData('rowData', $rowData);
  }
  
  /*private function _getValidBugTrackerJiraForView()
  {
    $bugTracker = $this->_getValidBugTracker();

    $bugTrackerMapper = new Administration_Model_BugTrackerMapper();
    $bugTracker = $bugTrackerMapper->getForView($bugTracker);

    if ($bugTracker === false)
    {
      throw new Custom_404Exception();
    }
    
    return $bugTracker;
  }*/
}