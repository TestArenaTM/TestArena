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
class Report_Model_ReleaseDbTable extends Custom_Model_DbTable_Criteria_AbstractList
{
  protected $_name = 'release';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql1 = '(SELECT COUNT(*) FROM task_run AS tr1 INNER JOIN phase AS p1 ON p1.id=tr1.phase_id WHERE p1.release_id=r.id)';
    $sql2 = '(SELECT COUNT(*) FROM task_run AS tr2 INNER JOIN phase AS p2 ON p2.id=tr2.phase_id WHERE p2.release_id=r.id AND tr2.status='.Application_Model_TaskRunStatus::OPEN.')';
    $sql3 = '(SELECT COUNT(*) FROM task_run AS tr3 INNER JOIN phase AS p3 ON p3.id=tr3.phase_id WHERE p3.release_id=r.id AND tr3.status='.Application_Model_TaskRunStatus::SUCCESS.')';
    $sql4 = '(SELECT COUNT(*) FROM task_run AS tr4 INNER JOIN phase AS p4 ON p4.id=tr4.phase_id WHERE p4.release_id=r.id AND tr4.status='.Application_Model_TaskRunStatus::FAILED.')';
    $sql5 = '(SELECT COUNT(*) FROM task_run AS tr5 INNER JOIN phase AS p5 ON p5.id=tr5.phase_id WHERE p5.release_id=r.id AND tr5.status IN ('.Application_Model_TaskRunStatus::SUSPENDED_OPEN.','.Application_Model_TaskRunStatus::SUSPENDED_IN_PROGRESS.'))';
    $sql6 = '(SELECT COUNT(*) FROM task_run AS tr6 INNER JOIN phase AS p6 ON p6.id=tr6.phase_id WHERE p6.release_id=r.id AND tr6.status IN ('.Application_Model_TaskRunStatus::SUCCESS.','.Application_Model_TaskRunStatus::FAILED.'))';
    $sql7 = '(SELECT COUNT(*) FROM task_run AS tr7 INNER JOIN phase AS p7 ON p7.id=tr7.phase_id WHERE p7.release_id=r.id AND tr7.status='.Application_Model_TaskRunStatus::IN_PROGRESS.')';
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'allTasks' => new Zend_Db_Expr($sql1),
        'openTasks' => new Zend_Db_Expr($sql2),
        'successTasks' => new Zend_Db_Expr($sql3),
        'failedTasks' => new Zend_Db_Expr($sql4),
        'suspendedTasks' => new Zend_Db_Expr($sql5),
        'closedTasks' => new Zend_Db_Expr($sql6),
        'inProgressTasks' => new Zend_Db_Expr($sql7)
      ))
      ->where('r.project_id = ?', $request->getParam('projectId'))
      ->group('r.id')
      ->setIntegrityCheck(false);

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(r.id)'))
      ->setIntegrityCheck(false);
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getSqlByIds(array $ids)
  {
    return $ids;
  }
  
  public function getAllForExport(Zend_Controller_Request_Abstract $request)
  {
    $sql1 = '(SELECT COUNT(*) FROM task_run AS tr1 INNER JOIN phase AS p1 ON p1.id=tr1.phase_id WHERE p1.release_id=r.id)';
    $sql2 = '(SELECT COUNT(*) FROM task_run AS tr2 INNER JOIN phase AS p2 ON p2.id=tr2.phase_id WHERE p2.release_id=r.id AND tr2.status='.Application_Model_TaskRunStatus::OPEN.')';
    $sql3 = '(SELECT COUNT(*) FROM task_run AS tr3 INNER JOIN phase AS p3 ON p3.id=tr3.phase_id WHERE p3.release_id=r.id AND tr3.status='.Application_Model_TaskRunStatus::SUCCESS.')';
    $sql4 = '(SELECT COUNT(*) FROM task_run AS tr4 INNER JOIN phase AS p4 ON p4.id=tr4.phase_id WHERE p4.release_id=r.id AND tr4.status='.Application_Model_TaskRunStatus::FAILED.')';
    $sql5 = '(SELECT COUNT(*) FROM task_run AS tr5 INNER JOIN phase AS p5 ON p5.id=tr5.phase_id WHERE p5.release_id=r.id AND tr5.status IN ('.Application_Model_TaskRunStatus::SUSPENDED_OPEN.','.Application_Model_TaskRunStatus::SUSPENDED_IN_PROGRESS.'))';
    $sql6 = '(SELECT COUNT(*) FROM task_run AS tr6 INNER JOIN phase AS p6 ON p6.id=tr6.phase_id WHERE p6.release_id=r.id AND tr6.status IN ('.Application_Model_TaskRunStatus::SUCCESS.','.Application_Model_TaskRunStatus::FAILED.'))';
    $sql7 = '(SELECT COUNT(*) FROM task_run AS tr7 INNER JOIN phase AS p7 ON p7.id=tr7.phase_id WHERE p7.release_id=r.id AND tr7.status='.Application_Model_TaskRunStatus::IN_PROGRESS.')';
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'name',
        'allTasks' => new Zend_Db_Expr($sql1),
        'openTasks' => new Zend_Db_Expr($sql2),
        'successTasks' => new Zend_Db_Expr($sql3),
        'failedTasks' => new Zend_Db_Expr($sql4),
        'suspendedTasks' => new Zend_Db_Expr($sql5),
        'closedTasks' => new Zend_Db_Expr($sql6),
        'inProgressTasks' => new Zend_Db_Expr($sql7)
      ))
      ->where('r.project_id = ?', $request->getParam('projectId'))
      ->group('r.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}