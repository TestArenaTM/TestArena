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
class Application_Model_ProjectDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'project';

  public function getByUserId($userId)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'id', 
        'prefix', 
        'status', 
        'create_date', 
        'name', 
        'open_status_color',
        'in_progress_status_color',
        'description'
      ))
      ->joinLeft(array('r' => 'role'), 'p.id=r.project_id', array())
      ->join(array('ru' => 'role_user'), 'r.id=ru.role_id', array())
      ->join(array('pbt' => 'project_bug_tracker'), 'pbt.project_id=p.id', $this->_createAlias('bugTracker', array(
        'id',
        'bug_tracker_id',
        'name',
        'bug_tracker_type',
        'bug_tracker_status'
      )))
      ->where('ru.user_id = ?', $userId)
      ->where('pbt.bug_tracker_status = ?', Application_Model_BugTrackerStatus::ACTIVE)
      ->order('p.name')
      ->group('p.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getById($id)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name))
      ->where('p.id = (?)', $id)
      ->limit(1);
    
    return $this->fetchRow($sql);
  }
}