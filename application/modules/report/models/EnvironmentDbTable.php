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
class Report_Model_EnvironmentDbTable extends Custom_Model_DbTable_Criteria_AbstractList
{
  protected $_name = 'environment';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql1 = '(SELECT COUNT(*) FROM task_run AS tr1 INNER JOIN task_run_environment AS tre1 ON tre1.task_run_id=tr1.id WHERE tre1.environment_id=e.id)';
    $sql2 = '(SELECT COUNT(*) FROM task_run AS tr2 INNER JOIN task_run_environment AS tre2 ON tre2.task_run_id=tr2.id WHERE tre2.environment_id=e.id AND tr2.status='.Application_Model_TaskRunStatus::OPEN.')';
    $sql3 = '(SELECT COUNT(*) FROM task_run AS tr3 INNER JOIN task_run_environment AS tre3 ON tre3.task_run_id=tr3.id WHERE tre3.environment_id=e.id AND tr3.status='.Application_Model_TaskRunStatus::SUCCESS.')';
    $sql4 = '(SELECT COUNT(*) FROM task_run AS tr4 INNER JOIN task_run_environment AS tre4 ON tre4.task_run_id=tr4.id WHERE tre4.environment_id=e.id AND tr4.status='.Application_Model_TaskRunStatus::FAILED.')';
    $sql5 = '(SELECT COUNT(*) FROM task_run AS tr5 INNER JOIN task_run_environment AS tre5 ON tre5.task_run_id=tr5.id WHERE tre5.environment_id=e.id AND tr5.status IN ('.Application_Model_TaskRunStatus::SUSPENDED_OPEN.','.Application_Model_TaskRunStatus::SUSPENDED_IN_PROGRESS.'))';
    $sql6 = '(SELECT COUNT(*) FROM task_run AS tr6 INNER JOIN task_run_environment AS tre6 ON tre6.task_run_id=tr6.id WHERE tre6.environment_id=e.id AND tr6.status IN ('.Application_Model_TaskRunStatus::SUCCESS.','.Application_Model_TaskRunStatus::FAILED.'))';
    $sql7 = '(SELECT COUNT(*) FROM task_run AS tr7 INNER JOIN task_run_environment AS tre7 ON tre7.task_run_id=tr7.id WHERE tre7.environment_id=e.id AND tr7.status='.Application_Model_TaskRunStatus::IN_PROGRESS.')';
    
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
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
      ->where('e.project_id = ?', $request->getParam('projectId'))
      ->group('e.id')
      ->setIntegrityCheck(false);

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(e.id)'))
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
    $sql1 = '(SELECT COUNT(*) FROM task_run AS tr1 INNER JOIN task_run_environment AS tre1 ON tre1.task_run_id=tr1.id WHERE tre1.environment_id=e.id)';
    $sql2 = '(SELECT COUNT(*) FROM task_run AS tr2 INNER JOIN task_run_environment AS tre2 ON tre2.task_run_id=tr2.id WHERE tre2.environment_id=e.id AND tr2.status='.Application_Model_TaskRunStatus::OPEN.')';
    $sql3 = '(SELECT COUNT(*) FROM task_run AS tr3 INNER JOIN task_run_environment AS tre3 ON tre3.task_run_id=tr3.id WHERE tre3.environment_id=e.id AND tr3.status='.Application_Model_TaskRunStatus::SUCCESS.')';
    $sql4 = '(SELECT COUNT(*) FROM task_run AS tr4 INNER JOIN task_run_environment AS tre4 ON tre4.task_run_id=tr4.id WHERE tre4.environment_id=e.id AND tr4.status='.Application_Model_TaskRunStatus::FAILED.')';
    $sql5 = '(SELECT COUNT(*) FROM task_run AS tr5 INNER JOIN task_run_environment AS tre5 ON tre5.task_run_id=tr5.id WHERE tre5.environment_id=e.id AND tr5.status IN ('.Application_Model_TaskRunStatus::SUSPENDED_OPEN.','.Application_Model_TaskRunStatus::SUSPENDED_IN_PROGRESS.'))';
    $sql6 = '(SELECT COUNT(*) FROM task_run AS tr6 INNER JOIN task_run_environment AS tre6 ON tre6.task_run_id=tr6.id WHERE tre6.environment_id=e.id AND tr6.status IN ('.Application_Model_TaskRunStatus::SUCCESS.','.Application_Model_TaskRunStatus::FAILED.'))';
    $sql7 = '(SELECT COUNT(*) FROM task_run AS tr7 INNER JOIN task_run_environment AS tre7 ON tre7.task_run_id=tr7.id WHERE tre7.environment_id=e.id AND tr7.status='.Application_Model_TaskRunStatus::IN_PROGRESS.')';
    
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
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
      ->where('e.project_id = ?', $request->getParam('projectId'))
      ->group('e.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}