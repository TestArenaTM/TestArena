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
class Application_Model_Task extends Custom_Model_Standard_Abstract implements Custom_Interface_HistorySubject
{
  protected $_map = array(
    'ordinal_no'  => 'ordinalNo',
    'create_date' => 'createDate',
    'modify_date' => 'modifyDate',
    'due_date'    => 'dueDate'
  );
  
  private $_id          = null;
  private $_ordinalNo   = null;
  private $_project     = null;
  private $_release     = null;
  private $_assigner    = null;
  private $_assignee    = null;
  private $_createDate  = null;
  private $_modifyDate  = null;
  private $_dueDate     = null;
  private $_status      = null;
  private $_priority    = null;
  private $_title       = null;
  private $_description = null;
  private $_resolution  = null;
  private $_author      = null;
  
  private $_taskTests   = array();

  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getOrdinalNo()
  {
    return $this->_ordinalNo;
  }
  
  public function getProject()
  {
    return $this->_project;
  }
  
  public function getProjectId()
  {
    return $this->_project->getId();
  }

  public function getRelease()
  {
    return $this->_release;
  }

  public function getCreateDate($showOnlyDate = false)
  {
    if ($showOnlyDate)
    {
      $buf = explode(' ', $this->_createDate);
      return $buf[0];
    }
    
    return $this->_createDate;
  }
  
  public function getModifyDate($showOnlyDate = false)
  {
    if ($showOnlyDate)
    {
      $buf = explode(' ', $this->_modifyDate);
      return $buf[0];
    }
    
    return $this->_modifyDate;
  }
  
  public function getDueDate($showOnlyDate = false)
  {
    if ($showOnlyDate)
    {
      $buf = explode(' ', $this->_dueDate);
      return $buf[0];
    }
    
    return $this->_dueDate;
  }
  
  public function getAssigner()
  {
    return $this->_assigner;
  }
  
  public function getAssignerId()
  {
    return $this->getAssigner()->getId();
  }
  
  public function getAssignee()
  {
    return $this->_assignee;
  }
  
  public function getAssigneeId()
  {
    return $this->getAssignee()->getId();
  }
  
  public function getStatus()
  {
    return $this->_status;
  }

  public function getStatusId()
  {
    return $this->_status->getId();
  }
  
  public function getPriority()
  {
    return $this->_priority;
  }
  
  public function getPriorityId()
  {
    return $this->_priority->getId();
  }
  public function getTitle()
  {
    return $this->_title;
  }

  public function getDescription()
  {
    return $this->_description;
  }
  
  public function getResolution()
  {
    return $this->_resolution;
  }
  
  public function getResolutionId()
  {
    return $this->_resolution->getId();
  }
  
  public function getAuthor()
  {
    return $this->_author;
  }
  
  public function getAuthorId()
  {
    return $this->getAuthor()->getId();
  }
  
  public function getTaskTests()
  {
    return $this->_taskTests;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }
  
  public function setOrdinalNo($ordinalNo)
  {
    $this->_ordinalNo = $ordinalNo;
    return $this;
  }
  
  public function setProject($propertyName, $propertyValue)
  {
    if (null === $this->_project)
    {
      $this->_project = new Application_Model_Project(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getProject()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setProjectObject(Application_Model_Project $project)
  {
    $this->_project = $project;
    return $this;
  }
  
  public function setRelease($propertyName, $propertyValue)
  {
    if (null === $this->_release)
    {
      $this->_release = new Application_Model_Release(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getRelease()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setAssigner($propertyName, $propertyValue)
  {
    if (null === $this->_assigner)
    {
      $this->_assigner = new Application_Model_User(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getAssigner()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setAssignerObject(Application_Model_User $user)
  {
    $this->_assigner = $user;    
    return $this;
  }

  public function setAssignee($propertyName, $propertyValue)
  {
    if (null === $this->_assignee)
    {
      $this->_assignee = new Application_Model_User(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getAssignee()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setAssigneeObject(Application_Model_User $user)
  {
    $this->_assignee = $user;    
    return $this;
  }

  public function setCreateDate($createDate)
  {
    $this->_createDate = $createDate;
    return $this;
  }
  
  public function setModifyDate($modifyDate)
  {
    $this->_modifyDate = $modifyDate;
    return $this;
  }
  
  public function setDueDate($dueDate)
  {
    $this->_dueDate = $dueDate;
    return $this;
  }
  
  public function setStatus($id)
  {
    $this->_status = new Application_Model_TaskStatus($id);
    return $this;
  }

  public function setPriority($id)
  {
    $this->_priority = new Application_Model_TaskPriority($id);
    return $this;
  }

  public function setTitle($title)
  {
    $this->_title = $title;
    return $this;
  }

  public function setDescription($description)
  {
    $this->_description = $description;
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
  
  public function setAuthor($propertyName, $propertyValue)
  {
    if (null === $this->_author)
    {
      $this->_author = new Application_Model_User(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getAuthor()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setAuthorObject(Application_Model_User $user)
  {
    $this->_author = $user;    
    return $this;
  }
  // </editor-fold>
  
  public function getIdForHistory()
  {
    return $this->getId();
  }
  
  public function getObjectNumber()
  {
    return $this->getProject()->getPrefix().'-'.$this->getOrdinalNo();
  }
  
  public function addTaskTest(Application_Model_TaskTest $taskTest)
  {
    $this->_taskTests[$taskTest->getTest()->getId()] = $taskTest;
  }
}