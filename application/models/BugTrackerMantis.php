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
class Application_Model_BugTrackerMantis extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'user_name'     => 'userName',
    'project_id'    => 'projectId',
    'project_name'  => 'projectName'
  );
  
  private $_id          = null;
  private $_userName    = null;
  private $_password    = null;
  private $_projectId   = null;
  private $_projectName = null;
  private $_url         = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getUserName()
  {
    return $this->_userName;
  }

  public function getPassword()
  {
    return $this->_password;
  }
  
  public function getProjectId()
  {
    return $this->_projectId;
  }
  
  public function getProjectName()
  {
    return $this->_projectName;
  }

  public function getUrl()
  {
    return $this->_url;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }
  
  public function setUserName($userName)
  {
    $this->_userName = $userName;
    return $this;
  }
  
  public function setProjectName($projectName)
  {
    $this->_projectName = $projectName;
    return $this;
  }
  
  public function setPassword($password)
  {
    $this->_password = $password;
    return $this;
  }
  
  public function setProjectId($id)
  {
    $this->_projectId = $id;
    return $this;
  }

  public function setUrl($url)
  {
    $this->_url = $url;
    return $this;
  }
  // </editor-fold>
}