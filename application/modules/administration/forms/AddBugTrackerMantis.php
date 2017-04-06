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
class Administration_Form_AddBugTrackerMantis extends Custom_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'name', array(
      'required'    => true,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('StringLength', false, array(2, 255, 'UTF-8')),
      )
    ));
    
    $this->addElement('hidden', 'project', array(
      'required'    => true,
      'value'       => 1,
      'validators'  => array(
        array('MantisSoapProject', true, array(
          'urlFieldName'          => 'url',
          'projectNameFieldName'  => 'projectName',
          'userNameFieldName'     => 'userName',
          'passwordFieldName'     => 'password'
        ))
      )
    ));

    $this->addElement('text', 'url', array(
      'required'    => true,
      'filters'     => array('StringTrim', 'Url'),
      'validators'  => array(
        array('Url', true, array('checkExists' => true))
      )
    ));
    
    $this->addElement('text', 'projectName', array(
      'required'    => true,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('StringLength', false, array(2, 255, 'UTF-8'))
      )
    ));
    
    $this->addElement('text', 'userName', array(
      'required'    => false,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('StringLength', false, array(2, 255, 'UTF-8')),
      )
    ));
    
    $this->addElement('password', 'password', array(
      'required'  => false,
      'filters'   => array('StringTrim')
    ));
    
    $this->addElement('checkbox', 'activate', array(
      'value' => false
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'add_bug_tracker_mantis',
      'timeout' => 600
    ));
  }
}