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
class Application_Model_Project extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'create_date'               => 'createDate',
    'open_status_color'         => 'openStatusColor',
    'in_progress_status_color'  => 'inProgressStatusColor'
  );
  
  private $_id                    = null;
  private $_prefix                = null;
  private $_status                = null;
  private $_createDate            = null;
  private $_name                  = null;
  private $_description           = null;
  private $_openStatusColor       = null;
  private $_inProgressStatusColor = null;
  
  private $_projectElements = array();  
  private $_releases = array();
  private $_bugTracker = null; 
  private $_resolutions = array();
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getPrefix()
  {
    return $this->_prefix;
  }

  public function getStatus()
  {
    return $this->_status;
  }

  public function getStatusId()
  {
    return $this->_status->getId();
  }
  
  public function getCreateDate()
  {
    return $this->_createDate;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function getDescription()
  {
    return $this->_description;
  }
  
  public function getOpenStatusColor()
  {
    return $this->_openStatusColor;
  }

  public function getInProgressStatusColor()
  {
    return $this->_inProgressStatusColor;
  }
  
  function getInternalDefects()
  {
    return $this->_internalDefects;
  }
  
  public function getProjectElements()
  {
    return $this->_projectElements;
  }
  
  public function isProjectElements()
  {
    return count($this->getProjectElements()) > 0;
  }
  
  public function getReleases()
  {
    return $this->_releases;
  }
  
  public function getBugTracker()
  {
    return $this->_bugTracker;
  }
  
  public function getResolutions()
  {
    return $this->_resolutions;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }

  public function setPrefix($prefix)
  {
    $this->_prefix = $prefix;
    return $this;
  }
  
  public function setStatus($id)
  {
    $this->_status = new Application_Model_ProjectStatus($id);
    return $this;
  }
  
  public function setCreateDate($createDate)
  {
    $this->_createDate = $createDate;
    return $this;
  }

  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }

  public function setDescription($description)
  {
    $this->_description = $description;
    return $this;
  }

  public function setOpenStatusColor($openStatusColor)
  {
    $this->_openStatusColor = $openStatusColor;
    return $this;
  }

  public function setInProgressStatusColor($inProgressStatusColor)
  {
    $this->_inProgressStatusColor = $inProgressStatusColor;
    return $this;
  }
  
  public function setProjectElements($projectElements)
  {
    if (!is_array($projectElements))
    {
      $projectElements = explode(',', $projectElements);
    }
    
    if (count($projectElements) > 0)
    {
      foreach($projectElements as $projectElement)
      {
        $this->setProjectElement($projectElement);
      }
    }
    
    return $this;
  }
  
  public function setProjectElement($projectElement)
  {
    if ($projectElement instanceof Application_Model_ProjectElement)
    {
      $this->_projectElements[] = $projectElement;
    }
    elseif(is_array($projectElement) && count($projectElement) > 0)
    {
      $this->_projectElements[] = new Application_Model_ProjectElement($projectElement);
    }
    elseif(is_numeric($projectElement))
    {
      $this->_projectElements[] = new Application_Model_ProjectElement(array('id' => $projectElement));
    }
    
    return $this;
  }
  
  public function setReleases($releases)
  {
    if (!is_array($releases))
    {
      $releases = explode(',', $releases);
    }
    
    if (count($releases) > 0)
    {
      foreach($releases as $release)
      {
        $this->setRelease($release);
      }
    }
    
    return $this;
  }
  
  public function setRelease($release)
  {
    if ($release instanceof Application_Model_Release)
    {
      $this->_releases[] = $release;
    }
    elseif(is_array($release) && count($release) > 0)
    {
      $this->_releases[] = new Application_Model_Release($release);
    }
    elseif(is_numeric($release))
    {
      $this->_releases[] = new Application_Model_Release(array('id' => $release));
    }
    
    return $this;
  }
  
  public function setBugTracker($propertyName, $propertyValue)
  {
    if (null === $this->_bugTracker)
    {
      $this->_bugTracker = new Application_Model_ProjectBugTracker(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getBugTracker()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setResolutions($resolutions)
  {
    $this->_resolutions = array();
    
    if (!is_array($resolutions))
    {
      $resolutions = explode(',', $resolutions);
    }
    
    if (count($resolutions) > 0)
    {
      foreach($resolutions as $resolution)
      {
        $this->setResolution($resolution);
      }
    }
    
    return $this;
  }
  
  public function setResolution($resolution)
  {
    if ($resolution instanceof Application_Model_Resolution)
    {
      $this->_resolutions[] = $resolution->setProjectObject($this);
    }
    elseif(is_array($resolution) && count($resolution) > 0)
    {
      $resolution = new Application_Model_Resolution($resolution);
      $this->_resolutions[] = $resolution->setProjectObject($this);
    }
    elseif(is_numeric($resolution))
    {
      $resolution = new Application_Model_Resolution(array('id' => $resolution));
      $this->_resolutions[] = $resolution->setProjectObject($this);
    }
    
    return $this;
  }
  // </editor-fold>

  public function isActive()
  {
    return $this->getStatusId() == Application_Model_ProjectStatus::ACTIVE;
  }

  public function isFinished()
  {
    return $this->getStatusId() == Application_Model_ProjectStatus::FINISHED;
  }

  public function isSuspended()
  {
    return $this->getStatusId() == Application_Model_ProjectStatus::SUSPENDED;
  }
  
  public function checkFinished()
  {
    if ($this->isFinished())
    {
      throw new Custom_404Exception();
    }
    
    return $this;
  }
  
  public function checkSuspended()
  {
    if ($this->isSuspended())
    {
      throw new Custom_404Exception();
    }
    
    return $this;
  }
  
  public function isInternalDefects()
  {
    return $this->getBugTracker()->getBugTrackerTypeId() == Application_Model_BugTrackerType::INTERNAL;
  }
}