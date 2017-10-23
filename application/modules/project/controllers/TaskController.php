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
class Project_TaskController extends Custom_Controller_Action_Application_Project_Abstract
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
    $userMapper = new Project_Model_UserMapper();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    
    $release = new Application_Model_Release();
    $release->setId($this->getRequest()->getParam('release', null));
    
    return new Project_Form_TaskFilter(array(
      'action'          => $this->_projectUrl(array(), 'task_list'),
      'userList'        => $userMapper->getByProjectAsOptions($this->_project),
      'releaseList'     => $releaseMapper->getByProjectAsOptions($this->_project),
      'environmentList' => $environmentMapper->getByProjectAsOptions($this->_project),
      'versionList'     => $versionMapper->getByProjectAsOptions($this->_project),
      'project'         => $this->_project
    ));
  }
    
  public function indexAction()
  {
    $this->_setCurrentBackUrl('task_list');
    $this->_setCurrentBackUrl('task_assignToMe');
    $this->_setCurrentBackUrl('task_changeStatus');
    $request = $this->_getRequestForFilter(Application_Model_FilterGroup::TASKS);
    $filterForm = $this->_getFilterForm();

    if ($filterForm->isValid($request->getParams()))
    {
      $this->_filterAction($filterForm->getValues(), 'task'.$this->_project->getId());
      $request->setParam('userId', $this->_user->getId());
      
      $taskMapper = new Project_Model_TaskMapper();
      list($list, $paginator) = $taskMapper->getAll($request, $this->_project);
      
      $allIds = $taskMapper->getAllIds($request);
    }
    else
    {
      $list = $allIds = array();
      $paginator = null;
    }
    
    $filter = $this->_user->getFilter(Application_Model_FilterGroup::TASKS);
    
    if ($filter !== null)
    {
      $savedValues = $filter->getData();

      if (array_key_exists('tags', $savedValues) && is_array($savedValues['tags']) && count($savedValues['tags']) > 0)
      {
        $tagMapper = new Project_Model_TagMapper();
        $savedValues['tags'] = $tagMapper->getForFilterByIds($savedValues['tags']);
      }
      
      if (array_key_exists('exceededDueDate', $savedValues))
      {
        $savedValues['exceededDueDate'] = (bool)$savedValues['exceededDueDate'];
      }
    
      $filterForm->prepareSavedValues($savedValues);
    }

    // Zapisanie kolejności zadań do sesji
    $session = new Zend_Session_Namespace('Task');
    $session->allIds = $allIds;

    $tagMapper = new Project_Model_TagMapper();
    $this->view->prePopulatedTags = $filterForm->prePopulateTags($tagMapper->getForPopulateByIds($filterForm->getTags()));
    
    $this->_setTranslateTitle();
    $this->view->tasks = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->taskUserPermissions = $this->_getAccessPermissionsForTasks();
    $this->view->allIds = $allIds;
  }
  
  private function _setViewNavigation(Application_Model_Task $task)
  { 
    // Odczyt kolejności zadań do sesji
    $session = new Zend_Session_Namespace('Task');
    $nextTask = null;
    $prevTask = null;
    
    if ($session->allIds)
    {
      $ids = $session->allIds;
      $currentIndex = array_search($task->getId(), $ids);

      if ($currentIndex !== false)
      {
        $taskMapper = new Project_Model_TaskMapper();
        $nextPrevIds = array();

        if ($currentIndex > 0)
        {
          $nextPrevIds['prev'] = $ids[$currentIndex - 1];
        }

        if ($currentIndex < count($ids) - 1)
        {
          $nextPrevIds['next'] = $ids[$currentIndex + 1];
        }

        $taskList = $taskMapper->getByIds($nextPrevIds);

        if (array_key_exists('prev', $nextPrevIds) && array_key_exists($nextPrevIds['prev'], $taskList))
        {
          $prevTask = $taskList[$nextPrevIds['prev']];
        }

        if (array_key_exists('next', $nextPrevIds) && array_key_exists($nextPrevIds['next'], $taskList))
        {
          $nextTask = $taskList[$nextPrevIds['next']];
        }
      }
    }
    
    $this->view->prevTask = $prevTask;
    $this->view->nextTask = $nextTask;
  }
  
  private function _prepareDefectsForView(Application_Model_Task $task)
  {
    switch ($this->_project->getBugTracker()->getBugTrackerTypeId())
    {
      default:
      case Application_Model_BugTrackerType::INTERNAL:
        $defectMapper = new Project_Model_DefectMapper();
        $this->view->defects = $defectMapper->getByTask($task);
        $this->view->bugTracker = null;
        break;
      
      case Application_Model_BugTrackerType::JIRA:
        $defectJiraMapper = new Project_Model_DefectJiraMapper();
        $this->view->defects = $defectJiraMapper->getByTask($task, $this->_project->getBugTracker());
        $bugTrackerJiraMapper = new Project_Model_BugTrackerJiraMapper();
        $this->view->bugTracker = $bugTrackerJiraMapper->getById($this->_project->getBugTRacker()->getBugTrackerJira());
        break;
      
      case Application_Model_BugTrackerType::MANTIS:
        $defectMantisMapper = new Project_Model_DefectMantisMapper();
        $this->view->defects = $defectMantisMapper->getByTask($task, $this->_project->getBugTracker());
        $bugTrackerMantisMapper = new Project_Model_BugTrackerMantisMapper();
        $this->view->bugTracker = $bugTrackerMantisMapper->getById($this->_project->getBugTRacker()->getBugTrackerMantis());
        break;
    }
  }
    
  public function viewAction()
  {    
    $task = $this->_getValidTaskForView();
    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('task_assignToMe');
    $this->_setCurrentBackUrl('task_changeStatus');
    
    $fileMapper = new Project_Model_FileMapper();
    $task->setExtraData('attachments', $fileMapper->getListByTask($task));
    
    $historyMapper = new Project_Model_HistoryMapper();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    $tagMapper = new Project_Model_TagMapper();
    $taskTestMapper = new Project_Model_TaskTestMapper();
    
    $this->_setTranslateTitle(array('name' => $task->getTitle()), 'headTitle');
    $this->view->backUrl = $this->_getBackUrl('task_list', $this->_projectUrl(array(), 'test_list'));
    $this->view->task = $task;
    $this->view->environments = $environmentMapper->getByTask($task);
    $this->view->versions = $versionMapper->getByTask($task);
    $this->view->tags = $tagMapper->getByTask($task);
    $this->view->history = $historyMapper->getByTask($task);
    $this->view->taskTests = $taskTestMapper->getByTask($task);
    $this->view->taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_getAccessPermissionsForTasks());
    
    $this->_setViewNavigation($task);
    $this->_prepareDefectsForView($task);
  }
  
  private function _getAddForm()
  {
    $options = array(
      'action'    => $this->_projectUrl(array(), 'task_add_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    );
    $releaseMapper = new Project_Model_ReleaseMapper();
    $request = $this->getRequest();
    $release = null;
    
    if ($request->isPost())
    {
      $releaseId = $request->getPost('releaseId', 0);
      
      if ($releaseId > 0)
      {
        $release = new Application_Model_Release();
        $release->setId($releaseId);
        $release = $releaseMapper->getBasicById($release);
      }
    }
    else
    {
      $release = $releaseMapper->getActive($this->_project);
    }

    if ($release !== null && $release->getId() > 0)
    {
      $options['minDate'] = $release->getStartDate();
      $options['maxDate'] = $release->getEndDate();
    }

    $form = new Project_Form_AddTask($options);

    if ($release !== null && $release->getId() > 0)
    {
      $form->populate(array(
        'releaseId'   => $release->getId(),
        'releaseName' => $release->getName()
      ));
    }
    
    return $form;
  }  

  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TASK_ADD, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddForm();
  }
  
  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TASK_ADD, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'task_add');
    }
    
    $form = $this->_getAddForm();
    $post = $form->prepareAttachments($request->getPost());
    
    if (!$form->isValid($post))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      $tagMapper = new Project_Model_TagMapper();

      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      $this->view->prePopulatedTags = $form->prePopulateTags($tagMapper->getForPopulateByIds($form->getTags()));
      return $this->render('add'); 
    }
    
    $task = new Application_Model_Task($form->getValues());
    $task->setProjectObject($this->_project);
    $task->setRelease('id', $form->getValue('releaseId'));
    $task->setAssignee('id', $form->getValue('assigneeId'));
    $task->setAssigner('id', $this->_user->getId());
    $task->setAuthor('id', $this->_user->getId());
    
    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->add($task))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType(Application_Model_HistoryType::CREATE_TASK);
      $history->setField1($task->getAssigneeId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history, $task->getCreateDate());
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      $this->projectRedirect(array('id' => $task->getId()), 'task_view');
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      return $this->projectRedirect($this->_getBackUrl('task_list', $this->_projectUrl(array(), 'task_list')));
    }
  }
  
  private function _getEditForm(Application_Model_Task $task)
  {
    $request = $this->getRequest();
    $options = array(
      'action'    => $this->_projectUrl(array('id' => $task->getId()), 'task_edit_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    );

    $rowData = $task->getExtraData('rowData');
    $release = new Application_Model_Release();

    if ($request->isPost())
    {
      $release->setId($request->getParam('releaseId'));
    }
    else
    {
      $release->setId($rowData['releaseId']);
    }

    if ($release->getId() > 0)
    {
      $releaseMapper = new Project_Model_ReleaseMapper();
      
      if ($releaseMapper->getBasicById($release) !== false)
      {
        $options['minDate'] = $release->getStartDate();
        $options['maxDate'] = $release->getEndDate();
      }
    }

    $form = new Project_Form_EditTask($options);
    return $form->populate($rowData);
  }

  public function editAction()
  {
    $task = $this->_getValidTaskForEdit();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    $tagMapper = new Project_Model_TagMapper();
    $form = $this->_getEditForm($task);
    $rowData = $task->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));

    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->task = $task;
    $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByTask($task));
    $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByTask($task));
    $this->view->prePopulatedTags = $form->prePopulateTags($tagMapper->getForPopulateByTask($task));
  }
  
  public function editProcessAction()
  {
    $task = $this->_getValidTaskForEdit();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'task_run_list');
    }
    
    $form = $this->_getEditForm($task);
    $post = $form->prepareAttachments($request->getPost());

    if (!$form->isValid($post))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      $tagMapper = new Project_Model_TagMapper();
      
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      $this->view->prePopulatedTags = $form->prePopulateTags($tagMapper->getForPopulateByIds($form->getTags()));
      return $this->render('edit'); 
    }

    if ($task->getAssigneeId() != $form->getValue('assigneeId'))
    {
      $historyType = Application_Model_HistoryType::CHANGE_AND_ASSIGN_TASK;
    }
    else
    {
      $historyType = Application_Model_HistoryType::CHANGE_TASK;
    }
    /*elseif (ZMIENIŁO SIĘ TYLKO PRZYPISANIE)
    {
      $historyType = Application_Model_HistoryType::ASSIGN_TASK;
    }*/
    
    $task->setDbProperties($form->getValues());
    $task->setRelease('id', $form->getValue('releaseId'));
    $task->setAssignee('id', $form->getValue('assigneeId'));
    $task->setAssigner('id', $this->_user->getId());

    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->save($task))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType($historyType);
      $history->setField1($task->getAssigneeId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
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
    $task = $this->_getValidTaskForDelete();
    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->delete($task))
    {
      $this->_removeIdFromMultiSelectIds('task', $task->getId());
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect($this->_getBackUrl('task_list', $this->_projectUrl(array(), 'task_list')));
  }
  
  public function multiDeleteAction()
  {
    $multiSelectName = 'task'.$this->_project->getId();
    $taskIds = $this->_getMultiSelectIds($multiSelectName, false);
    
    $taskMapper = new Project_Model_TaskMapper();
    $tasks = $taskMapper->getByIds4CheckAccess($taskIds);
    
    $this->_checkDeletePermissions4MultipleTasks($tasks);
    
    $t = new Custom_Translate();
    
    if ($taskMapper->deleteByIds($taskIds))
    {    
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      $this->_clearMultiSelectIds($multiSelectName);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect(array(), 'task_list');
  }
  
  public function startAction()
  {
    $task = $this->_getValidTaskForChangeStatus();
    
    if (!in_array($task->getStatusId(), array(Application_Model_TaskStatus::OPEN, Application_Model_TaskStatus::REOPEN)))
    {
      throw new Custom_404Exception();
    }
    
    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->start($task))
    {    
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType(Application_Model_HistoryType::CHANGE_TASK_STATUS);
      $history->setField1(Application_Model_TaskStatus::IN_PROGRESS);
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect($this->_getBackUrl('task_changeStatus', $this->_projectUrl(array(), 'task_list')));
  }
  
  private function _getAssignForm(Application_Model_Task $task)
  {
    $form = new Project_Form_AssignTask(array(
      'action'      => $this->_projectUrl(array('id' => $task->getId()), 'task_assign_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId()
    ));
    
    return $form->populate(array(
      'assigneeName'  => $task->getAssignee()->getFullName(),
      'assigneeId'    => $task->getAssigneeId()
    ));
  }
  
  public function assignAction()
  {
    $task = $this->_getValidTaskForAssign();
    $form = $this->_getAssignForm($task);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->task = $task;
  }
  
  public function assignProcessAction()
  {
    $task    = $this->_getValidTaskForAssign();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $task->getId()), 'task_assign');
    }
    
    $form = $this->_getAssignForm($task);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->task = $task;
      return $this->render('assign'); 
    }

    $task->setProperties($form->getValues());
    $task->setStatus(Application_Model_TaskStatus::OPEN);
    $task->setAssignee('id', $form->getValue('assigneeId'));
    $task->setAssigner('id', $this->_user->getId());
    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->assign($task))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType(Application_Model_HistoryType::ASSIGN_TASK);
      $history->setField1($task->getAssigneeId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($form->getBackUrl());
  }
  
  public function assignToMeAction()
  {
    $task = $this->_getValidTaskForAssign();
    
    if ($task->getAssigneeId() == $this->_user->getId())
    {
      throw new Custom_404Exception();
    }
    
    $task->setStatus(Application_Model_TaskStatus::OPEN);
    $task->setAssignee('id', $this->_user->getId());
    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->assign($task))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType(Application_Model_HistoryType::ASSIGN_TASK);
      $history->setField1($task->getAssigneeId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('task_assignToMe', $this->_projectUrl(array(), 'task_list')));
  }
  
  private function _getCloseForm(Application_Model_Task $task)
  {
    $resolutionMapper = new Project_Model_ResolutionMapper();
    
    return new Project_Form_CloseTask(array(
      'action'      => $this->_projectUrl(array('id' => $task->getId()), 'task_close_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId(),
      'resolutions' => $resolutionMapper->getByProjectAsOptions($this->_project)
    ));
  }
  
  public function closeAction()
  {
    $task = $this->_getValidTaskForChangeStatus();
    
    if ($task->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    $form = $this->_getCloseForm($task);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->task = $task;
    $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByTask($task));
    $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByTask($task));
  }
  
  public function closeProcessAction()
  {
    $task = $this->_getValidTaskForChangeStatus();
    
    if ($task->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $task->getId()), 'task_close');
    }

    $form = $this->_getCloseForm($task);
    
    if (!$form->isValid($request->getPost()))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->task = $task;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      return $this->render('close'); 
    }

    $task->setProperties($form->getValues());
    $task->setResolution('id', $form->getValue('resolutionId'));
    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->close($task))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType(Application_Model_HistoryType::CHANGE_TASK_STATUS);
      $history->setField1(Application_Model_TaskStatus::CLOSED);
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($form->getBackUrl());
  }
  
  private function _getReopenForm(Application_Model_Task $task)
  {
    $form = new Project_Form_ReopenTask(array(
      'action'      => $this->_projectUrl(array('id' => $task->getId()), 'task_reopen_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId()
    ));
    
    return $form->populate(array(
      'assigneeName'  => $task->getAssignee()->getFullName(),
      'assigneeId'    => $task->getAssigneeId()
    ));
  }
  
  public function reopenAction()
  {
    $task = $this->_getValidTaskForChangeStatus();
    
    if ($task->getStatusId() != Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $form = $this->_getReopenForm($task);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->task = $task;
  }
  
  public function reopenProcessAction()
  {
    $task = $this->_getValidTaskForChangeStatus();
    
    if ($task->getStatusId() != Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $task->getId()), 'task_reopen');
    }
    
    $form = $this->_getReopenForm($task);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->task = $task;
      return $this->render('reopen'); 
    }

    $task->setProperties($form->getValues());
    $task->setStatus(Application_Model_TaskStatus::REOPEN);
    $task->setAssignee('id', $form->getValue('assigneeId'));
    $taskMapper = new Project_Model_TaskMapper();
    $t = new Custom_Translate();
    
    if ($taskMapper->assign($task))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType(Application_Model_HistoryType::CHANGE_TASK_STATUS);
      $history->setField1($task->getStatusId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($form->getBackUrl());
  }
  
  public function addDefectAjaxAction()
  {
    $this->checkUserSession(true, true);
    $result = array(
      'status' => 'SUCCESS',
      'data'   => array(),
      'errors' => array()
    );
    
    $taskDefect = new Application_Model_TaskDefect();
    $taskDefect->setTaskObject($this->_getValidTaskForDefectAjax());
    $taskDefect->setBugTrackerId($this->_project->getBugTracker()->getBugTrackerId());
    $t = new Custom_Translate();
    $data = false;
    
    switch ($this->_project->getBugTracker()->getBugTrackerTypeId())
    {
      default:
        $taskDefect->setDefect('id', $this->getRequest()->getPost('defectId'));
        $defectMapper = new Project_Model_DefectMapper();
        $data = $defectMapper->getForViewAjax($taskDefect->getDefect(), $this->_project);
        
        if ($data !== false)
        {
          $data['status'] = $t->translate('DEFECT_'.$taskDefect->getDefect()->getStatus(), null, 'status');
          $data['rowStatus'] = $taskDefect->getDefect()->getStatus()->getName();
          $data['defectType'] = Application_Model_BugTrackerType::INTERNAL;
        }
        break;

      case Application_Model_BugTrackerType::JIRA:
        $taskDefect->setDefectJira('id', $this->getRequest()->getPost('defectId'));
        $defecJiraMapper = new Project_Model_DefectJiraMapper();
        $data = $defecJiraMapper->getForViewAjax($taskDefect->getDefect(), $this->_project->getBugTracker());
        
        if ($data != false)
        {
          $bugTrackerJiraMapper = new Project_Model_BugTrackerJiraMapper();
          $bugTrackerJira = $bugTrackerJiraMapper->getById($this->_project->getBugTracker()->getBugTrackerJira());

          $externalData = Utils_Api_Jira::getIssueSummaryAndStatus($data['objectNumber'], $bugTrackerJira->getUrl(), $bugTrackerJira->getUserName(), $bugTrackerJira->getPassword());
          $data['defectType'] = Application_Model_BugTrackerType::JIRA;
          
          if ($externalData === false)
          {
            $data['status'] = null;
          }
          else
          {
            $data['status'] = $externalData['status'];
          
            if ($externalData['summary'] != $data['name'])
            {
              $data['name'] = $externalData['summary'];
              $defectJira = new Application_Model_DefectJira();
              $defectJira->setId($data['id']);
              $defectJira->setSummary($data['name']);
              $defectJiraMapper = new Project_Model_DefectJiraMapper();
              $defectJiraMapper->save($defectJira);
            }
          }
        }
        break;

      case Application_Model_BugTrackerType::MANTIS:
        $taskDefect->setDefectMantis('id', $this->getRequest()->getPost('defectId'));
        $defecMantisMapper = new Project_Model_DefectMantisMapper();
        $data = $defecMantisMapper->getForViewAjax($taskDefect->getDefect(), $this->_project->getBugTracker());
        
        if ($data !== false)
        {
          try
          {
            $bugTrackerMantisMapper = new Project_Model_BugTrackerMantisMapper();
            $bugTrackerMantis = $bugTrackerMantisMapper->getById($this->_project->getBugTracker()->getBugTrackerMantis());

            $externalData = Utils_Api_Mantis::getIssueSummaryAndStatusById($data['objectNumber'], $bugTrackerMantis->getUrl(), $bugTrackerMantis->getUserName(), $bugTrackerMantis->getPassword());
            $data['status'] = $externalData['status'];
            $data['defectType'] = Application_Model_BugTrackerType::MANTIS;

            if ($externalData['summary'] != $data['name'])
            {
              $data['name'] = $externalData['summary'];
              $defectMantis = new Application_Model_DefectMantis();
              $defectMantis->setId($data['id']);
              $defectMantis->setSummary($data['name']);
              $defectMantisMapper = new Project_Model_DefectMantisMapper();
              $defectMantisMapper->save($defectMantis);
            }
          }
          catch (Exception $e)
          {
            Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
            $data['status'] = null;
          }
        }
        break;

    }
    
    if ($taskDefect->getDefect()->getId() > 0 && $data !== false)
    {
      $validator = new Custom_Validate_UniqueTaskDefect(array('criteria' => array(
        'task_id'         => $taskDefect->getTask()->getId(),
        'bug_tracker_id'  => $this->_project->getBugTracker()->getBugTrackerId()
      )));
      
      if ($validator->isValid($taskDefect->getDefect()->getId()))
      {
        $taskDefectMapper = new Project_Model_TaskDefectMapper();

        if ($taskDefectMapper->add($taskDefect))
        {
          $history = new Application_Model_History();
          $history->setUserObject($this->_user);
          $history->setSubjectObject($taskDefect->getTask());
          $history->setType(Application_Model_HistoryType::ADD_DEFECT_TO_TASK);
          $history->setField1($taskDefect->getDefect()->getId());
          $historyMapper = new Project_Model_HistoryMapper();
          $historyMapper->add($history);
          $result['data'] = $data;
        }
        else
        {
          $result['status'] = 'ERROR';
          $result['errors'][] = $t->translate('generalError');
        }
      }
      else
      {
        $result['status'] = 'ERROR';
        
        foreach ($validator->getErrors() as $error)
        {
          $result['errors'][] = $t->translate($error, null, 'error');
        }
      }
    }
    else
    {
      $result['status'] = 'ERROR';
      $result['errors'][] = $t->translate('generalError');
    }
    
    echo json_encode($result);
    exit;
  }

  public function deleteDefectAjaxAction()
  {
    $this->checkUserSession(true, true);
    
    $result = array(
      'status' => 'SUCCESS',
      'data'   => array(),
      'errors' => array()
    );
    
    $t = new Custom_Translate();

    $taskDefect = new Application_Model_TaskDefect();
    $taskDefect->setTaskObject($this->_getValidTaskForDefectAjax());
    $taskDefect->setDefect('id', $this->getRequest()->getParam('defectId'));
    
    if ($taskDefect->getDefect()->getId() > 0)
    {
      $taskDefectMapper = new Project_Model_TaskDefectMapper();

      if ($taskDefectMapper->delete($taskDefect))
      {
        $history = new Application_Model_History();
        $history->setUserObject($this->_user);
        $history->setSubjectObject($taskDefect->getTask());
        $history->setType(Application_Model_HistoryType::DELETE_DEFECT_FROM_TASK);
        $history->setField1($taskDefect->getDefect()->getId());
        $historyMapper = new Project_Model_HistoryMapper();
        $historyMapper->add($history);
        $result['data']['id'] = $taskDefect->getDefect()->getId();
      }
      else
      {
        $result['status'] = 'ERROR';
        $result['errors'][] = $t->translate('generalError');
      }
    }
    else
    {
      $result['status'] = 'ERROR';
      $result['errors'][] = $t->translate('generalError');
    }
    
    echo json_encode($result);
    exit;
  }
  
  private function _getValidTask()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $task = new Application_Model_Task($idValidator->getFilteredValues());
    $task->setProjectObject($this->_project);
    $task->setAssignee('id', $this->_user->getId());
    $task->setAssigner('id', $this->_user->getId());
    return $task;
  }
  
  private function _getValidTaskForEdit()
  {
    $task = $this->_getValidTask();
    $taskMapper = new Project_Model_TaskMapper();
    $rowData = $taskMapper->getForEdit($task);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($task);
    
    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getListByTask($task);
    $rowData['dueDate'] = substr($rowData['dueDate'], 0, 16);
    return $task->setExtraData('rowData', $rowData);
  }
  
  private function _getValidTaskForView()
  {
    $task = $this->_getValidTask();
    $taskMapper = new Project_Model_TaskMapper();
    
    if ($taskMapper->getForView($task) === false)
    {
      throw new Custom_404Exception();
    }
    
    return $task;
  }
  
  private function _getValidTaskForAssign()
  {
    $task = $this->_getValidTaskForView();
    
    if ($task->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkAssignPermissions($task);
    
    return $task;
  }
  
  private function _getValidTaskForChangeStatus()
  {
    $task = $this->_getValidTaskForView();
    $this->_checkChangeStatusPermissions($task);    
    return $task;
  }
  
  private function _getValidTaskForDelete()
  {
    $task = $this->_getValidTaskForView();
    $this->_checkDeletePermissions($task);    
    return $task;
  }
  
  private function _getValidTaskForDefectAjax()
  {
    $task = $this->_getValidTaskForView();
    
    if ($task->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      $this->_throwTask500ExceptionAjax();
    }
    
    $this->_checkDefectModifyPermissions($task);
    return $task;
  }
  
  private function _checkEditPermissions(Application_Model_Task $task)
  {
    $roleActionsForEdit = array(
      Application_Model_RoleAction::TASK_EDIT_CREATED_BY_YOU,
      Application_Model_RoleAction::TASK_EDIT_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_EDIT_ALL
    );
    
    $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_checkMultipleAccess($roleActionsForEdit));
    
    if (false === $taskUserPermission->isEditPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _checkChangeStatusPermissions(Application_Model_Task $task)
  {
    $roleActionsForChangeStatus = array(
      Application_Model_RoleAction::TASK_CHANGE_STATUS_CREATED_BY_YOU,
      Application_Model_RoleAction::TASK_CHANGE_STATUS_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_CHANGE_STATUS_ALL,
      Application_Model_RoleAction::TASK_EDIT_CREATED_BY_YOU,
      Application_Model_RoleAction::TASK_EDIT_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_EDIT_ALL
    );
    
    $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_checkMultipleAccess($roleActionsForChangeStatus));
    
    if (false === $taskUserPermission->isChangeStatusPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _checkAssignPermissions(Application_Model_Task $task)
  {
    $roleActionsForAssign = array(
      Application_Model_RoleAction::TASK_ASSIGN_ALL,
      Application_Model_RoleAction::TASK_EDIT_CREATED_BY_YOU,
      Application_Model_RoleAction::TASK_EDIT_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_EDIT_ALL
    );
    
    $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_checkMultipleAccess($roleActionsForAssign));
    
    if (false === $taskUserPermission->isAssignPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _checkDeletePermissions(Application_Model_Task $task)
  {
    $roleActionsForAssign = array(
      Application_Model_RoleAction::TASK_DELETE_ALL,
      Application_Model_RoleAction::TASK_DELETE_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_DELETE_CREATED_BY_YOU
    );
    
    $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_checkMultipleAccess($roleActionsForAssign));
    
    if (false === $taskUserPermission->isDeletePermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _checkDeletePermissions4MultipleTasks(array $tasks)
  {
    $roleActionsForAssign = array(
      Application_Model_RoleAction::TASK_DELETE_ALL,
      Application_Model_RoleAction::TASK_DELETE_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_DELETE_CREATED_BY_YOU
    );
    
    foreach ($tasks as $task)
    {
      $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_checkMultipleAccess($roleActionsForAssign));
    
      if (false === $taskUserPermission->isDeletePermission())
      {
        $this->_throwTaskAccessDeniedException();
      }
    }
  }
  
  private function _checkDefectModifyPermissions(Application_Model_Task $task)
  {
    $roleActionsForDefectModify = array(
      Application_Model_RoleAction::TASK_DEFECT_MODIFY_CREATED_BY_YOU,
      Application_Model_RoleAction::TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_DEFECT_MODIFY_ALL
    );
    
    $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_checkMultipleAccess($roleActionsForDefectModify));
    
    if (false === $taskUserPermission->isDefectModifyPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _getAccessPermissionsForTasks()
  {
    return $this->_checkMultipleAccess(Application_Model_TaskUserPermission::$_taskRoleActions);
  }
}