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
class Administration_ResolutionController extends Custom_Controller_Action_Administration_Abstract
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

  private function _getEditResolutionColorForDefectsForm(Application_Model_Project $project)
  {
    $form = new Administration_Form_EditResolutionColorForDefects(array(
      'action'  => $this->_url(array('projectId' => $project->getId()), 'admin_resolution_list'),
      'method'  => 'post',
      'id'      => $project->getId()
    ));
    $form->populate($project->getExtraData('rowData'));
    return $form;
  }

  private function _getEditResolutionColorForDefects($project)
  {
    $projectEdited = $this->_getValidProjectForEdit();
    $form = $this->_getEditResolutionColorForDefectsForm($projectEdited);
    $this->view->form = $form;
    $request = $this->getRequest();
    if ($request->isPost())
    {
      if ($form->isValid($request->getPost()))
      {
        $t = new Custom_Translate();
        $projectMapper = new Administration_Model_ProjectMapper();
        $project->setProperties($form->getValues());

        if ($projectMapper->save($project))
        {
          $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
        }
        else
        {
          $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
        }

        $this->redirect(array('projectId' => $project->getId()), 'admin_resolution_list');
      }
    }
  }
    
  public function indexAction()
  {
    $this->_setCurrentBackUrl('administrationResolutions');
    $project = $this->_getValidProjectForView();
    $this->_getEditResolutionColorForDefects($project);
    $resolutionMapper = new Administration_Model_ResolutionMapper();
    $this->_setTranslateTitle();
    $this->view->resolutions = $resolutionMapper->getAllByProject($project);
    $this->view->project = $project;
    
    if ($project->isFinished())
    {
      $this->render('index-project-finished');
    }
  }
    
  public function viewAction()
  {
    $resolution = $this->_getValidResolutionForView();
    
    $this->_setTranslateTitle();
    $this->view->resolution = $resolution;
    
    if ($resolution->getProject()->isFinished())
    {
      $this->render('view-project-finished');
    }
  }
  
  private function _getAddForm(Application_Model_Project $project)
  {
    return new Administration_Form_AddResolution(array(
      'action'    => $this->_url(array('projectId' => $project->getId()), 'admin_resolution_add_process'),
      'method'    => 'post',
      'backUrl'   => $this->_url(array('projectId' => $project->getId()), 'admin_resolution_list'),
      'projectId' => $project->getId()
    ));
  }
  
  public function addAction()
  {
    $project = $this->_getValidNotFinishedProjectForView();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddForm($project);
  }

  public function addProcessAction()
  {
    $project = $this->_getValidNotFinishedProjectForView();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('projectId' => $project->getId()), 'admin_resolution_add');
    }
    
    $form = $this->_getAddForm($project);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $resolution = new Application_Model_Resolution($form->getValues());
    $resolution->setProjectObject($project);
    $resolutionMapper = new Administration_Model_ResolutionMapper();
    $t = new Custom_Translate();

    if ($resolutionMapper->add($resolution))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getEditForm(Application_Model_Resolution $resolution)
  {
    $form = new Administration_Form_EditResolution(array(
      'action'    => $this->_url(array('id' => $resolution->getId(), 'projectId' => $resolution->getProject()->getId()), 'admin_resolution_edit_process'),
      'method'    => 'post',
      'backUrl'   => $this->_url(array('projectId' => $resolution->getProject()->getId()), 'admin_resolution_list'),
      'projectId' => $resolution->getProject()->getId(),
      'id'        => $resolution->getId()
    ));

    $form->populate($resolution->getExtraData('rowData'));
    return $form;
  }
  
  public function editAction()
  {
    $project = $this->_getValidProjectForView();
    $resolution = $this->_getValidResolutionForEdit($project);
    $this->_setTranslateTitle();
    $form = $this->_getEditForm($resolution);
    $this->view->project = $project;
    $this->view->form = $form;
  }

  public function editProcessAction()
  {
    $project = $this->_getValidProjectForView();
    $resolution = $this->_getValidResolutionForEdit($project);
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $resolution->getId(), 'projectId' => $resolution->getProject()->getId()), 'admin_resolution_edit');
    }
    
    $form = $this->_getEditForm($resolution);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $project;
      return $this->render('edit');
    }
    
    $t = new Custom_Translate();
    $resolutionMapper = new Administration_Model_ResolutionMapper();
    $resolution->setProperties($form->getValues());

    if ($resolutionMapper->save($resolution))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect(array('id' => $resolution->getId(), 'projectId' => $resolution->getProject()->getId()), 'admin_resolution_list');
  }

  public function deleteAction()
  {
    $resolution = $this->_getValidResolutionForView();
    
    if ($resolution->getProject()->isFinished() || $resolution->getExtraData('taskCount') > 0 || $resolution->getExtraData('testCount') > 0)
    {
      throw new Custom_404Exception();
    }
    
    $resolutionMapper = new Administration_Model_ResolutionMapper();
    $t = new Custom_Translate();
    
    if ($resolutionMapper->delete($resolution))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->_getBackUrl('administrationResolutions', ''));
  }
  
  private function _getValidProject()
  {
    $idValidator = new Application_Model_Validator_ProjectId();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
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

  private function _getValidProjectForEdit()
  {
    $project = $this->_getValidProject();

    $projectMapper = new Administration_Model_ProjectMapper();
    $rowData = $projectMapper->getForEdit($project);

    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }

    return $project->setExtraData('rowData', $rowData);
  }
  
  private function _getValidNotFinishedProjectForView()
  {
    $project = $this->_getValidProjectForView();

    if ($project->isFinished())
    {
      throw new Custom_404Exception();
    }
    
    return $project;
  }
  
  private function _getValidResolution()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    return new Application_Model_Resolution($idValidator->getFilteredValues());
  }
  
  private function _getValidResolutionForView()
  {
    $resolution = $this->_getValidResolution();

    $resolutionMapper = new Administration_Model_ResolutionMapper();
    $resolution = $resolutionMapper->getForView($resolution);

    if ($resolution === false)
    {
      throw new Custom_404Exception();
    }
    
    return $resolution;
  }
  
  private function _getValidResolutionForEdit(Application_Model_Project $project)
  {
    $resolution = $this->_getValidResolution();

    $resolutionMapper = new Administration_Model_ResolutionMapper();
    $rowData = $resolutionMapper->getForEdit($resolution);

    if ($rowData === false || $resolution->getProject()->getId() !== $project->getId())
    {
      throw new Custom_404Exception();
    }
    
    return $resolution->setExtraData('rowData', $rowData);
  }
}