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
class Administration_Model_ProjectBugTrackerDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'project_bug_tracker';
  
  public function getAllByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('bt' => $this->_name), array(
        'id',
        'bug_tracker_id',
        'name',
        'bug_tracker_type',
        'bug_tracker_status'
      ))
      ->where('bt.project_id = ?', $projectId)
      ->where('bt.bug_tracker_status != ?', Application_Model_BugTrackerStatus::DELETED)
      ->group('bt.id')
      ->order('bt.id DESC'); 

    return $this->fetchAll($sql);
  }
  
  public function getForView($id)
  {
    $sql = $this->select()
      ->from(array('bt' => $this->_name), array(
        'id',
        'bug_tracker_id',
        'name',
        'bug_tracker_type',
        'bug_tracker_status'
      ))
      ->join(array('p' => 'project'), 'p.id = bt.project_id', $this->_createAlias('project', array(
        'id',
        'status'
      )))
      ->where('bt.id = ?', $id)
      ->where('bt.bug_tracker_status != ?', Application_Model_BugTrackerStatus::DELETED)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getForEditJira($id)
  {
    $sql = $this->select()
      ->from(array('bt' => $this->_name), array(
        'id',
        'bug_tracker_id',
        'name',
        'bug_tracker_type',
        'bug_tracker_status'
      ))
      ->join(array('p' => 'project'), 'p.id = bt.project_id', $this->_createAlias('project', array(
        'id'
      )))
      ->join(array('btj' => 'bug_tracker_jira'), 'btj.id = bt.bug_tracker_id', array(
        'userName' => 'user_name',
        'password',
        'projectKey' => 'project_key',
        'url'
      ))
      ->where('bt.id = ?', $id)
      ->where('bt.bug_tracker_type = ?', Application_Model_BugTrackerType::JIRA)
      ->where('bt.bug_tracker_status != ?', Application_Model_BugTrackerStatus::DELETED)
      ->where('p.status != ?', Application_Model_ProjectStatus::FINISHED)
      ->group('bt.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getForEditMantis($id)
  {
    $sql = $this->select()
      ->from(array('bt' => $this->_name), array(
        'id',
        'bug_tracker_id',
        'name',
        'bug_tracker_type',
        'bug_tracker_status'
      ))
      ->join(array('p' => 'project'), 'p.id = bt.project_id', $this->_createAlias('project', array(
        'id'
      )))
      ->join(array('btm' => 'bug_tracker_mantis'), 'btm.id = bt.bug_tracker_id', array(
        'userName' => 'user_name',
        'password',
        'projectName' => 'project_name',
        'url'
      ))
      ->where('bt.id = ?', $id)
      ->where('bt.bug_tracker_type = ?', Application_Model_BugTrackerType::MANTIS)
      ->where('bt.bug_tracker_status != ?', Application_Model_BugTrackerStatus::DELETED)
      ->where('p.status != ?', Application_Model_ProjectStatus::FINISHED)
      ->group('bt.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getForExportByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('bt' => $this->_name), array(
        'bug_tracker_id',
        'name',
        'bug_tracker_type',
        'bug_tracker_status'
      ))
      ->where('bt.project_id = ?', $projectId)
      ->where('bt.bug_tracker_status = ?', Application_Model_BugTrackerStatus::ACTIVE)
      ->group('bt.id')
      ->order('bt.id DESC'); 

    return $this->fetchAll($sql);
  }
}