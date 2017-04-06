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
class Administration_Form_AddProject extends Custom_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'name', array(
      'required'    => true,
      'maxlength'   => 80,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('UniqueProjectName', true),
        array('StringLength', false, array(2, 80, 'UTF-8')),
      )
    ));
    
    $this->addElement('text', 'prefix', array(
      'required'    => true,
      'maxlength'   => 6,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'ProjectPrefix',
        array('UniqueProjectPrefix', true),
        array('StringLength', false, array(2, 6, 'UTF-8')),
      )
    ));
    
    $this->addElement('hidden', 'openStatusColor', array(
      'required'    => true,
      'maxlength'   => 7,
      'class'       => 'color',
      'value'       => Zend_Registry::get('config')->defaultProject->openStatusColor,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('StringLength', false, array(7, 7, 'UTF-8')),
      )
    ));
    
    $this->addElement('hidden', 'inProgressStatusColor', array(
      'required'    => true,
      'maxlength'   => 7,
      'class'       => 'color',
      'value'       => Zend_Registry::get('config')->defaultProject->inProgressStatusColor,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('StringLength', false, array(7, 7, 'UTF-8')),
      )
    ));
    
    $this->addElement('textarea', 'description', array(
      'maxlength'   => 1000,
      'required'    => false,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 1000, 'UTF-8')),
      ),
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'add_project',
      'timeout' => 600
    ));
  }
}