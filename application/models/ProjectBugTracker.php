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
class Application_Model_ProjectBugTracker extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'bug_tracker_id'      => 'bugTrackerId',
    'bug_tracker_status'  => 'bugTrackerStatus',
    'bug_tracker_type'    => 'bugTrackerType'
  );
  
  private $_id                = null;
  private $_project           = null;
  private $_bugTrackerId      = null;
  private $_bugTrackerJira    = null;
  private $_bugTrackerMantis  = null;
  private $_name              = null;
  private $_bugTrackerType    = null;
  private $_bugTrackerStatus  = null;

  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getProject()
  {
    return $this->_project;
  }

  public function getBugTrackerId()
  {
    return $this->_bugTrackerId;
  }

  public function getBugTrackerJira()
  {
    if ($this->_bugTrackerJira === null && $this->getBugTrackerTypeId() == Application_Model_BugTrackerType::JIRA && $this->getBugTrackerId() !== null)
    {
      $this->setBugTrackerJira('id', $this->getBugTrackerId());
    }
    
    return $this->_bugTrackerJira;
  }

  public function getBugTrackerMantis()
  {
    if ($this->_bugTrackerMantis === null && $this->getBugTrackerTypeId() == Application_Model_BugTrackerType::MANTIS && $this->getBugTrackerId() !== null)
    {
      $this->setBugTrackerMantis('id', $this->getBugTrackerId());
    }
    
    return $this->_bugTrackerMantis;
  }

  public function getName()
  {
    return $this->_name;
  }
  
  public function getBugTrackerStatus()
  {
    return $this->_bugTrackerStatus;
  }

  public function getBugTrackerStatusId()
  {
    return $this->_bugTrackerStatus->getId();
  }
  
  public function getBugTrackerType()
  {
    return $this->_bugTrackerType;
  }

  public function getBugTrackerTypeId()
  {
    return $this->_bugTrackerType->getId();
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = $id;
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
  
  public function setBugTrackerId($bugTrackerId)
  {
    $this->_bugTrackerId = $bugTrackerId;
    return $this;
  }
  
  public function setBugTrackerJira($propertyName, $propertyValue)
  {
    if (null === $this->_bugTrackerJira)
    {
      $this->_bugTrackerJira = new Application_Model_BugTrackerJira(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getBugTrackerJira()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setBugTrackerJiraObject(Application_Model_BugTrackerJira $bugTrackerJira)
  {
    $this->_bugTrackerJira = $bugTrackerJira;
    return $this;
  }
  
  public function setBugTrackerMantis($propertyName, $propertyValue)
  {
    if (null === $this->_bugTrackerMantis)
    {
      $this->_bugTrackerMantis = new Application_Model_BugTrackerMantis(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getBugTrackerMantis()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setBugTrackerMantisObject(Application_Model_BugTrackerMantis $bugTrackerMantis)
  {
    $this->_bugTrackerMantis = $bugTrackerMantis;
    return $this;
  }
  
  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }
    
  public function setBugTrackerStatus($id)
  {
    $this->_bugTrackerStatus = new Application_Model_BugTrackerStatus($id);
    return $this;
  }
    
  public function setBugTrackerType($id)
  {
    $this->_bugTrackerType = new Application_Model_BugTrackerType($id);
    return $this;
  }
  // </editor-fold>
}