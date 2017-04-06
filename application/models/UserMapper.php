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
class Application_Model_UserMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Application_Model_UserDbTable';

  public function getByEmail(Application_Model_User $user)
  {
    $row = $this->_getDbTable()->getByEmail($user->getEmail());
    
    if (null === $row)
    {
      return false;
    }
    
    return $user->setDbProperties($row->toArray());
  }

  public function changeDefaultProjectId(Application_Model_User $user)
  {
    $data = array(
      'default_project_id' => $user->getDefaultProjectId()
    );
    
    $where = array(
      'id = ?' => $user->getId()
    );
    
    $this->_getDbTable()->update($data, $where);
  }

  public function changeDefaultLocale(Application_Model_User $user)
  {
    $data = array(
      'default_locale' => $user->getDefaultLocale()
    );
    
    $where = array(
      'id = ?' => $user->getId()
    );
    
    $this->_getDbTable()->update($data, $where);
  }
}