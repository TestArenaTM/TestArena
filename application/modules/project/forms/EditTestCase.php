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
class Project_Form_EditTestCase extends Project_Form_AddTestCase
{
  private $_familyId;  
  
  public function __construct($options = null)
  {
    if (!array_key_exists('familyId', $options))
    {
       throw new Exception('Test family id not defined in form');
    }
    
    $this->_familyId = $options['familyId'];
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('checkbox', 'newVersion', array(
      'required'       => false,
      'uncheckedValue' => ''        
    ));
    
    $this->getElement('name')
      ->removeValidator('UniqueTestName')
      /*->addValidator('UniqueTestName', true, array(
        'criteria'  => array('project_id' => $this->_projectId),
        'exclude'   => $this->_familyId
      ))*/;
    
    $this->getElement('csrf')->setAttrib('salt', 'edit_test_case');
  }
}