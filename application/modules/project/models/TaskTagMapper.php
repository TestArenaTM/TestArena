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
class Project_Model_TaskTagMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TaskTagDbTable';

  public function save(Application_Model_Task $task)
  {
    $valueCount = count($task->getExtraData('tags'));
    
    if (count($valueCount) > 0)
    {
        $db = $this->_getDbTable();
        $adapter = $db->getAdapter();
        $data = array();
        $values = implode(',', array_fill(0, $valueCount, '(?, ?)'));

        foreach ($task->getExtraData('tags') as $tagId)
        {
          $data[] = $task->getId();
          $data[] = $tagId;
        }

        $db->delete(array('task_id = ?' => $task->getId()));

        if ($valueCount > 0)
        {
          $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (task_id, tag_id) VALUES '.$values);
          $statement->execute($data);
        }
    }
  }
  
  public function saveGroup(array $taskTags)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array();
    $values = implode(',', array_fill(0, count($taskTags), '(?, ?)'));
    
    foreach ($taskTags as $taskTag)
    {
      $data[] = $taskTag->getTaskId();
      $data[] = $taskTag->getTagId();
    }
    
    $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (task_id, tag_id) VALUES '.$values);
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