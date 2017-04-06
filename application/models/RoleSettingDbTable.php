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
class Application_Model_RoleSettingDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'role_settings';
  
  public function getUserRoleSettings(Application_Model_User $user, Application_Model_Project $project)
  {
    $db = $this->getAdapter();
    $sql = $this->select()
      ->from(array('rs' => $this->_name), array('rs.role_action_id'))
      ->join(array('ru' => 'role_user'), 'rs.role_id = ru.role_id AND ru.user_id = '.$db->quote($user->getId()), array())
      ->join(array('r' => 'role'), 'rs.role_id = r.id AND r.project_id = '.$db->quote($project->getId()), array())
      ->group('rs.role_action_id')
      ->setIntegrityCheck(false);
    
    return $this->getAdapter()->fetchAll($sql);
  }
}
