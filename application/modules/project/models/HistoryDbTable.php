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
class Project_Model_HistoryDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'history';
  
  public function getByTask($taskId, $bugTracker)
  {
    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => new Zend_Db_Expr(0),
        'data6' => new Zend_Db_Expr(0),
      ))
      ->join(array('a' => 'user'), 'a.id = h.field1', array(
        'data1' => new Zend_Db_Expr('CONCAT(a.firstname, " ", a.lastname)'),
        'data2' => 'email',
        'data3' => new Zend_Db_Expr(0),
        'data4' => new Zend_Db_Expr(0)
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::CREATE_TASK,
        Application_Model_HistoryType::ASSIGN_TASK,
        Application_Model_HistoryType::CHANGE_TASK,
        Application_Model_HistoryType::CHANGE_AND_ASSIGN_TASK
      ))
      ->setIntegrityCheck(false);

    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data1' => new Zend_Db_Expr(0),
        'data2' => new Zend_Db_Expr(0),
        'data3' => new Zend_Db_Expr(0),
        'data4' => new Zend_Db_Expr(0),
        'data5' => new Zend_Db_Expr(0),
        'data6' => new Zend_Db_Expr(0),
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type = ?', Application_Model_HistoryType::CHANGE_TASK_STATUS)
      ->setIntegrityCheck(false);


    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => new Zend_Db_Expr(0),
        'data6' => new Zend_Db_Expr(0),
      ))
      ->join(array('tt' => 'task_test'), 'tt.id = h.field1', array())
      ->join(array('t' => 'test'), 't.id = tt.test_id', array(
        'data1' => 'type',
        'data2' => 'name',
        'data3' => 'status',
        'data4' => new Zend_Db_Expr(0)
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::ADD_TASK_TEST_TO_TASK,
      ))
      ->setIntegrityCheck(false);

    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => new Zend_Db_Expr(0),
        'data6' => new Zend_Db_Expr(0),
      ))
      ->join(array('t' => 'test'), 't.id = h.field1', array(
        'data1' => 'type',
        'data2' => 'name',
        'data3' => 'status',
        'data4' => new Zend_Db_Expr(0)
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::ADD_TEST_TO_TASK,
        Application_Model_HistoryType::DELETE_TEST_FROM_TASK
      ))
      ->setIntegrityCheck(false);

    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => new Zend_Db_Expr(0),
        'data6' => new Zend_Db_Expr(0),
      ))
      ->join(array('t' => 'test'), 't.id = h.field1', array(
        'data1' => 'type',
        'data2' => 'name',
        'data3' => 'status'
      ))
      ->join(array('r' => 'resolution'), 'r.id = h.field2', array(
        'data4' => 'name'
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::RESOLVE_TEST,
        Application_Model_HistoryType::CHANGE_TEST_STATUS
      ))
      ->setIntegrityCheck(false);

    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => new Zend_Db_Expr(0),
        'data6' => new Zend_Db_Expr(0),
      ))
      ->join(array('tt' => 'task_test'), 'tt.id = h.field1', array())
      ->join(array('t' => 'test'), 't.id = tt.test_id', array(
        'data1' => 'type',
        'data2' => 'name',
        'data3' => 'status'
      ))
      ->join(array('r' => 'resolution'), 'r.id = h.field2', array(
        'data4' => 'name'
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::RESOLVE_TASK_TEST,
        Application_Model_HistoryType::CHANGE_TASK_TEST_STATUS
      ))
      ->setIntegrityCheck(false);

    $dynamicSql = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => 't.name',
        'data6' => 't.status',
      ));
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::INTERNAL) {
      $dynamicSql->join(array('d' => 'defect'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::INTERNAL),
        'data2' => 'title',
        'data3' => 'project_id',
      ));
    }
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::JIRA) {
      $dynamicSql->join(array('d' => 'defect_jira'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::JIRA),
        'data2' => 'summary',
        'data3' => 'no',
      ));
    }
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::MANTIS) {
      $dynamicSql->join(array('d' => 'defect_mantis'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::MANTIS),
        'data2' => 'summary',
        'data3' => 'no',
      ));
    }
    $dynamicSql
      ->join(array('tt' => 'task_test'), 'tt.id = h.field2', array())
      ->join(array('t' => 'test'), 't.id = tt.test_id', array(
        'data4' => 't.type',
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::ADD_DEFECT_TO_TASK_TEST,
        Application_Model_HistoryType::DELETE_DEFECT_FROM_TASK_TEST
      ))
      ->setIntegrityCheck(false);

    $sql[] = $dynamicSql;

    $dynamicSql = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => 't.name',
        'data6' => 't.status',
      ));
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::INTERNAL) {
      $dynamicSql->join(array('d' => 'defect'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::INTERNAL),
        'data2' => 'title',
        'data3' => 'project_id',
      ));
    }
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::JIRA) {
      $dynamicSql->join(array('d' => 'defect_jira'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::JIRA),
        'data2' => 'summary',
        'data3' => 'no',
      ));
    }
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::MANTIS) {
      $dynamicSql->join(array('d' => 'defect_mantis'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::MANTIS),
        'data2' => 'summary',
        'data3' => 'no',
      ));
    }
    $dynamicSql
      ->join(array('t' => 'test'), 't.id = h.field2', array(
        'data4' => 't.type',
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::ADD_DEFECT_TO_TEST,
        Application_Model_HistoryType::DELETE_DEFECT_FROM_TEST
      ))
      ->setIntegrityCheck(false);

    $sql[] = $dynamicSql;

    $dynamicSql = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data5' => new Zend_Db_Expr(0),
        'data6' => new Zend_Db_Expr(0),
      ));
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::INTERNAL) {
      $dynamicSql->join(array('d' => 'defect'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::INTERNAL),
        'data2' => 'title',
        'data3' => 'project_id',
        'data4' => new Zend_Db_Expr(0),
      ));
    }
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::JIRA) {
      $dynamicSql->join(array('d' => 'defect_jira'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::JIRA),
        'data2' => 'summary',
        'data3' => 'no',
        'data4' => new Zend_Db_Expr(0)
      ));
    }
    if ($bugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::MANTIS) {
      $dynamicSql->join(array('d' => 'defect_mantis'), 'd.id = h.field1', array(
        'data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::MANTIS),
        'data2' => 'summary',
        'data3' => 'no',
        'data4' => new Zend_Db_Expr(0)
      ));
    }
    $dynamicSql->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $taskId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::TASK)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::ADD_DEFECT_TO_TASK,
        Application_Model_HistoryType::DELETE_DEFECT_FROM_TASK
      ))
      ->setIntegrityCheck(false);

    $sql[] = $dynamicSql;
    
    $sql = $this->union($sql)
      ->group('h.id')
      ->order('date DESC');

    return $this->fetchAll($sql);
  }
  
  public function getByDefect($defectId)
  {
    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data4' => new Zend_Db_Expr(0),
        'data5' => new Zend_Db_Expr(0)
      ))
      ->join(array('a' => 'user'), 'a.id = h.field1', array(
        'data1' => new Zend_Db_Expr('CONCAT(a.firstname, " ", a.lastname)'),
        'data2' => 'email',
        'data3' => new Zend_Db_Expr(0)
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $defectId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::DEFECT)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::CREATE_DEFECT,
        Application_Model_HistoryType::ASSIGN_DEFECT,
        Application_Model_HistoryType::CHANGE_DEFECT,
        Application_Model_HistoryType::CHANGE_AND_ASSIGN_DEFECT,
      ))
      ->setIntegrityCheck(false);

    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data4' => new Zend_Db_Expr(0),
        'data5' => new Zend_Db_Expr(0)
      ))
      ->join(array('t' => 'test'), 't.id = h.field1', array(
        'data1' => 'type',
        'data2' => 'name',
        'data3' => 'status',
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $defectId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::DEFECT)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::ADD_TEST_TO_DEFECT,
        Application_Model_HistoryType::DELETE_TEST_FROM_DEFECT,
      ))
      ->setIntegrityCheck(false);

    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data4' => new Zend_Db_Expr(0),
        'data5' => new Zend_Db_Expr(0)
      ))
      ->join(array('t' => 'task'), 't.id = h.field1', array(
        'data1' => 'title',
        'data2' => new Zend_Db_Expr(0),
        'data3' => new Zend_Db_Expr(0),
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $defectId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::DEFECT)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::DELETE_TASK_FROM_DEFECT,
        Application_Model_HistoryType::ADD_TASK_TO_DEFECT,
      ))
      ->setIntegrityCheck(false);
    
    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'data4' => new Zend_Db_Expr(0),
        'data5' => new Zend_Db_Expr(0),
      ))
      ->joinLeft(array('a' => 'user'), 'a.id = h.field2', array(
        'data1' => new Zend_Db_Expr('CONCAT(a.firstname, " ", a.lastname)'),
        'data2' => 'email',
        'data3' => new Zend_Db_Expr(0)
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $defectId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::DEFECT)
      ->where('h.type = ?', Application_Model_HistoryType::CHANGE_DEFECT_STATUS)
      ->setIntegrityCheck(false);

    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
      ))
      ->join(array('ta' => 'task'), 'ta.id = h.field1', array(
        'data4' => 'title',
        'data5' => 'status',
      ))
      ->join(array('tt' => 'task_test'), 'tt.id = h.field2', array())
      ->join(array('t' => 'test'), 'tt.test_id = t.id', array(
        'data1' => 'type',
        'data2' => 'name',
        'data3' => 'status',
      ))
      ->join(array('u' => 'user'), 'u.id = h.user_id', $this->_createAlias('user', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('h.subject_id = ?', $defectId)
      ->where('h.subject_type = ?', Application_Model_HistorySubjectType::DEFECT)
      ->where('h.type IN (?)', array(
        Application_Model_HistoryType::DELETE_TASK_TEST_FROM_DEFECT,
        Application_Model_HistoryType::ADD_TASK_TEST_TO_DEFECT,
      ))
      ->setIntegrityCheck(false);

    $sql = $this->union($sql)
      ->group('h.id')
      ->order('date DESC')
      ->order('id DESC')
    ;
    return $this->fetchAll($sql);
  }
}