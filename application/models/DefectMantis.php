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
class Application_Model_DefectMantis extends Custom_Model_Standard_Abstract
{
  private $_id          = null;
  private $_bugTracker  = null;
  private $_no         = null;
  private $_summary     = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getBugTracker()
  {
    return $this->_bugTracker;
  }

  public function getNo($toView = false)
  {
    return $toView ? str_pad($this->_no, 7, '0', STR_PAD_LEFT) : $this->_no;
  }

  public function getSummary()
  {
    return $this->_summary;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }
  
  public function setBugTracker($propertyName, $propertyValue)
  {
    if (null === $this->_bugTracker)
    {
      $this->_bugTracker = new Application_Model_BugTrackerMantis(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getBugTracker()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }
  
  public function setNo($no)
  {
    $this->_no = $no;
    return $this;
  }

  public function setSummary($summary)
  {
    $this->_summary = $summary;
    return $this;
  }
  // </editor-fold>
}