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
class Project_Model_ChecklistItemMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_ChecklistItemDbTable';

  public function save(Application_Model_Checklist $checklist)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $taskChecklistItemMapper = new Project_Model_TaskChecklistItemMapper();
    $taskTestMapper = new Project_Model_TaskTestMapper();
    
    $itemIds = array_values($checklist->getExtraData('itemIds'));
    $itemNames = array_values($checklist->getExtraData('itemNames'));
    $newItemIds = array();
    $deletedItemIds = array();
  
    foreach ($itemIds as $i => $itemId)
    {
      if ($itemId == 0)
      {
        // Dodawanie
        $db->insert(array(
          'test_id' => $checklist->getId(),
          'name'    => $itemNames[$i]
        ));
        $newItemIds[] = $adapter->lastInsertId();
      }
      else
      {
        // Zmiana
        $db->update(array('name' => $itemNames[$i]), array('id = ?' => $itemId));
      }
    }

    // Dodawanie
    if (count($newItemIds) > 0)
    {
      $taskTestIds = $taskTestMapper->getIdsByTest($checklist);
      
      if (count($taskTestIds) > 0)
      {
        $taskChecklistItemMapper->addNewItems($taskTestIds, $newItemIds);
      }
    }

    // Usuwanie 
    foreach ($checklist->getItems() as $item)
    {
      if (!in_array($item->getId(), $itemIds))
      {
        $deletedItemIds[] = $item->getId();
      }
    }

    if (count($deletedItemIds) > 0)
    {
      $taskChecklistItemMapper->deleteByChecklistItemIds($deletedItemIds);
      $db->delete(array('id IN(?)' => $deletedItemIds));      
    }
  }
  
  public function getAllByTest(Application_Model_Test $test)
  {
    $rows = $this->_getDbTable()->getAllByTest($test->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_ChecklistItem($row);
    }
    
    return $list;
  }
}