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
class Application_Model_HistoryDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'history';
  
  public function getByTask($taskId)
  {
    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2'
      ))
      ->join(array('a' => 'user'), 'a.id = h.field1', array(
        'field1Data1' => new Zend_Db_Expr('CONCAT(a.firstname, " ", a.lastname)'),
        'field1Data2' => 'email',
        'field1Data3' => new Zend_Db_Expr(0)
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
        Application_Model_HistoryType::CHANGE_TASK
      ))
      ->setIntegrityCheck(false);
    
    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'field1Data1' => new Zend_Db_Expr(0),
        'field1Data2' => new Zend_Db_Expr(0),
        'field1Data3' => new Zend_Db_Expr(0)
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
        'field2'
      ))
      ->join(array('t' => 'test'), 't.id = h.field1', array(
        'field1Data1' => 'type',
        'field1Data2' => 'name',
        'field1Data3' => 'status'
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
        'field2'
      ))
      ->join(array('d' => 'defect'), 'd.id = h.field1', array(
        'field1Data1' => new Zend_Db_Expr(Application_Model_BugTrackerType::INTERNAL),
        'field1Data2' => 'title',
        'field1Data3' => 'project_id'
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
        Application_Model_HistoryType::ADD_DEFECT_TO_TASK,
        Application_Model_HistoryType::DELETE_DEFECT_FROM_TASK
      ))
      ->setIntegrityCheck(false);
    
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
        'field2'
      ))
      ->join(array('a' => 'user'), 'a.id = h.field1', array(
        'field1Data1' => new Zend_Db_Expr('CONCAT(a.firstname, " ", a.lastname)'),
        'field1Data2' => 'email',
        'field1Data3' => new Zend_Db_Expr(0)
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
        Application_Model_HistoryType::CHANGE_DEFECT
      ))
      ->setIntegrityCheck(false);
    
    $sql[] = $this->select()
      ->from(array('h' => $this->_name), array(
        'id',
        'date',
        'type',
        'field1',
        'field2',
        'field1Data1' => new Zend_Db_Expr(0),
        'field1Data2' => new Zend_Db_Expr(0),
        'field1Data3' => new Zend_Db_Expr(0)
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
    
    $sql = $this->union($sql)
      ->group('h.id')
      ->order('date DESC');

    return $this->fetchAll($sql);
  }
}