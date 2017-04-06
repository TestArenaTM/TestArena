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
class Project_DefectController extends Custom_Controller_Action_Application_Project_Abstract
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
    $userMapper = new Project_Model_UserMapper();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $phaseMapper = new Project_Model_PhaseMapper();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $request = $this->getRequest();
    
    $release = new Application_Model_Release();
    $release->setId($request->getParam('release', null));
    $phase = new Application_Model_Phase();
    $phase->setId($request->getParam('phase', null));
    
    if (!$release->getId() && $phase->getId())
    {
      $release = $releaseMapper->getByPhase($phase);
      $request->setParam('release', $release->getId());
    }
    
    $releaseList = $releaseMapper->getByProjectAsOptions($this->_project);
    $phaseList = $phaseMapper->getByReleaseAsOptions($release);
    
    return new Project_Form_DefectFilter(array(
      'action'          => $this->_url(array(), 'defect_list'),
      'userList'        => $userMapper->getByProjectAsOptions($this->_project),
      'releaseList'     => $releaseList,
      'phaseList'       => $phaseList,
      'environmentList' => $environmentMapper->getByProjectAsOptions($this->_project),
      'project'         => $this->_project
    ));
  }
    
  public function indexAction()
  {
    $request = $this->getRequest();
    $filterForm = $this->_getFilterForm();

    if ($filterForm->isValid($request->getParams()))
    {
      $defectMapper = new Project_Model_DefectMapper();
      list($list, $paginator) = $defectMapper->getAll($request, $this->_project);
    }
    else
    {
      $list = array();
      $paginator = null;
    }
    
    $this->_setTranslateTitle();
    $this->view->defects = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->defectUserPermissions = $this->_getAccessPermissionsForDefects();
  }
    
  public function viewAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    
    $defect = $this->_getValidDefect();
    $defectMapper = new Project_Model_DefectMapper();
    $defect = $defectMapper->getForView($defect);

    if ($defect === false)
    {
      throw new Custom_404Exception();
    }

    $fileMapper = new Project_Model_FileMapper();
    $defect->setExtraData('attachments', $fileMapper->getAllByDefect($defect));
    
    $historyMapper = new Application_Model_HistoryMapper();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    
    $this->_setTranslateTitle();
    $this->view->defect = $defect;
    $this->view->environments = $environmentMapper->getByDefect($defect);
    $this->view->versions = $versionMapper->getByDefect($defect);
    $this->view->history = $historyMapper->getByDefect($defect);
    $this->view->defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_getAccessPermissionsForDefects());
  }
  
  public function infoAjaxAction()
  {
    $this->checkUserSession(true, true);
    $no = $this->getRequest()->getPost('no', '');
    
    $result = array();
    
    switch ($this->_project->getBugTracker()->getBugTrackerTypeId())
    {
      default:
      case Application_Model_BugTrackerType::INTERNAL:
        if (preg_match('/^('.$this->_project->getPrefix().'\-)(.+)/i', $no, $matches) === 1)
        {
          $defect = new Application_Model_Defect();
          $defect->setOrdinalNo($matches[2]);
          $defectMapper = new Project_Model_DefectMapper();

          if ($defectMapper->getByOrdinalNoForAjax($defect, $this->_project) !== false)
          {
            $result[] = array(
              'id'    => $defect->getId(),
              'name'  => $defect->getExtraData('name')
            );
          }
        }
        break;
      
      case Application_Model_BugTrackerType::JIRA:
        $bugTrackerJiraMapper = new Project_Model_BugTrackerJiraMapper();
        $bugTrackerJira = $bugTrackerJiraMapper->getById($this->_project->getBugTracker()->getBugTrackerJira());
        
        if (preg_match('/^('.$bugTrackerJira->getProjectKey().'\-)(.+)/i', $no, $matches) === 1)
        {
          $key = $no;
          $summary = Utils_Api_Jira::getIssueSummary($key, $bugTrackerJira->getUrl(), $bugTrackerJira->getUserName(), $bugTrackerJira->getPassword());

          if ($summary !== false)
          {
            $no = $matches[2];
            $defectJira = new Application_Model_DefectJira();
            $defectJira->setNo($no);
            $defectJira->setBugTracker('id', $this->_project->getBugTracker()->getBugTrackerId());
            $defectJira->setSummary($summary);
            $defectJiraMapper = new Project_Model_DefectJiraMapper();

            if ($defectJiraMapper->getIdByNoForAjax($defectJira) === false)
            {
              $defectJiraMapper->add($defectJira);
            }
            else
            {
              $defectJiraMapper->save($defectJira);
            }

            $result[] = array(
              'id'    => $defectJira->getId(),
              'name'  => $key.' '.$defectJira->getSummary()
            );
          }
        }
        break;
      
      case Application_Model_BugTrackerType::MANTIS:
        $bugTrackerMantisMapper = new Project_Model_BugTrackerMantisMapper();
        $bugTrackerMantis = $bugTrackerMantisMapper->getById($this->_project->getBugTracker()->getBugTrackerMantis());
        
        try
        {
          $summary = Utils_Api_Mantis::getIssueSummaryById($no, $bugTrackerMantis->getUrl(), $bugTrackerMantis->getUserName(), $bugTrackerMantis->getPassword());
        
          $defectMantis = new Application_Model_DefectMantis();
          $defectMantis->setNo($no);
          $defectMantis->setBugTracker('id', $this->_project->getBugTracker()->getBugTrackerId());
          $defectMantis->setSummary($summary);
          $defectMantisMapper = new Project_Model_DefectMantisMapper();

          if ($defectMantisMapper->getIdByNoForAjax($defectMantis) === false)
          {
            $defectMantisMapper->add($defectMantis);
          }
          else
          {
            $defectMantisMapper->save($defectMantis);
          }

          $result[] = array(
            'id'    => $defectMantis->getId(),
            'name'  => $defectMantis->getNo(true).' '.$defectMantis->getSummary()
          );
        }
        catch (Exception $e)
        {
          Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
        }
        break;
    }
      
    echo json_encode($result);
    exit;
  }
  
  public function statusAjaxAction()
  {
    $this->checkUserSession(true, true);
    $key = $this->getRequest()->getPost('key', '');
    $name = $this->getRequest()->getPost('name', '');
    $id = (int)$this->getRequest()->getPost('id', 0);

    $result = array(
      'status' => 'ERROR',
      'key'    => $key
    );
    
    if (!empty($key))
    {
      switch ($this->_project->getBugTracker()->getBugTrackerTypeId())
      {
        default:
          break;

        case Application_Model_BugTrackerType::JIRA:
          $bugTrackerJiraMapper = new Project_Model_BugTrackerJiraMapper();
          $bugTrackerJira = $bugTrackerJiraMapper->getById($this->_project->getBugTracker()->getBugTrackerJira());

          $data = Utils_Api_Jira::getIssueSummaryAndStatus($key, $bugTrackerJira->getUrl(), $bugTrackerJira->getUserName(), $bugTrackerJira->getPassword());
          
          if ($data === false)
          {
            $result['status'] = 'NOT_EXISTS';
          }
          else
          {
            $result['status'] = 'OK';
            $result['data'] = $data;
            
            if ($key.' '.$data['summary'] != $name)
            {
              $defectJira = new Application_Model_DefectJira();
              $defectJira->setId($id);
              $defectJira->setSummary($data['summary']);
              $defectJiraMapper = new Project_Model_DefectJiraMapper();
              $defectJiraMapper->save($defectJira);
            }
          }
          break;

        case Application_Model_BugTrackerType::MANTIS:
          $bugTrackerMantisMapper = new Project_Model_BugTrackerMantisMapper();
          $bugTrackerMantis = $bugTrackerMantisMapper->getById($this->_project->getBugTracker()->getBugTrackerMantis());
          
          try
          {
            $data = Utils_Api_Mantis::getIssueSummaryAndStatusById($key, $bugTrackerMantis->getUrl(), $bugTrackerMantis->getUserName(), $bugTrackerMantis->getPassword());
          
            $result['status'] = 'OK';
            $result['data'] = $data;
            
            if ($key.' '.$data['summary'] != $name)
            {
              $defectMantis = new Application_Model_DefectMantis();
              $defectMantis->setId($id);
              $defectMantis->setSummary($data['summary']);
              $defectMantisMapper = new Project_Model_DefectMantisMapper();
              $defectMantisMapper->save($defectMantis);
            }
          }
          catch (Exception $e)
          {
            Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
            $result['status'] = 'NOT_EXISTS';
          }
          break;
      }
    }

    echo json_encode($result);
    exit;
  }
  
  private function _getAddForm()
  {
    return new Project_Form_AddDefect(array(
      'action'    => $this->_url(array(), 'defect_add_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
  }  

  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::DEFECT_ADD, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddForm();
  }
  
  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::DEFECT_ADD, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'defect_add');
    }
    
    $form = $this->_getAddForm();
    $post = $form->prepareAttachments($request->getPost());
    
    if (!$form->isValid($post))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();

      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      return $this->render('add'); 
    }
    
    $defect = new Application_Model_Defect($form->getValues());
    $defect->setProjectObject($this->_project);
    $defect->setRelease('id', $form->getValue('releaseId'));
    $defect->setPhase('id', $form->getValue('phaseId'));
    $defect->setAssignee('id', $form->getValue('assigneeId'));
    $defect->setAssigner('id', $this->_user->getId());
    $defect->setAuthor('id', $this->_user->getId());
    
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->add($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CREATE_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect(array('id' => $defect->getId()), 'defect_view');
  }
  
  private function _getEditForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_EditDefect(array(
      'action'    => $this->_url(array('id' => $defect->getId()), 'defect_edit_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
    return $form->populate($defect->getExtraData('rowData'));
  }

  public function editAction()
  {
    $defect = $this->_getValidDefectForEdit();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    $form = $this->_getEditForm($defect);
    $rowData = $defect->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));

    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
    $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByDefect($defect));
    $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByDefect($defect));
  }
  
  public function editProcessAction()
  {
    $defect = $this->_getValidDefectForEdit();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'defect_run_list');
    }
    
    $form = $this->_getEditForm($defect);
    $post = $form->prepareAttachments($request->getPost());

    if (!$form->isValid($post))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      return $this->render('edit'); 
    }

    $defect->setDbProperties($form->getValues());
    $defect->setRelease('id', $form->getValue('releaseId'));
    $defect->setPhase('id', $form->getValue('phaseId'));
    $defect->setAssignee('id', $form->getValue('assigneeId'));
    $defect->setAssigner('id', $this->_user->getId());

    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->save($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function startAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if (!in_array($defect->getStatusId(), array(Application_Model_DefectStatus::OPEN, Application_Model_DefectStatus::REOPEN)))
    {
      throw new Custom_404Exception();
    }
    
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->start($defect))
    {    
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1(Application_Model_DefectStatus::IN_PROGRESS);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
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
    $defect = $this->getValidDefectForMinorModification();
    
    if (!in_array($defect->getStatusId(), array(Application_Model_DefectStatus::OPEN, Application_Model_DefectStatus::REOPEN, Application_Model_DefectStatus::IN_PROGRESS)))
    {
      throw new Custom_404Exception();
    }
    
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->finish($defect))
    {    
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1(Application_Model_DefectStatus::FINISHED);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  private function _getResolveForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_ResolveDefect(array(
      'action'      => $this->_url(array('id' => $defect->getId()), 'defect_resolve_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId()
    ));
    
    return $form->populate(array(
      'assigneeName'  => $defect->getAssigner()->getFullName(),
      'assigneeId'    => $defect->getAssignerId()
    ));
  }
  
  public function resolveAction()
  {
    $defect = $this->_getValidDefectForAssign();
    $form = $this->_getResolveForm($defect);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
  }
  
  public function resolveProcessAction()
  {
    $defect = $this->_getValidDefectForAssign();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $defect->getId()), 'defect_resolve');
    }
    
    $form = $this->_getResolveForm($defect);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      return $this->render('resolve'); 
    }

    $defect->setProperties($form->getValues());
    $defect->setAssignee('id', $form->getValue('assigneeId'));
    $defect->setAssigner('id', $this->_user->getId());
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->resolve($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect($form->getBackUrl());
  }
  
  private function _getIsInvalidForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_IsInvalidDefect(array(
      'action'      => $this->_url(array('id' => $defect->getId()), 'defect_is_invalid_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId()
    ));
    
    return $form->populate(array(
      'assigneeName'  => $defect->getAssigner()->getFullName(),
      'assigneeId'    => $defect->getAssignerId()
    ));
  }
  
  public function isInvalidAction()
  {
    $defect = $this->_getValidDefectForAssign();
    $form = $this->_getIsInvalidForm($defect);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
  }
  
  public function isInvalidProcessAction()
  {
    $defect = $this->_getValidDefectForAssign();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $defect->getId()), 'defect_is_invalid');
    }
    
    $form = $this->_getIsInvalidForm($defect);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      return $this->render('is-invalid'); 
    }
    
    $defect->setProperties($form->getValues());
    $defect->setAssignee('id', $form->getValue('assigneeId'));
    $defect->setAssigner('id', $this->_user->getId());
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->isInvalid($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect($form->getBackUrl());
  }
  
  public function changeToResolvedAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if ($defect->getStatusId() != Application_Model_DefectStatus::INVALID)
    {
      throw new Custom_404Exception();
    }
    
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->changeStatusToResolved($defect))
    {    
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1(Application_Model_DefectStatus::RESOLVED);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  public function changeToInvalidAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if ($defect->getStatusId() != Application_Model_DefectStatus::RESOLVED)
    {
      throw new Custom_404Exception();
    }
    
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->changeStatusToInvalid($defect))
    {    
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1(Application_Model_DefectStatus::INVALID);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  private function _getCloseForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_CloseDefect(array(
      'action'      => $this->_url(array('id' => $defect->getId()), 'defect_close_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId()
    ));

    return $form->populate(array('status' => $defect->getStatusId() + 2));
  }
  
  public function closeAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::SUCCESS, Application_Model_DefectStatus::FAIL)))
    {
      throw new Custom_404Exception();
    }
    
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    $form = $this->_getCloseForm($defect);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
    $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByDefect($defect));
    $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByDefect($defect));
  }
  
  public function closeProcessAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::SUCCESS, Application_Model_DefectStatus::FAIL)))
    {
      throw new Custom_404Exception();
    }
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $defect->getId()), 'defect_close');
    }

    $form = $this->_getCloseForm($defect);
    
    if (!$form->isValid($request->getPost()))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      return $this->render('close'); 
    }

    $defect->setProperties($form->getValues());
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->close($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1($defect->getStatusId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect($form->getBackUrl());
  }
  
  public function changeToSuccessAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if ($defect->getStatusId() != Application_Model_DefectStatus::FAIL)
    {
      throw new Custom_404Exception();
    }
    
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->changeStatusToSuccess($defect))
    {    
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1(Application_Model_DefectStatus::SUCCESS);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  public function changeToFailAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if ($defect->getStatusId() != Application_Model_DefectStatus::SUCCESS)
    {
      throw new Custom_404Exception();
    }
    
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->changeStatusToFail($defect))
    {    
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1(Application_Model_DefectStatus::FAIL);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  private function _getReopenForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_ReopenDefect(array(
      'action'      => $this->_url(array('id' => $defect->getId()), 'defect_reopen_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId()
    ));
    
    return $form->populate(array(
      'assigneeName'  => $defect->getAssigner()->getFullName(),
      'assigneeId'    => $defect->getAssignerId()
    ));
  }
  
  public function reopenAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if (in_array($defect->getStatusId(), array(
      Application_Model_DefectStatus::OPEN, 
      Application_Model_DefectStatus::REOPEN,
      Application_Model_DefectStatus::IN_PROGRESS,
      Application_Model_DefectStatus::FINISHED,
    )))
    {
      throw new Custom_404Exception();
    }
    
    $form = $this->_getReopenForm($defect);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
  }
  
  public function reopenProcessAction()
  {
    $defect = $this->getValidDefectForMinorModification();
    
    if (in_array($defect->getStatusId(), array(
      Application_Model_DefectStatus::OPEN, 
      Application_Model_DefectStatus::REOPEN,
      Application_Model_DefectStatus::IN_PROGRESS,
      Application_Model_DefectStatus::FINISHED,
    )))
    {
      throw new Custom_404Exception();
    }
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $defect->getId()), 'defect_reopen');
    }
    
    $form = $this->_getReopenForm($defect);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      return $this->render('reopen'); 
    }

    $defect->setProperties($form->getValues());
    $defect->setAssignee('id', $form->getValue('assigneeId'));
    $defect->setAssigner('id', $this->_user->getId());
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->reopen($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect($form->getBackUrl());
  }
  
  private function _getAssignForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_AssignDefect(array(
      'action'      => $this->_url(array('id' => $defect->getId()), 'defect_assign_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId()
    ));
    
    return $form->populate(array(
      'assigneeName'  => $defect->getAssigner()->getFullName(),
      'assigneeId'    => $defect->getAssignerId()
    ));
  }
  
  public function assignAction()
  {
    $defect = $this->_getValidDefectForAssign();
    $form = $this->_getAssignForm($defect);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
  }
  
  public function assignProcessAction()
  {
    $defect = $this->_getValidDefectForAssign();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array('id' => $defect->getId()), 'defect_assign');
    }
    
    $form = $this->_getAssignForm($defect);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      return $this->render('assign'); 
    }

    $defect->setProperties($form->getValues());
    $defect->setAssignee('id', $form->getValue('assigneeId'));
    $defect->setAssigner('id', $this->_user->getId());
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->assign($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect($form->getBackUrl());
  }
  
  public function assignToMeAction()
  {
    $defect = $this->_getValidDefectForAssign();
    
    if ($defect->getAssigneeId() == $this->_user->getId())
    {
      throw new Custom_404Exception();
    }
    
    $defect->setAssignee('id', $this->_user->getId());
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();
    
    if ($defectMapper->assign($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  private function _getValidDefect()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $defect = new Application_Model_Defect($idValidator->getFilteredValues());
    $defect->setProjectObject($this->_project);
    $defect->setAssignee('id', $this->_user->getId());
    $defect->setAssigner('id', $this->_user->getId());
    return $defect;
  }
  
  private function _getValidDefectForEdit()
  {
    $defect = $this->_getValidDefect();
    $defectMapper = new Project_Model_DefectMapper();
    $rowData = $defectMapper->getForEdit($defect);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($defect);
    
    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getAllByDefect($defect);
    return $defect->setExtraData('rowData', $rowData);
  }
  
  private function _getValidDefectForAssign()
  {
    $defect = $this->_getValidDefect();
    $defectMapper = new Project_Model_DefectMapper();
    $defect = $defectMapper->getForView($defect);
    
    if ($defect === false 
      || $defect->getProject()->getId() != $this->_project->getId()
      || in_array($defect->getStatusId(), array(
        Application_Model_DefectStatus::SUCCESS,
        Application_Model_DefectStatus::FAIL
    )))
    {
      throw new Custom_404Exception();
    }
    
    return $defect;
  }
  
  private function getValidDefectForMinorModification()
  {
    $defect = $this->_getValidDefect();
    $defectMapper = new Project_Model_DefectMapper();
    $defect = $defectMapper->getForView($defect);
    
    if ($defect === false || $defect->getProject()->getId() != $this->_project->getId())
    {
      throw new Custom_404Exception();
    }
    
    return $defect;
  }
  
  private function _checkEditPermissions(Application_Model_Defect $defect)
  {
    $roleActionsForEdit = array(
      Application_Model_RoleAction::DEFECT_EDIT_CREATED_BY_YOU,
      Application_Model_RoleAction::DEFECT_EDIT_ALL
    );
    
    $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_checkMultipleAccess($roleActionsForEdit));
    
    if (false === $defectUserPermission->isEditPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _getAccessPermissionsForDefects()
  {
    return $this->_checkMultipleAccess(Application_Model_DefectUserPermission::$_defectRoleActions);
  }
}
