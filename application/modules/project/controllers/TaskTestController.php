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
class Project_TaskTestController extends Custom_Controller_Action_Application_Project_Abstract
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
      
      if (!in_array($this->getRequest()->getActionName(), array('index', 'view-other-test', 'view-test-case', 'view-exploratory-test', 'view-automatic-test', 'view-checklist')))
      {
        $this->_project->checkFinished();
        $this->_project->checkSuspended();
      }
    }
  }

  private function _getTaskTestForAddTestAjax()
  {
    $taskTest = $this->_getValidTaskTest();
    $taskTestMapper = new Project_Model_TaskTestMapper();
    return $taskTestMapper->getForView($taskTest);
  }

  private function _getDefectForAddTestAjax()
  {
    $defect = new Application_Model_Defect();
    $defect->setId($this->getRequest()->getPost('defectId'));
    $defect->setProjectObject($this->_project);

    return $defect;
  }
  
  public function addTestDefectAjaxAction()
  {
    $this->checkUserSession(true, true);

    $result = array(
      'status' => 'SUCCESS',
      'data'   => array(),
      'errors' => array()
    );

    $t = new Custom_Translate();

    $taskTest = $this->_getTaskTestForAddTestAjax();
    $defect = $this->_getDefectForAddTestAjax();

    if ($taskTest !== false)
    {
      $validator = new Custom_Validate_UniqueTestDefect(array('criteria' => array('task_test_id' => $taskTest->getId())));
      
      if ($validator->isValid($defect->getId()))
      {
        $testDefectMapper = new Project_Model_TestDefectMapper();
        $testDefect = new Application_Model_TestDefect();
        $testDefect->setTaskTestObject($taskTest);
        $testDefect->setBugTrackerId($this->_project->getBugTracker()->getBugTrackerId());

        switch ($this->_project->getBugTracker()->getBugTrackerTypeId())
        {
          default:
            $defectMapper = new Project_Model_DefectMapper();
            $defect = $defectMapper->getForView($defect);
            $testDefect->setDefectObject($defect);
            $data = $defectMapper->getForViewAjax($defect, $this->_project);
            $data['rowStatus'] = $defect->getStatus()->getName();
            $data['statusColor'] = $this->_getStatusColorForDefect($defect);
            $data['status'] = $t->translate('DEFECT_'.$defect->getStatus(), null, 'status');
            $data['issueType'] = $defect->getIssueType();
            $data['issueTypeTitle'] = $t->translate('ISSUE_'.$defect->getIssueType(), null, 'type');
            $data['defectType'] = Application_Model_BugTrackerType::INTERNAL;
            break;

          case Application_Model_BugTrackerType::JIRA:
            $testDefect->setDefectJira('id', $this->getRequest()->getPost('defectId'));
            $defectJiraMapper = new Project_Model_DefectJiraMapper();
            $data = $defectJiraMapper->getForViewAjax($testDefect->getDefect(), $this->_project->getBugTracker());

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
            $testDefect->setDefectMantis('id', $this->getRequest()->getPost('defectId'));
            $defectMantisMapper = new Project_Model_DefectMantisMapper();
            $data = $defectMantisMapper->getForViewAjax($testDefect->getDefect(), $this->_project->getBugTracker());

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

        if ($testDefectMapper->add($testDefect))
        {
          $result['data'] = $data;
          $result['data']['itemId'] = $result['data']['id'] .'_'. $taskTest->getId();

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

  private function _getStatusColorForDefect($defect)
  {
    $color = '';
    switch ($defect->getStatusId()) {
      case Application_Model_DefectStatus::OPEN:
        $color = $defect->getProject()->getOpenStatusColor();
        break;

      case Application_Model_DefectStatus::REOPEN:
        $color = $defect->getProject()->getReopenStatusColor();
        break;

      case Application_Model_DefectStatus::IN_PROGRESS:
        $color = $defect->getProject()->getInProgressStatusColor();
        break;

      case Application_Model_DefectStatus::FINISHED:
        $color = $defect->getProject()->getClosedStatusColor();
        break;

      case Application_Model_DefectStatus::INVALID:
        $color = $defect->getProject()->getInvalidStatusColor();
        break;

      case Application_Model_DefectStatus::RESOLVED:
        $color = $defect->getProject()->getResolvedStatusColor();
        break;
    }
    return $color;
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

    $taskTest = new Application_Model_TaskTest();
    $taskTest->setId($this->getRequest()->getParam('taskTestId'));

    $defect = new Application_Model_Defect();
    $defect->setId($this->getRequest()->getParam('defectId'));

    $testDefectMapper = new Project_Model_TestDefectMapper();
    $testDefect = $testDefectMapper->getForViewByTaskTestDefect($taskTest, $defect);
    if ($testDefectMapper->delete($testDefect))
    {
      $taskMapper = new Project_Model_TaskMapper();
      $task = $taskMapper->getForDeleteDefect($taskTest);
      $tests = $task->getTaskTests();
      $test = reset($tests);

      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($task);
      $history->setType(Application_Model_HistoryType::DELETE_DEFECT_FROM_TASK_TEST);
      $history->setField1($defect->getId());
      $history->setField2($taskTest->getId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);

      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($defect);
      $history->setType(Application_Model_HistoryType::DELETE_TASK_TEST_FROM_DEFECT);
      $history->setField1($task->getId());
      $history->setField2($taskTest->getId());
      $history->setExtraData('testId', $test->getId());
      $history->setExtraData('taskTestId', $taskTest->getId());
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);

      $result['data']['itemId'] = $defect->getId() .'_'. $taskTest->getId();
    }
    else
    {
      $result['status'] = 'ERROR';
      $result['errors'][] = $t->translate('generalError');
    }


      echo json_encode($result);
      exit;
  }

  public function addTestAjaxAction()
  {
    $this->checkUserSession(true, true);
    $result = array(
      'status' => 'SUCCESS',
      'data'   => array(),
      'errors' => array()
    );

    $t = new Custom_Translate();

    $taskTest = new Application_Model_TaskTest();
    $taskTest->setTaskObject($this->_getValidTask());
    $taskTest->setTest('id', $this->getRequest()->getPost('testId'));
    $taskTest->setDefect('id', $this->getRequest()->getParam('defectId'));
    $testMapper = new Project_Model_TestMapper();

    if ($taskTest->getTest()->getId() > 0 && ($test = $testMapper->getForViewInTask($taskTest->getTest(), $this->_project)) !== false)
    {
      $taskTest->setTestObject($test);
      $validator = new Custom_Validate_UniqueTaskTest(array('criteria' => array('task_id' => $taskTest->getTask()->getId())));

      if ($validator->isValid($taskTest->getTest()->getId()))
      {
        $taskTestMapper = new Project_Model_TaskTestMapper();

        if ($taskTestMapper->add($taskTest))
        {
          $history = new Application_Model_History();
          $history->setUserObject($this->_user);
          $history->setSubjectObject($taskTest->getTask());
          $history->setType(Application_Model_HistoryType::ADD_TASK_TEST_TO_TASK);
          $history->setField1($taskTest->getId());
          $historyMapper = new Project_Model_HistoryMapper();
          $historyMapper->add($history);
          $result['data']['taskTestId'] = $taskTest->getId();
          $result['data']['testType'] = $taskTest->getTest()->getTypeId();
          $result['data']['status'] = $taskTest->getTest()->getStatus();
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

  private function _getValidTask()
  {
    $idValidator = new Application_Model_Validator_Id();

    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }

    $task = new Application_Model_Task($idValidator->getFilteredValues());
    $task->setProjectObject($this->_project);
    $taskMapper = new Project_Model_TaskMapper();

    if ($taskMapper->getForView($task, $this->_project->getBugTracker()) === false)
    {
      throw new Custom_404Exception();
    }

    return $task;
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

    if ($taskTest->getId() > 0)
    {
      $taskTestMapper = new Project_Model_TaskTestMapper();

      if ($taskTestMapper->delete($taskTest))
      {
        $history = new Application_Model_History();
        $history->setUserObject($this->_user);
        $history->setSubjectObject($taskTest->getTask());
        $history->setType(Application_Model_HistoryType::DELETE_TEST_FROM_TASK);
        $history->setField1($taskTest->getTest()->getId());
        $history->setExtraData('testId', $taskTest->getTest()->getId());
        $history->setExtraData('taskTestId', $taskTest->getId());
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

  
  public function chnageChecklistItemStatusToNoneAction()
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
  
  public function viewOtherTestAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $taskTest = $this->_getValidTaskOtherTestForView();
    $fileMapper = new Project_Model_FileMapper();
    $taskTest->getTest()->setExtraData('attachments', $fileMapper->getListByTest($taskTest->getTest()));

    $this->_setTranslateTitle(array('name' => $taskTest->getTest()->getName()), 'headTitle');
    $this->view->taskTest = $taskTest;
    $this->view->taskUserPermission = new Application_Model_TaskUserPermission($taskTest->getTask(), $this->_user, $this->_getAccessPermissionsForTasks());
    $this->_prepareDefectsForView($taskTest);
  }
  
  public function viewTestCaseAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $taskTest = $this->_getValidTaskTestCaseForView();
    $fileMapper = new Project_Model_FileMapper();
    $taskTest->getTest()->setExtraData('attachments', $fileMapper->getListByTest($taskTest->getTest()));

    $this->_setTranslateTitle(array('name' => $taskTest->getTest()->getName()), 'headTitle');
    $this->view->taskTest = $taskTest;
    $this->view->taskUserPermission = new Application_Model_TaskUserPermission($taskTest->getTask(), $this->_user, $this->_getAccessPermissionsForTasks());
    $this->_prepareDefectsForView($taskTest);
  }
  
  public function viewExploratoryTestAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $taskTest = $this->_getValidTaskExploratoryTestForView();
    $fileMapper = new Project_Model_FileMapper();
    $taskTest->getTest()->setExtraData('attachments', $fileMapper->getListByTest($taskTest->getTest()));

    $this->_setTranslateTitle(array('name' => $taskTest->getTest()->getName()), 'headTitle');
    $this->view->taskTest = $taskTest;
    $this->view->taskUserPermission = new Application_Model_TaskUserPermission($taskTest->getTask(), $this->_user, $this->_getAccessPermissionsForTasks());
    $this->_prepareDefectsForView($taskTest);
  }
  
  public function viewAutomaticTestAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $taskTest = $this->_getValidTaskAutomaticTestForView();
    $fileMapper = new Project_Model_FileMapper();
    $taskTest->getTest()->setExtraData('attachments', $fileMapper->getListByTest($taskTest->getTest()));

    $this->_setTranslateTitle(array('name' => $taskTest->getTest()->getName()), 'headTitle');
    $this->view->taskTest = $taskTest;
    $this->view->taskUserPermission = new Application_Model_TaskUserPermission($taskTest->getTask(), $this->_user, $this->_getAccessPermissionsForTasks());
    $this->_prepareDefectsForView($taskTest);
  }
  
  public function viewChecklistAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('task_checklist_view');
    $taskTest = $this->_getValidTaskChecklistForView();
    $fileMapper = new Project_Model_FileMapper();
    $taskTest->getTest()->setExtraData('attachments', $fileMapper->getListByTest($taskTest->getTest()));
    $taskUserPermission = new Application_Model_TaskUserPermission($taskTest->getTask(), $this->_user, $this->_getAccessPermissionsForTasks());

    $this->_setTranslateTitle(array('name' => $taskTest->getTest()->getName()), 'headTitle');
    $this->view->taskTest = $taskTest;
    $this->view->taskUserPermission = $taskUserPermission;
    $this->_prepareDefectsForView($taskTest);
    
    if ($this->_project->isActive() 
      && $taskTest->getTask()->getStatusId() != Application_Model_TaskStatus::CLOSED
      && $taskUserPermission->isChangeStatusPermission())
    {
      $this->view->render('task-test/view-checklist.phtml');
    }
    else
    {
      $this->view->render('task-test/view-checklist-no-action.phtml');
    }
  }

  public function multiChangeStatusAction()
  {
    $taskTest = $this->_getValidTaskChecklistForView();

    $request = $this->getRequest();
    $ids = $request->getParam('ids');
    $status = (int) $request->getParam('status');
    $t = new Custom_Translate();

    $taskChecklistItemStatus = new Application_Model_TaskChecklistItemStatus();
    $statusName = $taskChecklistItemStatus->getName($status);
    $statusName = $t->translate('TASK_CHECKLIST_ITEM_'. $statusName, null, 'status');

    $validate = new Custom_Validate_TaskChecklistItemsChangeStatuses();
    $validate->setTaskTest($taskTest);
    if ($validate->isValid($status, $ids)) {
    $taskChecklistItemMapper = new Project_Model_TaskChecklistItemMapper();
      if ($taskChecklistItemMapper->changeStatusByIds($ids, $status)) {
        $this->_messageBox->set($t->translate('statusSuccess status', array("status_name" => $statusName)), Custom_MessageBox::TYPE_INFO);
      } else {
        $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      }
    } else {
      $this->_messageBox->set($t->translate('statusSuccess status', array("status_name" => $statusName)), Custom_MessageBox::TYPE_INFO);
    }

    return $this->projectRedirect($this->_getBackUrl('task_checklist_view', $this->_projectUrl(array(), 'task_list')));
  }
  
  private function _getResolveTaskTestForm(Application_Model_TaskTest $taskTest)
  {
    $resolutionMapper = new Project_Model_ResolutionMapper();
    
    return new Project_Form_ResolveTaskTest(array(
      'action'      => $this->_projectUrl(array('id' => $taskTest->getId()), 'task_test_resolve_process'),
      'method'      => 'post',
      'resolutions' => $resolutionMapper->getByProjectAsOptions($this->_project)
    ));
  }
  
  public function resolveTestAction()
  {
    $taskTest = $this->_getValidTaskTestForChange();
    if ($taskTest->getTask()->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }

    $form = $this->_getResolveTaskTestForm($taskTest);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->taskTest = $taskTest;
  }
  
  public function resolveTestProcessAction()
  {
    $taskTest = $this->_getValidTaskTestForChange();
    
    if ($taskTest->getTask()->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $taskTest->getId()), 'task_test_resolve');
    }

    $form = $this->_getResolveTaskTestForm($taskTest);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->taskTest = $taskTest;
      return $this->render('resolve-test'); 
    }
    
    $taskTest->setExtraData('comment', $form->getValue('comment'));
    $taskTest->setResolution('id', $form->getValue('resolutionId'));
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $t = new Custom_Translate();
    
    if ($taskTestMapper->changeResolution($taskTest, $this->_user))
    {      
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($taskTest->getTask());
      $history->setType(Application_Model_HistoryType::RESOLVE_TASK_TEST);
      $history->setField1($taskTest->getId());
      $history->setField2($taskTest->getResolution()->getId());
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

  private function _getChangeTaskTestForm(Application_Model_TaskTest $taskTest)
  {
    $resolutionMapper = new Project_Model_ResolutionMapper();
    
    $form = new Project_Form_ChangeTaskTest(array(
      'action'      => $this->_projectUrl(array('id' => $taskTest->getId()), 'task_test_change_process'),
      'method'      => 'post',
      'resolutions' => $resolutionMapper->getByProjectAsOptions($this->_project)
    ));
    
    return $form->populate(array(
      'resolutionId' => $taskTest->getResolutionId()
    ));
  }
  
  public function changeTestAction()
  {
    $taskTest = $this->_getValidTaskTestForChange();
    
    if ($taskTest->getTask()->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $form = $this->_getChangeTaskTestForm($taskTest);
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
    $this->view->taskTest = $taskTest;
  }
  
  public function changeTestProcessAction()
  {
    $taskTest = $this->_getValidTaskTestForChange();

    if ($taskTest->getTask()->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {
      throw new Custom_404Exception();
    }
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array('id' => $taskTest->getId()), 'task_test_resolve');
    }

    $form = $this->_getChangeTaskTestForm($taskTest);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->taskTest = $taskTest;
      return $this->render('change-test'); 
    }

    $taskTest->setExtraData('comment', $form->getValue('comment'));
    $taskTest->setResolution('id', $form->getValue('resolutionId'));
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $t = new Custom_Translate();

    if ($taskTestMapper->changeResolution($taskTest, $this->_user))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($taskTest->getTask());
      $history->setType(Application_Model_HistoryType::CHANGE_TASK_TEST_STATUS);
      $history->setField1($taskTest->getId());
      $history->setField2($taskTest->getResolution()->getId());
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
  
  private function _getValidTaskTest()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }

    $taskTest = new Application_Model_TaskTest($idValidator->getFilteredValues());
    
    $task = new Application_Model_Task();
    $task->setProjectObject($this->_project);
    $taskTest->setTaskObject($task);
    
    return $taskTest;
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
        || $taskTest->getTask()->getStatusId() == Application_Model_TaskStatus::CLOSED)
    {      
      if ($this->getRequest()->isXmlHttpRequest())
      {
        $this->_throwTask500ExceptionAjax();
      }
      else
      {
        throw new Custom_404Exception();
      }
    }
    
    $this->_checkTestModifyPermissions($taskTest->getTask()); 
    return $taskTest;
  }
  
  private function _getValidTaskOtherTestForView()
  {
    $taskTest = $this->_getValidTaskTest();
    
    $test = new Application_Model_Test();
    $test->setProjectObject($this->_project);
    $taskTest->setTestObject($test);
    
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $this->_checkTaskTestForView($taskTestMapper->getOtherTestForView($taskTest));
    return $taskTest;
  }
  
  private function _getValidTaskTestCaseForView()
  {    
    $taskTest = $this->_getValidTaskTest();
    
    $test = new Application_Model_TestCase();
    $test->setProjectObject($this->_project);
    $taskTest->setTestObject($test);
    
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $this->_checkTaskTestForView($taskTestMapper->getTestCaseForView($taskTest));
    return $taskTest;
  }
  
  private function _getValidTaskExploratoryTestForView()
  {
    $taskTest = $this->_getValidTaskTest();
    
    $test = new Application_Model_ExploratoryTest();
    $test->setProjectObject($this->_project);
    $taskTest->setTestObject($test);
    
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $this->_checkTaskTestForView($taskTestMapper->getExploratoryTestForView($taskTest));
    return $taskTest;
  }
  
  private function _getValidTaskAutomaticTestForView()
  {
    $taskTest = $this->_getValidTaskTest();   
    
    $test = new Application_Model_AutomaticTest();
    $test->setProjectObject($this->_project);
    $taskTest->setTestObject($test);
    
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $this->_checkTaskTestForView($taskTestMapper->getAutomaticTestForView($taskTest));
    return $taskTest;
  }
  
  private function _getValidTaskChecklistForView()
  {    
    $taskTest = $this->_getValidTaskTest();
    
    $test = new Application_Model_Checklist();
    $test->setProjectObject($this->_project);
    $taskTest->setTestObject($test);
    
    $taskTestMapper = new Project_Model_TaskTestMapper();    
    $this->_checkTaskTestForView($taskTestMapper->getChecklistForView($taskTest));
    return $taskTest;
  }
  
  private function _checkTaskTestForView($taskTest)
  {
    if ($taskTest === false)
    {      
      if ($this->getRequest()->isXmlHttpRequest())
      {
        $this->_throwTask500ExceptionAjax();
      }
      else
      {
        throw new Custom_404Exception();
      }
    }
  }
  
  private function _checkTestModifyPermissions(Application_Model_Task $task)
  {
    $roleActionsForTestModify = array(
      Application_Model_RoleAction::TASK_TEST_MODIFY_CREATED_BY_YOU,
      Application_Model_RoleAction::TASK_TEST_MODIFY_ASSIGNED_TO_YOU,
      Application_Model_RoleAction::TASK_TEST_MODIFY_ALL
    );
    
    $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->_user, $this->_checkMultipleAccess($roleActionsForTestModify));
    
    if (false === $taskUserPermission->isTestModifyPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _getAccessPermissionsForTasks()
  {
    return $this->_checkMultipleAccess(Application_Model_TaskUserPermission::$_taskRoleActions);
  }

  private function _getAccessPermissionsForTests()
  {
    return $this->_checkMultipleAccess(Application_Model_TestUserPermission::$_testRoleActions);
  }

  private function _prepareDefectsForView(Application_Model_TaskTest $taskTest)
  {
    $this->view->isDefectAddPermission = $this->_checkAccess(Application_Model_RoleAction::DEFECT_ADD);

    switch ($this->_project->getBugTracker()->getBugTrackerTypeId())
    {
      default:
      case Application_Model_BugTrackerType::INTERNAL:
        $defectMapper = new Project_Model_DefectMapper();
        $this->view->defects = $defectMapper->getByTaskTest($taskTest);
        $this->view->bugTracker = null;
        break;

      case Application_Model_BugTrackerType::JIRA:
        $defectJiraMapper = new Project_Model_DefectJiraMapper();
        $this->view->defects = $defectJiraMapper->getByTaskTest($taskTest, $this->_project->getBugTracker());
        $bugTrackerJiraMapper = new Project_Model_BugTrackerJiraMapper();
        $this->view->bugTracker = $bugTrackerJiraMapper->getById($this->_project->getBugTRacker()->getBugTrackerJira());
        break;

      case Application_Model_BugTrackerType::MANTIS:
        $defectMantisMapper = new Project_Model_DefectMantisMapper();
        $this->view->defects = $defectMantisMapper->getByTaskTest($taskTest, $this->_project->getBugTracker());
        $bugTrackerMantisMapper = new Project_Model_BugTrackerMantisMapper();
        $this->view->bugTracker = $bugTrackerMantisMapper->getById($this->_project->getBugTRacker()->getBugTrackerMantis());
        break;
    }

  }

}