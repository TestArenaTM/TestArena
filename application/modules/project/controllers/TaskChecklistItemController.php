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
class Project_TaskChecklistItemController extends Custom_Controller_Action_Application_Project_Abstract
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
  
  public function changeStatusToNoneAction()
  {
    $this->_chnageStatus(Application_Model_TaskChecklistItemStatus::NONE);
  }
  
  public function changeStatusToResolveAction()
  {
    $this->_chnageStatus(Application_Model_TaskChecklistItemStatus::RESOLVE);
  }
  
  public function changeStatusToUnresolveAction()
  {
    $this->_chnageStatus(Application_Model_TaskChecklistItemStatus::UNRESOLVE);
  }
  
  private function _chnageStatus($statusId)
  {
    $taskChecklistItem = $this->_getValidTaskChecklistItemForChange();
    
    if ($taskChecklistItem->getStatusId() == $statusId)
    {
      throw new Custom_404Exception();
    }
    
    $taskChecklistItemMapper = new Project_Model_TaskChecklistItemMapper();
    $t = new Custom_Translate();
    
    if ($taskChecklistItemMapper->changeStatus($taskChecklistItem, $statusId))
    {    
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect($this->_getBackUrl('task_checklist_view', $this->_projectUrl(array(), 'task_list')));
  }
  
  private function _getValidTaskChecklistItemForChange()
  {    
    $taskChecklistItem = $this->_getValidTaskChecklistItem();
    $taskChecklistItemMapper = new Project_Model_TaskChecklistItemMapper();
    $taskChecklistItemMapper->getForView($taskChecklistItem);
    $this->_checkTask($taskChecklistItem->getTaskTest()->getTask());
    $this->_checkTestModifyPermissions($taskChecklistItem->getTaskTest()->getTask()); 
    return $taskChecklistItem;
  }
  
  private function _getValidTaskChecklistItem()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    return new Application_Model_TaskChecklistItem($idValidator->getFilteredValues());
  }
  
  private function _checkTask($task)
  {
    if ($task === false 
        || $task->getProject()->getId() != $this->_project->getId()
        || $task->getStatusId() == Application_Model_TaskStatus::CLOSED)
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
}