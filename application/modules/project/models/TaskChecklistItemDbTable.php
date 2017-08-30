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
class Project_Model_TaskChecklistItemDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'task_checklist_item';
  
  public function getName()
  {
    return $this->_name;
  }
  
  public function getAllByTaskTest($taskTestId)
  {
    $sql = $this->select()
      ->from(array('tci' => $this->_name), array(
        'id',
        'status'
      ))
      ->join(array('ci' => 'checklist_item'), 'ci.id = tci.checklist_item_id', $this->_createAlias('checklistItem', array(
        'id',
        'name'
      )))
      ->where('tci.task_test_id = ?', $taskTestId)
      ->order('tci.checklist_item_id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getForView($id)
  {
    $sql = $this->select()
      ->from(array('tci' => $this->_name), array(
        'id',
        'status'
      ))
      ->join(array('tt' => 'task_test'), 'tt.id = tci.task_test_id', $this->_createAlias('taskTest', array(
        'id'
      )))
      ->join('task', 'task.id = tt.task_id', $this->_createAlias('taskTest'.self::TABLE_CONNECTOR.'task', array(
        'id',
        'status',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id',
        'project'.self::TABLE_CONNECTOR.'id' => 'project_id'
      )))
      ->where('tci.id = ?', $id)
      ->group('tci.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
}