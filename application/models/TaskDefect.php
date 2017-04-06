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
class Application_Model_TaskDefect extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'defect_id'       => 'defectId',
    'bug_tracker_id'  => 'bugTrackerId'
  );
  
  private $_id            = null;
  private $_task          = null;
  private $_defect        = null;
  private $_bugTrackerId  = null;

  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getTask()
  {
    return $this->_task;
  }

  public function getDefect()
  {
    return $this->_defect;
  }

  public function getDefectId()
  {
    return $this->getDefect()->getId();
  }
  
  public function getBugTrackerId()
  {
    return $this->_bugTrackerId;
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
  
  public function setDefectId($defectId)
  {
    $this->setDefect('id', $defectId);
    return $this;
  }
  
  public function setDefect($propertyName, $propertyValue)
  {
    if (null === $this->_defect)
    {
      $this->_defect = new Application_Model_Defect(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getDefect()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setDefectJira($propertyName, $propertyValue)
  {
    if (null === $this->_defect)
    {
      $this->_defect = new Application_Model_DefectJira(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getDefect()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setDefectMantis($propertyName, $propertyValue)
  {
    if (null === $this->_defect)
    {
      $this->_defect = new Application_Model_DefectMantis(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getDefect()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setBugTrackerId($id)
  {
    $this->_bugTrackerId = $id;
    return $this;
  }
  // </editor-fold>
}