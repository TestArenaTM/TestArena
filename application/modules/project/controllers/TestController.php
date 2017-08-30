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
  
  private function _getFilterForm()
  {
    $userMapper = new Project_Model_UserMapper();
    return new Project_Form_TestFilter(array(
      'action'   => $this->_projectUrl(array(), 'test_list'),
      'userList' => $userMapper->getByProjectAsOptions($this->_project)
    ));
  }
  
  public function indexAction()
  {
    $this->_setCurrentBackUrl('test_list');
    $this->_setCurrentBackUrl('otherTestEdit');
    $this->_setCurrentBackUrl('testCaseEdit');
    $this->_setCurrentBackUrl('exploratoryTestEdit');
    $this->_setCurrentBackUrl('automaticTestEdit');
    $this->_setCurrentBackUrl('checklistEdit');
    $request = $this->_getRequestForFilter(Application_Model_FilterGroup::TESTS);
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $this->_filterAction($filterForm->getValues(), 'test'.$this->_project->getId());
      $testMapper = new Project_Model_TestMapper();
      list($list, $paginator) = $testMapper->getAll($request, $this->_project);
      
      $allIds = $testMapper->getAllIds($request);
    }
    else
    {
      $list = $allIds = array();
      $paginator = null;
    }
    
    $filter = $this->_user->getFilter(Application_Model_FilterGroup::TESTS);
    
    if ($filter !== null)
    {
      $filterForm->prepareSavedValues($filter->getData());
    }
    
    $this->_setTranslateTitle();
    $this->view->tests = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->checkboxListForm = $this->_getCheckBoxListForm(array_keys($list));
    $this->view->testUserPermissions = $this->_getAccessPermissionsForTests();
    $this->view->allIds = $allIds;
    
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
    $test = $this->_getValidTest();
    $testMapper = new Project_Model_TestMapper();
    $test = $testMapper->getOtherTestForView($test);
    
    if ($test === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('otherTestEdit');
    
    $fileMapper = new Project_Model_FileMapper();
    $test->setExtraData('attachments', $fileMapper->getListByTest($test));
    
    $testMapper->getPreviousNextByTest($test);

    $this->_setTranslateTitle(array('name' => $test->getName()), 'headTitle');
    $this->view->test = $test;
    $this->view->backUrl = $this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'));
    $this->view->testUserPermission = new Application_Model_TestUserPermission($test, $this->_user, $this->_getAccessPermissionsForTests());
  }
  
  public function viewTestCaseAction()
  {
    $testCase = $this->_getValidTestCase();
    $testMapper = new Project_Model_TestMapper();    
    $testCase = $testMapper->getTestCaseForView($testCase);
    
    if (false === $testCase)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('testCaseEdit');
    
    $fileMapper = new Project_Model_FileMapper();
    $testCase->setExtraData('attachments', $fileMapper->getListByTest($testCase));
    
    $testMapper->getPreviousNextByTest($testCase);

    $this->_setTranslateTitle();
    $this->view->testCase = $testCase;
    $this->view->backUrl = $this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'));
    $this->view->testUserPermission = new Application_Model_TestUserPermission($testCase, $this->_user, $this->_getAccessPermissionsForTests());
  }
  
  public function viewExploratoryTestAction()
  {
    $exploratoryTest = $this->_getValidExploratoryTest();
    $testMapper = new Project_Model_TestMapper();    
    $exploratoryTest = $testMapper->getExploratoryTestForView($exploratoryTest);
    
    if (false === $exploratoryTest)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('exploratoryTestEdit');

    $fileMapper = new Project_Model_FileMapper();
    $exploratoryTest->setExtraData('attachments', $fileMapper->getListByTest($exploratoryTest));
    
    $testMapper->getPreviousNextByTest($exploratoryTest);

    $this->_setTranslateTitle();
    $this->view->exploratoryTest = $exploratoryTest;
    $this->view->backUrl = $this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'));
    $this->view->testUserPermission = new Application_Model_TestUserPermission($exploratoryTest, $this->_user, $this->_getAccessPermissionsForTests());
  }
  
  public function viewAutomaticTestAction()
  {
    $automaticTest = $this->_getValidAutomaticTest();
    $testMapper = new Project_Model_TestMapper();
    $automaticTest = $testMapper->getAutomaticTestForView($automaticTest);
    
    if (false === $automaticTest)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('automaticTestTestEdit');
    
    $fileMapper = new Project_Model_FileMapper();
    $automaticTest->setExtraData('attachments', $fileMapper->getListByTest($automaticTest));
    
    $testMapper->getPreviousNextByTest($automaticTest);

    $this->_setTranslateTitle(array('name' => $automaticTest->getName()), 'headTitle');
    $this->view->automaticTest = $automaticTest;
    $this->view->backUrl = $this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'));
    $this->view->testUserPermission = new Application_Model_TestUserPermission($automaticTest, $this->_user, $this->_getAccessPermissionsForTests());
  }
  
  public function viewChecklistAction()
  {
    $checklist = $this->_getValidChecklist();
    $testMapper = new Project_Model_TestMapper();
    $checklist = $testMapper->getChecklistForView($checklist);
    
    if (false === $checklist)
    {
      throw new Custom_404Exception();
    }
    
    $this->_setCurrentBackUrl('file_dwonload');
    $this->_setCurrentBackUrl('checklistTestEdit');
    
    $fileMapper = new Project_Model_FileMapper();
    $checklist->setExtraData('attachments', $fileMapper->getListByTest($checklist));
    
    $testMapper->getPreviousNextByTest($checklist);

    $this->_setTranslateTitle(array('name' => $checklist->getName()), 'headTitle');
    $this->view->checklist = $checklist;
    $this->view->backUrl = $this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'));
    $this->view->testUserPermission = new Application_Model_TestUserPermission($checklist, $this->_user, $this->_getAccessPermissionsForTests());
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
      return $this->projectRedirect(array(), 'test_add_other_test');
    }
    
    $form = $this->_getAddOtherTestForm();
    $post = $form->preparePostData($request->getPost());

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
    
    return $this->projectRedirect($this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'), true));
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
      return $this->projectRedirect(array(), 'test_add_test_case');
    }
    
    $form = $this->_getAddTestCaseForm();
    $post = $form->preparePostData($request->getPost());

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
    
    return $this->projectRedirect($this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'), true));
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
      return $this->projectRedirect(array(), 'test_add_exploratory_test');
    }
    
    $form = $this->_getAddExploratoryTestForm();
    $post = $form->preparePostData($request->getPost());
    
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
    
    return $this->projectRedirect($this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'), true));
  }
  
  public function addAutomaticTestAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddAutomaticTestForm();
  }
  
  public function addAutomaticTestProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'test_add_automatic_test');
    }
    
    $form = $this->_getAddAutomaticTestForm();
    $post = $form->preparePostData($request->getPost());

    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $this->_project;
      
      return $this->render('add-automatic-test'); 
    }
    
    $automaticTest = new Application_Model_AutomaticTest($form->getValues());
    $automaticTest->setAuthor('id', $this->_user->getId());
    $automaticTest->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->addAutomaticTest($automaticTest))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect($this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'), true));
  }
  
  public function addChecklistAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddChecklistForm();
  }
  
  public function addChecklistProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TEST_ADD, true);
    
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'test_add_checklist');
    }

    $form = $this->_getAddChecklistForm();
    $post = $form->preparePostData($request->getPost());

    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->project = $this->_project;
      
      return $this->render('add-checklist'); 
    }

    $checklist = new Application_Model_Checklist($form->getValues());
    $checklist->setAuthor('id', $this->_user->getId());
    $checklist->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->addChecklist($checklist))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect($this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'), true));
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
      return $this->projectRedirect(array(), 'test_add_other_test');
    }
    
    $test = $this->_getValidOtherTestForEdit();
    
    $form = $this->_getEditOtherTestForm($test);
    $post = $form->preparePostData($request->getPost());
    
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
      $historyMapper = new Project_Model_HistoryMapper();
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

        return $this->projectRedirect($route['params'], $route['name']);
      }
      
      return $this->projectRedirect($this->_getBackUrl('otherTestEdit', $this->_projectUrl(array(), 'test_list'), true));
    }

    return $this->projectRedirect($form->getBackUrl());
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
      return $this->projectRedirect(array(), 'test_add_test_case');
    }
    
    $testCase = $this->_getValidTestCaseForEdit();
    
    $form = $this->_getEditTestCaseForm($testCase);
    $post = $form->preparePostData($request->getPost());
    
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
      $historyMapper = new Project_Model_HistoryMapper();
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

        return $this->projectRedirect($route['params'], $route['name']);
      }
      
      return $this->projectRedirect($this->_getBackUrl('testCaseEdit', $this->_projectUrl(array(), 'test_list'), true));
    }

    return $this->projectRedirect($form->getBackUrl());
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
      return $this->projectRedirect(array(), 'test_add_exploratory_test');
    }
    
    $exploratoryTest = $this->_getValidExploratoryTestForEdit();
    $form = $this->_getEditExploratoryTestForm($exploratoryTest);
    $post = $form->preparePostData($request->getPost());
    
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
      $historyMapper = new Project_Model_HistoryMapper();
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

        return $this->projectRedirect($route['params'], $route['name']);
      }
      
      return $this->projectRedirect($this->_getBackUrl('exploratoryTestEdit', $this->_projectUrl(array(), 'test_list'), true));
    }

    return $this->projectRedirect($form->getBackUrl());
  }
  
  public function editAutomaticTestAction()
  {
    $automaticTest = $this->_getValidAutomaticTestForEdit();
    $form = $this->_getEditAutomaticTestForm($automaticTest);
    $rowData = $automaticTest->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
  }
  
  public function editAutomaticTestProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'test_add_automatic_test');
    }
    
    $automaticTest = $this->_getValidAutomaticTestForEdit();
    
    $form = $this->_getEditAutomaticTestForm($automaticTest);
    $post = $form->preparePostData($request->getPost());
    
    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit-automatic-test'); 
    }
    
    $automaticTest->setProperties($form->getValues());
    $automaticTest->setAuthor('id', $this->_user->getId());
    $automaticTest->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->editAutomaticTestCore($automaticTest))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($automaticTest);
      $history->setField1($automaticTest->getFamilyId());
      $history->setType(Application_Model_HistoryType::CHANGE_AUTOMATIC_TEST);
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);      
      $this->_messageBox->set($automaticTest->isNewVersion() ? $t->translate('statusNewVersionSuccess') : $t->translate('statusSuccess') , Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($automaticTest->isNewVersion() ? $t->translate('statusNewVersionError') : $t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    if ($automaticTest->isNewVersion())
    {
      if (($route = $this->_getBackRoute('automaticTestEdit', true)) !== false)
      {
        if ($route['name'] != 'test_list')
        {
          $route['params']['id'] = $automaticTest->getId();
        }

        return $this->projectRedirect($route['params'], $route['name']);
      }
      
      return $this->projectRedirect($this->_getBackUrl('automaticTestEdit', $this->_projectUrl(array(), 'test_list'), true));
    }

    return $this->projectRedirect($form->getBackUrl());
  }
  
  public function editChecklistAction()
  {
    $checklist = $this->_getValidChecklistForEdit();
    $form = $this->_getEditChecklistForm($checklist);
    $rowData = $checklist->getExtraData('rowData');
    $form->populate($form->prepareAttachmentsFromDb($rowData['attachments']));
    $form->populate($form->prepareItemsFromDb($rowData['items']));
    
    $this->_setTranslateTitle();
    $this->view->form = $form;
  }
  
  public function editChecklistProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'test_add_checklist');
    }
    
    $checklist = $this->_getValidChecklistForEdit();
    
    $form = $this->_getEditChecklistForm($checklist);
    $post = $form->preparePostData($request->getPost());
    
    if (!$form->isValid($post))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit-checklist'); 
    }
    
    $checklist->setProperties($form->getValues());
    $checklist->setAuthor('id', $this->_user->getId());
    $checklist->setProject('id', $this->_project->getId());
    
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->editChecklistCore($checklist))
    {
      $history = new Application_Model_History();
      $history->setUserObject($this->_user);
      $history->setSubjectObject($checklist);
      $history->setField1($checklist->getFamilyId());
      $history->setType(Application_Model_HistoryType::CHANGE_CHECKLIST);
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->add($history);      
      $this->_messageBox->set($checklist->isNewVersion() ? $t->translate('statusNewVersionSuccess') : $t->translate('statusSuccess') , Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($checklist->isNewVersion() ? $t->translate('statusNewVersionError') : $t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    if ($checklist->isNewVersion())
    {
      if (($route = $this->_getBackRoute('checklistEdit', true)) !== false)
      {
        if ($route['name'] != 'test_list')
        {
          $route['params']['id'] = $checklist->getId();
        }

        return $this->projectRedirect($route['params'], $route['name']);
      }
      
      return $this->projectRedirect($this->_getBackUrl('checklistEdit', $this->_projectUrl(array(), 'test_list'), true));
    }

    return $this->projectRedirect($form->getBackUrl());
  }
  
  public function deleteAction()
  {
    $test = $this->_getValidTestForDelete();
    $testMapper = new Project_Model_TestMapper();
    
    $t = new Custom_Translate();
    
    if ($testMapper->delete($test))
    {
      $this->_removeIdFromMultiSelectIds('test', $test->getId());
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    return $this->projectRedirect($this->_getBackUrl('test_list', $this->_projectUrl(array(), 'test_list'), true));
  }
  
  public function multiDeleteAction()
  {
    $multiSelectName = 'test'.$this->_project->getId();
    $testIds = $this->_getMultiSelectIds($multiSelectName, false);
    
    $testMapper = new Project_Model_TestMapper();
    $tests = $testMapper->getByIds4CheckAccess($testIds);
    
    $this->_checkDeletePermissions4MultipleTests($tests);
    
    $t = new Custom_Translate();
    
    if ($testMapper->deleteByIds($testIds))
    {    
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      $this->_clearMultiSelectIds($multiSelectName);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect(array(), 'test_list');
  }
  
  /* NIEUŻYWANE */
  public function groupForwardToExecuteAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getGroupForwardToExecuteForm($this->getRequest()->getPost());
  }
  
  /* NIEUŻYWANE */
  public function groupForwardToExecuteProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'test_group_forward_to_execute');
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
      $historyMapper = new Project_Model_HistoryMapper();

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
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  /* NIEUŻYWANE */
  private function _getCheckBoxListForm($ids)
  {
    return new Custom_Form_CheckboxList(array(
      'ids' => $ids
    ));
  }  

  private function _getAddOtherTestForm()
  {
    $form = new Project_Form_AddOtherTest(array(
      'action'    => $this->_projectUrl(array(), 'test_add_other_test_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
    
    $otherTest = $this->_getValidTest(false);
    
    if ($otherTest !== null)
    {
      $testMapper = new Project_Model_TestMapper();    
      $rowData = $testMapper->getOtherTestForEdit($otherTest);

      if ($rowData !== false)
      {
        $fileMapper = new Project_Model_FileMapper();
        $form->populate($rowData);
        $form->populate($form->prepareAttachmentsFromDb($fileMapper->getListByTest($otherTest)));
      }
    }
    
    return $form;
  }
  
  private function _getAddTestCaseForm()
  {
    $form = new Project_Form_AddTestCase(array(
      'action'    => $this->_projectUrl(array(), 'test_add_test_case_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
    
    $testCase = $this->_getValidTestCase(false);
    
    if ($testCase !== null)
    {
      $testMapper = new Project_Model_TestMapper();    
      $rowData = $testMapper->getTestCaseForEdit($testCase);

      if ($rowData !== false)
      {
        $fileMapper = new Project_Model_FileMapper();
        $form->populate($rowData);
        $form->populate($form->prepareAttachmentsFromDb($fileMapper->getListByTest($testCase)));
      }
    }
    
    return $form;
  }
  
  private function _getAddExploratoryTestForm()
  {
    $form = new Project_Form_AddExploratoryTest(array(
      'action'    => $this->_projectUrl(array(), 'test_add_exploratory_test_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
    
    $exploratoryTest = $this->_getValidExploratoryTest(false);
    
    if ($exploratoryTest !== null)
    {
      $testMapper = new Project_Model_TestMapper();    
      $rowData = $testMapper->getExploratoryTestForEdit($exploratoryTest);

      if ($rowData !== false)
      {
        $fileMapper = new Project_Model_FileMapper();
        $form->populate($rowData);
        $form->populate($form->prepareAttachmentsFromDb($fileMapper->getListByTest($exploratoryTest)));
      }
    }
    
    return $form;
  }

  private function _getAddAutomaticTestForm()
  {
    $form = new Project_Form_AddAutomaticTest(array(
      'action'    => $this->_projectUrl(array(), 'test_add_automatic_test_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
    
    $automaticTest = $this->_getValidAutomaticTest(false);
    
    if ($automaticTest !== null)
    {
      $testMapper = new Project_Model_TestMapper();    
      $rowData = $testMapper->getAutomaticTestForEdit($automaticTest);

      if ($rowData !== false)
      {
        $fileMapper = new Project_Model_FileMapper();
        $form->populate($rowData);
        $form->populate($form->prepareAttachmentsFromDb($fileMapper->getListByTest($automaticTest)));
      }
    }
    
    return $form;
  }

  private function _getAddChecklistForm()
  {
    $form = new Project_Form_AddChecklist(array(
      'action'    => $this->_projectUrl(array(), 'test_add_checklist_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
    
    $checklist = $this->_getValidChecklist(false);
    
    if ($checklist !== null)
    {
      $testMapper = new Project_Model_TestMapper();    
      $rowData = $testMapper->getChecklistForEdit($checklist);

      if ($rowData !== false)
      {    
        $checklistItemMapper = new Project_Model_ChecklistItemMapper();
        $fileMapper = new Project_Model_FileMapper();
        $items = array();

        foreach ($checklistItemMapper->getAllByTest($checklist) as $item)
        {
          $items[] = new Application_Model_ChecklistItem(array('name' => $item->getName()));
        }
        
        $form->populate($rowData);
        $form->populate($form->prepareItemsFromDb($items));
        $form->populate($form->prepareAttachmentsFromDb($fileMapper->getListByTest($checklist)));
      }
    }
    
    return $form;
  }

  private function _getEditOtherTestForm(Application_Model_Test $testOther)
  {
    $options = array(
      'action'    => $this->_projectUrl(array('id' => $testOther->getId()), 'test_edit_other_test_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'familyId'  => $testOther->getFamilyId()
    );
    
    $form = new Project_Form_EditOtherTest($options);
    $form->setAttrib('id', 'test_other_test_edit');
    return $form->populate($testOther->getExtraData('rowData'));
  }
  
  private function _getEditTestCaseForm(Application_Model_TestCase $testCase)
  {
    $options = array(
      'action'    => $this->_projectUrl(array('id' => $testCase->getId()), 'test_edit_test_case_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'familyId'  => $testCase->getFamilyId()
    );
    
    $form = new Project_Form_EditTestCase($options);
    $form->setAttrib('id', 'test_test_case_edit');
    return $form->populate($testCase->getExtraData('rowData'));
  }
  
  private function _getEditExploratoryTestForm(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $options = array(
      'action'    => $this->_projectUrl(array('id' => $exploratoryTest->getId()), 'test_edit_exploratory_test_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'familyId'  => $exploratoryTest->getFamilyId()
    );

    $form = new Project_Form_EditExploratoryTest($options);
    $form->setAttrib('id', 'test_exploration_edit');
    return $form->populate($exploratoryTest->getExtraData('rowData'));
  }

  private function _getEditAutomaticTestForm(Application_Model_Test $testAutomatic)
  {
    $options = array(
      'action'    => $this->_projectUrl(array('id' => $testAutomatic->getId()), 'test_edit_automatic_test_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'familyId'  => $testAutomatic->getFamilyId()
    );
    
    $form = new Project_Form_EditAutomaticTest($options);
    $form->setAttrib('id', 'test_automatic_test_edit');
    return $form->populate($testAutomatic->getExtraData('rowData'));
  }

  private function _getEditChecklistForm(Application_Model_Test $checklist)
  {
    $options = array(
      'action'    => $this->_projectUrl(array('id' => $checklist->getId()), 'test_edit_checklist_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'familyId'  => $checklist->getFamilyId()
    );
    
    $form = new Project_Form_EditChecklist($options);
    $form->setAttrib('id', 'test_checklist_edit');
    return $form->populate($checklist->getExtraData('rowData'));
  }
  
  private function _getValidTest($throw = true)
  {
    $test = null;
    $idValidator = new Application_Model_Validator_Id();
    
    if ($idValidator->isValid($this->_getAllParams()))
    {
      $test = new Application_Model_Test($idValidator->getFilteredValues());
      $test->setProjectObject($this->_project);
    }
    else if ($throw)
    {
      throw new Custom_404Exception();
    }
    
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
    
    $this->_checkDeletePermissions($test);
    
    return $test;
  }
  
  private function _getValidOtherTestForEdit()
  {
    $otherTest = $this->_getValidTest();
    $testMapper = new Project_Model_TestMapper();    
    $rowData = $testMapper->getOtherTestForEdit($otherTest);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($otherTest);
    
    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getListByTest($otherTest);
    return $otherTest->setExtraData('rowData', $rowData);
  }
  
  private function _getValidTestCase($throw = true)
  {
    $testCase = null;
    $idValidator = new Application_Model_Validator_Id();
    
    if ($idValidator->isValid($this->_getAllParams()))
    {
      $testCase = new Application_Model_TestCase($idValidator->getFilteredValues());
      $testCase->setProjectObject($this->_project);
    }
    else if ($throw)
    {
      throw new Custom_404Exception();
    }
    
    return $testCase;
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
    $rowData['attachments'] = $fileMapper->getListByTest($testCase);
    return $testCase->setExtraData('rowData', $rowData);
  }
  
  private function _getValidExploratoryTest($throw = true)
  {
    $exploratoryTest = null;
    $idValidator = new Application_Model_Validator_Id();
    
    if ($idValidator->isValid($this->_getAllParams()))
    {
      $exploratoryTest = new Application_Model_ExploratoryTest($idValidator->getFilteredValues());
      $exploratoryTest->setProjectObject($this->_project);
    }
    else if ($throw)
    {
      throw new Custom_404Exception();
    }
    
    return $exploratoryTest;
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
    $rowData['attachments'] = $fileMapper->getListByTest($exploratoryTest);    
    return $exploratoryTest->setExtraData('rowData', $rowData);
  }
  
  private function _getValidAutomaticTest($throw = true)
  {
    $automaticTest = null;
    $idValidator = new Application_Model_Validator_Id();
    
    if ($idValidator->isValid($this->_getAllParams()))
    {
      $automaticTest = new Application_Model_AutomaticTest($idValidator->getFilteredValues());
      $automaticTest->setProjectObject($this->_project);
    }
    else if ($throw)
    {
      throw new Custom_404Exception();
    }
    
    return $automaticTest;
  }
  
  private function _getValidAutomaticTestForEdit()
  {
    $automaticTest = $this->_getValidAutomaticTest();
    $testMapper = new Project_Model_TestMapper();
    
    $rowData = $testMapper->getAutomaticTestForEdit($automaticTest);
    
    if (false === $rowData)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($automaticTest);
    
    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getListByTest($automaticTest);
    return $automaticTest->setExtraData('rowData', $rowData);
  }
  
  private function _getValidChecklist($throw = true)
  {
    $checklist = null;
    $idValidator = new Application_Model_Validator_Id();
    
    if ($idValidator->isValid($this->_getAllParams()))
    {
      $checklist = new Application_Model_Checklist($idValidator->getFilteredValues());
      $checklist->setProjectObject($this->_project);
    }
    else if ($throw)
    {
      throw new Custom_404Exception();
    }
    
    return $checklist;
  }
  
  private function _getValidChecklistForEdit()
  {
    $checklist = $this->_getValidChecklist();
    $testMapper = new Project_Model_TestMapper();
    
    $rowData = $testMapper->getChecklistForEdit($checklist);
    
    if (false === $rowData)
    {
      throw new Custom_404Exception();
    }
    
    $this->_checkEditPermissions($checklist);
    
    $checklistItemMapper = new Project_Model_ChecklistItemMapper();
    $rowData['items'] = $checklistItemMapper->getAllByTest($checklist);
    $checklist->setItems($rowData['items']);

    $fileMapper = new Project_Model_FileMapper();
    $rowData['attachments'] = $fileMapper->getListByTest($checklist);
    return $checklist->setExtraData('rowData', $rowData);
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
  
  private function _checkDeletePermissions(Application_Model_Test $test)
  {
    $roleActionsForEdit = array(
      Application_Model_RoleAction::TEST_DELETE_CREATED_BY_YOU,
      Application_Model_RoleAction::TEST_DELETE_ALL
    );
    
    $testUserPermission = new Application_Model_TestUserPermission($test, $this->_user, $this->_checkMultipleAccess($roleActionsForEdit));
    
    if (false === $testUserPermission->isDeletePermission())
    {
      $this->_throwTaskAccessDeniedException();
    }
  }
  
  private function _checkDeletePermissions4MultipleTests(array $tests)
  {
    $roleActionsForAssign = array(
      Application_Model_RoleAction::TEST_DELETE_ALL,
      Application_Model_RoleAction::TEST_DELETE_CREATED_BY_YOU
    );
    
    foreach ($tests as $test)
    {
      $testUserPermission = new Application_Model_TestUserPermission($test, $this->_user, $this->_checkMultipleAccess($roleActionsForAssign));
    
      if (false === $testUserPermission->isDeletePermission())
      {
        $this->_throwTestAccessDeniedException();
      }
    }
  }
  
  private function _getAccessPermissionsForTests()
  {
    return $this->_checkMultipleAccess(Application_Model_TestUserPermission::$_testRoleActions);
  }
}