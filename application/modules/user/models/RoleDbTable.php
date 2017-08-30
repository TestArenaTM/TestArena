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
class User_Model_RoleDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'role';

  public function getForUserProfile($userId)
  {
    $sql = $this->select()
      ->from(array('ro' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('p' => 'project'), 'p.id = ro.project_id', $this->_createAlias('project', array(
        'id',
        'prefix',
        'name'
      )))
      ->join(array('ru' => 'role_user'), 'ru.role_id = ro.id', array())
      ->where('ru.user_id = ?', $userId)
      ->group('ro.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}