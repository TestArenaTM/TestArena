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
class Application_Model_TaskTest extends Custom_Model_Standard_Abstract
{
  private $_id         = null;
  private $_task       = null;
  private $_test       = null;
  private $_resolution = null;
  
  private $_checklistItems = array();
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getTask()
  {
    return $this->_task;
  }

  public function getTest()
  {
    return $this->_test;
  }

  public function getResolution()
  {
    return $this->_resolution;
  }

  public function getResolutionId()
  {
    return $this->_resolution->getId();
  }
  public function getChecklistItems()
  {
    return $this->_checklistItems;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = $id;
    return $this;
  }
  
  public function setTask($propertyName, $propertyValue)
  {
    if (null === $this->_task)
    {
      $this->_task = new Application_Model_Task(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getTask()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setTaskObject(Application_Model_Task $task)
  {
    $this->_task = $task;
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
  
  public function setTestObject(Custom_Interface_Test $test = null)
  {
    $this->_test = $test;
  }
  
  public function setOtherTest($propertyName, $propertyValue)
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
  
  public function setTestCase($propertyName, $propertyValue)
  {
    if (null === $this->_test)
    {
      $this->_test = new Application_Model_TestCase(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getTest()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setExploratoryTest($propertyName, $propertyValue)
  {
    if (null === $this->_test)
    {
      $this->_test = new Application_Model_ExploratoryTest(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getTest()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setAutomaticTest($propertyName, $propertyValue)
  {
    if (null === $this->_test)
    {
      $this->_test = new Application_Model_AutomaticTest(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getTest()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setChecklist($propertyName, $propertyValue)
  {
    if (null === $this->_test)
    {
      $this->_test = new Application_Model_Checklist(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getTest()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setResolution($propertyName, $propertyValue)
  {
    if (null === $this->_resolution)
    {
      $this->_resolution = new Application_Model_Resolution(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getResolution()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setChecklistItems($items)
  {
    if (!is_array($items))
    {
      $items = explode(',', $items);
    }
    
    if (count($items) > 0)
    {
      foreach($items as $item)
      {
        $this->addChecklistItem($item);
      }
    }
    
    return $this;
  }
  // </editor-fold>
  
  public function addChecklistItem($item)
  {
    if ($item instanceof Application_Model_TaskChecklistItem)
    {
      $this->_checklistItems[] = $item;
    }
    elseif(is_array($item) && count($item) > 0)
    {
      $this->_checklistItems[] = new Application_Model_TaskChecklistItem($item);
    }
    elseif(is_numeric($item))
    {
      $this->_checklistItems[] = new Application_Model_TaskChecklistItem(array('id' => $item));
    }
    
    return $this;
  }
}