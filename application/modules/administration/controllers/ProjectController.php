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
class Administration_ProjectController extends Custom_Controller_Action_Administration_Abstract
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
  
  private function _getFilterForm()
  {
    return new Administration_Form_ProjectFilter(array('action' => $this->_url(array(), 'admin_project_list')));
  }
    
  public function indexAction()
  {
    $this->_setCurrentBackUrl('projectExportDefects');
    $request = $this->getRequest();
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $projectMapper = new Administration_Model_ProjectMapper();
      list($list, $paginator) = $projectMapper->getAll($request);
    }
    else
    {
      $list = array();
      $paginator = null;
    }

    $this->_setTranslateTitle();
    $this->view->projects = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
  }
  
  private function _getExportForm(Application_Model_Project $project)
  {
    return new Administration_Form_ExportProject(array(
      'action'  => $this->_url(array('id' => $project->getId()), 'admin_project_export_process'),
      'method'  => 'post'
    ));
  }
  
  public function exportAction()
  {
    $project = $this->_getValidProjectForView();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getExportForm($project);
  }

  public function exportProcessAction()
  {
    $project = $this->_getValidProjectForView();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $project->getId()), 'admin_project_export');
    }
    
    $form = $this->_getExportForm($project);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('export');
    }
    
    $values = $form->getValues();
    
    if (array_sum($values) == 0)
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->formError = 'selectLeastOneCheckbox';
      return $this->render('export');
    }
    
    $t = new Custom_Translate();
    $projectMapper = new Administration_Model_ProjectMapper();
    $project->setProperties($values, true);    
    $project->setExtraData('fileDescription', $t->translate('Wyeksportowany projekt PROJECT.', array('project' => $project->getName())));

    if ($projectMapper->export($project))
    {
      $session = new Zend_Session_Namespace('FileDownload');
      $session->layout = 'administration';
      $this->_setBackUrl('file_dwonload', $form->getBackUrl());
      $this->redirect(array('projectPrefix' => $project->getPrefix(), 'id' => $project->getExtraData('fileId')), 'file_download');
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      $this->redirect($form->getBackUrl());
    }    
  }
  
  public function exportDefectsAction()
  {
    $project = $this->_getValidProjectForView();
    $taskRunMapper = new Administration_Model_TaskRunMapper();
    $backUrl = $this->_getBackUrl('projectExportDefects', $this->_url(array(), 'admin_project_list'));
    
    if ($taskRunMapper->exportDefectsByProject($project))
    {
      $t = new Custom_Translate();
      $session = new Zend_Session_Namespace('FileDownload');
      $session->layout = 'administration';
      $session->messages = array($t->translate('Wyeksportowane defekty projektu PROJECT.', array('project' => $project->getName())));
      $this->_setBackUrl('file_dwonload', $backUrl);
      $this->redirect(array('projectPrefix' => $project->getPrefix(), 'id' => $project->getExtraData('fileId')), 'file_download');
    }
    
    $this->redirect($backUrl);
    exit();
  }
  
  private function _getImportForm()
  {
    return new Administration_Form_ImportProject(array(
      'action'                => $this->_url(array(), 'admin_project_import_process'),
      'method'                => 'post',
      'enctype'               => 'multipart/form-data',
      'destinationDirectory'  => _TEMP_PATH,
      'fileName'              => Utils_Text::generateToken().'.zip'
    ));
  }
  
  public function importAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getImportForm();
  }

  public function importProcessAction()
  {
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'admin_project_import');
    }
    
    $form = $this->_getImportForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('import');
    }
    
    $t = new Custom_Translate();
    $projectMapper = new Administration_Model_ProjectMapper();
    $project = new Application_Model_Project($form->getValues());
    $project->setResolutions(array(
      array('name' => $t->translate('DEFAULT_SUCCESS_RESOLUTION', null, 'general'), 'color' => Zend_Registry::get('config')->defaultProject->successResolutionColor),
      array('name' => $t->translate('DEFAULT_FAIL_RESOLUTION', null, 'general'), 'color' => Zend_Registry::get('config')->defaultProject->failResolutionColor)
    ));

    if ($projectMapper->import($project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }    

    $this->redirect($form->getBackUrl());
  }
  
  private function _getAddProjectForm()
  {
    return new Administration_Form_AddProject(array(
      'action' => $this->_url(array(), 'admin_project_add_process'),
      'method' => 'post',
    ));
  }
  
  public function addAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddProjectForm();
  }

  public function addProcessAction()
  {
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'admin_project_add');
    }
    
    $form = $this->_getAddProjectForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $t = new Custom_Translate();
    $project = new Application_Model_Project($form->getValues());
    $project->setResolutions(array(
      array('name' => $t->translate('DEFAULT_SUCCESS_RESOLUTION', null, 'general'), 'color' => Zend_Registry::get('config')->defaultProject->successResolutionColor),
      array('name' => $t->translate('DEFAULT_FAIL_RESOLUTION', null, 'general'), 'color' => Zend_Registry::get('config')->defaultProject->failResolutionColor)
    ));
    $projectMapper = new Administration_Model_ProjectMapper();

    if ($projectMapper->add($project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect(array('id' => $project->getId()), 'admin_project_view');
  }
  
  private function _getEditProjectForm(Application_Model_Project $project)
  {
    $form = new Administration_Form_EditProject(array(
      'action'  => $this->_url(array('id' => $project->getId()), 'admin_project_edit_process'),
      'method'  => 'post',
      'id'      => $project->getId()
    ));

    $form->populate($project->getExtraData('rowData'));
    return $form;
  }
  
  public function editAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditProjectForm($project);
  }

  public function editProcessAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $project->getId()), 'admin_project_edit');
    }
    
    $form = $this->_getEditProjectForm($project);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit');
    }
    
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
    
    $this->redirect($form->getBackUrl());
  }
  
  public function activateAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $projectMapper = new Administration_Model_ProjectMapper();
    $t = new Custom_Translate();
    
    if ($projectMapper->activate($project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  public function suspendAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $projectMapper = new Administration_Model_ProjectMapper();
    $t = new Custom_Translate();
    
    if ($projectMapper->suspend($project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER')); 
  }
  
  public function finishAction()
  {
    $project = $this->_getValidNotFinishedProject();
    $projectMapper = new Administration_Model_ProjectMapper();
    $t = new Custom_Translate();
    
    if ($projectMapper->finish($project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER')); 
  }
  
  private function _getEditUsersRoleForm()
  {
    return new Administration_Form_EditUsersRole(array(
      'action'  => '',
      'method'  => 'post'
    ));
  }
    
  public function viewAction()
  {
    $project       = $this->_getValidProject();
    $projectMapper = new Administration_Model_ProjectMapper();
    $roleMapper    = new Administration_Model_RoleMapper();
    
    if ($projectMapper->getForView($project) === false)
    {
      throw new Custom_404Exception();
    }

    $form = $this->_getEditUsersRoleForm();
    $form->generateNewToken();
    $roles = $roleMapper->getListByProjectId($project);
    $multiPrePopulatedUsers = array();
    
    if (count($roles) > 0)
    {
      foreach ($roles as $role)
      {
        $multiPrePopulatedUsers[$role->getId()] = $form->prepareJsonUserDataByRole($role);
      }
    }
    
    $this->_setTranslateTitle();
    $this->view->project = $project;
    $this->view->roles   = $roles;
    $this->view->form    = $form;
    $this->view->multiPrePopulatedUsers = $multiPrePopulatedUsers;
    
    if ($project->getStatusId() == Application_Model_ProjectStatus::FINISHED)
    {
      $this->render('view-finished');
    }
  }
  
  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    
    $projectMapper = new Administration_Model_ProjectMapper();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    $result = $projectMapper->getAllAjax( $this->getRequest() );
    
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
  
  private function _getValidProject()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      return $this->redirect(array(), 'admin_project_list');
    }
    
    return new Application_Model_Project($idValidator->getFilteredValues());
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
}