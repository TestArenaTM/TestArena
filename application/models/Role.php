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
class Application_Model_Role extends Custom_Model_Standard_Abstract
{
  const TYPE_1 = 'CUSTOM';
  const TYPE_2 = 'LEADER';
  const TYPE_3 = 'TESTER';
  const TYPE_4 = 'PROGRAMMER';
  const TYPE_5 = 'GUEST';
  
  public static $defaultRoleTypes = array(
    self::TYPE_1 => self::TYPE_1,
    self::TYPE_2 => self::TYPE_2,
    self::TYPE_3 => self::TYPE_3,
    self::TYPE_4 => self::TYPE_4,
    self::TYPE_5 => self::TYPE_5
  );
  
  private $_id      = null;
  private $_project = null;
  private $_name    = null;
  
  private $_users        = array();
  private $_roleSettings = array();
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getProject()
  {
    return $this->_project;
  }
  
  public function getName()
  {
    return $this->_name;
  }
  
  public function getUsers()
  {
    return $this->_users;
  }
  
  public function getUsersAsString()
  {
    $result = '';
    $i = 0;
    
    foreach ($this->_users as $user)
    {
      if ($i++ > 0)
      {
        $result .= ',';
      }
      
      $result .= $user->getEmail();
    }
    
    return $result;
  }
  
  public function getRoleSettings()
  {
    return $this->_roleSettings;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
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
  
  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }
  
  public function setUsers($users)
  {
    if (!is_array($users))
    {
      $users = explode(',', $users);
    }
    
    if (count($users) > 0)
    {
      foreach($users as $user)
      {
        $this->setUser($user);
      }
    }
    return $this;
  }
  
  public function setUser($user)
  {
    if ($user instanceof Application_Model_User)
    {
      $this->_users[] = $user;
    }
    elseif(is_array($user) && count($user) > 0)
    {
      $this->_users[] = new Application_Model_User($user);
    }
    elseif(is_numeric($user))
    {
      $this->_users[] = new Application_Model_User(array('id' => $user));
    }
    
    return $this;
  }
  
  public function setRoleSettings(array $roleSettings)
  {
    $this->_roleSettings = array();
    
    if (count($roleSettings) > 0)
    {
      foreach($roleSettings as $setting)
      {
        $this->setRoleSetting($setting);
      }
    }
    
    return $this;
  }
  
  public function setRoleSetting($roleSetting)
  {
    if ($roleSetting instanceof Application_Model_RoleSetting)
    {
      $this->_roleSettings[] = $roleSetting;
    }
    elseif(is_array($roleSetting) && count($roleSetting) > 0)
    {
      $this->_roleSettings[] = new Application_Model_RoleSetting($roleSetting);
    }
    elseif(is_numeric($roleSetting) && $roleSetting > 0)
    {
      $this->_roleSettings[] = new Application_Model_RoleSetting(array('roleAction' => $roleSetting));
    }
    
    return $this;
  }
  
  public function getDefaultRoleTypesSetting()
  {
    $result   = array();
    
    foreach(self::$defaultRoleTypes as $type)
    {
      $result[$type] = $this->_getDefaultRoleSettingsByType($type);
    }
    
    return $result;
  }
  
  private function _getDefaultRoleSettingsByType($type)
  {
    $filePath = _APPLICATION_CONFIG_PATH.'/roles/'.strtolower($type).'.php';
    
    if (file_exists($filePath))
    {
      return include_once $filePath;
    }
    
    throw new Exception('Default role not defined!');
  }
  // </editor-fold>
  
  public function clearUsers()
  {
    $this->_users = array();
  }
}