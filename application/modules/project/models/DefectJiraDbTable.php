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
class Project_Model_DefectJiraDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'defect_jira';

  public function getByTaskTest($taskTestId, $bugTrackerId)
  {
    $sql = $this->select()
      ->from(array('dj' => $this->_name), array(
        'id',
        'no',
        'name' => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no, " ", dj.summary)'),
        'key'  => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no)')
      ))
      ->join(array('td' => 'test_defect'), 'td.defect_id = dj.id', array())
      ->join(array('btj' => 'bug_tracker_jira'), 'btj.id = dj.bug_tracker_id', array())
      ->where('td.bug_tracker_id = ?', $bugTrackerId)
      ->where('td.task_test_id = ?', $taskTestId)
      ->group('dj.id')
      ->order('dj.no')
      ->setIntegrityCheck(false);
    return $this->fetchAll($sql);
  }
  
  public function getByTask($taskId, $bugTrackerId)
  {
    /* task defect */
    $sqls[] = $this->select()
      ->from(array('dj' => $this->_name), array(
        'id',
        'no',
        'name' => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no, " ", dj.summary)'),
        'key'  => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no)'),
        'test_id' => new Zend_Db_Expr(0),
        'test_type' => new Zend_Db_Expr(0),
        'test_ordinal_no' => new Zend_Db_Expr(0),
        'test_name' => new Zend_Db_Expr(0),
        'task_test_id' => new Zend_Db_Expr(0),
        'task_defect_id' => new Zend_Db_Expr('td.id'),
        'project_prefix' => new Zend_Db_Expr(0),
        'project_open_status_color' => new Zend_Db_Expr(0),
        'project_in_progress_status_color' => new Zend_Db_Expr(0),
        'project_reopen_status_color' => new Zend_Db_Expr(0),
        'project_closed_status_color' => new Zend_Db_Expr(0),
        'project_invalid_status_color' => new Zend_Db_Expr(0),
        'project_resolved_status_color' => new Zend_Db_Expr(0),
      ))
      ->join(array('td' => 'task_defect'), 'td.defect_id = dj.id', array())
      ->join(array('btj' => 'bug_tracker_jira'), 'btj.id = dj.bug_tracker_id', array())
      ->where('td.bug_tracker_id = ?', $bugTrackerId)
      ->where('td.task_id = ?', $taskId)
      ->group('dj.id')
      ->order('dj.no')
      ->setIntegrityCheck(false);

    /* test defect */
    $sqls[] = $this->select()
      ->from(array('dj' => $this->_name), array(
        'id',
        'no',
        'name' => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no, " ", dj.summary)'),
        'key'  => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no)'),
        'test_id' => new Zend_Db_Expr('t.id'),
        'test_type' => new Zend_Db_Expr('t.type'),
        'test_ordinal_no' => new Zend_Db_Expr('t.ordinal_no'),
        'test_name' => new Zend_Db_Expr('t.name'),
        'task_test_id' => new Zend_Db_Expr('tt.id'),
        'task_defect_id' => new Zend_Db_Expr(0),
      ))
      ->join(array('td' => 'test_defect'), 'td.defect_id = dj.id', array())
      ->join(array('tt' => 'task_test'), 'tt.id = td.task_test_id', array())
      ->join(array('t' => 'test'), 't.id = tt.test_id', array())
      ->join(array('btj' => 'bug_tracker_jira'), 'btj.id = dj.bug_tracker_id', array())
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->where('tt.task_id = ?', $taskId)
      ->where('td.bug_tracker_id = ?', $bugTrackerId)
      ->setIntegrityCheck(false);

    return $this->fetchAll($this->union($sqls)->order('name DESC'));
  }
  
  public function getIdByNo($no, $bugTrackerId)
  {
    $sql = $this->select()
      ->from(array('dj' => $this->_name), array(
        'id'
      ))      
      ->where('dj.no = ?', $no)
      ->where('dj.bug_tracker_id = ?', $bugTrackerId)
      ->limit(1);

    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getForViewAjax($defectJiraId, $bugTrackerId)
  {
    $sql = $this->select()
      ->from(array('dj' => $this->_name), array(
        'id',
        'name'         => 'summary',
        'objectNumber' => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no)')
      ))
      ->join(array('btj' => 'bug_tracker_jira'), 'btj.id = dj.bug_tracker_id', array())
      ->where('dj.bug_tracker_id = ?', $bugTrackerId)
      ->where('dj.id = ?', $defectJiraId)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
}