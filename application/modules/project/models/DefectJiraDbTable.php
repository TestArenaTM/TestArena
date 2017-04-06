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
  
  public function getByTask($taskId, $bugTrackerId)
  {
    $sql = $this->select()
      ->from(array('dj' => $this->_name), array(
        'id',
        'no',
        'name' => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no, " ", dj.summary)'),
        'key'  => new Zend_Db_Expr('CONCAT(btj.project_key, "-", dj.no)')
      ))
      ->join(array('td' => 'task_defect'), 'td.defect_id = dj.id', array())
      ->join(array('btj' => 'bug_tracker_jira'), 'btj.id = dj.bug_tracker_id', array())
      ->where('dj.bug_tracker_id = ?', $bugTrackerId)
      ->where('td.task_id = ?', $taskId)
      ->group('dj.id')
      ->order('dj.no')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
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