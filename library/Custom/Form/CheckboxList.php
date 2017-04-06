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
class Custom_Form_CheckboxList extends Custom_Form_Abstract
{
  private $_ids;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('ids', $options))
    {
       throw new Exception('Ids not defined in form.');
    }

    $this->_ids = $options['ids'];

    if (array_key_exists('ids', $this->_ids))
    {
      $names = array_keys($this->_ids['ids']);
      $this->_ids = array();

      foreach ($names as $name)
      {
        $this->_ids[] = $this->_getIdFormName($name);
      }
    }
    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    $this->setName('CheckboxListForm');
    $this->setMethod('post');
    
    $this->addElement('hidden', 'ids', array(
      'required'    => false,
      'isArray'     => true,
      'value'       => '1'
    ));
    
    foreach ($this->_ids as $id)
    {
      $this->addElement('checkbox', 'checkBoxId_'.$id, array(
        'required'        => false,
        'filters'         => array('StringTrim'),
        'class'           => 'j_checkBoxId',
        'checkedValue'    => 1,
        'uncheckedValue'  => 0,
        'belongsTo'       => 'ids'
      ));
    }
  }
  
  private function _getIdFormName($name)
  {
    $buf = explode('_', $name, 2);
    return $buf[1];
  }
  
  public function isValid($data)
  {
    return parent::isValid($data['ids']);
  }
  
  public function getValues($suppressArrayNotation = false)
  {
    $values = parent::getValues($suppressArrayNotation);
    $ids = array();
    
    foreach ($values['ids'] as $name => $value)
    {
      if ($value)
      {
        $ids[] = $this->_getIdFormName($name);
      }
    }
    
    $values['ids'] = $ids;
    return $values;
  }
}