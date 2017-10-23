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
class Project_Model_HistoryMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_HistoryDbTable';

  public function add(Application_Model_History $history, $date = null)
  {
    $data = array(
      'user_id'       => $history->getUser()->getId(),
      'date'          => $date === null ? date('Y-m-d H:i:s') : $date,
      'subject_id'    => $history->getSubjectId(),
      'subject_type'  => $history->getSubjectTypeId(),
      'type'          => $history->getTypeId(),
      'field1'        => $history->getField1(),
      'field2'        => $history->getField2()
    );

    try
    {
      $this->_getDbTable()->insert($data);
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }

    return true;
  }

  public function getByTask(Application_Model_Task $task)
  {
    if ($task->getId() === null)
    {
      return false;
    }

    $rows = $this->_getDbTable()->getByTask($task->getId());

    if ($rows === null)
    {
      return false;
    }

    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_History($row);
    }

    return $list;
  } 

  public function getByDefect(Application_Model_Defect $defect)
  {
    if ($defect->getId() === null)
    {
      return false;
    }

    $rows = $this->_getDbTable()->getByDefect($defect->getId());

    if ($rows === null)
    {
      return false;
    }

    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_History($row);
    }

    return $list;
  }

  public function deleteByTask(Application_Model_Task $task)
  {
    $this->_getDbTable()->delete(array(
      'subject_id = ?' => $task->getId(),
      'subject_type = ?' => Application_Model_HistorySubjectType::TASK
    ));
  }

  public function deleteByTaskIds(array $taskIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $taskIds,
      'subject_type = ?' => Application_Model_HistorySubjectType::TASK
    ));
  }

  public function deleteByTaskTestIds(array $taskTestIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $taskTestIds,
      'subject_type = ?' => Application_Model_HistorySubjectType::TASK_TEST
    ));
  }

  public function deleteByDefect(Application_Model_Defect $defect)
  {
    $this->_getDbTable()->delete(array(
      'subject_id = ?' => $defect->getId(),
      'subject_type = ?' => Application_Model_HistorySubjectType::DEFECT
    ));
  }

  public function deleteByDefectIds(array $defectIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $defectIds,
      'subject_type = ?' => Application_Model_HistorySubjectType::DEFECT
    ));
  }
}