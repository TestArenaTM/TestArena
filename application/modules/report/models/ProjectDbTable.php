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
class Report_Model_ProjectDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'project';
  
  public function getOne(Zend_Controller_Request_Abstract $request)
  {
    $projectId = $request->getParam('projectId');
    $sql1 = '(SELECT COUNT(*) FROM task_run AS tr1 INNER JOIN task AS t1 ON t1.id=tr1.task_id WHERE t1.project_id='.$projectId.')';
    $sql2 = '(SELECT COUNT(*) FROM task_run AS tr2 INNER JOIN task AS t2 ON t2.id=tr2.task_id WHERE t2.project_id='.$projectId.' AND tr2.status='.Application_Model_TaskRunStatus::OPEN.')';
    $sql3 = '(SELECT COUNT(*) FROM task_run AS tr3 INNER JOIN task AS t3 ON t3.id=tr3.task_id WHERE t3.project_id='.$projectId.' AND tr3.status='.Application_Model_TaskRunStatus::SUCCESS.')';
    $sql4 = '(SELECT COUNT(*) FROM task_run AS tr4 INNER JOIN task AS t4 ON t4.id=tr4.task_id WHERE t4.project_id='.$projectId.' AND tr4.status='.Application_Model_TaskRunStatus::FAILED.')';
    $sql5 = '(SELECT COUNT(*) FROM task_run AS tr5 INNER JOIN task AS t5 ON t5.id=tr5.task_id WHERE t5.project_id='.$projectId.' AND tr5.status IN ('.Application_Model_TaskRunStatus::SUSPENDED_OPEN.','.Application_Model_TaskRunStatus::SUSPENDED_IN_PROGRESS.'))';
    $sql6 = '(SELECT COUNT(*) FROM task_run AS tr6 INNER JOIN task AS t6 ON t6.id=tr6.task_id WHERE t6.project_id='.$projectId.' AND tr6.status IN ('.Application_Model_TaskRunStatus::SUCCESS.','.Application_Model_TaskRunStatus::FAILED.'))';
    $sql7 = '(SELECT COUNT(*) FROM task_run AS tr7 INNER JOIN task AS t7 ON t7.id=tr7.task_id WHERE t7.project_id='.$projectId.' AND tr7.status='.Application_Model_TaskRunStatus::IN_PROGRESS.')';

    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'allTasks' => new Zend_Db_Expr($sql1),
        'openTasks' => new Zend_Db_Expr($sql2),
        'successTasks' => new Zend_Db_Expr($sql3),
        'failedTasks' => new Zend_Db_Expr($sql4),
        'suspendedTasks' => new Zend_Db_Expr($sql5),
        'closedTasks' => new Zend_Db_Expr($sql6),
        'inProgressTasks' => new Zend_Db_Expr($sql7)
      ))
      ->where('p.id = ?', $projectId)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
}