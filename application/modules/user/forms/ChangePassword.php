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
class User_Form_ChangePassword extends Custom_Form_Abstract
{
  private $_email;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('email', $options))
    {
      throw new Custom_NoLogException('E-mail not set for old password validation.');
    }
    
    $this->_email = $options['email'];

    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('password', 'oldPassword', array(
      'required'      => true,
      'autocomplete'  => 'off',
      'maxlength'     => 64,
      'validators'    => array(
        'Password',
        array('StringLength', false, array(6, 64, 'UTF-8')),
        array('UserPassword', true)
      )
    ));
    
    $this->addElement('password', 'password', array(
      'required'   => true,
      'maxlength'  => 64,
      'validators' => array(
        'Password',
        array('PasswordConfirmation', false, array('confirmFieldName' => 'confirmPassword')),
        array('StringLength', false, array(6, 64, 'UTF-8')),
      )
    ));
    
    $this->addElement('password', 'confirmPassword', array(
      'required'    => true,
      'maxlength'   => 64,
      'validators'  => array(
        array('StringLength', false, array(6, 64, 'UTF-8')),
      )
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'change_password',
      'timeout' => 600
    ));
  }
}