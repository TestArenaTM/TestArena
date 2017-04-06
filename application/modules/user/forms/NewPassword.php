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
class User_Form_NewPassword extends Custom_Form_Abstract
{
  public function init()
  {
    $this->addElement('password', 'password', array(
      'required'    => true,
      'maxlength'   => 64,
      'validators'  => array(
        'Password',
        array('PasswordConfirmation', false, array('confirmFieldName' => 'confirmPassword')),
        array('StringLength', false, array(6, 64, 'UTF-8')),
      )
    ));
    
    $this->addElement('password', 'confirmPassword', array(
      'required'  => true,
      'maxlength' => 64
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'new_password',
      'timeout' => 600
    ));
  }
}

