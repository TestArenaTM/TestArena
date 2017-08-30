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
class Application_Model_TaskChecklistItem extends Custom_Model_Standard_Abstract
{
  private $_id            = null;
  private $_taskTest      = null;
  private $_checklistItem = null;
  private $_status        = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getTaskTest()
  {
    return $this->_taskTest;
  }

  public function getChecklistItem()
  {
    return $this->_checklistItem;
  }

  public function getStatus()
  {
    return $this->_status;
  }

  public function getStatusId()
  {
    return $this->_status->getId();
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = $id;
    return $this;
  }
  
  public function setTaskTest($propertyName, $propertyValue)
  {
    if (null === $this->_taskTest)
    {
      $this->_taskTest = new Application_Model_TaskTest(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getTaskTest()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setTaskTestObject(Application_Model_TaskTest $taskTest)
  {
    $this->_taskTest = $taskTest;
  }
  
  public function setChecklistItem($propertyName, $propertyValue)
  {
    if (null === $this->_checklistItem)
    {
      $this->_checklistItem = new Application_Model_ChecklistItem(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getChecklistItem()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setChecklistItemObject(Application_Model_ChecklistItem $checklistItem)
  {
    $this->_checklistItem = $checklistItem;
  }

  public function setStatus($id)
  {
    $this->_status = new Application_Model_TaskChecklistItemStatus($id);
    return $this;
  }
  // </editor-fold>
}