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
class Project_Form_EditUsersRole extends Custom_Form_Abstract
{
  public function init()
  {
    $this->addElement('text', 'users', array(
      'required'   => false,
      'filters'    => array('StringTrim'),
      'validators' => array(
        'Users'
      )
    ));
    
    $this->addElement('hidden', 'authtoken', array(
      'value' => '',
      'required'   => true
    ));
  }
  
  public function prePopulateUsersByRole(Application_Model_Role $role)
  {
    $result = array();
    
    if (count($role->getUsers()) > 0)
    {
      foreach($role->getUsers() as $user)
      {
        $result[] = array(
          'email' => $user->getEmail(),
          'id'    => (string)$user->getId()
        );
      }
    }
    
    return json_encode($result);
  }
  
  public function checkToken($token)
  {
    $session = new Zend_Session_Namespace('authtoken');
    return $token == $session->authtoken;
  }
  
  public function generateNewToken()
  {
    $session = new Zend_Session_Namespace('authtoken');
    $session->setExpirationSeconds(600);
    $session->authtoken = $hash = Utils_Text::generateToken();
    $this->getElement('authtoken')->setValue($hash);
    return $hash;
  }
}