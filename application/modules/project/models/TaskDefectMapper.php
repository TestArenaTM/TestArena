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
class Project_Model_TaskDefectMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TaskDefectDbTable';
  
  public function add(Application_Model_TaskDefect $taskDefect)
  {
    $db = $this->_getDbTable(); 
    
    $data = array(
      'task_id'         => $taskDefect->getTask()->getId(),
      'defect_id'       => $taskDefect->getDefectId(),
      'bug_tracker_id'  => $taskDefect->getBugTrackerId()
    );
    
    try
    {
      $db->insert($data);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function delete(Application_Model_TaskDefect $taskDefect)
  {
    $where = array(
      'task_id = ?'   => $taskDefect->getTask()->getId(),
      'defect_id = ?' => $taskDefect->getDefect()->getId()
    );

    if ($taskDefect->getBugTrackerId() === null)
    {
      $where['bug_tracker_id IS NULL'] = null;
    }
    else
    {
      $where['bug_tracker_id = ?'] = $taskDefect->getBugTrackerId();
    }
    
    try
    {
      $this->_getDbTable()->delete($where);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }

  public function deleteByTask(Application_Model_Task $task)
  {
    $this->_getDbTable()->delete(array(
      'task_id = ?' => $task->getId()
    ));
  }

  public function deleteByTaskIds(array $taskIds)
  {
    $this->_getDbTable()->delete(array(
      'task_id IN(?)' => $taskIds
    ));
  }
}