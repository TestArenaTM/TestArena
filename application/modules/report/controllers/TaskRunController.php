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
class Report_TaskRunController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
    $this->_checkAccess(Application_Model_RoleAction::REPORT_GENERATE, true);
  }
  
  public function userTaskRunListAction()
  {
    $assignee = $this->_getValidAssignee();
    $taskRunMapper = new Report_Model_TaskRunMapper();
    list($list, $paginator) = $taskRunMapper->getAll($this->getRequest());

    $this->_setTranslateTitle(array('assignee' => $assignee->getFullname()));
    $this->view->taskRuns = $list;
    $this->view->paginator = $paginator;
    $this->view->assignee = $assignee;
  }
  
  public function listByPriorityAction()
  {
    $taskRunMapper = new Report_Model_TaskRunMapper();
    $this->_setTranslateTitle();
    $this->view->taskRuns = $taskRunMapper->getByPriority($this->getRequest());
  }
  
  public function exportAllByPriorityAction()
  {
    $taskRunMapper = new Report_Model_TaskRunMapper();
    $rows = $taskRunMapper->getByPriorityForExport($this->getRequest());
    
    $t = new Custom_Translate();
    $fileName = _TEMP_PATH.DIRECTORY_SEPARATOR.Utils_Text::generateToken().'.csv';

    $writer = new Utils_File_Writer_Table_Csv($fileName, array(
      'name'            => $t->translate('Priorytet'),
      'allTasks'        => $t->translate('Wszystkie zadania'),
      'openTasks'       => $t->translate('Zadania niewykonane'),
      'closedTasks'     => $t->translate('Zadania zakończone'),
      'successTasks'    => $t->translate('Zadania zakończone powodzeniem'),
      'failedTasks'     => $t->translate('Zadania zakończone niepowodzeniem'),
      'inProgressTasks' => $t->translate('Zadania w toku'),
      'suspendedTasks'  => $t->translate('Zadania zawieszone'),
      'progress'        => $t->translate('Postęp').'[%]'
    ));
    
    $priorities = new Application_Model_TaskRunPriority();
    $priorities = $priorities->getNames();
    
    foreach ($rows as $row)
    {
      $row['name'] = $t->translate('TASK_RUN_PRIORITY_'.$priorities[$row['priority']], null, 'type');
      $row['progress'] = $this->getHelper('Percent')->run($row['allTasks'], $row['closedTasks']);
      $writer->write($row);
    }   
    
    $writer->close();
    
    $download = new Utils_Download($fileName, $t->translate('fileName', array('project' => $this->_project->getName())).'.csv');
    $download->save();
    exit();
  }
  
  private function _getValidAssignee()
  {
    $idValidator = new Application_Model_Validator_AssigneeId();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $user = new Application_Model_User();
    $user->setId($idValidator->getFilteredValue('assigneeId'));
    $userMapper = new Report_Model_UserMapper();
    $result = $userMapper->getById($user);
    
    if ($result === false)
    {
      throw new Custom_404Exception();
    }
    
    return $user;
  }
}