<?php
/*
Copyright © 2014 TaskTestArena 

This file is part of TaskTestArena.

TaskTestArena is free software; you can redistribute it and/or modify
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
class Project_Model_TaskChecklistItemMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TaskChecklistItemDbTable';
  
  public function addByTaskTest(Application_Model_TaskTest $taskTest)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $values = implode(',', array_fill(0, count($taskTest->getTest()->getItems()), '(?, ?, ?)'));
    $data = array();
    
    foreach ($taskTest->getTest()->getItems() as $item)
    {
      $data[] = $taskTest->getId();
      $data[] = $item->getId();
      $data[] = Application_Model_TaskChecklistItemStatus::NONE;
    }
    
    $statement = $adapter->prepare('INSERT INTO '.$db->getName().'(task_test_id, checklist_item_id, status) VALUES '.$values);
    $statement->execute($data);
  }
  
  public function addNewItems(array $taskTestIds, array $checklistItemIds)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $values = implode(',', array_fill(0, count($taskTestIds) * count($checklistItemIds), '(?, ?, ?)'));
    $data = array();
    
    foreach ($taskTestIds as $taskTestId)
    {
      foreach ($checklistItemIds as $checklistItemId)
      {
        $data[] = $taskTestId;
        $data[] = $checklistItemId;
        $data[] = Application_Model_TaskChecklistItemStatus::NONE;
      }
    }
    //echo 'a'.count($taskTestIds);echo 'b'.count($checklistItemIds);print_r($data);print_r($data);echo $values;die;
    $statement = $adapter->prepare('INSERT INTO '.$db->getName().'(task_test_id, checklist_item_id, status) VALUES '.$values);
    $statement->execute($data);
  }
  
  public function deleteByChecklistItemIds(array $checklistItemIds)
  {
    $this->_getDbTable()->delete(array('checklist_item_id IN (?)' => $checklistItemIds));
  }
  
  public function deleteByTaskTest(Application_Model_TaskTest $taskTest)
  {
    $this->_getDbTable()->delete(array('task_test_id = ?' => $taskTest->getId()));
  }
  
  public function getAllByTaskTest(Application_Model_TaskTest $taskTest)
  {
    $rows = $this->_getDbTable()->getAllByTaskTest($taskTest->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $taskChecklistItem = new Application_Model_TaskChecklistItem($row);
      $taskChecklistItem->setTaskTestObject($taskTest);
      $taskTest->addChecklistItem($taskChecklistItem);
      $list[] = $taskChecklistItem;
    }
    
    return $list;
  }
  
  public function getForView(Application_Model_TaskChecklistItem $taskChecklistItem)
  {
    $row = $this->_getDbTable()->getForView($taskChecklistItem->getId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $taskChecklistItem->setDbProperties($row->toArray());
  }
  
  public function changeStatusToNone(Application_Model_TaskChecklistItem $taskChecklistItem)
  {
    return $this->changeStatus($taskChecklistItem, Application_Model_TaskChecklistItemStatus::NONE);
  }
  
  public function changeStatusToResolve(Application_Model_TaskChecklistItem $taskChecklistItem)
  {
    return $this->changeStatus($taskChecklistItem, Application_Model_TaskChecklistItemStatus::RESOLVE);
  }
  
  public function changeStatusTounresolve(Application_Model_TaskChecklistItem $taskChecklistItem)
  {
    return $this->changeStatus($taskChecklistItem, Application_Model_TaskChecklistItemStatus::UNRESOLVE);
  }
  
  public function changeStatus(Application_Model_TaskChecklistItem $taskChecklistItem, $status)
  {
    if ($taskChecklistItem->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => $status
    );
    
    $where = array(
      'status != ?' => $status,
      'id = ?'      => $taskChecklistItem->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function saveGroup(array $taskTestChecklistItems)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array();
    $values = implode(',', array_fill(0, count($taskTestChecklistItems), '(?, ?, ?)'));
    
    foreach ($taskTestChecklistItems as $taskTestChecklistItem)
    {
      $data[] = $taskTestChecklistItem->getTaskTest()->getId();
      $data[] = $taskTestChecklistItem->getChecklistItem()->getId();
      $data[] = Application_Model_TaskChecklistItemStatus::NONE;
    }
    
    $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (task_test_id, checklist_item_id, status) VALUES '.$values);
    return $statement->execute($data);
  }

  public function deleteByTaskTestIds(array $taskTestIds)
  {
    $this->_getDbTable()->delete(array(
      'task_test_id IN(?)' => $taskTestIds
    ));
  }
}