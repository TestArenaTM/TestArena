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
abstract class Custom_InputData_Abstract
{
  private $_validators = null;
  private $_required = array();
  private $_filters = null;
  private $_filteredValues = array();
  
  abstract public function initValidators();
  abstract public function initFilters();
  
  public function addValidators($name, array $validators, $reuired = false)
  {
    $this->_validators[$name] = new Zend_Validate();
    $this->_required[$name] = $reuired;
    
    foreach ($validators as $validator)
    {
      $this->_validators[$name]->addValidator($validator);
    }
  }
  
  public function addFilters($name, array $filters)
  {
    $this->_filters[$name] = new Zend_Filter();
    
    foreach ($filters as $filter)
    {
      $this->_filters[$name]->addFilter($filter);
    }
  }
  
  public function isValid(array $values)
  {
    if ($this->_validators === null)
    {
      $this->initValidators();
    }    
    
    foreach ($this->_required as $name => $required)
    {
      $isValue = array_key_exists($name, $values);
      
      if ($required)
      {
        if (!$isValue || !$this->_validators[$name]->isValid($values[$name]))
        {
          return false;
        }
      }
      else
      {
        if ($isValue && !$this->_validators[$name]->isValid($values[$name]))
        {
          return false;
        }
      }
    }

    $this->_filter($values);
    return true;
  }
  
  private function _filter($values)
  {
    if ($this->_filters === null)
    {
      $this->initFilters();
    }

    foreach ($values as $name => $value)
    {
      if (array_key_exists($name, $this->_filters))
      {
        $this->_filteredValues[$name] = $this->_filters[$name]->filter($value);
      }
    }
  }
  
  public function getFilteredValues()
  {
    return $this->_filteredValues;
  }
  
  public function getFilteredValue($name)
  {
    if (!array_key_exists($name, $this->_filteredValues))
    {
      throw new Exception('Filtered value not exists.');
    }
    
    return $this->_filteredValues[$name];
  }
  
  public function isFilteredValue($name)
  {
    if (array_key_exists($name, $this->_filteredValues))
    {
      return true;
    }
    
    return false;
  }
  
  public function setRequired($name, $reuired = true)
  {
    $this->_required[$name] = $reuired;
    
    return $this;
  }
}