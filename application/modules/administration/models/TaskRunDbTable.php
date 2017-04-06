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
class Administration_Model_TaskRunDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'task_run';

  public function getForExportDefectsByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('tr' => $this->_name), array(
        'create_date',
        'status',
        'priority',
        'environments' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT e.name SEPARATOR ", ")'),
        'versions' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT v.name SEPARATOR ", ")')
      ))
      ->join(array('t' => 'task'), 'tr.task_id = t.id', array(
        'name',
        'description'
      ))
      ->joinLeft(array('tre' => 'task_run_environment'), 'tr.id = tre.task_run_id', array())
      ->joinLeft(array('e' => 'environment'), 'tre.environment_id = e.id', array())
      ->joinLeft(array('trv' => 'task_run_version'), 'tr.id = trv.task_run_id', array())
      ->joinLeft(array('v' => 'version'), 'trv.version_id = v.id', array())
      ->where('t.type = ?', Application_Model_TaskType::DEFECT)
      ->where('t.current_version = 1')
      ->where('t.status != ?', Application_Model_TaskStatus::DELETED)
      ->where('t.project_id = ?', $projectId)
      ->group('tr.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}