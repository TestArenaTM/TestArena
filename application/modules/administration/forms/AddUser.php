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
class Administration_Form_AddUser extends Custom_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'firstname', array(
      'required'    => true,
      'maxlength'   => 32,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Firstname',
        array('StringLength', false, array(2, 32, 'UTF-8'))
      )
    ));
    
    $this->addElement('text', 'lastname', array(
      'required'    => true,
      'maxlength'   => 64,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Lastname',
        array('StringLength', false, array(2, 64, 'UTF-8'))
      )
    ));
    
    $this->addElement('text', 'email', array(
      'required'    => true,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'EmailAddressSimpleMessage',
        array('UniqueEmail', true),
        array('StringLength', false, array(6, 255, 'UTF-8'))
      )
    ));
    
    $this->addElement('text', 'organization', array(
      'required'    => false,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLength', false, array(1, 255, 'UTF-8')),
      )
    ));
    
    $this->addElement('text', 'department', array(
      'required'    => false,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLength', false, array(1, 255, 'UTF-8')),
      )
    ));
    
    $this->addElement('text', 'phoneNumber', array(
      'required'    => false,
      'maxlength'   => 20,
      'filters'     => array('StringTrim', 'WhitespaceReduce'),
      'validators'  => array(
        'PhoneNumber',
        array('StringLength', false, array(7, 20, 'UTF-8')),
      )
    ));
    
    $this->addElement('checkbox', 'activeUser', array(
      'value' => true
    ));
    
    $this->addElement('checkbox', 'administrator', array(
      'value' => false
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'add_user',
      'timeout' => 600
    ));
  }
}

