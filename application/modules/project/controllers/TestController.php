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
class Project_TestController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    
    if (!$this->getRequest()->isXmlHttpRequest())
    {
      $this->checkUserSession(true);
      
      if (!in_array($this->getRequest()->getActionName(), array('index', 'view-other-test', 'view-test-case', 'view-exploratory-test')))
      {
        $this->_project->checkFinished();
        $this->_project->checkSuspended();
      }
    }
  }
  
  public function indexAction()
  {
    $request = $this->getRequest();    
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $this->_setCurrentBackUrl('otherTestEdit');
      $this->_setCurrentBackUrl('testCaseEdit');
      $this->_setCurrentBackUrl('exploratoryTestEdit');
      $testMapper = new Project_Model_TestMapper();
      list($list, $paginator) = $testMapper->getAll($request);
    }
    else
    {
      $list = array();
      $paginator = null;
    }
    
    $this->_setTranslateTitle();
    $this->view->tests = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->checkboxListForm = $this->_getCheckBoxListForm(array_keys($list));
    $this->view->testUserPermissions = $this->_getAccessPermissionsForTests();
    
    if (!$this->_project->isActive())
    {
      $this->render('index-not-active');
    }
  }
  
  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    $testMapper = new Project_Model_TestMapper();
    $result = $testMapper->getAllAjax($this->getRequest(), $this->_project);    
    echo json_encode($result);
    exit;
  }
  
  public function viewOtherTestAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $test = $this->_getValidTest();
    $testMapper = new Project_Model_TestMapper();
    $test = $testMapper->getOtherTestForView($test);
    $fileMapper = new Project_Model_FileMapper();
    $test->setExtraData('attachments', $fileMapper->getAllByTest($test));
    
    if ($test === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('otherTestEdit');
    $testMapper->getPreviousNextByTest($test);

    $this->_setTranslateTitle();
    $this->view->test = $test;
    $this->view->testUserPermission = new Application_Model_TestUserPermission($test, $this->_user, $this->_getAccessPermissionsForTests());
  }
  
  public function viewTestCaseAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $testCase = $this->_getValidTestCase();
    $testMapper = new Project_Model_TestMapper();    
    $testCase = $testMapper->getTestCaseForView($testCase);
    $fileMapper = new Project_Model_FileMapper();
    $testCase->setExtraData('attachments', $fileMapper->getAllByTest($testCase));
    
    if (false === $testCase)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('testCaseEdit');
    $testMapper->getPreviousNextByTest($testCase);

    $this->_setTranslateTitle();
    $this->view->testCase = $testCase;
    $this->view->testUserPermission = new Application_Model_TestUserPermission($testCase, $this->_user, $this->_getAccessPermissionsForTests());
  }
  
  public function viewExploratoryTestAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $exploratoryTest = $this->_getValidExploratoryTest();
    $testMapper = new Project_Model_TestMapper();    
    $exploratoryTest = $testMapper->getExploratoryTestForView($exploratoryTest);
    $fileMapper = new Project_Model_FileMapper();
    $exploratoryTest->setExtraData('attachments', $fileMapper->getAllByTest($exploratoryTest));
    
    if (false === $exploratoryTest)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('exploratoryTestEdit');
    $testMapper->getPreviousNextByTest($exploratoryTest);

    $this->_setTranslateTitle();
    $this->view->exploratoryTest = $exploratoryTest;
    $this->view->testUserPermission = new Application_Model_TestUserPermission($exploratoryTest, $this->_user, $this->_getAccessPermissionsForTests());
  }
  
  public function addOtherTestAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddOtherTestForm();
  }
  
  public function addOtherTestProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'test_add_other_test');
    }
    
    $form = $this->_getAddOtherTestForm();
    $post = $form->prepareAttachments($request->getPost());

    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $this->_project;
      
      return $this->render('add-other-test'); 
    }
    
    $test = new Application_Model_Test($form->getValues());
    $test->setAuthor('id', $this->_user->getId());
    $test->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->addOtherTest($test))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function addTestCaseAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddTestCaseForm();
  }
  
  public function addTestCaseProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'test_add_test_case');
    }
    
    $form = $this->_getAddTestCaseForm();
    $post = $form->prepareAttachments($request->getPost());

    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $this->_project;
      
      return $this->render('add-test-case'); 
    }
    
    $testCase = new Application_Model_TestCase($form->getValues());
    $testCase->setAuthor('id', $this->_user->getId());
    $testCase->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();    
    $t = new Custom_Translate();
    
    if ($testMapper->addTestCase($testCase))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function addExploratoryTestAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddExploratoryTestForm();
  }
  
  public function addExploratoryTestProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'test_add_exploratory_test');
    }
    
    $form = $this->_getAddExploratoryTestForm();
    $post = $form->prepareAttachments($request->getPost());
    
    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $this->_project;
      
      return $this->render('add-exploratory-test'); 
    }
    
    $exploratoryTest = new Application_Model_ExploratoryTest($form->getValues());
    $exploratoryTest->setAuthor('id', $this->_user->getId());
    $exploratoryTest->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->addExploratoryTest($exploratoryTest))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function editOtherTestAction()
  {
    $test = $this->_getValidOtherTestForEdit();
    $form = $this->_getEditOtherTestForm($test);
    $rowData = $test->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
  }
  
  public function editOtherTestProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'test_add_other_test');
    }
    
    $test = $this->_getValidOtherTestForEdit();
    
    $form = $this->_getEditOtherTestForm($test);
    $post = $form->prepareAttachments($request->getPost());
    
    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit-other-test'); 
    }
    
    $test->setProperties($form->getValues());
    $test->setAuthor('id', $this->_user->getId());
    $test->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->editOtherTestCore($test))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($test);
      $history->setField1($test->getFamilyId());
      $history->setType(Application_Model_HistoryType::CHANGE_OTHER_TEST);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);      
      $this->_messageBox->set($test->isNewVersion() ? $t->translate('statusNewVersionSuccess') : $t->translate('statusSuccess') , Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($test->isNewVersion() ? $t->translate('statusNewVersionError') : $t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    if ($test->isNewVersion())
    {
      if (($route = $this->_getBackRoute('otherTestEdit', true)) !== false)
      {
        if ($route['name'] != 'test_list')
        {
          $route['params']['id'] = $test->getId();
        }

        return $this->redirect($route['params'], $route['name']);
      }
      
      return $this->redirect($this->_getBackUrl('otherTestEdit', $this->_url(array(), 'test_list'), true));
    }

    return $this->redirect($form->getBackUrl());
  }
  
  public function editTestCaseAction()
  {
    $testCase = $this->_getValidTestCaseForEdit();
    $form = $this->_getEditTestCaseForm($testCase);
    $rowData = $testCase->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
  }
  
  public function editTestCaseProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'test_add_test_case');
    }
    
    $testCase = $this->_getValidTestCaseForEdit();
    
    $form = $this->_getEditTestCaseForm($testCase);
    $post = $form->prepareAttachments($request->getPost());
    
    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit-test-case'); 
    }
    
    $testCase->setProperties($form->getValues());
    $testCase->setAuthor('id', $this->_user->getId());
    $testCase->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->editTestCaseCore($testCase))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($testCase);
      $history->setField1($testCase->getFamilyId());
      $history->setType(Application_Model_HistoryType::CHANGE_TEST_CASE);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($testCase->isNewVersion() ? $t->translate('statusNewVersionSuccess') : $t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($testCase->isNewVersion() ? $t->translate('statusNewVersionError') : $t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    if ($testCase->isNewVersion())
    {
      if (($route = $this->_getBackRoute('testCaseEdit', true)) !== false)
      {
        if ($route['name'] != 'test_list')
        {
          $route['params']['id'] = $testCase->getId();
        }

        return $this->redirect($route['params'], $route['name']);
      }
      
      return $this->redirect($this->_getBackUrl('testCaseEdit', $this->_url(array(), 'test_list'), true));
    }

    return $this->redirect($form->getBackUrl());
  }
  
  public function editExploratoryTestAction()
  {
    $exploratoryTest = $this->_getValidExploratoryTestForEdit();
    $form = $this->_getEditExploratoryTestForm($exploratoryTest);
    $rowData = $exploratoryTest->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
  }
  
  public function editExploratoryTestProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'test_add_exploratory_test');
    }
    
    $exploratoryTest = $this->_getValidExploratoryTestForEdit();
    $form = $this->_getEditExploratoryTestForm($exploratoryTest);
    $post = $form->prepareAttachments($request->getPost());
    
    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit-exploratory-test'); 
    }
    
    $exploratoryTest->setProperties($form->getValues());
    $exploratoryTest->setAuthor('id', $this->_user->getId());
    $exploratoryTest->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->editExploratoryTestCore($exploratoryTest))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($exploratoryTest);
      $history->setField1($exploratoryTest->getFamilyId());
      $history->setType(Application_Model_HistoryType::CHANGE_EXPLORATORY_TEST);
      $historyMapper = new Application_Model_HistoryMapper();
      $historyMapper->add($history);
      $this->_messageBox->set($exploratoryTest->isNewVersion() ? $t->translate('statusNewVersionSuccess') : $t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($exploratoryTest->isNewVersion() ? $t->translate('statusNewVersionError') : $t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    if ($exploratoryTest->isNewVersion())
    {
      if (($route = $this->_getBackRoute('exploratoryTestEdit', true)) !== false)
      {
        if ($route['name'] != 'test_list')
        {
          $route['params']['id'] = $exploratoryTest->getId();
        }

        return $this->redirect($route['params'], $route['name']);
      }
      
      return $this->redirect($this->_getBackUrl('exploratoryTestEdit', $this->_url(array(), 'test_list'), true));
    }

    return $this->redirect($form->getBackUrl());
  }
  
  public function deleteAction()
  {
    $test = $this->_getValidTestForDelete();
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->delete($test))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->redirect(array(), 'test_list');
  }
  
  public function groupForwardToExecuteAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getGroupForwardToExecuteForm($this->getRequest()->getPost());
  }
  
  public function groupForwardToExecuteProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'test_group_forward_to_execute');
    }
    
    $form = $this->_getGroupForwardToExecuteForm($request->getPost());
    $post = $request->getPost();
    
    if (!$form->isValid($post))
    {
      $environmentMapper = new Project_Model_EnvironmentMapper();

      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->prePopulatedEnvironments = $form->prePopulateEnvironments($environmentMapper->getForPopulateByIds(explode(',',$form->getValue('environments'))));
      
      return $this->render('group-forward-to-execute'); 
    }
    
    $testRun = new Application_Model_TestRun($form->getValues());
    $testRun->setPhase('id', $form->getValue('phaseId'));
    $testRun->setAssignee('id', $form->getValue('assigneeId'));
    $testRun->setAssigner('id', $this->_user->getId());
    
    $testRunMapper = new Project_Model_TestRunMapper();
    $t = new Custom_Translate();
    
    if ($testRunMapper->addGroup($testRun))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectType(Application_Model_HistorySubjectType::TASK_RUN);
      $history->setType(Application_Model_HistoryType::CREATE_TASK_RUN);
      $history->setField1($testRun->getAssigneeId());
      $historyMapper = new Application_Model_HistoryMapper();

      foreach ($testRun->getExtraData('testRunIds') as $testRunId)
      {
        $history->setSubjectId($testRunId);
        $historyMapper->add($history);
      }

      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getFilterForm()
  {
    $userMapper = new Project_Model_UserMapper();
    
    return new Project_Form_TestFilter(array(
      'action'    => $this->_url(array(), 'test_list'),
      'userList'  => $userMapper->getByProjectAsOptions($this->_project)
    ));
  }
  
  private function _getCheckBoxListForm($ids)
  {
    return new Custom_Form_CheckboxList(array(
      'ids' => $ids
    ));
  }  

  private function _getAddOtherTestForm()
  {
    return new Project_Form_AddOtherTest(array(
      'action' => $this->_url(array(), 'test_add_other_test_process'),
      'method' => 'post'
    ));
  }
  
  private function _getAddTestCaseForm()
  {
    return new Project_Form_AddTestCase(array(
      'action' => $this->_url(array(), 'test_add_test_case_process'),
      'method' => 'post'
    ));
  }
  
  private function _getAddExploratoryTestForm()
  {
    return new Project_Form_AddExploratoryTest(array(
      'action' => $this->_url(array(), 'test_add_exploratory_test_process'),
      'method' => 'post'
    ));
  }

  private function _getEditOtherTestForm(Application_Model_Test $testOther)
  {
    $options = array(
      'action'      => $this->_url(array('id' => $testOther->getId()), 'test_edit_other_test_process'),
      'method'      => 'post'
    );
    
    $form = new Project_Form_EditOtherTest($options);
    $form->setAttrib('id', 'test_other_test_edit');
    return $form->populate($testOther->getExtraData('rowData'));
  }
  
  private function _getEditTestCaseForm(Application_Model_TestCase $testCase)
  {
    $options = array(
      'action' => $this->_url(array('id' => $testCase->getId()), 'test_edit_test_case_process'),
      'method' => 'post'
    );
    
    $form = new Project_Form_EditTestCase($options);
    $form->setAttrib('id', 'test_test_case_edit');
    return $form->populate($testCase->getExtraData('rowData'));
  }
  
  private function _getEditExploratoryTestForm(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $options = array(
      'action' => $this->_url(array('id' => $exploratoryTest->getId()), 'test_edit_exploratory_test_process'),
      'method' => 'post'
    );
    
    $form = new Project_Form_EditExploratoryTest($options);
    $form->setAttrib('id', 'test_exploration_edit');
    return $form->populate($exploratoryTest->getExtraData('rowData'));
  }
  
  private function _getValidTest()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $test = new Application_Model_Test($idValidator->getFilteredValues());
    $test->setProjectObject($this->_project);
    return $test;
  }
  
  private function _getValidTestForDelete()
  {
    $test = $this->_getValidTest();
    $testMapper = new Project_Model_TestMapper();
    $rowData = $testMapper->getForView($test);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($test);
    
    return $test;
  }
  
  private function _getValidOtherTestForEdit()
  {
    $test = $this->_getValidTest();
    $testMapper = new Project_Model_TestMapper();
    
    $rowData = $testMapper->getOtherTestForEdit($test);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($test);
    
    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getAllByTest($test);
    return $test->setExtraData('rowData', $rowData);
  }
  
  private function _getValidTestCase()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $testCase = new Application_Model_TestCase($idValidator->getFilteredValues());
    return $testCase->setProjectObject($this->_project);
  }
  
  private function _getValidTestCaseForEdit()
  {
    $testCase = $this->_getValidTestCase();
    $testMapper = new Project_Model_TestMapper();
    
    $rowData = $testMapper->getTestCaseForEdit($testCase);
    
    if (false === $rowData)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($testCase);
    
    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getAllByTest($testCase);
    return $testCase->setExtraData('rowData', $rowData);
  }
  
  private function _getValidExploratoryTest()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $exploratoryTest = new Application_Model_ExploratoryTest($idValidator->getFilteredValues());
    return $exploratoryTest->setProjectObject($this->_project);
  }
  
  private function _getValidExploratoryTestForEdit()
  {
    $exploratoryTest = $this->_getValidExploratoryTest();
    $testMapper = new Project_Model_TestMapper();
    
    $rowData = $testMapper->getExploratoryTestForEdit($exploratoryTest);
    
    if (false === $rowData)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($exploratoryTest);
    
    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getAllByTest($exploratoryTest);    
    return $exploratoryTest->setExtraData('rowData', $rowData);
  }
  
  private function _checkEditPermissions(Application_Model_Test $test)
  {
    $roleActionsForEdit = array(
      Application_Model_RoleAction::TEST_EDIT_CREATED_BY_YOU,
      Application_Model_RoleAction::TEST_EDIT_ALL
    );
    
    $testUserPermission = new Application_Model_TestUserPermission($test, $this->_user, $this->_checkMultipleAccess($roleActionsForEdit));
    
    if (false === $testUserPermission->isEditPermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _getAccessPermissionsForTests()
  {
    return $this->_checkMultipleAccess(Application_Model_TestUserPermission::$_testRoleActions);
  }
}