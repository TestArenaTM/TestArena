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
class Administration_Model_TaskTestCaseDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'task_test_case';

  public function getForExportByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('ttc' => $this->_name), array(
        'task_id',
        'presuppositions',
        'result'
      ))
      ->join(array('t' => 'task'), 't.id = ttc.task_id', array())
      ->where('t.current_version = 1')
      ->where('t.status != ?', Application_Model_TaskStatus::DELETED)
      ->where('t.project_id = ?', $projectId)
      ->group('t.family_id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}