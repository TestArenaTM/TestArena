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
class Project_Model_TaskTestDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'task_test';
  
  public function getByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('test', array(
        'id',
        'ordinal_no',
        'type',
        'name'
      )))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('test'.self::TABLE_CONNECTOR.'project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.task_id = ?', $taskId)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join('task', 'task.id = tt.task_id', $this->_createAlias('task', array(
        'id',
        'ordinal_no',
        'title',
        'status',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id'
      )))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('test', array(
        'id',
        'ordinal_no',
        'type',
        'name',
        'description',
        'create_date'
      )))
      ->join(array('a' => 'user'), 'a.id = t.author_id', $this->_createAlias('test'.self::TABLE_CONNECTOR.'author', array(
        'id',
        'firstname',
        'lastname'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('task.project_id = ?', $projectId)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getOtherTestForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join('task', 'task.id = tt.task_id', $this->_createAlias('task', array(
        'id',
        'ordinal_no',
        'title',
        'status',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id'
      )))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('otherTest', array(
        'id',
        'ordinal_no',
        'type',
        'name',
        'description',
        'create_date'
      )))
      ->join(array('a' => 'user'), 'a.id = t.author_id', $this->_createAlias('otherTest'.self::TABLE_CONNECTOR.'author', array(
        'id',
        'firstname',
        'lastname'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('task.project_id = ?', $projectId)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getTestCaseForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join('task', 'task.id = tt.task_id', $this->_createAlias('task', array(
        'id',
        'ordinal_no',
        'title',
        'status',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id'
      )))
      ->join(array('p1' => 'project'), 'p1.id = task.project_id', $this->_createAlias('task'.self::TABLE_CONNECTOR.'project', array(
        'id',
        'prefix'
      )))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('testCase', array(
        'id',
        'ordinal_no',
        'type',
        'name',
        'description',
        'create_date'
      )))
      ->join(array('tt2' => 'test_case'), 'tt2.test_id = t.id', $this->_createAlias('testCase', array(
        'presuppositions',
        'result'
      )))
      ->join(array('a' => 'user'), 'a.id = t.author_id', $this->_createAlias('testCase'.self::TABLE_CONNECTOR.'author', array(
        'id',
        'firstname',
        'lastname'
      )))
      ->join(array('p2' => 'project'), 'p2.id = t.project_id', $this->_createAlias('testCase'.self::TABLE_CONNECTOR.'project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('task.project_id = ?', $projectId)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getExploratoryTestForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join('task', 'task.id = tt.task_id', $this->_createAlias('task', array(
        'id',
        'ordinal_no',
        'title',
        'status',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id'
      )))
      ->join(array('p1' => 'project'), 'p1.id = task.project_id', $this->_createAlias('task'.self::TABLE_CONNECTOR.'project', array(
        'id',
        'prefix'
      )))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('exploratoryTest', array(
        'id',
        'ordinal_no',
        'type',
        'name',
        'description',
        'create_date'
      )))
      ->join(array('et' => 'exploratory_test'), 'et.test_id = t.id', $this->_createAlias('exploratoryTest', array(
        'duration',
        'test_card'
      )))
      ->join(array('a' => 'user'), 'a.id = t.author_id', $this->_createAlias('exploratoryTest'.self::TABLE_CONNECTOR.'author', array(
        'id',
        'firstname',
        'lastname'
      )))
      ->join(array('p2' => 'project'), 'p2.id = t.project_id', $this->_createAlias('exploratoryTest'.self::TABLE_CONNECTOR.'project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('task.project_id = ?', $projectId)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getAutomaticTestForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join('task', 'task.id = tt.task_id', $this->_createAlias('task', array(
        'id',
        'ordinal_no',
        'title',
        'status',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id'
      )))
      ->join(array('p1' => 'project'), 'p1.id = task.project_id', $this->_createAlias('task'.self::TABLE_CONNECTOR.'project', array(
        'id',
        'prefix'
      )))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('automaticTest', array(
        'id',
        'ordinal_no',
        'type',
        'name',
        'description',
        'create_date'
      )))
      ->join(array('a' => 'user'), 'a.id = t.author_id', $this->_createAlias('automaticTest'.self::TABLE_CONNECTOR.'author', array(
        'id',
        'firstname',
        'lastname'
      )))
      ->join(array('p2' => 'project'), 'p2.id = t.project_id', $this->_createAlias('automaticTest'.self::TABLE_CONNECTOR.'project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('task.project_id = ?', $projectId)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getChecklistForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join('task', 'task.id = tt.task_id', $this->_createAlias('task', array(
        'id',
        'ordinal_no',
        'title',
        'status',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id'
      )))
      ->join(array('p1' => 'project'), 'p1.id = task.project_id', $this->_createAlias('task'.self::TABLE_CONNECTOR.'project', array(
        'id',
        'prefix'
      )))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('checklist', array(
        'id',
        'ordinal_no',
        'type',
        'name',
        'description',
        'create_date'
      )))
      ->join(array('a' => 'user'), 'a.id = t.author_id', $this->_createAlias('checklist'.self::TABLE_CONNECTOR.'author', array(
        'id',
        'firstname',
        'lastname'
      )))
      ->join(array('p2' => 'project'), 'p2.id = t.project_id', $this->_createAlias('checklist'.self::TABLE_CONNECTOR.'project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('task.project_id = ?', $projectId)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getName()
  {
    return $this->_name;
  }
  
  public function getByTaskIds(array $taskIds)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->join(array('t' => 'test'), 't.id = tt.test_id', $this->_createAlias('test', array(
        'id',
        'project'.self::TABLE_CONNECTOR.'id' => 'project_id',
        'ordinal_no',
        'type',
        'name'
      )))
      ->join(array('ta' => 'task'), 'ta.id = tt.task_id', $this->_createAlias('task', array(
        'id'
      )))
      ->join(array('p' => 'project'), 'p.id = ta.project_id', $this->_createAlias('test'.self::TABLE_CONNECTOR.'project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('tt.task_id IN(?)', $taskIds)
      ->group('tt.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getIdsByTest($testId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->where('tt.test_id = ?', $testId)
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getIdByTaskTestData($taskId, $testId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->where('tt.task_id = ?', $taskId)
      ->where('tt.test_id = ?', $testId)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getIdsByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->where('tt.task_id = ?', $taskId)
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getIdsByTaskIds($taskIds)
  {
    $sql = $this->select()
      ->from(array('tt' => $this->_name), array(
        'id'
      ))
      ->where('tt.task_id IN(?)', $taskIds)
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}