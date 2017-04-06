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
class Administration_Model_RoleUserDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'role_user';

  public function getName()
  {
    return $this->_name;
  }

  public function getForExportByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('ru' => $this->_name), array(
        'role_id',
        'user_id'
      ))
      ->join(array('ro' => 'role'), 'ro.id = ru.role_id', array())
      ->where('ro.project_id = ?', $projectId)
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}
