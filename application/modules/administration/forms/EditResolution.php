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
class Administration_Form_EditResolution extends Administration_Form_AddResolution
{
  private $_id;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('id', $options))
    {
      throw new Exception('Resolution id not defined in edit form');
    }

    $this->_id = $options['id'];    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->getElement('name')
      ->removeValidator('UniqueProjectResolutionName')
      ->addValidator('UniqueProjectResolutionName', true, array(
        'criteria' => array('project_id' => $this->_projectId),
        'exclude' => $this->_id
      ));
    
    $this->getElement('color')
      ->removeValidator('UniqueProjectResolutionColor')
      ->addValidator('UniqueProjectResolutionColor', true, array(
        'criteria' => array('project_id' => $this->_projectId),
        'exclude' => $this->_id
      ));
    
    $this->getElement('csrf')->setAttrib('salt', 'edit_resolution');
  }
}