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
class Project_Model_TaskVersionMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TaskVersionDbTable';

  public function save(Application_Model_Task $task)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array();
    $values = implode(',', array_fill(0, count($task->getExtraData('versions')), '(?, ?)'));
    
    foreach ($task->getExtraData('versions') as $versionId)
    {
      $data[] = $task->getId();
      $data[] = $versionId;
    }
    
    $db->delete(array('task_id = ?' => $task->getId()));
    $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (task_id, version_id) VALUES '.$values);
    return $statement->execute($data);
  }
  
  public function saveGroup(array $taskVersions)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array();
    $values = implode(',', array_fill(0, count($taskVersions), '(?, ?)'));
    
    foreach ($taskVersions as $taskVersion)
    {
      $data[] = $taskVersion->getTaskId();
      $data[] = $taskVersion->getVersionId();
    }
    
    $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (task_id, version_id) VALUES '.$values);
    return $statement->execute($data);
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