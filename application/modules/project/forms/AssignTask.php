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
class Project_Form_AssignTask extends Custom_Form_Abstract
{
  protected $_projectId = null;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('projectId', $options))
    {
      throw new Exception('Project id not defined in form.');
    }
    
    $this->_projectId = $options['projectId'];    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'assigneeName', array(
      'required'    => false,
      'class'       => 'autocomplete', 
      'maxlength'   => 255
    ));
    
    $this->addElement('hidden', 'assigneeId', array(
      'required'    => true,
      'validators'  => array(
        'Id',
        array('UserIdExists', true)
      )
    ));
    
    $this->addElement('textarea', 'comment', array(
      'maxlength'   => 160,
      'required'    => false,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 160, 'UTF-8')),
      ),
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'assign_task',
      'timeout' => 600
    ));
  }
}