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
class Project_Model_RoleDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'role';

  public function getListByProjectId($projectId)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'users' => new Zend_Db_Expr('GROUP_CONCAT(ur.user_id)')
      ))
      ->joinLeft(array('ur' => 'role_user'), 'ur.role_id = r.id', array())
      ->where('r.project_id = ?', $projectId)
      ->group('r.id')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
}