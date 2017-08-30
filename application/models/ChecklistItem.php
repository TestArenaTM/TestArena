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
class Application_Model_ChecklistItem extends Custom_Model_Standard_Abstract
{
  private $_id     = null;
  private $_test   = null;
  private $_name   = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getTest()
  {
    return $this->_test;
  }

  public function getName()
  {
    return $this->_name;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = $id;
    return $this;
  }
  
  public function setTest($propertyName, $propertyValue)
  {
    if (null === $this->_test)
    {
      $this->_test = new Application_Model_Test(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getTest()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setTestObject(Application_Model_Test $test)
  {
    $this->_test = $test;
  }

  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }
  // </editor-fold>
}