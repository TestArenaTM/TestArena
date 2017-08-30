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
class Zend_View_Helper_PrepareTaskActions extends Zend_View_Helper_Abstract
{
  public function prepareTaskActions(Application_Model_Task $task, array $userPermissions = array(), Application_Model_TaskUserPermission $taskUserPermission = null)
  {
    if (null === $taskUserPermission)
    {
      $taskUserPermission = new Application_Model_TaskUserPermission($task, $this->view->authUser, $userPermissions);
    }
    
    $actions = array();
    
    if (in_array($task->getStatusId(), array(Application_Model_TaskStatus::OPEN,
                                             Application_Model_TaskStatus::REOPEN,
                                             Application_Model_TaskStatus::IN_PROGRESS)))
    {
      if (in_array($task->getStatusId(), array(Application_Model_TaskStatus::OPEN,
                                               Application_Model_TaskStatus::REOPEN))
          && $taskUserPermission->isChangeStatusPermission())
      {
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $task->getId()), 'task_start'), 'text' => 'Rozpocznij', 'class' => ''); 
      }
      
      if ($taskUserPermission->isChangeStatusPermission())
      {
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $task->getId()), 'task_close'), 'text' => 'Zamknij', 'class' => '');
      }
      
      if ($taskUserPermission->isAssignPermission())
      {
        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $task->getId()), 'task_assign'), 'text' => 'Przypisz', 'class' => '');
        
        if ($task->getAssigneeId() != $this->view->authUser->getId())
        {
          $actions[] = array('url' => $this->view->projectUrl(array('id' => $task->getId()), 'task_assign_to_me'), 'text' => 'Przypisz do mnie', 'class' => '');
        }
      }
    }
    elseif ($task->getStatusId() == Application_Model_TaskStatus::CLOSED
            && $taskUserPermission->isChangeStatusPermission())
    {
      $actions[] = array('url' => $this->view->projectUrl(array('id' => $task->getId()), 'task_reopen'), 'text' => 'Otwórz ponownie', 'class' => '');
    }
    
    $isEditAction = false;
    
    if ($task->getStatusId() != Application_Model_TaskStatus::CLOSED
        && $taskUserPermission->isEditPermission())
    {
      $isEditAction = true;
      $actions[] = null;
      $actions[] = array('url' => $this->view->projectUrl(array('id' => $task->getId()), 'task_edit'), 'text' => 'Edytuj', 'class' => '');
    }
    
    if ($taskUserPermission->isDeletePermission())
    {
      if (!$isEditAction)
      {
        $actions[] =  null;
      }
      
      $actions[] = array('url' => $this->view->projectUrl(array('id' => $task->getId()), 'task_delete'), 'text' => 'Usuń', 'class' => 'j_delete_task');
    }
    
    return $actions;
  }
}
