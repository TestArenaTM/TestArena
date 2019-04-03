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
class Administration_Form_EditResolutionColorForDefects extends Custom_Form_Abstract
{
  private $_id;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('id', $options))
    {
      throw new Exception('Project id not defined in edit form');
    }

    $this->_id = $options['id'];
    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();

    $this->addElement('hidden', 'invalidStatusColor', array(
      'required'    => true,
      'maxlength'   => 7,
      'class'       => 'color',
      'value'       => Zend_Registry::get('config')->defaultProject->invalidStatusColor,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('StringLength', false, array(7, 7, 'UTF-8')),
      )
    ));

    $this->addElement('hidden', 'resolvedStatusColor', array(
      'required'    => true,
      'maxlength'   => 7,
      'class'       => 'color',
      'value'       => Zend_Registry::get('config')->defaultProject->resolvedStatusColor,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('StringLength', false, array(7, 7, 'UTF-8')),
      )
    ));
  }
}