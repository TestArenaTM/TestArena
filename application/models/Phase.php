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
class Application_Model_Phase extends Custom_Model_Standard_Abstract implements Custom_Interface_StarEndDate
{
  protected $_map = array(
    'start_date'  => 'startDate',
    'end_date'    => 'endDate'    
  );
  
  private $_id          = null;
  private $_release     = null;
  private $_startDate   = null;
  private $_endDate     = null;
  private $_name        = null;
  private $_description = null;
  
  private $_taskRuns = array();
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getRelease()
  {
    return $this->_release;
  }

  public function getReleaseId()
  {
    return $this->_release->getId();
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
  
  public function getTaskRuns()
  {
    return $this->_taskRuns;
  }
  
  public function getTaskRunIds()
  {
    $ids = array();
    
    foreach ($this->_taskRuns as $taskRun)
    {
      $ids[] = $taskRun->getId();
    }
    
    return $ids;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
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

  public function setReleaseObject(Application_Model_Release $release)
  {
    $this->_release = $release;
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
  
  public function setTaskRuns($taskRuns)
  {
    if (!is_array($taskRuns))
    {
      $taskRuns = explode(',', $taskRuns);
    }
    
    foreach ($taskRuns as $taskRun)
    {
      $this->setTaskRun($taskRun);
    }
  }
  
  public function setTaskRun($taskRun)
  {
    if ($taskRun instanceof Application_Model_TaskRun)
    {
      $this->_taskRuns[] = $taskRun;
    }
    elseif (is_array($taskRun) && count($taskRun) > 0)
    {
      $this->_taskRuns[] = new Application_Model_Task($taskRun);
    }
    elseif (is_numeric($taskRun))
    {
      $this->_taskRuns[] = new Application_Model_Task(array('id' => $taskRun));
    }
  }
  // </editor-fold>
  
  public function isTaskRuns()
  {
    return count($this->_taskRuns) > 0;
  }
}