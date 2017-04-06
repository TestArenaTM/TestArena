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
abstract class Custom_Model_Standard_Abstract extends Custom_Model_Abstract
{
  protected $_extraData = array();
  protected $_map       = array();
  
  private $_methods = null;
  
  public function __construct(array $properties = null, $setExtraData = true)
  {
    if (is_array($properties))
    {
      $this->setProperties($properties, $setExtraData);
    }
  }
 
  public function setDbProperties(array $properties)
  {
    return $this->setProperties($this->map($properties));
  }
 
  public function setProperties(array $properties, $setExtraData = true)
  {
    foreach ($properties as $name => $value)
    {
      $this->setProperty($name, $value, $setExtraData);
    }
    
    return $this;
  }
  
  public function setProperty($name, $value, $setExtraData = true)
  {
    $buffor = explode('$', $name, 2);
    
    if (count($buffor) == 2)
    {
      $method = 'set'.ucfirst($buffor[0]);
      
      if ($this->_methodExists($method))
      {
        $this->$method($buffor[1], $value);
      }
      else
      {
        throw new Exception('Property not found.');
      }
    }
    else
    {
      if (strpos($name, '_') !== false)
      {
        $name = $this->mapSingle($name);
      }
      
      $method = 'set'.ucfirst($name);

      if ($this->_methodExists($method))
      {
        $this->$method($value);
      }
      elseif ($setExtraData)
      {
        $this->setExtraData($name, $value);
      }
    }
    return $this;
  }

  private function _methodExists($method)
  {
    if ($this->_methods === null)
    {
      $this->_methods = get_class_methods($this);
    }
    
    return in_array($method, $this->_methods);
  }
  
  public function map(array $array)
  {
    $result = array();

    foreach ($array as $name => $value)
    {
      $result[$this->mapSingle($name)] = $value;
    }
    
    return $result;
  }
  
  public function mapSingle($name)
  {
    if (array_key_exists($name, $this->_map))
    {
      return $this->_map[$name];
    }
    
    return $name;
  }
  
  public function setExtraData($name, $value)
  {
    $this->_extraData[$name] = $value;
    return $this;
  }
  
  public function hasExtraData($name)
  {
    return array_key_exists($name, $this->_extraData);
  }
  
  public function getExtraData($name, $default = null)
  {
    return $this->hasExtraData($name) ? $this->_extraData[$name] : $default;
  }
  
  public function getAllExtraData()
  {
    return $this->_extraData;
  }
}