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
      if ($this->_project === null || $this->_project->getBugTracker()->getBugTrackerTypeId() != Application_Model_BugTrackerType::INTERNAL)
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

    return new Project_Form_DefectFilter(array(
      'action'          => $this->_projectUrl(array('page' => 1), 'defect_list'),
      'userList'        => $userMapper->getByProjectAsOptions($this->_project),
      'releaseList'     => $releaseMapper->getByProjectAsOptions($this->_project),
      'environmentList' => $environmentMapper->getByProjectAsOptions($this->_project),
      'versionList'     => $versionMapper->getByProjectAsOptions($this->_project),
      'project'         => $this->_project
    ));
  }

  public function indexAction()
  {
    $this->_setCurrentBackUrl('defect_list');
    $this->_setCurrentBackUrl('defect_assignToMe');
    $this->_setCurrentBackUrl('defect_changeStatus');
    $request = $this->_getRequestForFilter(Application_Model_FilterGroup::DEFECTS);
    $filterForm = $this->_getFilterForm();

    if ($filterForm->isValid($request->getParams()))
    {
      $this->_filterAction($filterForm->getValues(), 'defect'.$this->_project->getId());
      $defectMapper = new Project_Model_DefectMapper();
      $request->setParam('search', $filterForm->getValue('search'));
      list($list, $paginator, $numberRecords) = $defectMapper->getAll($request, $this->_project);

      $allIds = $defectMapper->getAllIds($request, $this->_user, $this->_getAccessPermissionsForDefects());
    }
    else
    {
      $list = $allIds = array();
      $numberRecords = 0;
      $paginator = null;
    }

    $filter = $this->_user->getFilter(Application_Model_FilterGroup::DEFECTS);

    if ($filter !== null)
    {
      $savedValues = $filter->getData();

      if (array_key_exists('tags', $savedValues) && is_array($savedValues['tags']) && count($savedValues['tags']) > 0)
      {
        $tagMapper = new Project_Model_TagMapper();
        $savedValues['tags'] = $tagMapper->getForFilterByIds($savedValues['tags']);
      }

      $filterForm->prepareSavedValues($savedValues);
    }

    $tagMapper = new Project_Model_TagMapper();
    $this->view->prePopulatedTags = $filterForm->prePopulateTags($tagMapper->getForPopulateByIds($filterForm->getTags()));

    $this->_setTranslateTitle();
    $this->view->numberRecords = $numberRecords;
    $this->view->defects = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->defectUserPermissions = $this->_getAccessPermissionsForDefects();
    $this->view->allIds = $allIds;
  }

  public function viewAction()
  {
    $defect = $this->_getValidDefectForView();

    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('defect_assignToMe');

    $fileMapper = new Project_Model_FileMapper();
    $defect->setExtraData('attachments', $fileMapper->getListByDefect($defect));

    $historyMapper = new Project_Model_HistoryMapper();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    $tagMapper = new Project_Model_TagMapper();
    $taskMapper = new Project_Model_TaskMapper();

    $this->_setTranslateTitle(array('name' => $defect->getTitle()), 'headTitle');

    $this->view->defect = $defect;
    $this->view->tasks = $taskMapper->getByDefect($defect, $this->_project);
    $this->view->environmentsReported = $environmentMapper->getByDefect($defect, 'reported');
    $this->view->versionsReported = $versionMapper->getByDefect($defect, 'reported');
    $this->view->environmentsResolved = $environmentMapper->getByDefect($defect, 'resolved');
    $this->view->versionsResolved = $versionMapper->getByDefect($defect, 'resolved');
    $this->view->tags = $tagMapper->getByDefect($defect);
    $this->view->history = $historyMapper->getByDefect($defect);
    $this->view->defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_getAccessPermissionsForDefects());
  }

  public function deleteTaskAjaxAction()
  {
    $this->checkUserSession(true, true);

    $result = array(
      'status' => 'SUCCESS',
      'data'   => array(),
      'errors' => array()
    );

    $t = new Custom_Translate();
    $defect = $this->_getValidDefectForView();
    $task = $this->_getValidTaskForDeleteTask();

    // $this->checkTaskDefectModifyPermission($defect); - dorobić zabezpieczenie na uprawnienia o ID: 37, 38, 39

    if ($task->getId() > 0)
    {
      $taskTest = new Application_Model_TaskDefect();
      $taskTest->setTaskObject($task);
      $taskTest->setDefect('id', $defect->getId());

      $taskDefectMapper = new Project_Model_TaskDefectMapper();
      if ($taskDefectMapper->delete($taskTest))
      {
        $historyMapper = new Project_Model_HistoryMapper();
        $history = new Application_Model_History();
        $history->setUserObject($this->_user);
        $history->setSubjectObject($defect);
        $history->setType(Application_Model_HistoryType::DELETE_TASK_FROM_DEFECT);
        $history->setField1($task->getId());
        $historyMapper->add($history);

        $history = new Application_Model_History();
        $history->setUserObject($this->_user);
        $history->setSubjectObject($task);
        $history->setType(Application_Model_HistoryType::DELETE_DEFECT_FROM_TASK);
        $history->setField1($defect->getId());
        $historyMapper->add($history);

        $result['data']['id'] = $task->getId() .'_'. $defect->getId();
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

  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    $defectMapper = new Project_Model_DefectMapper();
    $result = $defectMapper->getAllAjax($this->getRequest(), $this->_project);
    echo json_encode($result);
    exit;
  }

  /* to jest jakaś pozostałość - jeśli nie będzie zgłoszeń w tej sprawie należy usunąć */
  /*
  public function addTaskAjaxAction()
  {
    $this->checkUserSession(true, true);
    $result = array(
      'status' => 'SUCCESS',
      'data'   => array(),
      'errors' => array()
    );
    $t = new Custom_Translate();

    $defect = $this->_getValidDefect();
    $task = $this->_getValidTask();

    $taskDefect = new Application_Model_TaskDefect();
    $taskDefect->setDefect('id', $defect->getId());
    $taskDefect->setTaskObject($task);
    $taskDefect->setTestObject(new Application_Model_Test());

    if ($taskDefect->getTask()->getId() > 0)
    {
      $validator = new Custom_Validate_UniqueDefectTask(array('criteria' => array(
        'task_id' => $taskDefect->getTask()->getId(),
      )));

      if ($validator->isValid($taskDefect->getDefect()->getId()))
      {
        $taskDefectMapper = new Project_Model_TaskDefectMapper();

        if ($taskDefectMapper->add($taskDefect))
        {

          $history = new Application_Model_History();
          $history->setUserObject($this->_user);
          $history->setSubjectObject($defect);
          $history->setType(Application_Model_HistoryType::ADD_TASK_TO_DEFECT);
          $history->setField1($taskDefect->getTask()->getId());
          $historyMapper = new Project_Model_HistoryMapper();
          $historyMapper->add($history);

          $result['data']['taskId'] = $taskDefect->getTask()->getId();
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

  public function _getValidTest()
  {
    $test = new Application_Model_Test(array('id' => $this->getRequest()->getPost('testId')));
    $testMapper = new Project_Model_TestMapper();
    $test->setProjectObject($this->_project);
    $rowData = $testMapper->getForView($test);
    if ($rowData === false) {
      throw new Custom_404Exception();
    }
    return $test;
  }
  */

  private function _getValidTaskForDeleteTask()
  {
    $task = new Application_Model_Task(array('id' => $this->getRequest()->getParam('taskId')));

    $task->setProjectObject($this->_project);
    $task->setAssignee('id', $this->_user->getId());
    $task->setAssigner('id', $this->_user->getId());
    return $task;
  }

  private function _getValidTask()
  {
    $task = new Application_Model_Task(array('id' => $this->getRequest()->getPost('taskId')));

    $task->setProjectObject($this->_project);
    $task->setAssignee('id', $this->_user->getId());
    $task->setAssigner('id', $this->_user->getId());
    return $task;
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
    $releaseMapper = new Project_Model_ReleaseMapper();
    $release = $releaseMapper->getActive($this->_project);
    $request = $this->getRequest();

    $form = new Project_Form_AddDefect(array(
      'action'    => $this->_projectUrl(
        array(
          'layout' => $this->getRequest()->getParam('layout'),
          'taskId' => $request->getParam('taskId'),
          'taskTestId' => $request->getParam('taskTestId')
        ),
        'defect_add_process'
      ),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));

    if (isset($release) && $release->getId() > 0)
    {
      $form->populate(array(
        'releaseId'   => $release->getId(),
        'releaseName' => $release->getName()
      ));
    }
    $this->view->layout = $this->_helper->_layout->getLayout();
    return $form;
  }

  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::DEFECT_ADD, true);

    $this->_setTranslateTitle();
    $request = $this->getRequest();
    $form = $this->_getAddForm();
    if (!$request->isPost()) {
      $form->issueType->setValue($this->getRequest()->getParam('issueType'));
    }
    $this->_helper->_layout->setLayout($request->getParam('layout'));
    $this->view->layout = $request->getParam('layout');
    $this->view->form = $form;

    $releaseMapper = new Project_Model_ReleaseMapper();
    $this->view->prePopulatedRelease = $form->prePopulateRelease($releaseMapper->getForPopulateById($form->getRelease()));
  }

  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::DEFECT_ADD, true);

    $request = $this->getRequest();
    $this->_helper->_layout->setLayout($request->getParam('layout'));

    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'defect_add', 'default', ['type' => $request->getParam('type')]);
    }

    $form = $this->_getAddForm();
    $post = $form->prepareAttachments($request->getPost());

    if (!$form->isValid($post))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      $tagMapper = new Project_Model_TagMapper();
      $releaseMapper = new Project_Model_ReleaseMapper();

      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      $this->view->prePopulatedTags = $form->prePopulateTags($tagMapper->getForPopulateByIds($form->getTags()));
      $this->view->prePopulatedRelease = $form->prePopulateRelease($releaseMapper->getForPopulateById($form->getRelease()));

      return $this->render('add');
    }

    $defect = new Application_Model_Defect($form->getValues());
    $defect->setProjectObject($this->_project);
    $defect->setRelease('id', $form->getValue('releaseId'));
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
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history, $defect->getCreateDate());
      $this->_messageBox->set($t->translate('statusSuccess_'. $defect->getIssueType()), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      $this->projectRedirect(array('id' => $defect->getId()), 'defect_list');
    }

    if ($this->_helper->_layout->getLayout() === 'iframe') {

      if ($this->getRequest()->getParam('taskTestId') > 0)
      {
        $task = new Application_Model_Task();
        $task->setProjectObject($this->_project);

        $taskTest = new Application_Model_TaskTest();
        $taskTest->setId($request->getParam('taskTestId'));
        $taskTest->setTaskObject($task);

        $testDefect = new Application_Model_TestDefect();
        $testDefect->setDefectObject($defect);
        $testDefect->setTaskTestObject($taskTest);
        $testDefect->setBugTrackerId($this->_project->getBugTracker()->getBugTrackerId());

        $testDefectMapper = new Project_Model_TaskTestMapper();
        $taskTest = $testDefectMapper->getForView($taskTest);

        $taskDefectMapper = new Project_Model_TestDefectMapper();
        if ($taskDefectMapper->add($testDefect))
        {
          $historyMapper = new Project_Model_HistoryMapper();
          $history = new Application_Model_History();
          $history->setUserObject($this->_user);
          $history->setSubjectObject($taskTest->getTask());
          $history->setType(Application_Model_HistoryType::ADD_DEFECT_TO_TASK_TEST);
          $history->setField1($defect->getId());
          $history->setField2($taskTest->getId());
          $historyMapper->add($history);

          $history = new Application_Model_History();
          $history->setUserObject($this->_user);
          $history->setSubjectObject($defect);
          $history->setType(Application_Model_HistoryType::ADD_TASK_TEST_TO_DEFECT);
          $history->setField1($taskTest->getTask()->getId());
          $history->setField2($taskTest->getId());
          $historyMapper->add($history);
        }
      }
      else
      {
        $taskDefect = new Application_Model_TaskDefect();
        $taskDefect->setDefectId($defect->getId());
        $taskDefect->setTask('id', $request->getParam('taskId'));

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

          $history->setUserObject($this->_user);
          $history->setSubjectObject($taskDefect->getDefect());
          $history->setType(Application_Model_HistoryType::ADD_TASK_TO_DEFECT);
          $history->setField1($taskDefect->getTask()->getId());
          $historyMapper->add($history);
        }
      }
      $request->setControllerName('');
      return $this->render('partials/dialog-iframe-close');
    }

    $this->projectRedirect(array('id' => $defect->getId()), 'defect_view');
  }

  private function _getEditForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_EditDefect(array(
      'action'    => $this->_projectUrl(array('id' => $defect->getId()), 'defect_edit_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));

    $form->populate($defect->getExtraData('rowData'));

    return $form;
  }

  public function editAction()
  {
    $defect = $this->_getValidDefectForEdit();
    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();
    $tagMapper = new Project_Model_TagMapper();
    $releaseMapper = new Project_Model_ReleaseMapper();

    $form = $this->_getEditForm($defect);
    $rowData = $defect->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));

    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
    $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByDefect($defect));
    $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByDefect($defect));
    $this->view->prePopulatedTags = $form->prePopulateTags($tagMapper->getForPopulateByDefect($defect));
    $this->view->prePopulatedRelease = $form->prePopulateRelease($releaseMapper->getForPopulateById($form->getRelease()));
    $this->view->accessAssing = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL);
  }

  public function editProcessAction()
  {
    $defect = $this->_getValidDefectForEdit();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'defect_run_list');
    }

    $form = $this->_getEditForm($defect);
    $post = $form->prepareAttachments($request->getPost());

    if (!$form->isValid($post))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      $tagMapper = new Project_Model_TagMapper();
      $releaseMapper = new Project_Model_ReleaseMapper();

      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));
      $this->view->prePopulatedTags = $form->prePopulateTags($tagMapper->getForPopulateByIds($form->getTags()));
      $this->view->prePopulatedRelease = $form->prePopulateRelease($releaseMapper->getForPopulateById($form->getRelease()));

      return $this->render('edit');
    }

    if ($defect->getAssigneeId() != $form->getValue('assigneeId'))
    {
      $historyType = Application_Model_HistoryType::CHANGE_AND_ASSIGN_DEFECT;
    }
    else
    {
      $historyType = Application_Model_HistoryType::CHANGE_DEFECT;
    }
    /*elseif (ZMIENIŁO SIĘ TYLKO PRZYPISANIE)
    {
      $historyType = Application_Model_HistoryType::ASSIGN_TASK;
    }*/


    $defect->setDbProperties($form->getValues());
    $defect->setRelease('id', $form->getValue('releaseId'));

    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();

    if ($defectMapper->save($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType($historyType);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess_'. $defect->getIssueType()), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    $this->projectRedirect($form->getBackUrl());
  }

  public function deleteAction()
  {
    $defect = $this->_getValidDefectForDelete();
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();

    if ($defectMapper->delete($defect))
    {
      $this->_removeIdFromMultiSelectIds('defect', $defect->getId());
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_list', $this->_projectUrl(array(), 'defect_list')));
  }

  public function multiDeleteAction()
  {
    $multiSelectName = 'defect'.$this->_project->getId();
    $defectIds = $this->_getMultiSelectIds($multiSelectName, false);

    $defectMapper = new Project_Model_DefectMapper();
    $defects = $defectMapper->getByIds4CheckAccess($defectIds);

    $request = $this->getRequest();
    $allIds = $defectMapper->getAllIds($request, $this->_user, $this->_getAccessPermissionsForDefects());
    $skipIds = array();
    foreach($defectIds as $key => $id) {
      if (!in_array($id, $allIds)) {
        $skipIds[] = $id;
        unset($defectIds[$key]);
      }
    }

    $this->_checkDeletePermissions4MultipleDefects($defects);

    $t = new Custom_Translate();

    if ($defectMapper->deleteByIds($defectIds))
    {
      if (empty($skipIds))
      {
        $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      }
      else
      {
        $defects = $defectMapper->getByIds($skipIds);
        $titles = array();
        /** @var Application_Model_Defect $defect */
        foreach ($defects as $defect)
        {
          $titles[] = $defect->getTitle();
        }

        $message = $t->translate('statusWarning', array('names' => '"'.  implode('", "', $titles ) .'"'));
        $this->_messageBox->set($message, Custom_MessageBox::TYPE_WARNING);
      }
      $this->_clearMultiSelectIds($multiSelectName);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect(array(), 'defect_list');
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
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_changeStatus', $this->_projectUrl(array(), 'defect_list')));
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
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_changeStatus', $this->_projectUrl(array(), 'defect_list')));
  }

  private function _getResolveForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_ResolveDefect(array(
      'action'         => $this->_projectUrl(array('id' => $defect->getId()), 'defect_resolve_process'),
      'method'         => 'post',
      'projectId'      => $this->_project->getId(),
      'isAccessAssing' => $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL),
    ));

    if ($this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL)) {
      return $form->populate(array(
        'assigneeName' => $defect->getAssignee()->getFullName(),
        'assigneeId' => $defect->getAssigneeId()
      ));
    }
    else {
      return $form->populate(array(
        'assigneeName' => $defect->getAssigner()->getFullName(),
        'assigneeId' => $defect->getAssignerId()
      ));
    }

  }

  public function resolveAction()
  {
    $defect = $this->_getValidDefectForChangeStatus();
    $form = $this->_getResolveForm($defect);

    $environmentMapper = new Project_Model_EnvironmentMapper();
    $versionMapper = new Project_Model_VersionMapper();

    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
    $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByDefect($defect, 'resolved'));
    $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByDefect($defect, 'resolved'));
    $this->view->accessAssing = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL);
  }

  public function resolveProcessAction()
  {
    $defect = $this->_getValidDefectForChangeStatus();
    $request = $this->getRequest();

    $isChangeAssignee = false;
    if ($defect->getAssigneeId() !== (int) $request->getParam('assigneeId'))
    {
      $isChangeAssignee = true;
    }

    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $defect->getId()), 'defect_resolve');
    }

    $form = $this->_getResolveForm($defect);

    if (!$form->isValid($request->getPost()))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();
      $versionMapper = new Project_Model_VersionMapper();
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      $this->view->accessAssing = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL);
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds($form->getEnvironments()));
      $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByIds($form->getVersions()));

      return $this->render('resolve');
    }

    $defect->setProperties($form->getValues());
    $defect->setAssignee('id', $form->getValue('assigneeId'));
    $defect->setAssigner('id', $this->_user->getId());
    $defect->setStatus(Application_Model_DefectStatus::RESOLVED);
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();

    if ($defectMapper->resolve($defect, $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL)))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1($defect->getStatusId());
      if ($isChangeAssignee)
      {
        $history->setField2($defect->getAssigneeId());
      }

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

  private function _getIsInvalidForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_IsInvalidDefect(array(
      'action'         => $this->_projectUrl(array('id' => $defect->getId()), 'defect_is_invalid_process'),
      'method'         => 'post',
      'projectId'      => $this->_project->getId(),
      'isAccessAssing' => $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL),
    ));

    return $form->populate(array(
      'assigneeName'  => $defect->getAssignee()->getFullName(),
      'assigneeId'    => $defect->getAssigneeId()
    ));

  }

  public function isInvalidAction()
  {
    $defect = $this->_getValidDefectForChangeStatus();
    if (!$this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL))
    {
      $defect->setAssigneeObject($defect->getAssigner());
    }
    $form = $this->_getIsInvalidForm($defect);

    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->defect = $defect;
    $this->view->accessAssing = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL);
  }

  public function isInvalidProcessAction()
  {
    $defect = $this->_getValidDefectForChangeStatus();
    $request = $this->getRequest();

    $isChangeAssignee = false;
    if ($defect->getAssigneeId() !== (int) $request->getParam('assigneeId'))
    {
      $isChangeAssignee = true;
    }

    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $defect->getId()), 'defect_is_invalid');
    }

    $form = $this->_getIsInvalidForm($defect);

    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      $this->view->accessAssing = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL);
      return $this->render('is-invalid');
    }

    $defect->setProperties($form->getValues());
    if ($this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL))
    {
      $defect->setAssignee('id', $form->getValue('assigneeId'));
      $defect->setAssigner('id', $this->_user->getId());
    }

    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();

    if ($defectMapper->isInvalid($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1($defect->getStatusId());

      if ($isChangeAssignee)
      {
        $history->setField2($defect->getAssigneeId());
      }

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
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_changeStatus', $this->_projectUrl(array(), 'defect_list')));
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
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_changeStatus', $this->_projectUrl(array(), 'defect_list')));
  }

  private function _getCloseForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_CloseDefect(array(
      'action'      => $this->_projectUrl(array('id' => $defect->getId()), 'defect_close_process'),
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

    $type = $defect->getStatusId() == Application_Model_DefectStatus::RESOLVED ? 'resolved' : 'reported';
    $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByDefect($defect, $type));
    $this->view->prePopulatedVersions = $form->prePopulateVersions($versionMapper->getForPopulateByDefect($defect, $type));
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
      return $this->projectRedirect(array('id' => $defect->getId()), 'defect_close');
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
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_changeStatus', $this->_projectUrl(array(), 'defect_list')));
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
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_changeStatus', $this->_projectUrl(array(), 'defect_list')));
  }

  private function _getReopenForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_ReopenDefect(array(
      'action'         => $this->_projectUrl(array('id' => $defect->getId()), 'defect_reopen_process'),
      'method'         => 'post',
      'projectId'      => $this->_project->getId(),
      'isAccessAssing' => $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL)
    ));

    return $form->populate(array(
      'assigneeName'  => $defect->getAssignee()->getFullName(),
      'assigneeId'    => $defect->getAssigneeId()
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
    $this->view->accessAssing = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL);
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

    $isChangeAssignee = false;
    if ($defect->getAssigneeId() !== (int) $request->getParam('assigneeId'))
    {
      $isChangeAssignee = true;
    }

    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $defect->getId()), 'defect_reopen');
    }

    $form = $this->_getReopenForm($defect);

    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->defect = $defect;
      $this->view->accessAssing = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL);
      return $this->render('reopen');
    }

    $defect->setProperties($form->getValues());
    if ($this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL))
    {
      $defect->setAssignee('id', $form->getValue('assigneeId'));
      $defect->setAssigner('id', $this->_user->getId());
    }
    $defectMapper = new Project_Model_DefectMapper();
    $t = new Custom_Translate();

    $defect->setStatus(Application_Model_DefectStatus::REOPEN);
    if ($defectMapper->reopen($defect))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::CHANGE_DEFECT_STATUS);
      $history->setField1($defect->getStatusId());

      if ($isChangeAssignee)
      {
        $history->setField2($defect->getAssigneeId());
      }

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

  private function _getAssignForm(Application_Model_Defect $defect)
  {
    $form = new Project_Form_AssignDefect(array(
      'action'      => $this->_projectUrl(array('id' => $defect->getId()), 'defect_assign_process'),
      'method'      => 'post',
      'projectId'   => $this->_project->getId(),
      'isAccessAssing' => $this->_checkAccess(Application_Model_RoleAction::DEFECT_ASSIGN_ALL)
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
      return $this->projectRedirect(array('id' => $defect->getId()), 'defect_assign');
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
      $history->setType(Application_Model_HistoryType::ASSIGN_DEFECT);
      $history->setField1($defect->getAssigneeId());
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
      $history->setType(Application_Model_HistoryType::ASSIGN_DEFECT);
      $history->setField1($defect->getAssigneeId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('defect_assignToMe', $this->_projectUrl(array(), 'defect_list')));
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

  private function _getValidDefectForView()
  {
    $defect = $this->_getValidDefect();
    $defectMapper = new Project_Model_DefectMapper();

    if ($defectMapper->getForView($defect) === false)
    {
      throw new Custom_404Exception();
    }

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
    $rowData['attachments'] = $fileMapper->getListByDefect($defect);
    return $defect->setExtraData('rowData', $rowData);
  }

  private function _getValidDefectForDelete()
  {
    $defect = $this->_getValidDefect();
    $defectMapper = new Project_Model_DefectMapper();

    if ($defectMapper->getForDelete($defect) === false)
    {
      throw new Custom_404Exception();
    }

    $this->_checkDeletePermissions($defect);
    return $defect;
  }

  private function _getValidDefectForAssign()
  {
    $defect = $this->_getValidDefectForView();

    if (in_array($defect->getStatusId(), array(
      Application_Model_DefectStatus::SUCCESS,
      Application_Model_DefectStatus::FAIL
    )))
    {
      throw new Custom_404Exception();
    }

    $this->_checkAssignPermissions($defect);

    return $defect;
  }

  private function _getValidDefectForChangeStatus()
  {
    $defect = $this->_getValidDefectForView();

    if (in_array($defect->getStatusId(), array(
      Application_Model_DefectStatus::SUCCESS,
      Application_Model_DefectStatus::FAIL
    )))
    {
      throw new Custom_404Exception();
    }

    $this->_checkChangeStatusPermissions($defect);

    return $defect;
  }

  private function getValidDefectForMinorModification()
  {
    $defect = $this->_getValidDefectForView();

    $this->_checkChangeStatusPermissions($defect);

    return $defect;
  }

  private function _checkEditPermissions(Application_Model_Defect $defect)
  {
    $roleActionsForEdit = array(
      Application_Model_RoleAction::DEFECT_EDIT_CREATED_BY_YOU,
      Application_Model_RoleAction::DEFECT_EDIT_ALL,
      Application_Model_RoleAction::DEFECT_EDIT_ASSIGNED_TO_YOU
    );

    $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_checkMultipleAccess($roleActionsForEdit));

    if (false === $defectUserPermission->isEditPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }

  private function _checkDeletePermissions(Application_Model_Defect $defect)
  {
    $roleActionsForAssign = array(
      Application_Model_RoleAction::DEFECT_DELETE_ALL,
      Application_Model_RoleAction::DEFECT_DELETE_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::DEFECT_DELETE_CREATED_BY_YOU
    );

    $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_checkMultipleAccess($roleActionsForAssign));

    if (false === $defectUserPermission->isDeletePermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }

  private function _checkDeletePermissions4MultipleDefects(array $defects)
  {
    $roleActionsForAssign = array(
      Application_Model_RoleAction::DEFECT_DELETE_ALL,
      Application_Model_RoleAction::DEFECT_DELETE_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::DEFECT_DELETE_CREATED_BY_YOU
    );

    foreach ($defects as $defect)
    {
      $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_checkMultipleAccess($roleActionsForAssign));

      if (false === $defectUserPermission->isDeletePermission())
      {
        $this->_throwDefectAccessDeniedException();
      }
    }
  }

  private function _checkAssignPermissions(Application_Model_Defect $defect)
  {
    $roleActionsForEdit = array(
      Application_Model_RoleAction::DEFECT_ASSIGN_ALL,
    );

    $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_checkMultipleAccess($roleActionsForEdit));

    if (false === $defectUserPermission->isAssignPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }

  private function _checkChangeStatusPermissions(Application_Model_Defect $defect)
  {
    $roleActionsForEdit = array(
      Application_Model_RoleAction::DEFECT_CHANGE_STATUS_ALL,
      Application_Model_RoleAction::DEFECT_CHANGE_STATUS_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::DEFECT_CHANGE_STATUS_CREATED_BY_YOU,
      Application_Model_RoleAction::DEFECT_EDIT_CREATED_BY_YOU,
      Application_Model_RoleAction::DEFECT_EDIT_ALL,
      Application_Model_RoleAction::DEFECT_EDIT_ASSIGNED_TO_YOU
    );

    $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_checkMultipleAccess($roleActionsForEdit));

    if (false === $defectUserPermission->isChangeStatusPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }


  public function checkTaskDefectModifyPermission(Application_Model_Defect $defect)
  {
    $roleActionsForTaskDefectModify = array(
      Application_Model_RoleAction::TASK_DEFECT_MODIFY_CREATED_BY_YOU,
      Application_Model_RoleAction::TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_DEFECT_MODIFY_ALL
    );

    $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->_user, $this->_checkMultipleAccess($roleActionsForTaskDefectModify));

    if (false === $defectUserPermission->isTaskDefectModifyPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }

  private function _getAccessPermissionsForDefects()
  {
    return $this->_checkMultipleAccess(Application_Model_DefectUserPermission::$_defectRoleActions);
  }

  private function _getValidTaskTestForChange()
  {
    $taskTest = $this->_getValidTaskTest();

    $test = new Application_Model_Test();
    $test->setProjectObject($this->_project);
    $taskTest->setTestObject($test);
    $taskTestMapper = new Project_Model_TaskTestMapper();

    if ($taskTestMapper->getForView($taskTest) === false
      || $taskTest->getTask()->getProject()->getId() != $this->_project->getId()
    ) {
      if ($this->getRequest()->isXmlHttpRequest()) {
        $this->_throwTask500ExceptionAjax();
      } else {
        throw new Custom_404Exception();
      }
    }

    return $taskTest;
  }

  public function deleteTestAjaxAction()
  {
    $this->checkUserSession(true, true);

    $result = array(
      'status' => 'SUCCESS',
      'data'   => array(),
      'errors' => array()
    );

    $t = new Custom_Translate();
    $taskTest = $this->_getValidTaskTestForChange();
    $defect = $this->_getValidDefectForView();

    if ($taskTest->getId() > 0)
    {
      $taskTestMapper = new Project_Model_TaskTestMapper();

      if ($taskTestMapper->delete($taskTest))
      {
        $history = new Application_Model_History();
        $history->setUserObject($this->_user);
        $history->setSubjectObject($defect);
        $history->setType(Application_Model_HistoryType::DELETE_TEST_FROM_DEFECT);
        $history->setField1($taskTest->getTest()->getId());
        $historyMapper = new Project_Model_HistoryMapper();
        $historyMapper->add($history);
        $result['data']['taskTestId'] = $taskTest->getId();
      }
      else
      {
        $result['status'] = 'ERROR';
        $result['errors'][] = $t->translate('generalError').'1';
      }
    }
    else
    {
      $result['status'] = 'ERROR';
      $result['errors'][] = $t->translate('generalError').'2';
    }

    echo json_encode($result);
    exit;
  }

  private function _getValidTaskTest()
  {
    $taskId = (int) $this->getRequest()->getParam('taskId');

    if ($taskId <= 0)
    {
      throw new Custom_404Exception();
    }

    $taskTest = new Application_Model_TaskTest(array('id' => $taskId));

    $task = new Application_Model_Task();
    $task->setProjectObject($this->_project);
    $taskTest->setTaskObject($task);

    return $taskTest;
  }
}
