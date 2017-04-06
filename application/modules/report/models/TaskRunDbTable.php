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
class Report_Model_TaskRunDbTable extends Custom_Model_DbTable_Criteria_AbstractList
{
  protected $_name = 'task_run';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('tr' => $this->_name), array(
        'id',
        'status',
        'create_date'
      ))
      ->join(array('t' => 'task'), 't.id = tr.task_id', $this->_createAlias('task', array(
        'id',
        'name',
        'type'
      )))
      ->join(array('ph' => 'phase'), 'ph.id = tr.phase_id', $this->_createAlias('phase', array(
        'id',
        'name'
      )))
      ->join(array('r' => 'release'), 'r.id = ph.release_id', $this->_createAlias('phase'.self::TABLE_CONNECTOR.'release', array(
        'id',
        'name'
      )))
      ->group('tr.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('tr' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(tr.id)'))
      ->join(array('ph' => 'phase'), 'ph.id = tr.phase_id', array())
      ->join(array('r' => 'release'), 'r.id = ph.release_id', array())
      ->setIntegrityCheck(false);
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getSqlByIds(array $ids)
  {
    return $ids;
  }
  
  public function getByPriority(Zend_Controller_Request_Abstract $request)
  {
    $projectId = $request->getParam('projectId');
    $sql1 = '(SELECT COUNT(*) FROM task_run AS tr1 INNER JOIN task AS t1 ON t1.id=tr1.task_id WHERE t1.project_id='.$projectId.' AND tr1.priority=tr.priority)';
    $sql2 = '(SELECT COUNT(*) FROM task_run AS tr2 INNER JOIN task AS t2 ON t2.id=tr2.task_id WHERE t2.project_id='.$projectId.' AND tr2.priority=tr.priority AND tr2.status='.Application_Model_TaskRunStatus::OPEN.')';
    $sql3 = '(SELECT COUNT(*) FROM task_run AS tr3 INNER JOIN task AS t3 ON t3.id=tr3.task_id WHERE t3.project_id='.$projectId.' AND tr3.priority=tr.priority AND tr3.status='.Application_Model_TaskRunStatus::SUCCESS.')';
    $sql4 = '(SELECT COUNT(*) FROM task_run AS tr4 INNER JOIN task AS t4 ON t4.id=tr4.task_id WHERE t4.project_id='.$projectId.' AND tr4.priority=tr.priority AND tr4.status='.Application_Model_TaskRunStatus::FAILED.')';
    $sql5 = '(SELECT COUNT(*) FROM task_run AS tr5 INNER JOIN task AS t5 ON t5.id=tr5.task_id WHERE t5.project_id='.$projectId.' AND tr5.priority=tr.priority AND tr5.status IN ('.Application_Model_TaskRunStatus::SUSPENDED_OPEN.','.Application_Model_TaskRunStatus::SUSPENDED_IN_PROGRESS.'))';
    $sql6 = '(SELECT COUNT(*) FROM task_run AS tr6 INNER JOIN task AS t6 ON t6.id=tr6.task_id WHERE t6.project_id='.$projectId.' AND tr6.priority=tr.priority AND tr6.status IN ('.Application_Model_TaskRunStatus::SUCCESS.','.Application_Model_TaskRunStatus::FAILED.'))';
    $sql7 = '(SELECT COUNT(*) FROM task_run AS tr7 INNER JOIN task AS t7 ON t7.id=tr7.task_id WHERE t7.project_id='.$projectId.' AND tr7.priority=tr.priority AND tr7.status='.Application_Model_TaskRunStatus::IN_PROGRESS.')';

    $sql = $this->select()
      ->from(array('tr' => $this->_name), array(
        'priority',
        'allTasks' => new Zend_Db_Expr($sql1),
        'openTasks' => new Zend_Db_Expr($sql2),
        'successTasks' => new Zend_Db_Expr($sql3),
        'failedTasks' => new Zend_Db_Expr($sql4),
        'suspendedTasks' => new Zend_Db_Expr($sql5),
        'closedTasks' => new Zend_Db_Expr($sql6),
        'inProgressTasks' => new Zend_Db_Expr($sql7)
      ))
      ->join(array('t' => 'task'), 't.id = tr.task_id', array())
      ->join(array('r' => 'release'), 'r.project_id = t.project_id', array())
      ->group('tr.priority')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}