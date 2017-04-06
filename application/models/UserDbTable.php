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
class Application_Model_UserDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'user';

  public function getByEmail($email)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email',
        'reset_password',
        'status',
        'create_date',
        'last_login_date',
        'firstname',
        'lastname',
        'administrator',
        'organization',
        'department',
        'phone_number',
        'default_project_id',
        'default_locale'
      ))      
      ->where('u.status = ?', Application_Model_UserStatus::ACTIVE)
      ->where('u.email = ?', $email);
    
    return $this->fetchRow($sql);
  }
}




  