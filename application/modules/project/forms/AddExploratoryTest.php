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
class Project_Form_AddExploratoryTest extends Project_Form_AddOtherTest
{
  public function init()
  {
    parent::init();
    
    $this->removeElement('description');
    
    $this->addElement('text', 'duration', array(
      'required'    => false,
      'maxlength'   => 9,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Int',
        array('StringLength', false, array(0, 9, 'UTF-8'))
      )
    ));
    
    $this->addElement('textarea', 'testCard', array(
      'maxlength'   => 5000,
      'required'    => false,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 5000, 'UTF-8')),
      ),
    ));
    
    $this->getElement('csrf')->setAttrib('salt', 'add_exploratory_test');
  }
}