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
abstract class Custom_Form_AbstractFilter extends Custom_Form_Abstract
{
  protected $_savedValues = array();
  
  public function init()
  {
    parent::init();
    $this->setMethod('get');
    $this->setName('filterForm');
    
    $this->addElement('hidden', 'filterAction', array( 
      'required' => false,
      'value'    => 0
    ));
    
    $this->addElement('select', 'resultCountPerPage', array( 
      'required'     => false,
      'value'        => 10,
      'multiOptions' => array(
         10 => 10,
         20 => 20,
         50 => 50,
        100 => 100,
      )
    ));
  }
  
  public function getValues($suppressArrayNotation = false)
  {
    $values = parent::getValues($suppressArrayNotation);
    
    if (array_key_exists('filterAction', $values))
    {
      $this->getElement('filterAction')->setValue(0);
    }
    
    return $values;
  }
  
  public function prepareSavedValues(array $data)
  {    
    $this->_savedValues = array();
    
    foreach ($data as $key => $value)
    {
      if (is_array($value))
      {
        $this->_savedValues[$key] = array(
          'type'    => 'tokenInput',
          'values'  => $value
        );
      }
      else
      {
        $this->_savedValues[$key] = $value;
      }      
    }
  }
  
  public function getSavedValues()
  {
    return json_encode($this->_savedValues);
  }
}