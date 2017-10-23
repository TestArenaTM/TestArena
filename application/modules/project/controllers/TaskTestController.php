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
          $history->setType(Application_Model_HistoryType::ADD_TEST_TO_TASK);
          $history->setField1($taskTest->getTest()->getId());
          $historyMapper = new Project_Model_HistoryMapper();
          $historyMapper->add($history);
          $result['data']['taskTestId'] = $taskTest->getId();
          $result['data']['testType'] = $taskTest->getTest()->getTypeId();
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
    
    if ($taskTestMapper->changeResolution($taskTest))
    {      
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($taskTest->getTask());
      $history->setType(Application_Model_HistoryType::RESOLVE_TEST);
      $history->setField1($taskTest->getTest()->getId());
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
    
    if ($taskTestMapper->changeResolution($taskTest))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($taskTest->getTask());
      $history->setType(Application_Model_HistoryType::CHANGE_TEST_STATUS);
      $history->setField1($taskTest->getTest()->getId());
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
    
    if ($taskMapper->getForView($task) === false)
    {
      throw new Custom_404Exception();
    }
    
    return $task;
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
}