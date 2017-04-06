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
class Application_Model_Test extends Custom_Model_Standard_Abstract implements Custom_Interface_Test, Custom_Interface_HistorySubject
{
  protected $_map = array(
    'ordinal_no'      => 'ordinalNo',
    'create_date'     => 'createDate',
    'family_id'       => 'familyId',
    'current_version' => 'currentVersion'
  );
  
  private $_id              = null;
  private $_ordinalNo       = null;
  private $_project         = null;
  private $_status          = null;
  private $_type            = null;
  private $_author          = null;
  private $_createDate      = null;
  private $_name            = null;
  private $_description     = null;
  private $_familyId        = null;
  private $_currentVersion  = true;
  
  private $_testRuns        = array();
  private $_newVersion      = false;
  private $_previous        = null;
  private $_next            = null;

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
    return $this->getProject()->getId();
  }

  public function getStatus()
  {
    return $this->_status;
  }

  public function getStatusId()
  {
    return $this->_status->getId();
  }

  public function getType()
  {
    return $this->_type;
  }

  public function getTypeId()
  {
    return $this->_type->getId();
  }

  public function getAuthor()
  {
    return $this->_author;
  }
  
  public function getAuthorId()
  {
    return $this->getAuthor()->getId();
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

  public function getName()
  {
    return $this->_name;
  }

  public function getDescription()
  {
    return $this->_description;
  }

  public function getFamilyId()
  {
    return $this->_familyId;
  }

  public function getCurrentVersion()
  {
    return $this->_currentVersion;
  }
  
  public function getTestRuns()
  {
    return $this->_testRuns;
  }
  
  public function getPrevious()
  {
    return $this->_previous;
  }
  
  public function getNext()
  {
    return $this->_next;
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
  
  public function setStatus($id)
  {
    $this->_status = new Application_Model_TestStatus($id);
    return $this;
  }
  
  public function setType($id)
  {
    $this->_type = new Application_Model_TestType($id);
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
  
  public function setFamilyId($familyId)
  {
    $this->_familyId = $familyId;
    return $this;
  }

  public function setCurrentVersion($currentVersion)
  {
    $this->_currentVersion = (bool)$currentVersion;
    return $this;
  }
  
  public function setTestRuns($testRuns)
  {
    if (!is_array($testRuns))
    {
      $testRuns = explode(',', $testRuns);
    }
    
    if (count($testRuns) > 0)
    {
      foreach($testRuns as $testRuns)
      {
        $this->setTestRun($testRuns);
      }
    }
    return $this;
  }
  
  public function setTestRun($testRun)
  {
    if ($testRun instanceof Application_Model_TestRun)
    {
      $this->_testRuns[] = $testRun;
    }
    elseif(is_array($testRun) && count($testRun) > 0)
    {
      $this->_testRuns[] = new Application_Model_TestRun($testRun);
    }
    elseif(is_numeric($testRun))
    {
      $this->_testRuns[] = new Application_Model_TestRun(array('id' => $testRun));
    }
    
    return $this;
  }
  
  public function setNewVersion($newVersion)
  {
    $this->_newVersion = (bool)$newVersion;
    return $this;
  }
  
  public function setPrevious(Application_Model_Test $test)
  {
    $this->_previous = $test;
    return $this;
  }
  
  public function setNext(Application_Model_Test $test)
  {
    $this->_next = $test;
    return $this;
  }
  // </editor-fold>
  
  public function isNewVersion()
  {
    return $this->_newVersion;
  }
  
  public function getIdForHistory()
  {
    return $this->getId();
  }
  
  public function getObjectNumber()
  {
    return $this->getProject()->getPrefix().'-'.$this->getOrdinalNo();
  }
}