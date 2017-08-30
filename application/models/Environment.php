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
class Application_Model_Environment extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
  );
  
  private $_id          = null;
  private $_project     = null;
  private $_name        = null;
  private $_description = null;
  
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

  public function getName()
  {
    return $this->_name;
  }

  public function getDescription()
  {
    return $this->_description;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
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

  public function setName($name)
  {
    $this->_name = $name;
  }

  public function setDescription($description)
  {
    $this->_description = $description;
  }
  // </editor-fold>
}