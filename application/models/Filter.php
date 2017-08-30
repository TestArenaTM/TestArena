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
class Application_Model_Filter extends Custom_Model_Standard_Abstract
{
  private $_id      = null;
  private $_user    = null;
  private $_project = null;
  private $_group   = null;
  private $_data    = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getUser()
  {
    return $this->_user;
  }

  public function getProject()
  {
    return $this->_project;
  }

  public function getGroup()
  {
    return $this->_group;
  }

  public function getGroupId()
  {
    return $this->_group->getId();
  }

  public function getData($serialize = false)
  {
    return $serialize ? serialize($this->_data) : $this->_data;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = $id;
    return $this;
  }

  public function setUser($propertyName, $propertyValue)
  {
    if (null === $this->_user)
    {
      $this->_user = new Application_Model_User(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getUser()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setUserObject(Application_Model_User $user)
  {
    $this->_user = $user;
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

  public function setGroup($id)
  {
    $this->_group = new Application_Model_FilterGroup($id);
    return $this;
  }

  public function setData($data)
  {
    $this->_data = is_array($data) ? $data : unserialize($data);
    return $this;
  }
  // </editor-fold>  
  
  public function prepareRequest(Zend_Controller_Request_Abstract $request)
  {
    $params = $request->getParams();
    
    foreach ($this->_data as $key => $value)
    {
      if (!array_key_exists($key, $params))
      {
        $request->setParam($key, is_array($value) ? implode(',', $value) : $value);
      }
    }
  }
}