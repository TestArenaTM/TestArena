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
class Application_Model_Release extends Custom_Model_Standard_Abstract implements Custom_Interface_StarEndDate
{
  const PDF_REPORT = 1;
  const CSV_REPORT = 2;  

  protected $_map = array(
    'start_date'  => 'startDate',
    'end_date'    => 'endDate'    
  );
  
  private $_id          = null;
  private $_project     = null;
  private $_startDate   = null;
  private $_endDate     = null;
  private $_name        = null;
  private $_description = null;
  private $_active      = null;
  
  private $_phases = array();
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getProject()
  {
    return $this->_project;
  }

  public function getProjectId()
  {
    return $this->_project->getId();
  }

  public function getStartDate($asTime = false)
  {
    return $asTime ? strtotime($this->_startDate.' 00:00:00') : $this->_startDate;
  }

  public function getEndDate($asTime = false)
  {
    return $asTime ? strtotime($this->_endDate.' 23:59:59') : $this->_endDate; 
  }
  
  public function getName()
  {
    return $this->_name;
  }

  public function getDescription()
  {
    return $this->_description;
  }
  
  public function getPhases()
  {
    return $this->_phases;
  }
  
  public function getSinglePhase($key)
  {
    return $this->_phases[$key];
  }
  
  public function isPhases()
  {
    return count($this->getPhases()) > 0;
  }
  
  public function isActive()
  {
    return $this->_active;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = $id;
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

  public function setStartDate($date)
  {
    $this->_startDate = $date == '0000-00-00' ? null : $date;
    return $this;
  }

  public function setEndDate($date)
  {
    $this->_endDate = $date == '0000-00-00' ? null : $date;
    return $this;
  }

  public function setName($name)
  {
    $this->_name = $name;
  }

  public function setDescription($description)
  {
    $this->_description = $description;
  }
  
  public function setActive($active)
  {
    $this->_active = (bool)$active;
  }
  
  public function setPhases($phases)
  {
    if (!is_array($phases))
    {
      $phases = explode(',', $phases);
    }
    
    if (count($phases) > 0)
    {
      foreach($phases as $phase)
      {
        $this->setPhase($phase);
      }
    }
    
    return $this;
  }
  
  public function setPhase($phase)
  {
    if ($phase instanceof Application_Model_Phase)
    {
      $this->_phases[] = $phase;
    }
    elseif(is_array($phase) && count($phase) > 0)
    {
      $this->_phases[] = new Application_Model_Phase($phase);
    }
    elseif(is_numeric($phase))
    {
      $this->_phases[] = new Application_Model_Phase(array('id' => $phase));
    }
    
    return $this;
  }
  // </editor-fold>
}